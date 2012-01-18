<?php

// $Id$

	class ChronoValue extends Core {

		public $_value = array();

		function __construct($owner = NULL, $id = 0) {
			if (($id > 0) && ($owner <> NULL)) {

				parent::__construct($owner, $id, $owner->getConnection());

				$value = $this->getConnection()->execute('select * from cs_chrono_value where id = '.$this->_id)->fetch();
				foreach ($value as $key => $data) {
					$this->_value[$key] = $data;
				}
				
				$this->_value['[model]']	= "CsChronoValue";
			}
		}

		function getProperty($name) {
			return stripslashes(htmlentities($this->getEngine()->getTemplate()->simpleProcess($this->_value[$name]), ENT_COMPAT, DEFAULT_CHARSET));
		}

		function getMimeType() {
			return preg_replace('/^(.*)(\|)(.*)$/ui', '$1', $this->getProperty('mime_type'));
		}

		function getFileName() {
			return preg_replace('/^(.*)(\|)(.*)$/ui', '$3', $this->getProperty('mime_type'));
		}

	}
?>