<?php

// $Id$

	class Post extends Core {

		public	$_post = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
			if (($id > 0) && ($owner <> NULL)) {

				parent::__construct($owner, $id, $owner->getConnection());

				if (is_null($options['data'])) {
					$post = $this->getConnection()->execute('select * from cs_post where id = '.$this->_id)->fetch();
				} else {
					$post = $options['data'];
				}
				foreach ($post as $key => $data) {
					$this->_post[$key] = $data;
				}

				$this->_post['[model]'] = "CsPost";
			}
		}

		function getProperty($name) {
			return $this->_post[$name];
		}
	}
?>