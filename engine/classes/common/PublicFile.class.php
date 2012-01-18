<?php

// $Id$

class PublicFile extends Core {

	public	$_file = array();

	function __construct($owner = NULL, $id = 0) {
		if ($owner <> NULL) {

			parent::__construct($owner, $id, $owner->getConnection());

			$file = $this->getConnection()->execute('select * from cs_public_file where id = '.$this->_id)->fetch();
			foreach ($file as $key => $data) {
				$this->_file[$key] = $data;
			}

			$this->_file['[model]'] = "CsPublicFile";
		}
	}

	function getProperty($name) {
		return $this->_file[$name];
	}

	function setProperty($name, $value) {
		$this->_file[$name] = $value;
	}

	function save() {
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_file['[model]']);
		return $this->saveData($this->_file['[model]'], $this->_file);
	}
}
?>