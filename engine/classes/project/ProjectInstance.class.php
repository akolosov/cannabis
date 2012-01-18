<?php

// $Id$

class ProjectInstance extends Core {

	public $_instance = array();

	function __construct($owner = NULL, $id = 0,  array $options = array('limit' => 0, 'workingonly' => true, 'ownedby' => NULL, 'onlyprocess' => NULL, 'data' => NULL)) {
		if (($id > 0) && ($owner <> NULL)) {

			parent::__construct($owner, $id, $owner->getConnection());

			if (is_null($options['data'])) {
				$instance = $this->getConnection()->execute('select * from projects_instances where id = '.$this->_id)->fetch();
			} else {
				$instance = $options['data'];
			}
			foreach ($instance as $key => $data) {
				$this->_instance[$key] = $data;
			}

			$this->_instance['[properties]']	= NULL;
			$this->_instance['[processes]']		= NULL;
			$this->_instance['[project]']		= new Project($this, $this->getProperty('project_id'));
			$this->_instance['[workingonly]']	= $options['workingonly'];
			$this->_instance['[onlyprocess]']	= $options['onlyprocess'];
			$this->_instance['[ownedby]']		= $options['ownedby'];
			$this->_instance['[limit]']			= $options['limit'];
			$this->_instance['[model]']			= "CsProjectInstance";

			if (is_a($this->_owner, 'Engine')) {
				$this->_instance['[security]']	= $this->_owner->getSecurity();
			}

			$this->initProperties();
			$this->initProcesses();
		}
	}

	function getProperty($name) {
		return $this->_instance[$name];
	}

	private function initProperties() {
		$this->_instance['[properties]']	= NULL;
		$this->_instance['[properties]']	= new Collection($this);

		logRuntime('['.get_class($this).'.initProperties->'.$this->getProperty('name').'] try to initialize properties');

		$properties = $this->getConnection()->execute('select * from project_instance_properties_list where instance_id = '.$this->_id)->fetchAll();
		foreach ($properties as $property) {
			$this->_instance['[properties]']->setElement($property['name'], new ProjectInstanceProperty($this, $property['id'], array('data' => $property)));
		}
		logRuntime('['.get_class($this).'.initProperties->'.$this->getProperty('name').'] properties initialized');
	}

	private function initProcesses() {
		$this->_instance['[processes]']		= NULL;
		$this->_instance['[processes]']		= new Collection($this);

		logRuntime('['.get_class($this).'.initProcesses->'.$this->getProperty('name').'] try to initialize processes');
		
		if ($this->getProperty('[ownedby]')) {
			$ownedby = ' and initiator_id = '.$this->getProperty('[ownedby]');
		} else {
			$ownedby = '';
		}

		if ($this->getProperty('[onlyprocess]')) {
			$onlyprocess = ' and id = '.$this->getProperty('[onlyprocess]');
		} else {
			$onlyprocess = '';
		}
		
		if ($this->getProperty('[workingonly]')) {
			$working = ' and (status_id = '.Constants::PROCESS_STATUS_IN_PROGRESS.' or status_id = '.Constants::PROCESS_STATUS_WAITING.' or status_id = '.Constants::PROCESS_STATUS_CHILD_IN_PROGRESS.' or status_id = '.Constants::PROCESS_STATUS_CHILD_WAITING.')';
		} else {
			$working = '';
		}
		
		$processes = $this->getConnection()->execute('select * from project_processes_instances_list where project_instance_id = '.$this->_id.($this->getProperty('[workingonly]')?' and (status_id = '.Constants::PROCESS_STATUS_IN_PROGRESS.' or status_id = '.Constants::PROCESS_STATUS_CHILD_IN_PROGRESS.')':'').$ownedby.$working.$onlyprocess.($this->getProperty('[limit]') > 0?' limit '.$this->getProperty('[limit]'):''))->fetchAll();
		foreach ($processes as $process) {
			$this->_instance['[processes]']->setElement($process['name']."-".$process['id'], new ProcessInstanceWrapper($this, $process['id'], array('ownedby' => $this->getProperty('[ownedby]'), 'data' => $process)));
		}
		logRuntime('['.get_class($this).'.initProcesses->'.$this->getProperty('name').'] processes initialized');
	}

	function reinitProcess($id) {
		return $this->getProperty('[processes]')->findElementByID($id)->reinitProcess();
	}
	
	function save() {
		$this->saveData($this->_instance['[model]'], $this->_instance);
	}

	private function saveProperties () {
		foreach ($this->getProperties() as $property) {
			$property->saveValue();
		}
	}

	private function saveProcesses () {
		foreach ($this->getProcesses() as $process) {
			$process->save();
		}
	}

	function saveAll() {
		$this->saveProperties();
		$this->saveProcesses();
		$this->save();
	}

