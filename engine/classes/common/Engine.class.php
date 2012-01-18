<?php

// $Id$

	class Engine extends Core {

		function __construct($connection, $account = 0) {

			parent::__construct(NULL, 0, $connection);

			if ($account == 0) {
				$this->_account	= new Account($this, USER_CODE);
				$account = USER_CODE;
			} else {
				$this->_account	= new Account($this, $account);
			}

			$this->_security	= new Security($this);
			$this->_template	= new Template($this);
			$this->_formanager	= new FormManager($this);
			$this->_settings	= new EngineSettings($this, $account);
		}

		function getAccount() {
			return $this->_account;
		}

		function getSecurity() {
			return $this->_security;
		}

		function getFormManager() {
			return $this->_formanager;
		}

		function getEngineSettings() {
			return $this->_settings;
		}

		function reinitFormManager() {
			$this->_formanager	= NULL;
			$this->_formanager	= new FormManager($this);
		}

		function getTemplate() {
			return $this->_template;
		}
	}
?>