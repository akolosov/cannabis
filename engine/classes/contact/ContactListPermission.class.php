<?php
	class ContactListPermission extends Core {

		public $_contactlistpermission = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {

			if (isNotNULL($owner)) {
				parent::__construct($owner, $id, $owner->getConnection());

				if (($id > 0) or (isNotNULL($options['data']))) {
					if (is_null($options['data'])) {
						$contactlistpermission = $this->getConnection()->execute('select * from contact_permissions_list where id = '.$this->_id)->fetch();
					} else {
						$contactlistpermission = $options['data'];
					}
					foreach ($contactlistpermission as $key => $data) {
						$this->_contactlistpermission[$key] = $data;
					}
				}

			}

			$this->_contactlistpermission['[model]'] = 'CsContactPermission';
		}

		static function create($options = array()) {
			$permission = new ContactListPermission($options['owner']);
			$permission->setProperty('contact_id', (isNotNULL($options['owner'])?$options['owner']->getProperty('id'):$options['contact_id']));
			$permission->setProperty('account_id', $options['account_id']);
			$permission->setProperty('permission_id', $options['permission_id']);
			return $permission;
		}

		function getPermission() {
			return $this->getProperty('permission_id');
		}

		function getProperty($name) {
			return $this->_contactlistpermission[$name];
		}

		function setProperty($name, $value) {
			$this->_contactlistpermission[$name] = $value;
		}

		function save($contact_id = 0) {
			logRuntime('['.get_class($this).'.save->'.$this->getProperty('permissionname').'] data saved to '.$this->_contactlistpermission['[model]']);
			if ($contact_id > 0) {
				$this->setProperty('contact_id', $contact_id);
			}
			return $this->saveData($this->_contactlistpermission['[model]'], $this->_contactlistpermission);
		}
	}
?>