<?php
	class ContactListManager extends Core {

		public $_contactlistmanager = array();

		function __construct($owner = NULL, $id = 0) {
			if (isNotNULL($owner)) {
				if (isNULL($id)) {
					$id = USER_CODE;
				}

				parent::__construct($owner, $id, $owner->getConnection());

				$this->reinitContactListManager();
			}
		}

		function reinitContactListManager() {
			$this->initOwnedContactLists();
			$this->initDelegatedContactLists();
			$this->initPublicContactLists();
		}

		function initOwnedContactLists() {
			$this->_contactlistmanager['[owned]'] = new Collection($this);

			$ownedcontactlists = $this->getConnection()->execute('select * from contacts_list where owner_id = '.$this->_id)->fetchAll();
			foreach ($ownedcontactlists as $ownedcontactlist) {
				$this->_contactlistmanager['[owned]']->setElement($ownedcontactlist['id'], new ContactList($this, $ownedcontactlist['id'], array('data' => $ownedcontactlist)));
			}
		}

		function getOwnedContactList($contactlist_id = NULL) {
			return $this->_contactlistmanager['[owned]']->getElement($contactlist_id);
		}

		function getOwnedContactLists() {
//			return $this->_contactlistmanager['[owned]']->getElements();
		}

		function getPublicContactLists() {
//			return $this->_contactlistmanager['[public]']->getElements();
		}

		function getPublicContactList($contactlist_id = NULL) {
			return $this->_contactlistmanager['[public]']->getElement($contactlist_id);
		}

		function getDelegatedContactList($contactlist_id = NULL) {
			return $this->_contactlistmanager['[delegated]']->getElement($contactlist_id);
		}

		function getDelegatedContactLists() {
//			return $this->_contactlistmanager['[delegated]']->getElements();
		}

		function getContactList($contactlist_id = NULL) {
			if ($this->_contactlistmanager['[owned]']->elementExists($contactlist_id)) {
				return $this->_contactlistmanager['[owned]']->getElement($contactlist_id);
			} elseif ($this->_contactlistmanager['[delegated]']->elementExists($contactlist_id)) {
				return $this->_contactlistmanager['[delegated]']->getElement($contactlist_id);
			} elseif ($this->_contactlistmanager['[public]']->elementExists($contactlist_id)) {
				return $this->_contactlistmanager['[public]']->getElement($contactlist_id);
			} else {
				return NULL;
			}
		}

		function initDelegatedContactLists() {
			$this->_contactlistmanager['[delegated]'] = new Collection($this);

			$delegatedcontactlists = $this->getConnection()->execute('select * from contacts_list where is_public = false and is_deleted = false and id in (select contact_id from cs_contact_permission where account_id = '.$this->_id.')')->fetchAll();
			foreach ($delegatedcontactlists as $delegatedcontactlist) {
				$this->_contactlistmanager['[delegated]']->setElement($delegatedcontactlist['id'], new ContactList($this, $delegatedcontactlist['id'], array('data' => $delegatedcontactlist)));
			}
		}

		function initPublicContactLists() {
			$this->_contactlistmanager['[public]'] = new Collection($this);

			$publiccontactlists = $this->getConnection()->execute('select * from contacts_list where is_public = true and is_deleted = false and owner_id <> '.$this->_id)->fetchAll();
			foreach ($publiccontactlists as $publiccontactlist) {
				$this->_contactlistmanager['[public]']->setElement($publiccontactlist['id'], new ContactList($this, $publiccontactlist['id'], array('data' => $publiccontactlist)));
			}
		}

		function createContactList($options = array()) {
			$options['owner'] = $this;
			$contactlist = ContactList::create($options);
			$this->_contactlistmanager['[owned]']->setElement($contactlist->getProperty('id'), $contactlist);
			return $contactlist;
		}

		function deleteContactList($contactlist_id = NULL) {
			if (isNotNULL($contactlist_id)) {
				if ($this->_contactlistmanager['[owned]']->elementExists($contactlist_id)) {
					$this->_contactlistmanager['[owned]']->getElement($contactlist_id)->delete();
					$this->initOwnedContactLists();
				} elseif (($this->_contactlistmanager['[delegated]']->elementExists($contactlist_id)) and
					($this->_contactlistmanager['[delegated]']->getElement($contactlist_id)->getPermissions()->elementExists(USER_NAME)) and 
					(($this->_contactlistmanager['[delegated]']->getElement($contactlist_id)->getPermissionValue(USER_NAME) == Constants::PERMISSION_READ_WRITE_DELETE) or
					($this->_contactlistmanager['[delegated]']->getElement($contactlist_id)->getPermissionValue(USER_NAME) == Constants::PERMISSION_FULL_ACCESS))
				) {
					$this->_contactlistmanager['[delegated]']->getElement($contactlist_id)->delete();
					$this->initDelegatedContactLists();
				}
			}
		}

		function undeleteContactList($contactlist_id = NULL) {
			if (isNotNULL($contactlist_id)) {
				if ($this->_contactlistmanager['[owned]']->elementExists($contactlist_id)) {
					$this->_contactlistmanager['[owned]']->getElement($contactlist_id)->undelete();
					$this->initOwnedContactLists();
				} elseif (($this->_contactlistmanager['[delegated]']->elementExists($contactlist_id)) and
					($this->_contactlistmanager['[delegated]']->getElement($contactlist_id)->getPermissions()->elementExists(USER_NAME)) and 
					(($this->_contactlistmanager['[delegated]']->getElement($contactlist_id)->getPermissionValue(USER_NAME) == Constants::PERMISSION_READ_WRITE_DELETE) or
					($this->_contactlistmanager['[delegated]']->getElement($contactlist_id)->getPermissionValue(USER_NAME) == Constants::PERMISSION_FULL_ACCESS))
				) {
					$this->_contactlistmanager['[delegated]']->getElement($contactlist_id)->undelete();
					$this->initDelegatedContactLists();
				}
			}
		}

		function eraseContactList($contactlist_id = NULL) {
			if (isNotNULL($contactlist_id)) {
				if ($this->_contactlistmanager['[owned]']->elementExists($contactlist_id)) {
					$this->_contactlistmanager['[owned]']->getElement($contactlist_id)->erase();
					$this->initOwnedContactLists();
				}
			}
		}

		function contactlistExists($contactlist_id = NULL) {
			if ($this->_contactlistmanager['[owned]']->elementExists($contactlist_id)) {
				return ($this->_contactlistmanager['[owned]']->elementExists($contactlist_id));
			} elseif ($this->_contactlistmanager['[delegated]']->elementExists($contactlist_id)) {
				return ($this->_contactlistmanager['[delegated]']->elementExists($contactlist_id));
			} elseif ($this->_contactlistmanager['[public]']->elementExists($contactlist_id)) {
				return ($this->_contactlistmanager['[public]']->elementExists($contactlist_id));
			}
		}

		function isDeleted($contactlist_id = NULL) {
			if ($this->_contactlistmanager['[owned]']->elementExists($contactlist_id)) {
				return ($this->_contactlistmanager['[owned]']->getElement($contactlist_id)->isDeleted());
			} elseif ($this->_contactlistmanager['[delegated]']->elementExists($contactlist_id)) {
				return ($this->_contactlistmanager['[delegated]']->getElement($contactlist_id)->isDeleted());
			} elseif ($this->_contactlistmanager['[public]']->elementExists($contactlist_id)) {
				return ($this->_contactlistmanager['[public]']->getElement($contactlist_id)->isDeleted());
			}
		}
	}
?>