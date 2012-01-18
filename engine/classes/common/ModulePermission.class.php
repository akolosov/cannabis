<?php

// $Id$

	class ModulePermission extends Core {

		public	$_modulepermission = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
			if (($id > 0) && ($owner <> NULL)) {

				parent::__construct($owner, $id, $owner->getConnection());

				if (is_null($options['data'])) {
					$modulepermission = $this->getConnection()->execute('select * from permissions_list where id = '.$this->_id)->fetch();
				} else {
					$modulepermission = $options['data'];
				}
				foreach ($modulepermission as $key => $data) {
					$this->_modulepermission[$key] = $data;
				}

				$this->_modulepermission['[module]']	= new Module($this, $this->getProperty('module_id'));
				$this->_modulepermission['[model]']	= "CsPermissionList";
			}
		}

		function getProperty($name) {
			return $this->_action[$name];
		}
	}
?>