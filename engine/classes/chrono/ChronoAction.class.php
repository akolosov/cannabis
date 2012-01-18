<?php

// $Id$

class ChronoAction extends Core {

	public $_action = array();

	function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
		if (($id > 0) && ($owner <> NULL)) {

			parent::__construct($owner, $id, $owner->getConnection());

			if (is_null($options['data'])) {
				$action = $this->getConnection()->execute('select * from chrono_process_instance_actions_list where id = '.$this->_id)->fetch();
			} else {
				$action = $options['data'];
			}
			foreach ($action as $key => $data) {
				$this->_action[$key] = $data;
			}

			$this->_action['[action]']	= new ProcessAction($this, $this->getProperty('action_id'));
			$this->_action['[form_file]']	= CACHE_PATH.DIRECTORY_SEPARATOR."form_".$this->getProperty('action_instance_id')."_".$this->getProperty('id')."_".USER_CODE.".php";
			$this->_action['[model]']	= "CsChronoAction";
		}
	}

	function getProperty($name) {
		return $this->_action[$name];
	}

	function getAction() {
		return $this->_action;
	}

	function view($print = false) {
		// сохранение формы в файл 
		$file = fopen($this->getProperty('[form_file]'), 'w+');
		flock($file, LOCK_EX);
		fwrite($file, $this->getFormManager()->generateForm(array('action' => $this, 'print' => $print, 'chrono' => true)));
		flock($file, LOCK_UN);
		fclose($file);
		logRuntime('['.get_class($this).'.execute->'.$this->getProperty('name').'] '.$this->getProperty('name').' action form cached');
	}

	function haveObjectProperty() {
		foreach ($this->getProperty('[action]')->getProperty('[properties]')->getElements() as $property) {
			if (($property->getProperty('type_id') == Constants::PROPERTY_TYPE_OBJECT) and ($property->getProperty('is_active') == Constants::TRUE)) {
				return true;
			}
		}
		return false;
	}

	function haveNonReadonlyProperty() {
		foreach ($this->getProperty('[action]')->getProperty('[properties]')->getElements() as $property) {
			if (($property->getProperty('is_readonly') == Constants::FALSE) and ($property->getProperty('is_hidden') == Constants::FALSE) and ($property->getProperty('is_active') == Constants::TRUE)) {
				return true;
			}
		}
		return false;
	}
	
}
?>