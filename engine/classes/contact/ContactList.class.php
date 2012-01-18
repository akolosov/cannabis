<?php
	class ContactList extends Core {

		public $_contactlist = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
			if (isNotNULL($owner)) {
				parent::__construct($owner, $id, $owner->getConnection());

				if (($id > 0) or (isNotNULL($options['data']))) {
					if (is_null($options['data'])) {
						$contactlist = $this->getConnection()->execute('select * from contacts_list where id = '.$this->_id)->fetch();
					} else {
						$contactlist = $options['data'];
					}
					foreach ($contactlist as $key => $data) {
						$this->_contactlist[$key] = $data;
					}
					$this->reinitContactList();
				}

				$this->_contactlist['[model]'] = 'CsContact';

			}
		}

		static function create($options = array()) {
			$contactlist = new ContactList($options['owner']);
			$contactlist->setProperty('name', $options['name']);
			$contactlist->setProperty('description', $options['description']);
			$contactlist->setProperty('owner_id', USER_CODE);
			$contactlist->setProperty('is_public', $options['is_public']);
			$contactlist->setProperty('[permissions]', new Collection($this));
			$contactlist->setProperty('[contacts]', new Collection($this));
			return $contactlist;
		}

		function createAccount($options = array()) {
			$options['owner'] = $this;
			$options['contact_id'] = $this->getProperty('id');
			if ($this->_contactlist['[contacts]']->elementExists($options['account_id'])) {
				return $this->_contactlist['[contacts]']->getElement($options['account_id']);
			} else {
				$contact = ContactListAccount::create($options);
				$this->_contactlist['[contacts]']->setElement($options['account_id'], $contact);
				return $contact;
			}
		}

		function createPermission($options = array()) {
			$options['owner'] = $this;
			$options['contact_id'] = $this->getProperty('id');
			if ($this->_contactlist['[permissions]']->elementExists($options['account_id'])) {
				return $this->_contactlist['[permissions]']->getElement($options['account_id']);
			} else {
				$permission = ContactListPermission::create($options);
				$this->_contactlist['[permissions]']->setElement($options['account_id'], $permission);
				return $permission;
			}
		}

		function addPermission($permission = NULL) {
			if ((isNotNULL($permission)) and (is_a($reciever, 'ContactListPermission'))) {
				$this->_contactlist['[permissions]']->setElement($permission->getProperty('account_id'), $permission);
			}
		}

		function addAccount($account = NULL) {
			if ((isNotNULL($account)) and (is_a($account, 'ContactListAccount'))) {
				$this->_contactlist['[contacts]']->setElement($account->getProperty('account_id'), $account);
			}
		}

		function reinitContactList() {
			$this->initContacts();
			$this->initPermissions();
		}

		function isPublic() {
			return $this->getProperty('is_public');
		}

		function initPermissions() {
			$this->_contactlist['[permissions]'] = new Collection($this);
			$permissions = $this->getConnection()->execute('select * from contact_permissions_list where contact_id = '.$this->_id)->fetchAll();
			foreach ($permissions as $permission) {
				$this->_contactlist['[permissions]']->setElement($permission['account_id'], new ContactListPermission($this, $permission['id'], array('data' => $permission)));
			}
		}

		function initContacts() {
			$this->_contactlist['[contacts]'] = new Collection($this);
			$contacts = $this->getConnection()->execute('select * from contact_accounts_list where contact_id = '.$this->_id)->fetchAll();
			foreach ($contacts as $contact) {
				$this->_contactlist['[contacts]']->setElement($contact['account_id'], new ContactListAccount($this, $contact['id'], array('data' => $contact)));
			}
		}

		function copyPermissionsFrom($parent = NULL) {
			if ((isNotNULL($parent)) and (is_a($parent, 'ContactList'))) {
				$this->setPermissions($parent->getPermissions());
			}
		}

		function setPermission($account = NULL, $permission = NULL) {
			if (isNotNULL($account)) {
				if (isNULL($permission)) {
					$permission = new FilePermission($this);
					$permission->setProperty('file_id', $this->getProperty('id'));
					$permission->setProperty('permission_id', Constants::PERMISSION_READ_ONLY);
					$permission->setProperty('account_id', $account);
				}
				$this->getProperty('[permissions]')->setElement($account, $permission);
			}
		}

		function setPermissions($permissions = array()) {
			if (isNotNULL($permissions)) {
				foreach ($permissions as $key => $data) {
					$this->setPermission($key, $data);
				}
			}
		}

		function getPermissionValue($account = NULL) {
			if ($this->getProperty('[permissions]')->elementExists($account)) {
				return $this->getProperty('[permissions]')->getElement($account)->getPermission();
			} else {
				return Constants::PERMISSION_NO_ACCESS;
			}
		}

		function getPermissions() {
			return $this->getProperty('[permissions]')->getElements();
		}

		function getPermissionValue($account = NULL) {
			
			return $this->getProperty('[permissions]')->getElement($account)->getPermission();
		}

		function getAccount($account = NULL) {
			return $this->getProperty('[contacts]')->getElement($account);
		}

		function getAccounts() {
			return $this->getProperty('[contacts]')->getElements();
		}

		function getAccountProperty($account = NULL, $propertyname = NULL) {
			if ((isNotNULL($account)) and (isNotNULL($propertyname)) and ($this->getProperty('[contacts]')->elementExists($account))) {
				return $this->getProperty('[contacts]')->getElement($account)->getAccountProperty($propertyname);
			} else {
				return NULL;
			}
		}

		function getAccountsProperty($propertyname = NULL) {
			$result = array();
			if (isNotNULL($propertyname)) {
				foreach ($this->getAccounts() as $account) {
					if (isNotNULL($account->getAccountProperty($propertyname))) {
						$result[$account->getProperty('account_id')] = $account->getAccountProperty($propertyname);
					}
				}
			}
			return $result;
		}

		function getProperty($name) {
			return $this->_contactlist[$name];
		}

		function setProperty($name, $value) {
			$this->_contactlist[$name] = $value;
		}

		private function savePermissions() {
			foreach ($this->getPermissions() as $permission) {
				$permission->save($this->getProperty('id'));
			}
		}

		private function saveAccounts() {
			foreach ($this->getAccounts() as $account) {
				$account->save($this->getProperty('id'));
			}
		}

		function deleteAccount($account = NULL) {
			if ((isNotNULL($account)) and ($this->getProperty('[contacts]')->elementExists($account))) {
				$this->getProperty('[contacts]')->getElement($account)->delete();
			}
		}

		function undeleteAccount($account = NULL) {
			if ((isNotNULL($account)) and ($this->getProperty('[contacts]')->elementExists($account))) {
				$this->getProperty('[contacts]')->getElement($account)->undelete();
			}
		}

		function eraseAccount($account = NULL) {
			if ((isNotNULL($account)) and ($this->getProperty('[contacts]')->elementExists($account))) {
				$this->getProperty('[contacts]')->getElement($account)->erase();
			}
		}

		function erasePermission($account = NULL) {
			if ((isNotNULL($account)) and ($this->getProperty('[permissions]')->elementExists($account))) {
				$this->getProperty('[permissions]')->getElement($account)->erase();
			}
		}

		function isAccountDeleted($account = NULL) {
			if ((isNotNULL($account)) and ($this->getProperty('[contacts]')->elementExists($account))) {
				return $this->getProperty('[contacts]')->getElement($account)->isDeleted();
			} else {
				return false;
			}
		}

		function permissionExists($account = NULL) {
			return $this->getProperty('[permissions]')->elementExists($account);
		}

		function accountExists($account = NULL) {
			return $this->getProperty('[contacts]')->elementExists($account);
		}

		function save() {
			logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_contactlist['[model]']);

			if (isNULL($this->getProperty('owner_id'))) {
				$this->setProperty('owner_id', USER_CODE);
			}
			$result = $this->saveData($this->_contactlist['[model]'], $this->_contactlist);
			$this->setProperty('id', $result);
			$this->saveAccounts();
			$this->savePermissions();
			return $result;
		}
	}
?>