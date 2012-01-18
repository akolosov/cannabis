<?php

// $Id$

	class ProjectProperty extends Core {

		public $_property = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
			if (($id > 0) && ($owner <> NULL)) {

				parent::__construct($owner, $id, $owner->getConnection());

				if (is_null($options['data'])) {
					$property = $this->getConnection()->execute('select cs_project_property.*, cs_sign.name as signname, cs_property_type.name as typename from cs_project_property, cs_sign, cs_property_type where cs_project_property.sign_id = cs_sign.id and cs_project_property.type_id = cs_property_type.id and cs_project_property.id = '.$this->_id.' order by cs_project_property.id, cs_project_property.name')->fetch();
				} else {
					$property = $options['data'];
				}
				foreach ($property as $key => $data) {
					$this->_property[$key] = $data;
				}
				
				$this->_property['[model]']	= "CsProjectProperty";
			}
		}

		function getProperty($name) {
			return $this->_property[$name];
		}
	}
?>