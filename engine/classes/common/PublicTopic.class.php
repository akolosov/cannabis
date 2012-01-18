<?php

// $Id$

class PublicTopic extends Core {

	public	$_topic = array();

	function __construct($owner = NULL, $id = 0) {
		if ($owner <> NULL) {

			parent::__construct($owner, $id, $owner->getConnection());

			$topic = $this->getConnection()->execute('select * from cs_public_topic where id = '.$this->_id)->fetch();
			foreach ($topic as $key => $data) {
				$this->_topic[$key] = $data;
			}

			$this->_topic['[documents]']	= NULL;
			$this->_topic['[model]']		= "CsPublicTopic";
		}
	}

	function getProperty($name) {
		return $this->_topic[$name];
	}

	function setProperty($name, $value) {
		$this->_topic[$name] = $value;
	}

	function save() {
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_topic['[model]']);
		return $this->saveData($this->_topic['[model]'], $this->_topic);
	}

	function initDocuments() {
		$this->_topic['[documents]'] = NULL;
		$this->_topic['[documents]'] = new Collection($this);

		$documents = $this->getConnection()->execute('select * from public_documents_tree where topic_id = '.$this->_id)->fetchAll();
		foreach ($documents as $document) {
			$this->_document['[documents]']->setElement($document['id'].'_'.$document['parent_id'].'_'.$document['name'], new PublicDocument($this, $document['id'], array('data' => $document)));
		}
	}
}
?>