<?php
	class FilePermission extends Core {

		public $_filepermission = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {

			if (isNotNULL($owner)) {
				parent::__construct($owner, $id, $owner->getConnection());

				if (($id > 0) or (isNotNULL($options['data']))) {
					if (is_null($options['data'])) {
						$filepermission = $this->getConnection()->execute('select * from file_permissions_list where id = '.$this->_id)->fetch();
					} else {
						$filepermission = $options['data'];
					}
					foreach ($filepermission as $key => $data) {
						$this->_filepermission[$key] = $data;
					}
				}

			}

			$this->_filepermission['[model]'] = 'CsFilePermission';
		}

		static function create($options = array()) {
			$permission = new FilePermission($options['owner']);
			$permission->setProperty('file_id', (isNotNULL($options['owner'])?$options['owner']->getProperty('id'):$options['file_id']));
			$permission->setProperty('account_id', $options['account_id']);
			$permission->setProperty('permission_id', $options['permission_id']);
			return $permission;
		}

		function getProperty($name) {
			return $this->_filepermission[$name];
		}

		function getPermission() {
			return $this->getProperty('permission_id');
		}

		function setProperty($name, $value) {
			$this->_filepermission[$name] = $value;
		}

		function save($file_id = 0) {
			logRuntime('['.get_class($this).'.save->'.$this->getProperty('permissionname').'] data saved to '.$this->_filepermission['[model]']);
			if ($file_id > 0) {
				$this->setProperty('file_id', $file_id);
			}
			return $this->saveData($this->_filepermission['[model]'], $this->_filepermission);
		}

	}
?>