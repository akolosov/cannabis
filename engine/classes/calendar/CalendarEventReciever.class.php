<?php
	class CalendarEventReciever extends Core {

		public $_calendareventreciever = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {

			if (isNotNULL($owner)) {
				parent::__construct($owner, $id, $owner->getConnection());

				if (($id > 0) or (isNotNULL($options['data']))) {
					if (is_null($options['data'])) {
						$calendareventreciever = $this->getConnection()->execute('select * from calendar_event_recievers_list where id = '.$this->_id)->fetch();
					} else {
						$calendareventreciever = $options['data'];
					}
					foreach ($calendareventreciever as $key => $data) {
						$this->_calendareventreciever[$key] = $data;
					}
				}

			}

			$this->_calendareventreciever['[model]'] = 'CsCalendarEventReciever';
		}

		static function create($options = array()) {
			$reciever = new CalendarEventReciever($options['owner']);
			$reciever->setProperty('event_id', (isNotNULL($options['owner'])?$options['owner']->getProperty('id'):$options['event_id']));
			$reciever->setProperty('account_id', $options['account_id']);
			$reciever->setProperty('status_id', Constants::EVENT_STATUS_WAITING);
			$reciever->setProperty('permission_id', $options['permission_id']);
			return $reciever;
		}

		function initAccount() {
			$this->_calendareventreciever['[account]'] = new Account($this, $this->getProperty('account_id'));
		}

		function getAccount() {
			if (isNULL($this->_calendareventreciever['[account]'])) {
				$this->initAccount();
			}
			return $this->_calendareventreciever['[account]'];
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
				$this->_calendareventreciever['[account]'] = $account;
				$this->setProperty('account_id', $account->getProperty('id'));
			}
		}

		function getProperty($name) {
			return $this->_calendareventreciever[$name];
		}

		function setProperty($name, $value) {
			$this->_calendareventreciever[$name] = $value;
		}

		function save($event_id = 0) {
			if ($event_id > 0) {
				$this->setProperty('event_id', $event_id);
			}
			return $this->saveData($this->_calendareventreciever['[model]'], $this->_calendareventreciever);
		}
	}
?>