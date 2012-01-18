<?php

// $Id$

class DirectoryField extends Core {

	public	$_field = array();

	function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
		if ($owner <> NULL) {

			parent::__construct($owner, $id, $owner->getConnection());

			if (is_null($options['data'])) {
				if ($this->_id <> 0 ) {
					$field = $this->getConnection()->execute('select * from cs_directory_field where id = '.$this->_id)->fetch();
				} else {
					if (is_a($this->_owner, 'DirectoryInfo')) {
						$this->_record['directory_id'] = $this->_owner->getProperty('id');
					}
				}
			} else {
				$field = $options['data'];
			}
			foreach ($field as $key => $data) {
				$this->_field[$key] = $data;
			}

			$this->_field['[model]']	= "CsDirectoryField";

		}
	}

	function getProperty($name) {
		return $this->_field[$name];
	}

	function setProperty($name, $value) {
		$this->_field[$name] = $value;
	}

	function save() {
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_field['[model]']);
		return $this->saveData($this->_field['[model]'], $this->_field);
	}

	function export() {
		return "<field>\n".$this->exportData($this->_field['[model]'], $this->_field)."</field>\n";
	}

	static function import(array $fielddata = array(), $connection = NULL) {
		global $engine;

		$field = self::importData("DirectoryField", "CsDirectoryField", $fielddata, (is_resource($connection)?$connection:$engine->getConnection()));

		if (($find = $field->getConnection()->execute('select * from '.$field->getConnection()->getTable($field->getProperty('[model]'))->getTableName().' where name = \''.trim($field->getProperty('name')).'\' and directory_id = '.trim($field->getProperty('directory_id')))->fetch()) != false) {
			$field->setProperty('id', $find['id']);
			$field->_id = $find['id'];
		}
		$field->setProperty('id', $field->save());
		
		return $field;
	}
}
?>