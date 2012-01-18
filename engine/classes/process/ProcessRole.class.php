<?php

// $Id$

class ProcessRole extends Core {

	public $_role = array();

	function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
		if (($id > 0) && ($owner <> NULL)) {

			parent::__construct($owner, $id, $owner->getConnection());

			if (is_null($options['data'])) {
				$role = $this->getConnection()->execute('select * from process_roles where id = '.$this->_id)->fetch();
			} else {
				$role = $options['data'];
			}
			foreach ($role as $key => $data) {
				$this->_role[$key] = $data;
			}

			$this->_role['[role]']	= new Role($this, $this->getProperty('role_id')); 
			$this->_role['[model]']	= "CsProcessRole";
		}
	}

	function getProperty($name) {
		return $this->_role[$name];
	}

	function setProperty($name, $value) {
		$this->_role[$name] = $value;
	}

	function save() {
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('rolename').'] data saved to '.$this->_role['[model]']);
		if (isNotNULL($this->_role['[role]'])) {
			$this->_role['role_id'] = $this->_role['[role]']->save();
		}
		return $this->saveData($this->_role['[model]'], $this->_role);
	}

	function export() {
		return "<role>\n".$this->exportData($this->_role['[model]'], $this->_role).$this->_role['[role]']->export()."</role>\n";
	}

	static function import(array $roledata = array(), $connection = NULL) {
		global $engine;

		$role = self::importData("ProcessRole", "CsProcessRole", $roledata, (is_resource($connection)?$connection:$engine->getConnection()));

		$role->setProperty('[role]', Role::import(get_object_vars($roledata['roledata']), (is_resource($connection)?$connection:$engine->getConnection())));

		$role->setProperty('role_id', $role->getProperty('[role]')->getProperty('id'));
		
		if (($find = $role->getConnection()->execute('select * from '.$role->getConnection()->getTable($role->getProperty('[model]'))->getTableName().' where role_id = '.$role->getProperty('role_id').' and process_id = '.$role->getProperty('process_id'))->fetch()) != false) {
			$role->setProperty('id', $find['id']);
			$role->_id = $find['id'];
		}

		$role->setProperty('account_id', USER_CODE);
		$role->setProperty('id', $role->save());
		
		return $role;
	}
}
?>