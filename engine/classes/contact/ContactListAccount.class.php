<?php
	class ContactListAccount extends Core {

		public	$_contactaccount = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
			if ($owner <> NULL) {

				parent::__construct($owner, $id, $owner->getConnection());

				if (($id > 0) or (isNotNULL($options['data']))) {
					if (is_null($options['data'])) {
						$contactaccount = $this->getConnection()->execute('select * from contact_accounts_list where id = '.$this->_id)->fetch();
					} else {
						$contactaccount = $options['data'];
					}
					foreach ($contactaccount as $key => $data) {
						$this->_contactaccount[$key] = $data;
					}
				}

				$this->_contactaccount['[model]'] = "CsContactList";
			}
		}

		static function create($options = array()) {
			$contact = new ContactListAccount($options['owner']);
			$contact->setProperty('contact_id', (isNotNULL($options['owner'])?$options['owner']->getProperty('id'):$options['contact_id']));
			$contact->setProperty('account_id', $options['account_id']);
			return $contact;
		}

		function initAccount() {
			$account = $this->getConnection()->execute('select * from accounts_tree where id = '.$this->getProperty('account_id'))->fetch();
			$this->_contactaccount['[account]'] = new Account($this, $this->getProperty('account_id'), array('data' => $account));
		}

		function getProperty($name) {
			return $this->_contactaccount[$name];
		}

		function setProperty($name, $value) {
			$this->_contactaccount[$name] = $value;
		}

		function getAccount() {
			if (isNULL($this->_contactaccount['[account]'])) {
				$this->initAccount();
			}
			return $this->_contactaccount['[account]'];
		}

		function getAccountProperty($propertyname = NULL) {
			if (isNotNULL($propertyname)) {
				return $this->getAccount()->getProperty($propertyname);
			} else {
				return NULL;
			}
		}

		function setAccount($account = NULL) {
			if ((isNotNULL($account)) and (is_a($account, 'Account'))) {
				$this->_contactaccount['[account]'] = $account;
				$this->setProperty('account_id', $account->getProperty('id'));
			}
		}

		function save($contact_id = 0) {
			logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_contactaccount['[model]']);
			if ($contact_id > 0) {
				$this->setProperty('contact_id', $contact_id);
			}
			return $this->saveData($this->_contactaccount['[model]'], $this->_contactaccount);
		}
	}
?>