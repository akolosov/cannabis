<?php

// $Id$

	class DirectoryBlob extends Core {

		public	$_blob = array();

		function __construct($owner = NULL, $id = 0, $full = false) {
			if (($id > 0) && ($owner <> NULL)) {

				parent::__construct($owner, $id, $owner->getConnection());

				$blob = $this->getConnection()->execute('select id, value_id'.($full?', blob':'').' from cs_directory_blob where id = '.$this->_id)->fetch();

				foreach ($blob as $key => $data) {
					$this->_blob[$key] = $data;
				}

			}
			$this->_blob['[model]'] = "CsDirectoryBlob";
		}

		static function getBlob($owner = NULL, $value_id = 0, $full = false) {
			if (($value_id > 0) && ($owner <> NULL)) {
				$blob = $owner->getConnection()->execute('select id from cs_directory_blob where value_id = '.$value_id)->fetch();
				
				$result = new DirectoryBlob($owner, $blob['id'], $full);
				$result->_owner = $owner;
				$result->_connection = $owner->getConnection();
				$result->_id = $blob['id'];
				$result->setProperty('value_id', $value_id);
				
				return $result; 
			}
		}
		
		function getProperty($name) {
			return $this->_blob[$name];
		}

		function setProperty($name, $value) {
			$this->_blob[$name] = $value;
		}

		function save() {
			logRuntime('['.get_class($this).'.save->ID_'.$this->getProperty('id').'] save data to '.$this->_blob['[model]']);
			$this->saveData($this->_blob['[model]'], $this->_blob);
		}
	
		function export() {
			return "<blob>\n".$this->exportData($this->_blob['[model]'], $this->_blob)."</blob>\n";
		}
	
		static function import(array $blobdata = array(), $connection = NULL) {
			global $engine;
	
			$blob = self::importData("DirectoryBlob", "CsDirectoryBlob", $blobdata, (is_resource($connection)?$connection:$engine->getConnection()));
	
			if (($find = $blob->getConnection()->execute('select * from '.$blob->getConnection()->getTable($blob->getProperty('[model]'))->getTableName().' where value_id = '.$blob->getProperty('value_id').' and id = '.$blob->getProperty('__id__'))->fetch()) != false) {
				$blob->setProperty('id', $find['id']);
				$blob->_id = $find['id'];
			}
			$blob->setProperty('id', $blob->save());
			
			return $blob;
		}	
	}
?>