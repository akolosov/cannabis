<?php

// $Id$

class ProcessAction extends Core {

	public	$_action = array();

	function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
		if (($id > 0) && ($owner <> NULL)) {

			parent::__construct($owner, $id, $owner->getConnection());

			if (is_null($options['data'])) {
				$action = $this->getConnection()->execute('select * from process_actions_list where id = '.$this->_id)->fetch();
			} else {
				$action = $options['data'];
			}
			foreach ($action as $key => $data) {
				$this->_action[$key] = $data;
			}

			$this->_action['[role]']		= new ProcessRole($this, $this->getProperty('role_id'));
			$this->_action['[transports]']	= new Collection($this);
			$this->_action['[properties]']	= new Collection($this);
			$this->_action['[model]']		= "CsProcessAction";
			
			$this->initTransports();
			$this->initProperties();
		}
	}

	function getProperty($name) {
		return $this->_action[$name];
	}

	function setProperty($name, $value) {
		$this->_action[$name] = $value;
	}
	
	function getTransport($name) {
		return $this->_action['[transports]']->getElement($name);
	}

	function getTransports() {
		return $this->_action['[transports]']->getElements();
	}

	function getActionProperty($name) {
		return $this->_action['[properties]']->getElement($name);
	}

	function getActionProperties() {
		return $this->_action['[properties]']->getElements();
	}

	function initTransports() {
		$transports = $this->getConnection()->execute('select * from process_action_transports_list where action_id = '.$this->_id)->fetchAll();
		foreach ($transports as $transport) {
			$this->_action['[transports]']->setElement($transport['name'], new ProcessActionTransport($this, $transport['id'], array('data' => $transport)));
		}
	}

	function initProperties() {
		$properties = $this->getConnection()->execute('select * from process_action_properties_list where action_id = '.$this->_id.' order by npp')->fetchAll();
		foreach ($properties as $property) {
			$this->_action['[properties]']->setElement($property['name'], new ProcessActionProperty($this, $property['id'], array('data' => $property)));
		}
	}
	
	function save() {
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_action['[model]']);
		return $this->saveData($this->_action['[model]'], $this->_action);
	}

	function saveTransports() {
		foreach ($this->getTransports() as $transport) {
			$transport->save();
		}
	}
	
	function saveProperties() {
		foreach ($this->getActionProperties() as $property) {
			$property->save();
		}
	}

	function saveAll() {
		$this->saveTransports();
		$this->saveProperties();
		
		return $this->save();
	}
	
	function exportTransports() {
		$result = "<transports>\n";
		foreach ($this->getTransports() as $transport) {
			$result .= $transport->export();
		}
		return $result."</transports>\n";
	}

	function exportProperties() {
		$result = "<properties>\n";
		foreach ($this->getActionProperties() as $property) {
			$result .= $property->export();
		}
		return $result."</properties>\n";
	}
	
	function export() {
		return "<action>\n".$this->exportData($this->_action['[model]'], $this->_action).$this->exportTransports().$this->exportProperties()."</action>\n";
	}
	
	static function import(array $actiondata = array(), $properties = array(), $connection = NULL) {
		global $engine;

		$action = self::importData("ProcessAction", "CsProcessAction", $actiondata, (is_resource($connection)?$connection:$engine->getConnection()));

		if (($find = $action->getConnection()->execute('select * from '.$action->getConnection()->getTable($action->getProperty('[model]'))->getTableName().' where name = \''.trim($action->getProperty('name')).'\' and process_id = '.$action->getProperty('process_id'))->fetch()) != false) {
			$action->setProperty('id', $find['id']);
			$action->_id = $find['id'];
		}
		
		$action->setProperty('id', $action->save());
		
		foreach ($actiondata['transports']->transport as $actiontransportdata) {
			$actiontransportdata = get_object_vars($actiontransportdata);
			$actiontransportdata['action_id'] = $action->getProperty('id');
			$actiontransport = ProcessActionTransport::import($actiontransportdata, (is_resource($connection)?$connection:$engine->getConnection()));
		}

		foreach ($actiondata['properties']->property as $actionpropertydata) {
			$actionpropertydata = get_object_vars($actionpropertydata);
			$actionpropertydata['action_id'] = $action->getProperty('id');
			$actionpropertydata['property_id'] = $properties['['.$actionpropertydata['property_id'].']'];
			$actionproperty = ProcessActionProperty::import($actionpropertydata, (is_resource($connection)?$connection:$engine->getConnection()));
		}
	
		return $action;
	}
}
?>