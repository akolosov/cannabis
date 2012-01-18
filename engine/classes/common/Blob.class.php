<?php

// $Id$

	class Blob extends Core {

		public	$_blob = array();

		function __construct($owner = NULL, $id = 0, $full = false) {
			if (($id > 0) && ($owner <> NULL)) {

				parent::__construct($owner, $id, $owner->getConnection());

				$blob = $this->getConnection()->execute('select id, value_id'.($full?', blob':'').' from cs_blob where id = '.$this->_id)->fetch();

				foreach ($blob as $key => $data) {
					$this->_blob[$key] = $data;
				}

			}
			$this->_blob['[model]'] = "CsBlob";
		}

		static function getBlob($owner = NULL, $value_id = 0, $full = false) {
			if (($value_id > 0) && ($owner <> NULL)) {
				$blob = $owner->getConnection()->execute('select id from cs_blob where value_id = '.$value_id)->fetch();

				$result = new Blob($owner, $blob['id'], $full);
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

	}
?>