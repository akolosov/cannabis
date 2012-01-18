<?php
	class FileManager extends Core { 

		public $_filemanager = array();

		function __construct($owner = NULL, $id = 0) {
			if (isNotNULL($owner)) {
				if (isNULL($id)) {
					$id = USER_CODE;
				}
				parent::__construct($owner, $id, $owner->getConnection());

				$this->reinitFileManager();
			}
		}

		function reinitFileManager() {
			$this->initOwnedFiles();
			$this->initDelegatedFiles();
		}

		function initOwnedFiles() {
			$this->_filemanager['[owned]'] = new Collection($this);

			$ownedfiles = $this->getConnection()->execute('select * from files_tree where owner_id = '.$this->_id)->fetchAll();
			foreach ($ownedfiles as $ownedfile) {
				$this->_filemanager['[owned]']->setElement($ownedfile['id'], new File($this, $ownedfile['id'], array('data' => $ownedfile)));
			}
		}

		function getOwnedFile($file_id = 0) {
			return $this->_filemanager['[owned]']->getElement($file_id);
		}

		function fileExists($file_id) {
			if ($this->_filemanager['[owned]']->elementExists($file_id)) {
				return true;
			} elseif ($this->_filemanager['[delegated]']->elementExists($file_id)) {
				return true;
			} else {
				return false;
			}
		}

		function getOwnedFiles() {
			return $this->_filemanager['[owned]']->getElements();
		}

		function getDelegatedFile($file_id = 0) {
			return $this->_filemanager['[delegated]']->getElement($file_id);
		}

		function getDelegatedFiles() {
			return $this->_filemanager['[delegated]']->getElements();
		}

		function getFile($file_id = 0) {
			if ($this->_filemanager['[owned]']->elementExists($file_id)) {
				return $this->_filemanager['[owned]']->getElement($file_id);
			} elseif ($this->_filemanager['[delegated]']->elementExists($file_id)) {
				return $this->_filemanager['[delegated]']->getElement($file_id);
			} else {
				return NULL;
			}
		}

		function isDeleted($file_id = 0) {
			if ($this->_filemanager['[owned]']->elementExists($file_id)) {
				return $this->_filemanager['[owned]']->getElement($file_id)->isDeleted();
			} elseif ($this->_filemanager['[delegated]']->elementExists($file_id)) {
				return $this->_filemanager['[delegated]']->getElement($file_id)->isDeleted();
			} else {
				return false;
			}
		}

		function isFolder($file_id = 0) {
			if ($this->_filemanager['[owned]']->elementExists($file_id)) {
				return $this->_filemanager['[owned]']->getElement($file_id)->isFolder();
			} elseif ($this->_filemanager['[delegated]']->elementExists($file_id)) {
				return $this->_filemanager['[delegated]']->getElement($file_id)->isFolder();
			} else {
				return false;
			}
		}

		function initDelegatedFiles() {
			$this->_filemanager['[delegated]'] = new Collection($this);

			$delegatedfiles = $this->getConnection()->execute('select * from files_tree where is_deleted = false and id in (select file_id from cs_file_permission where account_id = '.$this->_id.')')->fetchAll();
			foreach ($delegatedfiles as $delegatedfile) {
				$this->_filemanager['[delegated]']->setElement($delegatedfile['id'], new File($this, $delegatedfile['id'], array('data' => $delegatedfile)));
			}
		}

		function createFile($options = array()) {
			$options['owner'] = $this;
			$file = File::create($options);
			$this->_filemanager['[owned]']->setElement((($file->getProperty('id') > 0)?$file->getProperty('id'):mt_rand()), $file);
			return $file;
		}

		function deleteFile($file_id = 0) {
			if ($file_id > 0) {
				if ($this->_filemanager['[owned]']->elementExists($file_id)) {
					$this->_filemanager['[owned]']->getElement($file_id)->delete();
				} elseif (($this->_filemanager['[delegated]']->elementExists($file_id)) and
					($this->_filemanager['[delegated]']->getElement($file_id)->getPermissions()->elementExists(USER_CODE)) and 
					(($this->_filemanager['[delegated]']->getElement($file_id)->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
					($this->_filemanager['[delegated]']->getElement($file_id)->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS))
				) {
					$this->_filemanager['[delegated]']->getElement($file_id)->delete();
				}
			}
		}

		function saveFile($file_id = 0) {
			if ($file_id > 0) {
				if ($this->_filemanager['[owned]']->elementExists($file_id)) {
					$this->_filemanager['[owned]']->getElement($file_id)->save();
				} elseif (($this->_filemanager['[delegated]']->elementExists($file_id)) and
					($this->_filemanager['[delegated]']->getElement($file_id)->getPermissions()->elementExists(USER_CODE)) and 
					(($this->_filemanager['[delegated]']->getElement($file_id)->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE) or
					($this->_filemanager['[delegated]']->getElement($file_id)->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
					($this->_filemanager['[delegated]']->getElement($file_id)->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS))
				) {
					$this->_filemanager['[delegated]']->getElement($file_id)->save();
				}
			}
		}

		function undeleteFile($file_id = 0) {
			if ($file_id > 0) {
				if ($this->_filemanager['[owned]']->elementExists($file_id)) {
					$this->_filemanager['[owned]']->getElement($file_id)->undelete();
				} elseif (($this->_filemanager['[delegated]']->elementExists($file_id)) and
					($this->_filemanager['[delegated]']->getElement($file_id)->getPermissions()->elementExists(USER_CODE)) and 
					(($this->_filemanager['[delegated]']->getElement($file_id)->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
					($this->_filemanager['[delegated]']->getElement($file_id)->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS))
				) {
					$this->_filemanager['[delegated]']->getElement($file_id)->undelete();
				}
			}
		}

		function eraseFile($file_id = 0) {
			if ($file_id > 0) {
				if ($this->_filemanager['[owned]']->elementExists($file_id)) {
					$this->_filemanager['[owned]']->getElement($file_id)->erase();
					$this->initOwnedFiles();
				}
			}
		}

	}
?>