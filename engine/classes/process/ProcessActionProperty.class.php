<?php

// $Id$

class ProcessActionProperty extends Core {

	public $_property = array();

	function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
		if (($id > 0) && ($owner <> NULL)) {

			parent::__construct($owner, $id, $owner->getConnection());

			if (is_null($options['data'])) {
				$property = $this->getConnection()->execute('select * from process_action_properties_list where id = '.$this->_id)->fetch();
			} else {
				$property =$options['data'];
			}
			foreach ($property as $key => $data) {
				$this->_property[$key] = $data;
			}

			if (is_a($this->_owner->_owner, 'Process')) {
				$this->_property['[property]']	=  $this->_owner->_owner->getProperty('[properties]')->findElementByID($this->getProperty('property_id'));
			} else {
				$this->_property['[property]']	=  new ProcessProperty($this, $this->getProperty('property_id'));
			}

			$this->_property['[model]']		= "CsProcessActionProperty";
		}
	}

	function getProperty($name) {
		return $this->_property[$name];
	}

	function setProperty($name, $value) {
		$this->_property[$name] = $value;
	}

	function save() {
		if (isNotNULL($this->_property['[property]'])) {
			$this->_property['property_id'] = $this->_property['[property]']->save();
		}
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('id').'] data saved to '.$this->_property['[model]']);
		return $this->saveData($this->_property['[model]'], $this->_property);
	}

	function export() {
		return "<property>\n".$this->exportData($this->_property['[model]'], $this->_property).(isNotNULL($this->_property['[property]'])?$this->_property['[property]']->export():'')."</property>\n";
	}

	static function import(array $propertydata = array(), $connection = NULL) {
		global $engine;

		$property = self::importData("ProcessActionProperty", "CsProcessActionProperty", $propertydata, (is_resource($connection)?$connection:$engine->getConnection()));

		if (($find = $property->getConnection()->execute('select * from '.$property->getConnection()->getTable($property->getProperty('[model]'))->getTableName().' where action_id = '.$property->getProperty('action_id').' and property_id = '.$property->getProperty('property_id'))->fetch()) != false) {
			$property->setProperty('id', $find['id']);
			$property->_id = $find['id'];
		}

		$property->setProperty('id', $property->save());
		
		return $property;
	}
}
?>