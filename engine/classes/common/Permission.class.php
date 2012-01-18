<?php

// $Id$

	class Permission extends Core {

		public	$_permission = array();

		function __construct($owner = NULL, $id = 0) {
			if (($id > 0) && ($owner <> NULL)) {

				parent::__construct($owner, $id, $owner->getConnection());

				$permission = $this->getConnection()->execute('select * from cs_permission where id = '.$this->_id)->fetch();
				foreach ($permission as $key => $data) {
					$this->_permission[$key] = $data;
				}

				$this->_permission['[permissionlist]']	= new Collection($this);

				$permissionlist = $this->getConnection()->execute('select * from permissions_list where permission_id = '.$this->_id)->fetchAll();
				foreach ($permissionlist as $permission) {
					$this->_permission['[permissionlist]']->setElement($permission['modulename'], new ModulePermission($this, $this->getProperty('id'), array('data' => $permission)));
				}

				$this->_permission['[model]']		= "CsPermission";
			}
		}

		function getProperty($name) {
			return $this->_permission[$name];
		}
	}
?>