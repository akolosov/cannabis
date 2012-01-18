<?php
	class Message extends Core {

		public $_message = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {

			if (isNotNULL($owner)) {
				parent::__construct($owner, $id, $owner->getConnection());

				if (($id > 0) or (isNotNULL($options['data']))) {
					if (is_null($options['data'])) {
						$message = $this->getConnection()->execute('select * from messages_list where id = '.$this->_id)->fetch();
					} else {
						$message = $options['data'];
					}
					foreach ($message as $key => $data) {
						$this->_message[$key] = $data;
					}
					$this->reinitMessage();
				}

			}

			$this->_message['[model]'] = 'CsMessage';
		}

		static function create($options = array()) {
			$message = new Message($options['owner']);
			$message->setProperty('subject', $options['subject']);
			$message->setProperty('message', $options['text']);
			$message->setProperty('status_id', Constants::MESSAGE_CREATED);
			$message->setProperty('is_recieved', $options['is_recieved']);
			$message->_message['[recievers]'] = new Collection($message);
			$message->_message['[blobs]'] = new Collection($message);
			
			foreach ($options['recievers'] as $reciever) {
				$this->addReciever(MessageReciever::create(array('owner' => $message, 'reciever_id' => $reciever)));
			}
			foreach ($options['blobs'] as $blob) {
				$this->addBlob(MessageBlob::create(array('owner' => $message, 'name' => $blob['name'], 'blob' => $blob['blob'])));
			}
			return $message;
		}

		function addReciever($reciever = NULL) {
			if ((isNotNULL($reciever)) and (is_a($reciever, 'MessageReciever'))) {
				$this->_message['[recievers]']->setElement($reciever->getProperty('reciever_id'), $reciever);
			}
		}

		function addBlob($blob = NULL) {
			if ((isNotNULL($blob)) and (is_a($blob, 'MessageBlob'))) {
				if (isNULL($this->_message['[blobs]'])) {
					$this->initBlobs();
				}
				$this->_message['[blobs]']->setElement($blob->getProperty('id'), $blob);
			}
		}

		function reinitMessage() {
			$this->initRecievers();
			$this->_message['[blobs]'] = NULL;
		}

		function initRecievers() {
			$this->_message['[recievers]'] = new Collection($this);
			$recievers = $this->getConnection()->execute('select * from message_recievers_list where message_id = '.$this->getProperty('id'))->fetchAll();
			foreach ($recievers as $reciever) {
				$this->_message['[recievers]']->setElement($reciever['reciever_id'], new MessageReciever($this, $reciever['id'], array('data' => $reciever)));
			}
		}

		function initBlobs() {
			$this->_message['[blobs]'] = new Collection($this);
			$blobs = $this->getConnection()->execute('select * from cs_message_blob where message_id = '.$this->getProperty('id'))->fetchAll();
			foreach ($blobs as $blob) {
				$this->_message['[blobs]']->setElement($blob['id'], new MessageBlob($this, $blob['id'], array('data' => $blob)));
			}
		}

		function getProperty($name) {
			return $this->_message[$name];
		}

		function setProperty($name, $value) {
			$this->_message[$name] = $value;
		}

		function getBlobs() {
			if (isNULL($this->_message['[blobs]'])) {
				$this->initBlobs();
			}
			return $this->getProperty('[blobs]')->getElements();
		}

		function getBlob($id = NULL) {
			if (isNULL($this->_message['[blobs]'])) {
				$this->initBlobs();
			}
			if ((isNotNULL($id)) and ($this->getProperty('[blobs]')->elementExists($id))) {
				return $this->getProperty('[blobs]')->getElement($id);
			} else {
				return NULL;
			}
		}

		function eraseBlob($id = NULL) {
			if (isNULL($this->_message['[blobs]'])) {
				$this->initBlobs();
			}
			if ((isNotNULL($id)) and ($this->getProperty('[blobs]')->elementExists($id))) {
				$this->getProperty('[blobs]')->getElement($id)->erase();
				$this->getProperty('[blobs]')->purgeNulls();
			}
		}

		function getRecievers() {
			return $this->getProperty('[recievers]')->getElements();
		}

		function getReciever($id = NULL) {
			if ((isNotNULL($id)) and ($this->getProperty('[recievers]')->elementExists($id))) {
				return $this->getProperty('[recievers]')->getElement($id);
			} else {
				return NULL;
			}
		}

		function eraseReciever($id = NULL) {
			if ((isNotNULL($id)) and ($this->getProperty('[recievers]')->elementExists($id))) {
				$this->getProperty('[recievers]')->getElement($id)->erase();
				$this->getProperty('[recievers]')->purgeNulls();
			}
		}

		function clearAllRecievers() {
			foreach ($this->getRecievers() as $reciever) {
				$reciever->realerase();
			}
			$this->_message['[recievers]'] = new Collection($this);
		}

		function clearAllBlobs() {
			foreach ($this->getBlobs() as $blob) {
				$blob->erase();
			}
			$this->_message['[blobs]'] = new Collection($this);
		}

		private function saveBlobs() {
			foreach ($this->getBlobs() as $blob) {
				$blob->save($this->getProperty('id'));
			}
		}

		private function saveRecievers() {
			foreach ($this->getRecievers() as $reciever) {
				$reciever->save($this->getProperty('id'));
			}
		}

		function erase() {
			if (($this->getProperty('is_recieved')) or ($this->getProperty('author_id') <> USER_CODE)) {
				if ($this->getProperty('[recievers]')->elementExists(USER_CODE)) {
					$this->getProperty('[recievers]')->getElement(USER_CODE)->erase();
					$this->getProperty('[recievers]')->getElement(USER_CODE)->save();
				}
			} else {
				$this->setProperty('is_erased', true);
				$this->save();
			}
		}

		function unerase() {
			if (($this->getProperty('is_recieved')) or ($this->getProperty('author_id') <> USER_CODE)) {
				if ($this->getProperty('[recievers]')->elementExists(USER_CODE)) {
					$this->getProperty('[recievers]')->getElement(USER_CODE)->unerase();
					$this->getProperty('[recievers]')->getElement(USER_CODE)->save();
				}
			} else {
				$this->setProperty('is_erased', false);
				$this->save();
			}
		}

		function send() {
			if ($this->isDeleted()) {
				$this->undelete();
			}

			$this->setProperty('status_id', Constants::MESSAGE_SENDED);
			$this->setProperty('sended_at', strftime("%Y-%m-%d %H:%M:%S", time()));
			foreach ($this->getRecievers() as $reciever) {
				$reciever->setProperty('recieved_at', strftime("%Y-%m-%d %H:%M:%S", time()));
				$reciever->setProperty('status_id', Constants::MESSAGE_RECIEVED);
				$reciever->setProperty('is_deleted', false);
				$reciever->setProperty('is_erased', false);
			}
			$this->save();
		}

		function resend() {
			if ($this->isDeleted()) {
				$this->undelete();
			}

			$this->setProperty('status_id', Constants::MESSAGE_RESENDED);
			$this->setProperty('sended_at', strftime("%Y-%m-%d %H:%M:%S", time()));
			foreach ($this->getRecievers() as $reciever) {
				$reciever->setProperty('recieved_at', strftime("%Y-%m-%d %H:%M:%S", time()));
				$reciever->setProperty('status_id', Constants::MESSAGE_RERECIEVED);
				$reciever->setProperty('is_deleted', false);
				$reciever->setProperty('is_erased', false);
			}
			$this->save();
		}

		function delete() {
			if (($this->getProperty('is_recieved')) or ($this->getProperty('author_id') <> USER_CODE)) {
				if ($this->getProperty('[recievers]')->elementExists(USER_CODE)) {
					$this->getProperty('[recievers]')->getElement(USER_CODE)->delete();
				}
			} else {
				$this->setProperty('status_id', Constants::MESSAGE_DELETED);
			}
			parent::delete();
		}

		function undelete() {
			if (($this->getProperty('is_recieved')) or ($this->getProperty('author_id') <> USER_CODE)) {
				if ($this->getProperty('[recievers]')->elementExists(USER_CODE)) {
					$this->getProperty('[recievers]')->getElement(USER_CODE)->undelete();
				}
			} else {
				$this->setProperty('status_id', Constants::MESSAGE_SENDED);
			}
			parent::undelete();
		}

		function isErased() {
			if (($this->getProperty('is_recieved')) or ($this->getProperty('author_id') <> USER_CODE)) {
				if ($this->getProperty('[recievers]')->elementExists(USER_CODE)) {
					return $this->getProperty('[recievers]')->getElement(USER_CODE)->isErased();
				} else {
					return false;
				}
			} else {
				return $this->getProperty('is_erased');
			}
		}

		function isDeleted() {
			if (($this->getProperty('is_recieved')) or ($this->getProperty('author_id') <> USER_CODE)) {
				if ($this->getProperty('[recievers]')->elementExists(USER_CODE)) {
					return $this->getProperty('[recievers]')->getElement(USER_CODE)->isDeleted();
				} else {
					return false;
				}
			} else {
				return parent::isDeleted();
			}
		}

		function isRecieved() {
			if (($this->getProperty('is_recieved')) or ($this->getProperty('author_id') <> USER_CODE)) {
				if ($this->getProperty('[recievers]')->elementExists(USER_CODE)) {
					return $this->getProperty('[recievers]')->getElement(USER_CODE)->isRecieved();
				} else {
					return false;
				}
			} else {
				return (($this->getProperty('status_id') == Constants::MESSAGE_RECIEVED) or ($this->getProperty('status_id') == Constants::MESSAGE_RERECIEVED));
			}
		}

		function isSended() {
			if (($this->getProperty('is_recieved')) or ($this->getProperty('author_id') <> USER_CODE)) {
				if ($this->getProperty('[recievers]')->elementExists(USER_CODE)) {
					return $this->getProperty('[recievers]')->getElement(USER_CODE)->isSended();
				} else {
					return false;
				}
			} else {
				return (($this->getProperty('status_id') == Constants::MESSAGE_SENDED) or ($this->getProperty('status_id') == Constants::MESSAGE_RESENDED));
			}
		}

		function isReaded() {
			if (($this->getProperty('is_recieved')) or ($this->getProperty('author_id') <> USER_CODE)) {
				if ($this->getProperty('[recievers]')->elementExists(USER_CODE)) {
					return $this->getProperty('[recievers]')->getElement(USER_CODE)->isReaded();
				} else {
					return false;
				}
			} else {
				return ($this->getProperty('status_id') == Constants::MESSAGE_READED);
			}
		}

		function isCreated() {
			if (($this->getProperty('is_recieved')) or ($this->getProperty('author_id') <> USER_CODE)) {
				if ($this->getProperty('[recievers]')->elementExists(USER_CODE)) {
					return $this->getProperty('[recievers]')->getElement(USER_CODE)->isCreated();
				} else {
					return false;
				}
			} else {
				return ($this->getProperty('status_id') == Constants::MESSAGE_CREATED);
			}
		}

		function getRecieversProperty($propertyname = NULL) {
			$result = array();

			if (isNotNULL($propertyname)) {
				foreach ($this->getRecievers() as $reciever) {
					$result[$reciever->getProperty('reciever_id')] = $reciever->getProperty($propertyname);
				}
			}

			return $result;
		}

		function getBlobsProperty($propertyname = NULL) {
			$result = array();

			if (isNotNULL($propertyname)) {
				foreach ($this->getBlobs() as $blob) {
					$result[$blob->getProperty('id')] = $blob->getProperty($propertyname);
				}
			}

			return $result;
		}

		function save() {
			logRuntime('['.get_class($this).'.save->'.$this->getProperty('subject').'] data saved to '.$this->_message['[model]']);

			if (isNULL($this->getProperty('created_at'))) {
				$this->setProperty('author_id', USER_CODE);
				$this->setProperty('created_at', strftime("%Y-%m-%d %H:%M:%S", time()));
			}
			$result = $this->saveData($this->_message['[model]'], $this->_message);
			$this->setProperty('id', $result);
			$this->saveBlobs();
			$this->saveRecievers();
			return $result;
		}

	}
?>