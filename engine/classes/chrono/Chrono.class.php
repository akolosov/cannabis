<?php

// $Id$

class Chrono extends Core { // экземпляр процесса

	public $_instance = array();

	function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
		if (($id > 0) && ($owner <> NULL)) {

			parent::__construct($owner, $id, $owner->getConnection());

			if (is_null($options['data'])) {
				$instance = $this->getConnection()->execute('select * from chrono_process_instances_list where id = '.$this->_id)->fetch();
			} else {
				$instance = $options['data'];
			}
			foreach ($instance as $key => $data) {
				$this->_instance[$key] = $data;
			}

			$this->_instance['[actions]']			= NULL;
			$this->_instance['[properties]']		= NULL;
			$this->_instance['[infoproperties]']	= NULL;
			$this->_instance['[model]']				= "CsChrono";

			$this->initActions();
			$this->initProperties();
			$this->initInfoProperties();
		}
	}

	function getProperty($name) {
		return $this->_instance[$name];
	}

	private function initActions() {
		// инициализация действий экземпляра процесса
		$this->_instance['[actions]'] = NULL;
		$this->_instance['[actions]'] = new Collection($this);

		$actions = $this->getConnection()->execute('select * from chrono_process_instance_actions_list where chrono_id = '.$this->_id.' order by npp')->fetchAll();
		foreach ($actions as $action) {
			$this->_instance['[actions]']->setElement($action['name'], new ChronoAction($this, $action['id'], array('data' => $action)));
		}

		logRuntime('['.get_class($this).'.initActions->'.$this->getProperty('name').'] actions initialized');
	}

	private function initProperties() {
		// инициализация свойств экземпляра процесса
		$this->_instance['[properties]'] = NULL;
		$this->_instance['[properties]'] = new Collection($this);

		$properties = $this->getConnection()->execute('select * from chrono_process_instance_properties_list where chrono_id = '.$this->_id.' order by id, name, type_id')->fetchAll();
		foreach ($properties as $property) {
			$this->_instance['[properties]']->setElement($property['name'], new ChronoProperty($this, $property['id'], array('data' => $property)));
		}

		logRuntime('['.get_class($this).'.initProperties->'.$this->getProperty('name').'] properties initialized');
	}

	private function initInfoProperties() {
		// инициализация информационных свойств экземпляра процесса
		$this->_instance['[infoproperties]'] = NULL;
		$this->_instance['[infoproperties]'] = new Collection($this);

		$properties = $this->getConnection()->execute('select * from process_info_properties_list where process_id = '.$this->getProperty('process_id').' order by id, name, type_id')->fetchAll();
		foreach ($properties as $property) {
			$this->_instance['[infoproperties]']->setElement($property['name'], $this->getProperty('[properties]')->findElementByName($property['name']));
		}

		logRuntime('['.get_class($this).'.initInfoProperties->'.$this->getProperty('name').'] info properties initialized');
	}

	function getPropertyValue($name) {
		return $this->_instance['[properties]']->getElement($name)->getPropertyValue();
	}

	function getPropertyMimeType($name) {
		return $this->_instance['[properties]']->getElement($name)->getPropertyMimeType();
	}

	function getPropertyFileName($name) {
		return $this->_instance['[properties]']->getElement($name)->getPropertyFileName();
	}

	function getProperties() {
		return $this->_instance['[properties]']->getElements();
	}

	function getInfoProperties() {
		return $this->_instance['[infoproperties]']->getElements();
	}

	private function getInfoAction() {
		foreach ($this->getProperty('[actions]')->getElements() as $action) {
			if ($action->getProperty('type_id') == Constants::ACTION_TYPE_INFO) {
				return $action;
			}
		}

		return NULL;
	}

	function view($print = false) {
		// просмотр процесса если есть действие типа Информация
		$action = $this->getInfoAction();
		if (isNotNULL($action)) {
			$action->view($print);
			include_once($action->getProperty('[form_file]'));
			unlink($action->getProperty('[form_file]'));
		}
	}

	function propertyExists($name) {
		return $this->getProperty('[properties]')->elementExists($name);
	}

}
?>