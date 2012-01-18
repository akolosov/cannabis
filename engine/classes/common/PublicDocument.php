<?php

// $Id$

class PublicDocument extends Core {

	public	$_document = array();

	function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
		if ($owner <> NULL) {

			parent::__construct($owner, $id, $owner->getConnection());

			if (is_null($options['data'])) {
				$document = $this->getConnection()->execute('select * from public_documents_tree where id = '.$this->_id)->fetch();
			} else {
				$document = $options['data'];
			}
			foreach ($document as $key => $data) {
				$this->_document[$key] = $data;
			}

			$this->_document['[model]'] = "CsPublicDocument";
		}
	}

	function getProperty($name) {
		return $this->_document[$name];
	}

	function setProperty($name, $value) {
		$this->_document[$name] = $value;
	}

	function save() {
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_document['[model]']);
		return $this->saveData($this->_document['[model]'], $this->_document);
	}
}
?>