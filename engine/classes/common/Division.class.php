<?php

// $Id$

	class Division extends Core {

		public	$_division = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
			if ($owner <> NULL) {

				parent::__construct($owner, $id, $owner->getConnection());

				if (is_null($options['data'])) {
					$division = $this->getConnection()->execute('select * from divisions_tree where id = '.$this->_id)->fetch();
				} else {
					$division = $options['data'];
				}
				foreach ($division as $key => $data) {
					$this->_division[$key] = $data;
				}

				$this->_division['[model]'] = "CsDivision";
			}
		}

		function getProperty($name) {
			return $this->_division[$name];
		}
	}
?>