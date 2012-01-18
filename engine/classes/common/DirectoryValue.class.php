<?php

// $Id$

class DirectoryValue extends Core {

	public	$_value = array();

	function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
		if ($owner <> NULL) {

			parent::__construct($owner, $id, $owner->getConnection());

			if (is_null($options['data'])) {
				if ($this->_id <> 0) {
					$value = $this->getConnection()->execute('select cs_directory_value.*, cs_directory_field.name, cs_directory_field.caption from cs_directory_value, cs_directory_field where cs_directory_value.field_id = cs_directory_field.id and cs_directory_value.id = '.$this->_id)->fetch();
				} else {
					if (is_a($this->_owner, 'DirectoryRecord')) {
						$this->_value['record_id'] = $this->_owner->getProperty('id');
					}
				}
			} else {
				$value = $options['data'];
			}
			foreach ($value as $key => $data) {
				$this->_value[$key] = $data;
			}

			$this->_value['[model]']	= "CsDirectoryValue";

		}
	}

	function getProperty($name) {
		return $this->_value[$name];
	}

	function setProperty($name, $value) {
		$this->_value[$name] = $value;
	}

	function getMimeType() {
		return preg_replace('/^(.*)(\|)(.*)$/ui', '$1', $this->getProperty('mime_type'));
	}

	function getFileName() {
		return preg_replace('/^(.*)(\|)(.*)$/ui', '$3', $this->getProperty('mime_type'));
	}
	
	function save() {
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_value['[model]']);
		return $this->saveData($this->_value['[model]'], $this->_value);
	}

	function exportBlob() {
		if (isNotNULL($this->getProperty('mime_type'))) {
			$blob = DirectoryBlob::getBlob($this, $this->getProperty('id'), true);
			return $blob->export(); 
		}
		return "";
	}

	function export() {
		return "<value>\n".$this->exportData($this->_value['[model]'], $this->_value).$this->exportBlob()."</value>\n";
	}

	static function import(array $valuedata = array(), $connection = NULL) {
		global $engine;

		$value = self::importData("DirectoryValue", "CsDirectoryValue", $valuedata, (is_resource($connection)?$connection:$engine->getConnection()));

		if (($find = $value->getConnection()->execute('select * from '.$value->getConnection()->getTable($value->getProperty('[model]'))->getTableName().' where field_id = '.trim($value->getProperty('field_id')).' and record_id = '.trim($value->getProperty('record_id')))->fetch()) != false) {
			$value->setProperty('id', $find['id']);
			$value->_id = $find['id'];
		}
		$value->setProperty('id', $value->save());
		
		if (!is_null($valuedata['blob'])) {
			$blobdata = get_object_vars($valuedata['blob']);
			$blobdata['value_id'] = $value->getProperty('id');
			$blob = DirectoryBlob::import($blobdata, (is_resource($connection)?$connection:$engine->getConnection()));
		}
		
		return $value;
	}
}
?>