<?php

// $Id$

	class ProjectInstanceProperty extends Core {

		public $_property = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
			if (($id > 0) && ($owner <> NULL)) {

				parent::__construct($owner, $id, $owner->getConnection());

				if (is_null($options['data'])) {
					$property = $this->getConnection()->execute('select * from project_instance_properties_list where id = '.$this->_id)->fetch();
				} else {
					$property = $options['data'];
				}

				foreach ($property as $key => $data) {
					$this->_property[$key] = $data;
				}

				if ((is_a($this->_owner, 'ProjectInstance')) or (is_a($this->_owner, 'ProjectInstanceWrapper'))) {
					$this->_property['[property]']	= $this->_owner->getProperty('[project]')->getProjectProperty($this->getProperty('name'));
				} else {
					$this->_property['[property]']	= new ProjectProperty($this, $this->getProperty('property_id'));
				}

				$this->_property['[value]']	= new PropertyValue($this, $this->getProperty('value_id'));
				$this->_property['[changed]']	= false;
				$this->_property['[model]']	= "CsProjectPropertyValue";
			}
		}

		function getProperty($name) {
			return $this->_property[$name];
		}

		function getPropertyValue() {
			return $this->_property['[value]']->getProperty('value');
		}
		
		function getPropertyMimeType() {
			return $this->_property['[value]']->getProperty('mime_type');
		}

		function getProjectProperty($name) {
			return $this->_property['[property]']->getProperty($name);
		}

		function setPropertyValue($value, $force = false) {
			if (($this->getProperty('sign_id') <> Constants::PROPERTY_SIGN_READONLY) or ($force)) {
				logRuntime('['.get_class($this).'.setPropertyValue->'.$this->getProperty('name').'] set property '.$this->getProperty('name').' value to '.(($value)?$value:'NULL').($force?" (forced)":""));
				$this->_property['[value]']->setProperty('value', $value, true);
				$this->_property['[changed]'] = true;
				$this->saveValue($force);
			} else {
				logRuntime('['.get_class($this).'.setPropertyValue->'.$this->getProperty('name').'] property '.$this->getProperty('name').' is readonly!');
			}
		}

		function setPropertyMimeType($value, $force = false) {
			if (($this->getProperty('sign_id') <> Constants::PROPERTY_SIGN_READONLY) or ($force)) {
				logRuntime('['.get_class($this).'.setPropertyMimetype->'.$this->getProperty('name').'] set property '.$this->getProperty('name').' mimetype to '.(($value)?$value:'NULL').($force?" (forced)":""));
				$this->_property['[value]']->setProperty('mime_type', $value, true);
				$this->_property['[changed]'] = true;
				$this->saveValue($force);
			} else {
				logRuntime('['.get_class($this).'.setPropertyMimeType->'.$this->getProperty('name').'] property '.$this->getProperty('name').' is readonly!');
			}
		}

		function save() {
			$this->saveData($this->_property['[model]'], $this->_property);
		}

		function saveValue($force = false) {
			if ((($this->getProperty('sign_id') <> Constants::PROPERTY_SIGN_READONLY) && ($this->getProperty('[changed]'))) or ($force)) {
				$this->_property['[value]']->save();
				$this->_property['[changed]'] = false;
			}
		}
		
		function saveAll() {
			$this->save();
			$this->saveValue();
		}

	}
?>