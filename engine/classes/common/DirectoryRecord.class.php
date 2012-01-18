<?php

// $Id$

class DirectoryRecord extends Core {

	public	$_record = array();

	function __construct($owner = NULL, $id = 0, $options = array('data' => NULL, 'valuedata' => NULL)) {
		if ($owner <> NULL) {

			parent::__construct($owner, $id, $owner->getConnection());

			if (is_null($options['data'])) {
				if ($this->_id <> 0 ) {
					$record = $this->getConnection()->execute('select * from cs_directory_record where id = '.$this->_id)->fetch();
				} else {
					if (is_a($this->_owner, 'DirectoryInfo')) {
						$this->_record['directory_id'] = $this->_owner->getProperty('id');
					}
				}
			} else {
				$record = $options['data'];
			}
			foreach ($record as $key => $data) {
				$this->_record[$key] = $data;
			}

			$this->_record['[model]']	= "CsDirectoryRecord";
			$this->_record['[values]']	= new Collection($this);
			
			if (($this->_id <> 0 ) or (isNotNULL($options['valuedata']))) {
				$this->initValues($options['valuedata']);
			}
		}
	}

	function getProperty($name) {
		return $this->_record[$name];
	}

	function setProperty($name, $value) {
		$this->_record[$name] = $value;
	}
	
	function getValues() {
		return $this->getProperty('[values]')->getElements();
	}

	function getValue($name) {
		return $this->getProperty('[values]')->getElement($name);
	}

	function initValues(array $data = array()) {
		if (isNotNULL($data)) {
			$fields = $this->getConnection()->execute('select * from cs_directory_field where directory_id = '.$this->getProperty('directory_id').' order by id, name')->fetchAll();
			foreach ($fields as $field) {
				$this->getProperty('[values]')->setElement($field['name'], new DirectoryValue($this, 0, array('data' => array('field_id' => $field['id'], 'record_id' => $this->getProperty('id'), 'value' => $data[$field['name']]))));
			}
		} else {
			$values = $this->getConnection()->execute('select cs_directory_value.*, cs_directory_field.name, cs_directory_field.caption from cs_directory_value, cs_directory_field where cs_directory_value.field_id = cs_directory_field.id and cs_directory_value.record_id = '.$this->getProperty('id'))->fetchAll();
			foreach ($values as $value) {
				$this->getProperty('[values]')->setElement($value['name'], new DirectoryValue($this, $value['id'], array('data' => $value)));
			}
		}
	}
	
	function save() {
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_record['[model]']);
		if (isNotNULL($this->_record['[values]'])) {
			foreach ($this->getValues() as $value) {
				$value->save();
			}
		}
		return $this->saveData($this->_record['[model]'], $this->_record);
	}

	function exportValues() {
		$result .= "<values>";
		if (isNotNULL($this->_record['[values]'])) {
			foreach ($this->getValues() as $value) {
				$result .= $value->export();
			}
		}
		return $result."</values>";
	}

	function export() {
		return "<record>\n".$this->exportData($this->_record['[model]'], $this->_record).$this->exportValues()."</record>\n";
	}

	static function import(array $recorddata = array(), $connection = NULL, array $fields = array()) {
		global $engine;

		$record = self::importData("DirectoryRecord", "CsDirectoryRecord", $recorddata, (is_resource($connection)?$connection:$engine->getConnection()));

		if (($find = $record->getConnection()->execute('select * from '.$record->getConnection()->getTable($record->getProperty('[model]'))->getTableName().' where id = '.trim($record->getProperty('__id__')).' and directory_id = '.trim($record->getProperty('directory_id')))->fetch()) != false) {
			$record->setProperty('id', $find['id']);
			$record->_id = $find['id'];
		}
		$record->setProperty('id', $record->save());

		foreach ($recorddata['values']->value as $valuedata) {
				$valuedata = get_object_vars($valuedata);
				$valuedata['field_id'] = $fields['['.$valuedata['field_id'].']'];
				$valuedata['record_id'] = $record->getProperty('id');
				$value = DirectoryValue::import($valuedata, (is_resource($connection)?$connection:$engine->getConnection()));
		}		
	
		return $record;
	}
}
?>