<?php

// $Id$

class Role extends Core {

	public	$_role = array();

	function __construct($owner = NULL, $id = 0) {
		if (($id > 0) && ($owner <> NULL)) {

			parent::__construct($owner, $id, $owner->getConnection());

			$role = $this->getConnection()->execute('select * from cs_role where id = '.$this->_id)->fetch();
			foreach ($role as $key => $data) {
				$this->_role[$key] = $data;
			}

			$this->_role['[model]'] = "CsRole";
		}
	}

	function getProperty($name) {
		return $this->_role[$name];
	}

	function setProperty($name, $value) {
		$this->_role[$name] = $value;
	}

	function save() {
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_role['[model]']);
		return $this->saveData($this->_role['[model]'], $this->_role);
	}

	function export() {
		return "<roledata>\n".$this->exportData($this->_role['[model]'], $this->_role)."</roledata>\n";
	}

	static function import(array $roledata = array(), $connection = NULL) {
		global $engine;

		$role = self::importData("Role", "CsRole", $roledata, (is_resource($connection)?$connection:$engine->getConnection()));

		if (($find = $role->getConnection()->execute('select * from '.$role->getConnection()->getTable($role->getProperty('[model]'))->getTableName().' where name = \''.trim($role->getProperty('name')).'\'')->fetch()) != false) {
			$role->setProperty('id', $find['id']);
			$role->_id = $find['id'];
		}
		$role->setProperty('id', $role->save());
		
		return $role;
	}
}
?>