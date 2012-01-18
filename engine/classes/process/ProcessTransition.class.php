<?php

// $Id$

class ProcessTransition extends Core {

	public $_transition = array();

	function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
		if (($id > 0) && ($owner <> NULL)) {

			parent::__construct($owner, $id, $owner->getConnection());

			if (is_null($options['data'])) {
				$transition = $this->getConnection()->execute('select cs_process_transition.*, cs_from_action.name as fromname, cs_to_action.name as toname from cs_process_transition, cs_process_action as cs_from_action, cs_process_action as cs_to_action where cs_process_transition.from_action_id = cs_from_action.id and cs_process_transition.to_action_id = cs_to_action.id and cs_process_transition.id = '.$this->_id)->fetch();
			} else {
				$transition = $options['data'];
			}
			foreach ($transition as $key => $data) {
				$this->_transition[$key] = $data;
			}

			$this->_transition['[model]']	= "CsProcessTransition";
		}
	}

	function getProperty($name) {
		return $this->_transition[$name];
	}

	function setProperty($name, $value) {
		$this->_transition[$name] = $value;
	}
	
	function save() {
		$this->saveData($this->_transition['[model]'], $this->_transition);
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('fromname').'>'.$this->getProperty('toname').'] data saved to '.$this->_transition['[model]']);
	}

	function export() {
		return "<transition>\n".$this->exportData($this->_transition['[model]'], $this->_transition)."</transition>\n";
	}

	static function import(array $transitiondata = array(), $connection = NULL) {
		global $engine;

		$transition = ProcessTransition::importData("ProcessTransition", "CsProcessTransition", $transitiondata, (is_resource($connection)?$connection:$engine->getConnection()));
		
		if (($find = $transition->getConnection()->execute('select * from '.$transition->getConnection()->getTable($transition->getProperty('[model]'))->getTableName().' where process_id = '.$transition->getProperty('process_id').' and from_action_id = '.$transition->getProperty('from_action_id').' and to_action_id = '.$transition->getProperty('to_action_id'))->fetch()) != false) {
			$transition->setProperty('id', $find['id']);
			$transition->_id = $find['id'];
		}
		
		$transition->setProperty('id', $transition->save());
		
		return $transition;
	}
}
?>