<?php
	class MessageReciever extends Core {

		public $_messagereciever = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {

			if (isNotNULL($owner)) {
				parent::__construct($owner, $id, $owner->getConnection());

				if (($id > 0) or (isNotNULL($options['data']))) {
					if (is_null($options['data'])) {
						$message = $this->getConnection()->execute('select * from message_recievers_list where id = '.$this->_id)->fetch();
					} else {
						$message = $options['data'];
					}
					foreach ($message as $key => $data) {
						$this->_messagereciever[$key] = $data;
					}
				}
			}

			$this->_messagereciever['[model]'] = 'CsMessageReciever';
		}

		static function create($options = array()) {
			$reciever = new MessageReciever($options['owner']);
			$reciever->setProperty('message_id', (isNotNULL($options['owner'])?$options['owner']->getProperty('id'):$options['message_id']));
			$reciever->setProperty('reciever_id', $options['reciever_id']);
			$reciever->setProperty('status_id', Constants::MESSAGE_CREATED);
			return $reciever;
		}

		function initAccount() {
			$this->_messagereciever['[account]'] = new Account($this, $this->getProperty('reciever_id'));
		}

		function getAccount() {
			if (isNULL($this->_messagereciever['[account]'])) {
				$this->initAccount();
			}
			return $this->_messagereciever['[account]'];
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
				$this->_messagereciever['[account]'] = $account;
				$this->setProperty('reciever_id', $account->getProperty('id'));
			}
		}

		function getProperty($name) {
			return $this->_messagereciever[$name];
		}

		function setProperty($name, $value) {
			$this->_messagereciever[$name] = $value;
		}

		function isRecieved() {
			return (($this->getProperty('status_id') == Constants::MESSAGE_RECIEVED) or ($this->getProperty('status_id') == Constants::MESSAGE_RERECIEVED));
		}

		function isSended() {
			return (($this->getProperty('status_id') == Constants::MESSAGE_SENDED) or ($this->getProperty('status_id') == Constants::MESSAGE_RESENDED));
		}

		function isReaded() {
			return ($this->getProperty('status_id') == Constants::MESSAGE_READED);
		}

		function isCreated() {
			return ($this->getProperty('status_id') == Constants::MESSAGE_CREATED);
		}

		function isErased() {
			return $this->getProperty('is_erased');
		}

		function delete() {
			$this->setProperty('status_id', Constants::MESSAGE_DELETED);
			parent::delete();
		}

		function undelete() {
			$this->setProperty('status_id', Constants::MESSAGE_READED);
			parent::undelete();
		}

		function erase() {
			$this->setProperty('is_erased', true);
			$this->save();
		}

		function realerase() {
			parent::erase();
		}

		function unerase() {
			$this->setProperty('is_erased', false);
			$this->save();
		}

		function save($message_id = NULL) {
			if (isNotNULL($message_id)) {
				$this->setProperty('message_id', $message_id);
			}
			logRuntime('['.get_class($this).'.save->'.$this->getProperty('recievername').'] data saved to '.$this->_messagereciever['[model]']);
			return $this->saveData($this->_messagereciever['[model]'], $this->_messagereciever);
		}

	}
?>