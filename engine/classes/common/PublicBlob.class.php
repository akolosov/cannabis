<?php

// $Id$

	class PublicBlob extends Core {

		public	$_blob = array();

		function __construct($owner = NULL, $id = 0, $full = false) {
			if (($id > 0) && ($owner <> NULL)) {

				parent::__construct($owner, $id, $owner->getConnection());

				$blob = $this->getConnection()->execute('select id, file_id'.($full?', blob':'').' from cs_public_blob where id = '.$this->_id)->fetch();

				foreach ($blob as $key => $data) {
					$this->_blob[$key] = $data;
				}

			}
			$this->_blob['[model]'] = "CsPublicBlob";
		}

		static function getBlob($owner = NULL, $file_id = 0, $full = false) {
			if (($file_id > 0) && ($owner <> NULL)) {
				$blob = $owner->getConnection()->execute('select id from cs_public_blob where file_id = '.$file_id)->fetch();

				$result = new PublicBlob($owner, $blob['id'], $full);
				$result->_owner = $owner;
				$result->_connection = $owner->getConnection();
				$result->_id = $blob['id'];
				$result->setProperty('file_id', $file_id);

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