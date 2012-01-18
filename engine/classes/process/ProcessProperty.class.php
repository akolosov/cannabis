<?php

// $Id$

class ProcessProperty extends Core {

	public $_property		= array();

	function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
		if (($id > 0) && ($owner <> NULL)) {

			parent::__construct($owner, $id, $owner->getConnection());

			if (is_null($options['data'])) {
				$property = $this->getConnection()->execute('select * from process_properties_list where id = '.$this->_id.' order by id, name')->fetch();
			} else {
				$property = $options['data'];
			}
			foreach ($property as $key => $data) {
				$this->_property[$key] = $data;
			}

			if (($this->getProperty('is_list') == Constants::TRUE) and (isNotNULL($this->getProperty('directory_id')))) {
				$this->_property['[directory]']	= new DirectoryInfo($this, $this->getProperty('directory_id'));
			} else {
				$this->_property['[directory]']	= NULL;
			}
			$this->_property['[model]']	= "CsProcessProperty";
		}
	}

	function getProperty($name) {
		return $this->_property[$name];
	}

	function setProperty($name, $value) {
		$this->_property[$name] = $value;
	}
	
	function save() {
		if (isNotNULL($this->_property['[directory]'])) {
			$this->_property['directory_id'] = $this->_property['[directory]']->save();
		}
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_property['[model]']);
		return $this->saveData($this->_property['[model]'], $this->_property);
	}
	
	function exportDirectory() {
		if (isNotNULL($this->_property['[directory]'])) {
			return $this->_property['[directory]']->export();
		} else {
			return ''; 
		}
	}

	function export() {
		return "<property>\n".$this->exportData($this->_property['[model]'], $this->_property).$this->exportDirectory()."</property>\n";
	}
	
	static function import(array $propertydata = array(), $connection = NULL) {
		global $engine;

		$property = self::importData("ProcessProperty", "CsProcessProperty", $propertydata, (is_resource($connection)?$connection:$engine->getConnection()));

		if (($property->getProperty('is_list') == Constants::TRUE) and (isNotNULL(get_object_vars($propertydata['directory'])))) {
			$property->setProperty('[directory]', DirectoryInfo::import(get_object_vars($propertydata['directory']), (is_resource($connection)?$connection:$engine->getConnection())));
			$property->setProperty('directory_id', $property->getProperty('[directory]')->getProperty('id'));
		} else {
			$property->setProperty('[directory]', NULL);
			$property->setProperty('directory_id', NULL);
			$property->setProperty('is_list', false);
			$property->setProperty('is_name_as_value', false);
		}
		
		if (($find = $property->getConnection()->execute('select * from '.$property->getConnection()->getTable($property->getProperty('[model]'))->getTableName().' where name = \''.trim($property->getProperty('name')).'\' and process_id = '.$property->getProperty('process_id'))->fetch()) != false) {
			$property->setProperty('id', $find['id']);
			$property->_id = $find['id'];
		}
		
		$property->setProperty('id', $property->save());

		return $property;
	}
}
?>