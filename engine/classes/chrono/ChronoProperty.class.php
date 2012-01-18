<?php

// $Id$

class ChronoProperty extends Core {

	public $_property = array();

	function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {

		if (($id > 0) && ($owner <> NULL)) {
			parent::__construct($owner, $id, $owner->getConnection());

			if (is_null($options['data'])) {
				$property = $this->getConnection()->execute('select * from chrono_process_instance_properties_list where id = '.$this->_id)->fetch();
			} else {
				$property = $options['data'];
			}
			foreach ($property as $key => $data) {
				$this->_property[$key] = $data;
			}

			$this->_property['[value]']	= new ChronoValue($this, $this->getProperty('value_id'));
			$this->_property['[changed]']	= false;
			$this->_property['[model]']	= "CsProcessPropertyValue";
		}
	}

	function getProperty($name) {
		return $this->_property[$name];
	}

	function getValueProperty($name) {
		return $this->_property['[value]']->getProperty($name);
	}

	function getProcessProperty($name) {
		return $this->_property['[property]']->getProperty($name);
	}
	
	function getPropertyValue() {
		return $this->_property['[value]']->getProperty('value');
	}
	
	function getPropertyMimeType() {
		return preg_replace('/^(.*)(\|)(.*)$/ui', '$1', $this->_property['[value]']->getProperty('mime_type'));
	}

	function getPropertyFileName() {
		return preg_replace('/^(.*)(\|)(.*)$/ui', '$3', $this->_property['[value]']->getProperty('mime_type'));
	}
}
?>