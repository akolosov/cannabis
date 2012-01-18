<?php

// $Id$

	class PropertyValue extends Core {

		public $_value = array();

		function __construct($owner = NULL, $id = 0) {
			if (($id > 0) && ($owner <> NULL)) {

				parent::__construct($owner, $id, $owner->getConnection());

				$value = $this->getConnection()->execute('select * from cs_property_value where id = '.$this->_id)->fetch();
				foreach ($value as $key => $data) {
					$this->_value[$key] = $data;
				}
				
				$this->_value['[model]']	= "CsPropertyValue";
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
	
		function setProperty($name, $value, $lazy = false) {
			$value = stripslashes(html_entity_decode($this->getEngine()->getTemplate()->simpleProcess($value), ENT_COMPAT, DEFAULT_CHARSET));
			logRuntime('['.get_class($this).'.setProperty->ID_'.$this->getProperty('id').'] set '.$name.' to '.$value);
			$this->_value[$name] = $value;
			if ($lazy == false) {
				$this->save();
			}
		}
		
		function save() {
			logRuntime('['.get_class($this).'.save->ID_'.$this->getProperty('id').'] save data to '.$this->_value['[model]']);
			$this->saveData($this->_value['[model]'], $this->_value);
		}
	}
?>