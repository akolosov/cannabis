<?php
	class MessageManager extends Core {

		public $_messagemanager = array();

		function __construct($owner = NULL, $id = 0) {

			if (isNotNULL($owner)) {
				if (isNULL($id)) {
					$id = USER_CODE;
				}

				parent::__construct($owner, $id, $owner->getConnection());

				$this->reinitMessageManager();
			}
		}

		function getProperty($name) {
			return $this->_messagemanager[$name];
		}

		function setProperty($name, $value) {
			$this->_messagemanager[$name] = $value;
		}

		function createMessage($options = array()) {
			$options['owner'] = $this;
			$message = Message::create($options);
			$this->getProperty('[owned]')->setElement((($message->getProperty('id') > 0)?$message->getProperty('id'):mt_rand()), $message);
			return $message;
		}

		function reinitMessageManager() {
			$this->initOwnedMessages();
			$this->initRecievedMessages();
		}

		function initOwnedMessages() {
			global $user_permissions;

			$this->setProperty('[owned]', new Collection($this));
			$messages = $this->getConnection()->execute('select * from messages_list where '.(($user_permissions[getParentModule()][getChildModule()]['can_admin'])?'':'is_erased = false and').' author_id = '.$this->_id)->fetchAll();
			foreach ($messages as $message) {
				$this->getProperty('[owned]')->setElement($message['id'], new Message($this, $message['id'], array('data' => $message)));
			}
		}

		function initRecievedMessages() {
			global $user_permissions;

			$this->setProperty('[recieved]', new Collection($this));
			$messages = $this->getConnection()->execute('select * from messages_list where status_id > '.Constants::MESSAGE_CREATED.' and id in (select message_id from cs_message_reciever where '.(($user_permissions[getParentModule()][getChildModule()]['can_admin'])?'':'cs_message_reciever.is_erased = false and').' cs_message_reciever.reciever_id = '.$this->_id.')')->fetchAll();
			foreach ($messages as $message) {
				$message['is_recieved'] = true;
				$this->getProperty('[recieved]')->setElement($message['id'], new Message($this, $message['id'], array('data' => $message)));
			}
		}

		function getOwnedMessages() {
			return $this->getProperty('[owned]')->getElements();
		}

		function getRecievedMessages() {
			return $this->getProperty('[recieved]')->getElements();
		}

		function getOwnedMessage($message_id = 0) {
			if (($message_id > 0) and ($this->getProperty('[owned]')->elementExists($message_id))) {
				return $this->getProperty('[owned]')->getElement($message_id);
			} else {
				return NULL;
			}
		}

		function getErased() {
			$result = array();

			foreach ($this->getOwnedMessages() as $message) {
				if ($message->isErased()) {
					$result[$message->getProperty('id')] = $message;
				}
			}

			foreach ($this->getRecievedMessages() as $message) {
				if ($message->isErased()) {
					$result[$message->getProperty('id')] = $message;
				}
			}

			return $result;
		}

		function getDeleted() {
			$result = array();

			foreach ($this->getOwnedMessages() as $message) {
				if ((!$message->isErased()) and ($message->isDeleted())) {
					$result[$message->getProperty('id')] = $message;
				}
			}

			foreach ($this->getRecievedMessages() as $message) {
				if ((!$message->isErased()) and ($message->isDeleted())) {
					$result[$message->getProperty('id')] = $message;
				}
			}

			return $result;
		}

		function getRecieved() {
			$result = array();

			foreach ($this->getRecievedMessages() as $message) {
				if ((!$message->isErased()) and ($message->isRecieved())) {
					$result[$message->getProperty('id')] = $message;
				}
			}

			return $result;
		}

		function getRecievedAndReaded() {
			$result = array();

			foreach ($this->getRecievedMessages() as $message) {
				if ((!$message->isErased()) and ($message->isRecieved())) {
					$result[$message->getProperty('id')] = $message;
				}
			}

			foreach ($this->getRecievedMessages() as $message) {
				if ((!$message->isErased()) and ($message->isReaded())) {
					$result[$message->getProperty('id')] = $message;
				}
			}
			
			return $result;
		}

		function getReaded() {
			$result = array();
			
			foreach ($this->getRecievedMessages() as $message) {
				if ((!$message->isErased()) and ($message->isReaded())) {
					$result[$message->getProperty('id')] = $message;
				}
			}

			return $result;
		}

		function getSended() {
			$result = array();

			foreach ($this->getOwnedMessages() as $message) {
				if ((!$message->isErased()) and ($message->isSended())) {
					$result[$message->getProperty('id')] = $message;
				}
			}

			return $result;
		}

		function getCreated() {
			$result = array();

			foreach ($this->getOwnedMessages() as $message) {
				if ((!$message->isErased()) and ($message->isCreated())) {
					$result[$message->getProperty('id')] = $message;
				}
			}

			return $result;
		}

		function messageExists($message_id = 0) {
			if ($this->getProperty('[owned]')->elementExists($message_id)) {
				return true;
			} elseif ($this->getProperty('[recieved]')->elementExists($message_id)) {
				return true;
			} else {
				return false;
			}
		}

		function getMessage($message_id = 0) {
			if ($this->getProperty('[owned]')->elementExists($message_id)) {
				return $this->getProperty('[owned]')->getElement($message_id);
			} elseif ($this->getProperty('[recieved]')->elementExists($message_id)) {
				return $this->getProperty('[recieved]')->getElement($message_id);
			} else {
				return NULL;
			}
		}

		function deleteOwned($message_id = 0) {
			if (($message_id > 0) and ($this->getProperty('[owned]')->elementExists($message_id))) {
				$this->getProperty('[owned]')->getElement($message_id)->delete();
			}
		}

		function undeleteOwned($message_id = 0) {
			if (($message_id > 0) and ($this->getProperty('[owned]')->elementExists($message_id))) {
				$this->getProperty('[owned]')->getElement($message_id)->undelete();
			}
		}

		function getRecievedMessage($message_id = 0) {
			if (($message_id > 0) and ($this->getProperty('[recieved]')->elementExists($message_id))) {
				return $this->getProperty('[recieved]')->getElement($message_id);
			} else {
				return NULL;
			}
		}

		function deleteRecieved($message_id = 0) {
			if (($message_id > 0) and ($this->getProperty('[recieved]')->elementExists($message_id)) and ($this->getProperty('[recieved]')->getElement($message_id)->getProperty('[recievers]')->elementExists(USER_CODE))) {
				$this->getProperty('[recieved]')->getElement($message_id)->getReciever(USER_CODE)->delete();
				return true;
			} else {
				return false;
			}
		}

		function undeleteRecieved($message_id = 0) {
			if (($message_id > 0) and ($this->getProperty('[recieved]')->elementExists($message_id)) and ($this->getProperty('[recieved]')->getElement($message_id)->getProperty('[recievers]')->elementExists(USER_CODE))) {
				$this->getProperty('[recieved]')->getElement($message_id)->getReciever(USER_CODE)->undelete();
				return true;
			} else {
				return false;
			}
		}

		function eraseRecieved($message_id = 0) {
			if (($message_id > 0) and ($this->getProperty('[recieved]')->elementExists($message_id)) and ($this->getProperty('[recieved]')->getElement($message_id)->getProperty('[recievers]')->elementExists(USER_CODE))) {
				$this->getProperty('[recieved]')->getElement($message_id)->getReciever(USER_CODE)->erase();
				return true;
			} else {
				return false;
			}
		}

		function uneraseRecieved($message_id = 0) {
			if (($message_id > 0) and ($this->getProperty('[recieved]')->elementExists($message_id)) and ($this->getProperty('[recieved]')->getElement($message_id)->getProperty('[recievers]')->elementExists(USER_CODE))) {
				$this->getProperty('[recieved]')->getElement($message_id)->getReciever(USER_CODE)->unerase();
				return true;
			} else {
				return false;
			}
		}

		function deleteMessage($message_id = 0) {
			if (!$this->deleteRecieved($message_id)) {
				$this->deleteOwned($message_id);
			}
		}

		function undeleteMessage($message_id = 0) {
			if (!$this->undeleteRecieved($message_id)) {
				$this->undeleteOwned($message_id);
			}
		}

		function eraseMessage($message_id = 0) {
			if (!$this->eraseRecieved($message_id)) {
				$this->eraseOwned($message_id);
			}
		}

		function uneraseMessage($message_id = 0) {
			if (!$this->uneraseRecieved($message_id)) {
				$this->uneraseOwned($message_id);
			}
		}

		function eraseOwned($message_id = 0) {
			if (($message_id > 0) and ($this->getProperty('[owned]')->elementExists($message_id))) {
				$this->getProperty('[owned]')->getElement($message_id)->erase();
			}
		}

		function uneraseOwned($message_id = 0) {
			if (($message_id > 0) and ($this->getProperty('[owned]')->elementExists($message_id))) {
				$this->getProperty('[owned]')->getElement($message_id)->unerase();
			}
		}

		function sendMessage($message_id = 0) {
			if (($message_id > 0) and ($this->getProperty('[owned]')->elementExists($message_id))) {
				$this->getProperty('[owned]')->getElement($message_id)->send();
			}
		}

		function resendMessage($message_id = 0) {
			if (($message_id > 0) and ($this->getProperty('[owned]')->elementExists($message_id))) {
				$this->getProperty('[owned]')->getElement($message_id)->resend();
			}
		}

	}
?>