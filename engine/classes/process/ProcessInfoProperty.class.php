<?php

// $Id$

class ProcessInfoProperty extends Core {

	public $_property = array();

	function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
		if (($id > 0) && ($owner <> NULL)) {

			parent::__construct($owner, $id, $owner->getConnection());

			if (is_null($options['data'])) {
				$property = $this->getConnection()->execute('select * from process_info_properties_list where id = '.$this->_id)->fetch();
			} else {
				$property = $options['data'];
			}
			foreach ($property as $key => $data) {
				$this->_property[$key] = $data;
			}

			$this->_property['[model]']	= "CsProcessInfoProperty";
		}
	}

	function getProperty($name) {
		return $this->_property[$name];
	}

	function setProperty($name, $value) {
		$this->_property[$name] = $value;
	}
	
	function save() {
		$this->saveData($this->_property['[model]'], $this->_property);
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_property['[model]']);
	}

	function export() {
		return "<infoproperty>\n".$this->exportData($this->_property['[model]'], $this->_property)."</infoproperty>\n";
	}

	static function import(array $propertydata = array(), $connection = NULL) {
		global $engine;

		$property = ProcessProperty::importData("ProcessInfoProperty", "CsProcessInfoProperty", $propertydata, (is_resource($connection)?$connection:$engine->getConnection()));
		
		if (($find = $property->getConnection()->execute('select * from '.$property->getConnection()->getTable($property->getProperty('[model]'))->getTableName().' where process_id = '.$property->getProperty('process_id').' and property_id = '.$property->getProperty('property_id'))->fetch()) != false) {
			$property->setProperty('id', $find['id']);
			$property->_id = $find['id'];
		}
		
		$property->setProperty('id', $property->save());
		
		return $property;
	}
}
?>