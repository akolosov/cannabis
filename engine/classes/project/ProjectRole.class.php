<?php

// $Id$

	class ProjectRole extends Core {

		public $_role = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
			if (($id > 0) && ($owner <> NULL)) {

				parent::__construct($owner, $id, $owner->getConnection());

				if (is_null($options['data'])) {
					$role = $this->getConnection()->execute('select * from project_roles where id = '.$this->_id)->fetch();
				} else {
					$role = $options['data'];
				}
				foreach ($role as $key => $data) {
					$this->_role[$key] = $data;
				}
				
				$this->_role['[model]']	= "CsProjectRole";
			}
		}

		function getProperty($name) {
			return $this->_role[$name];
		}
		
	}
?>