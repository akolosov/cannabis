<?php

// $Id$

	class Module extends Core {

		public	$_module = array();

		function __construct($owner = NULL, $id = 0) {
			if (($id > 0) && ($owner <> NULL)) {

				parent::__construct($owner, $id, $owner->getConnection());

				$module = $this->getConnection()->execute('select * from modules_tree where id = '.$this->_id)->fetch();
				foreach ($module as $key => $data) {
					$this->_module[$key] = $data;
				}

				$this->_module['[model]'] = "CsModule";
			}
		}

		function getProperty($name) {
			return $this->_module[$name];
		}
	}
?>