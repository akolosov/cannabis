<?php

// $Id$

class DirectoryInfo extends Core {

	public	$_directory = array();

	function __construct($owner = NULL, $id = 0) {
		if (($id > 0) && ($owner <> NULL)) {

			parent::__construct($owner, $id, $owner->getConnection());

			$directory = $this->getConnection()->execute('select * from cs_directory where id = '.$this->_id)->fetch();
			foreach ($directory as $key => $data) {
				$this->_directory[$key] = $data;
			}

			$this->_directory['[fields]']	= new Collection($this);
			$this->_directory['[records]']	= new Collection($this);
			$this->_directory['[model]']	= "CsDirectory";

			if ($this->getProperty('custom') == Constants::TRUE) {
				$this->initFields();
				$this->initRecords();
			}
		}
	}

	function getProperty($name) {
		return $this->_directory[$name];
	}

	function setProperty($name, $value) {
		$this->_directory[$name] = $value;
	}

	function getFields() {
		return $this->getProperty('[fields]')->getElements();
	}

	function getField($name) {
		return $this->getProperty('[fields]')->getElement($name);
	}
	
	function getRecords() {
		return $this->getProperty('[records]')->getElements();
	}

	function getRecord($name) {
		return $this->getProperty('[records]')->getElement($name);
	}

	function reinitDirectory() {
		if ($this->getProperty('custom') == Constants::TRUE) {
			$this->_directory['[fields]']	= NULL;
			$this->_directory['[records]']	= NULL;

			$this->_directory['[fields]']	= new Collection($this);
			$this->_directory['[records]']	= new Collection($this);
			
			$this->initFields();
			$this->initRecords();
		}
	}

	function haveObjectFields() {
		foreach ($this->getFields() as $field) {
			if ($field->getProperty('type_id') == Constants::PROPERTY_TYPE_OBJECT) {
				return true;
			}
		}
		return false;
	}

	function save() {
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_directory['[model]']);
		if (isNotNULL($this->_directory['[fields]'])) {
			foreach ($this->getFields() as $field) {
				$field->save();
			}
		}
		if (isNotNULL($this->_directory['[records]'])) {
			foreach ($this->getRecords() as $record) {
				$record->save();
			}
		}
		return $this->saveData($this->_directory['[model]'], $this->_directory);
	}

	function initFields() {
		$fields = $this->getConnection()->execute('select * from cs_directory_field where directory_id = '.$this->getProperty('id').' order by id, name')->fetchAll();
		foreach ($fields as $field) {
			$this->_directory['[fields]']->setElement($field['name'], new DirectoryField($this, $field['id'], array('data' => $field)));
		}
	}

	function initRecords() {
		$records = $this->getConnection()->execute('select * from cs_directory_record where directory_id = '.$this->getProperty('id').' order by id')->fetchAll();
		foreach ($records as $record) {
			$this->_directory['[records]']->setElement($record['id'], new DirectoryRecord($this, $record['id'], array('data' => $record)));
		}
	}
	
	function exportFields() {
		$result = "<fields>\n";
		if (isNotNULL($this->_directory['[fields]'])) {
			foreach ($this->getFields() as $field) {
				$result .= $field->export();
			}
		}
		return $result."</fields>\n";
	}
	
	function exportRecords() {
		$result = "<records>\n";
		if (isNotNULL($this->_directory['[records]'])) {
			foreach ($this->getRecords() as $record) {
				$result .= $record->export();
			}
		}
		return $result."</records>\n";
	}
	
	function export() {
		return "<directory>\n".$this->exportData($this->_directory['[model]'], $this->_directory).$this->exportFields().$this->exportRecords()."</directory>\n";
	}

	static function import(array $directorydata = array(), $connection = NULL) {
		global $engine;

		$directory = self::importData("DirectoryInfo", "CsDirectory", $directorydata, (is_resource($connection)?$connection:$engine->getConnection()));

		if (($find = $directory->getConnection()->execute('select * from '.$directory->getConnection()->getTable($directory->getProperty('[model]'))->getTableName().' where name = \''.trim($directory->getProperty('name')).'\' and id = '.trim($directory->getProperty('__id__')))->fetch()) != false) {
			$directory->setProperty('id', $find['id']);
			$directory->_id = $find['id'];
		}

		$directory->setProperty('id', $directory->save());

		foreach ($directorydata['fields']->field as $fielddata) {
				$fielddata = get_object_vars($fielddata);
				$fielddata['directory_id'] = $directory->getProperty('id');
				$field = DirectoryField::import($fielddata, (is_resource($connection)?$connection:$engine->getConnection()));
				$fields['['.$field->getProperty('__id__').']'] = $field->getProperty('id');
		}		

		foreach ($directorydata['records']->record as $recorddata) {
				$recorddata = get_object_vars($recorddata);
				$recorddata['directory_id'] = $directory->getProperty('id');
				$record = DirectoryRecord::import($recorddata, (is_resource($connection)?$connection:$engine->getConnection()), $fields);
		}		
		
		return $directory;
	}
}
?>