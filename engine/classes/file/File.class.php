<?php
	class File extends Core {

		public $_file = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {

			if (isNotNULL($owner)) {
				parent::__construct($owner, $id, $owner->getConnection());

				if (($id > 0) or (isNotNULL($options['data']))) {
					if (is_null($options['data'])) {
						$file = $this->getConnection()->execute('select * from files_tree where id = '.$this->_id)->fetch();
					} else {
						$file = $options['data'];
					}
					foreach ($file as $key => $data) {
						$this->_file[$key] = $data;
					}
	
					$this->initPermissions();
				}
			}

			$this->_file['[model]'] = 'CsFile';
		}

		static function create($options = array()) {
			$file = new File($options['owner']);
			$file->setProperty('name', $options['name']);
			$file->setProperty('description', $options['description']);
			$file->setProperty('parent_id', $options['parent_id']);
			$file->setProperty('owner_id', USER_CODE);
			$file->setProperty('ownername', USER_NAME);
			$file->setProperty('created_at', strftime("%Y-%m-%d %H:%M:%S", time()));
			$file->setProperty('updated_by', USER_CODE);
			$file->setProperty('updatername', USER_NAME);
			$file->setProperty('updated_at', strftime("%Y-%m-%d %H:%M:%S", time()));
			$file->setProperty('blob', $options['blob']);
			$file->setProperty('mime', $options['mime']);
			$file->setProperty('is_folder', $options['is_folder']);
			$file->_file['[permissions]'] = new Collection($file);
			$file->setPermissions($options['permissions']);
			return $file;
		}

		function createPermission($options = array()) {
			$options['owner'] = $this;
			$options['file_id'] = $this->getProperty('id');
			if ($this->_file['[permissions]']->elementExists($options['account_id'])) {
				$this->_file['[permissions]']->getElement($options['account_id'])->setProperty('permission_id', $options['permission_id']);
				return $this->_file['[permissions]']->getElement($options['account_id']);
			} else {
				$permission = FilePermission::create($options);
				$this->_file['[permissions]']->setElement($options['account_id'], $permission);
				return $permission;
			}
		}

		function addPermission($permission = NULL) {
			if ((isNotNULL($permission)) and (is_a($permission, 'FilePermission'))) {
				$permission->setProperty('file_id', $this->getProperty('id'));
				$this->_file['[permissions]']->setElement($permission->getProperty('account_id'), $permission);
			}
		}

		function isFolder() {
			return $this->getProperty('is_folder');
		}

		function initPermissions() {
			$this->_file['[permissions]'] = new Collection($this);
			$permissions = $this->getConnection()->execute('select * from file_permissions_list where file_id = '.$this->getProperty('id'))->fetchAll();
			foreach ($permissions as $permission) {
				$this->_file['[permissions]']->setElement($permission['account_id'], new FilePermission($this, $permission['id'], array('data' => $permission)));
			}
		}

		function copyPermissionsFrom($parent = NULL) {
			if ((isNotNULL($parent)) and (is_a($parent, 'File'))) {
				$this->setPermissions($parent->getPermissions(), true);
			}
		}

		function setPermission($account = NULL, $permission = NULL, $copy = false) {
			if (isNotNULL($account)) {
				if (isNULL($permission)) {
					$permission = new FilePermission($this);
					$permission->setProperty('file_id', $this->getProperty('id'));
					$permission->setProperty('permission_id', Constants::PERMISSION_READ_ONLY);
					$permission->setProperty('account_id', $account);
				}
				if ($copy) {
					$permission->setProperty('id', NULL);
					$permission->_id  = NULL;
				}
				$this->_file['[permissions]']->setElement($account, $permission);
			}
		}

		function setPermissions($permissions = array(), $copy = false) {
			if (isNotNULL($permissions)) {
				foreach ($permissions as $key => $data) {
					if ($copy) {
						$data->setProperty('id', NULL);
						$data->_id	= NULL;
					}
					$this->setPermission($key, $data, $copy);
				}
			}
		}

		function getPermission($account = NULL) {
			return $this->_file['[permissions]']->getElement($account);
		}

		function getPermissionValue($account = NULL) {
			return $this->_file['[permissions]']->getElement($account)->getPermission();
		}

		function getPermissions() {
			return $this->_file['[permissions]']->getElements();
		}

		function getProperty($name) {
			return $this->_file[$name];
		}

		function setProperty($name, $value) {
			$this->_file[$name] = $value;
		}

		private function savePermissions() {
			foreach ($this->getPermissions() as $permission) {
				$permission->save($this->getProperty('id'));
			}
		}

		private function saveBlob() {
			if (isNULL($this->getProperty('blob_id'))) {
				$blob = FileBlob::getBlob($this, $this->getProperty('id'));
			} else {
				$blob = new FileBlob($this, $this->getProperty('blob_id'));
			}
			$blob->setProperty('file_id', $this->getProperty('id'));
			$blob->setProperty('blob', $this->getProperty('blob'));
			$blob->save();
		}

		function clearAllPermissions() {
			foreach ($this->getPermissions() as $permission) {
				$permission->erase();
			}
			$this->_file['[permissions]'] = new Collection($this);
		}

		function clearPermission($permission_id = NULL) {
			if ((isNotNULL($permission_id)) and ($this->_file['[permissions]']->elementExists($permission_id))) {
				$this->_file['[permissions]']->getElement($permission_id)->erase();
				$this->_file['[permissions]']->setElement($permission_id, NULL);
			}
		}

		function permissionExists($permission_id = NULL) {
			return $this->_file['[permissions]']->elementExists($permission_id);
		}

		function delete() {
			$this->setProperty('updater_by', USER_CODE);
			$this->setProperty('updatername', USER_NAME);
			$this->setProperty('updated_at', strftime("%Y-%m-%d %H:%M:%S", time()));
			parent::delete();
		}

		function undelete() {
			$this->setProperty('updater_by', USER_CODE);
			$this->setProperty('updatername', USER_NAME);
			$this->setProperty('updated_at', strftime("%Y-%m-%d %H:%M:%S", time()));
			parent::undelete();
		}

		function save() {
			logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_file['[model]']);

			if (isNotNULL($this->getProperty('created_at'))) {
				$this->setProperty('updated_by', USER_CODE);
				$this->setProperty('updated_at', strftime("%Y-%m-%d %H:%M:%S", time()));
			} else {
				$this->setProperty('owner_id', USER_CODE);
				$this->setProperty('created_at', strftime("%Y-%m-%d %H:%M:%S", time()));
			}
			$result = $this->saveData($this->_file['[model]'], $this->_file);
			$this->setProperty('id', $result);
			$this->saveBlob();
			$this->savePermissions();
			return $result;
		}

	}
?>