	function getProperties() {
		return $this->_instance['[properties]']->getElements();
	}

	function getProcesses() {
		return $this->_instance['[processes]']->getElements();
	}

	function getSecurity() {
		return $this->_owner->getSecurity();
	}

	function getPropertyValue($name) {
		return $this->instance['[properties]']->getElement($name)->getPropertyValue();
	}

	function getPropertyMimeType($name) {
		return $this->instance['[properties]']->getElement($name)->getPropertyMimeType();
	}

	function getProjectProcess($name) {
		return $this->instance['[processes]']->getElement($name);
	}

	function getProjectProcesses() {
		return $this->instance['[processes]']->getElements();
	}

	function setPropertyValue($name, $value) {
		if ($this->propertyExists($name)) {
			logRuntime('['.get_class($this).'.setPropertyValue] set property '.$name.' value to '.(($value)?$value:'NULL'));
			$this->_instance['[properties]']->getElement($name)->setPropertyValue($value);
		} else {
			logRuntime('['.get_class($this).'.setPropertyValue] property '.$name.' not found! please add it first!');
		}
	}

	function setPropertyMimeType($name, $value) {
		if ($this->propertyExists($name)) {
			logRuntime('['.get_class($this).'.setPropertyMimeType] set property '.$name.' mimetype to '.(($value)?$value:'NULL'));
			$this->_instance['[properties]']->getElement($name)->setPropertyMimeType($value);
		} else {
			logRuntime('['.get_class($this).'.setPropertyMimeType] property '.$name.' not found! please add it first!');
		}
	}

	function addProperty(array $options = array('name' => '', 'description' => '', 'sign_id' => 1, 'type_id' => 1, 'default_value' => NULL, 'mime_type' => NULL, 'value' => NULL)) {
		if ($this->propertyExists($options['name'])) {
			// уже есть такое свойство
			if ($options['mime_type']) {
				$this->setPropertyMimeType($options['name'], $options['mime_type']);
			}
			if ($options['value']) {
				$this->setPropertyValue($options['name'], $options['value']);
			}
		} else {
			// такого свойства нет... добавляем
			$property = $this->getConnection()->getTable('CsProjectProperty')->create();
			$property['name']			= $options['name'];
			$property['description']	= $options['description'];
			$property['project_id']		= $this->getProperty('[project]')->getProperty('id');
			$property['sign_id']		= $options['sign_id'];
			$property['type_id']		= $options['type_id'];
			$property['default_value']	= $options['default_value'];
			$property->save();

			$propertyvalue = $this->getConnection()->getTable('CsPropertyValue')->create();
			$propertyvalue['mime_type']	= $options['mime_type'];
			$propertyvalue['value']		= ($options['value']?$options['value']:$options['default_value']);
			$propertyvalue->save();

			$propertyinstance = $this->getConnection()->getTable('CsProjectPropertyValue')->create();
			$propertyinstance['instance_id']	= $this->getProperty('id');
			$propertyinstance['property_id']	= $property['id'];
			$propertyinstance['value_id']		= $propertyvalue['id'];
			$propertyinstance->save();

			$this->initProperties();
		}
	}

	function propertyExists($name) {
		return $this->getProperty('[properties]')->elementExists($name);
	}

	function createProcessInstance($process_id, $initiator_id = 0, $parent_id = NULL) {
		if (preg_match("/[A-Za-zА-Яа-я]+/u", $initiator_id)) {
			$initiator_id = $this->getIDByName($initiator_id, 'CsAccount');
		}
		return $this->getConnection()->execute('select create_process_instance('.$this->getProperty('id').', '.$process_id.', '.($initiator_id >= 0?$initiator_id:USER_CODE).', '.$parent_id.');')->fetch();
	}

	private function actiateProcessInstance($process_instance_id) {
		return $this->getConnection()->execute('select activate_process_instance('.$process_instance_id.');')->fetch();
	}

	private function deactiateProcessInstance($process_instance_id) {
		return $this->getConnection()->execute('select deactivate_process_instance('.$process_instance_id.');')->fetch();
	}

	private function deleteProcessInstance($process_instance_id) {
		return $this->getConnection()->execute('select delete_process_instance('.$process_instance_id.');')->fetch();
	}

	private function terminateProcessInstance($process_instance_id) {
		return $this->getConnection()->execute('select terminate_process_instance('.$process_instance_id.');')->fetch();
	}

	private function errorProcessInstance($process_instance_id) {
		return $this->getConnection()->execute('select error_process_instance('.$process_instance_id.');')->fetch();
	}

	private function eraseProcessInstance($process_instance_id) {
		return $this->getConnection()->execute('select erase_process_instance('.$process_instance_id.');')->fetch();
	}
}
?>