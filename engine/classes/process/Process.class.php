<?php

// $Id$

class Process extends Core {

	public	$_process = array();

	function __construct($owner = NULL, $id = 0, $options = array('full' => false, 'data' => NULL)) {
		if (($id > 0) && ($owner <> NULL)) {

			parent::__construct($owner, $id, $owner->getConnection());

			if (is_null($options['data'])) {
				$process = $this->getConnection()->execute('select * from processes_tree where id = '.$this->_id)->fetch();
			} else {
				$process = $options['data'];
			}
			foreach ($process as $key => $data) {
				$this->_process[$key] = $data;
			}

			$this->_process['[actions]']		= new Collection($this);
			$this->_process['[properties]']		= new Collection($this);
			$this->_process['[infoproperties]']	= new Collection($this);
			$this->_process['[transitions]']	= new Collection($this);
			$this->_process['[childs]']			= new Collection($this);
			$this->_process['[roles]']			= new Collection($this);
			$this->_process['[transports]']		= new Collection($this);
			$this->_process['[model]']			= "CsProcess";

			$this->initActions();
			$this->initProperties();
			$this->initInfoProperties();
			$this->initRoles();
			$this->initTransports();
			$this->initTransitions();
			if ($options['full']) {
				$this->initChilds();
			}
		}
	}

	function getProperty($name) {
		return $this->_process[$name];
	}

	function setProperty($name, $value) {
		$this->_process[$name] = $value;
	}
	
	function getProcessAction($name) {
		return $this->_process['[actions]']->getElement($name);
	}

	function getProcessChild($name) {
		return $this->_process['[childs]']->getElement($name);
	}

	function getProcessTransition($name) {
		return $this->_process['[transitions]']->getElement($name);
	}

	function getProcessTransport($name) {
		return $this->_process['[transports]']->getElement($name);
	}

	function getProcessProperty($name) {
		return $this->_process['[properties]']->getElement($name);
	}

	function getProcessInfoProperty($name) {
		return $this->_process['[infoproperties]']->getElement($name);
	}

	function getProcessRole($name) {
		return $this->_process['[roles]']->getElement($name);
	}
	
	function getProcessActions() {
		return $this->_process['[actions]']->getElements();
	}

	function getProcessChilds() {
		return $this->_process['[childs]']->getElements();
	}

	function getProcessTransitions() {
		return $this->_process['[transitions]']->getElements();
	}

	function getProcessTransitionsList($from = 0) {
		$result = array();

		foreach ($this->_process['[transitions]']->getElementsFromPropertyValue('from_action_id', $from) as $transition) {
			$result[] = $transition->getProperty('to_action_id');
		}

		return $result;
	}
	
	function getProcessTransports() {
		return $this->_process['[transports]']->getElements();
	}

	function getProcessProperties() {
		return $this->_process['[properties]']->getElements();
	}

	function getProcessInfoProperties() {
		return $this->_process['[infoproperties]']->getElements();
	}

	function getProcessRoles() {
		return $this->_process['[roles]']->getElements();
	}
	
	function initChilds() {
		$childs = $this->getConnection()->execute('select * from processes_tree where parent_id = '.$this->_id)->fetchAll();
		foreach ($childs as $child) {
			$this->_process['[childs]']->setElement($child['name'], new Process($this, $child['id'], array('data' => $child)));
		}
	}

	function initActions() {
		$actions = $this->getConnection()->execute('select * from process_actions_list where process_id = '.$this->_id.' order by type_id, id, name')->fetchAll();
		foreach ($actions as $action) {
			$this->_process['[actions]']->setElement($action['name'], new ProcessAction($this, $action['id'], array('data' => $action)));
		}
	}

	function initTransitions() {
		$transitions = $this->getConnection()->execute('select * from process_transitions where process_id = '.$this->_id)->fetchAll();
		foreach ($transitions as $transition) {
			$this->_process['[transitions]']->setElement($transition['fromname']."-".$transition['toname']."-".$transition['id'], new ProcessTransition($this, $transition['id'], array('data' => $transition)));
		}
	}

	function initProperties() {
		$properties = $this->getConnection()->execute('select * from process_properties_list where process_id = '.$this->_id.' order by id')->fetchAll();
		foreach ($properties as $property) {
			$this->_process['[properties]']->setElement($property['name'], new ProcessProperty($this, $property['id'], array('data' => $property)));
		}
	}

	function initInfoProperties() {
		$properties = $this->getConnection()->execute('select * from process_info_properties_list where process_id = '.$this->_id.' order by id')->fetchAll();
		foreach ($properties as $property) {
			$this->_process['[infoproperties]']->setElement($property['name'], new ProcessInfoProperty($this, $property['id'], array('data' => $property)));
		}
	}
	
	function initRoles() {
		$roles = $this->getConnection()->execute('select * from process_roles where process_id = '.$this->_id)->fetchAll();
		foreach ($roles as $role) {
			$this->_process['[roles]']->setElement($role['rolename'], new ProcessRole($this, $role['id'], array('data' => $role)));
		}
	}

	function initTransports() {
		$transports = $this->getConnection()->execute('select * from process_transports_list where process_id = '.$this->_id)->fetchAll();
		foreach ($transports as $transport) {
			$this->_process['[transports]']->setElement($transport['name'].'-'.$transport['id'].'-'.$transport['eventname'], new ProcessTransport($this, $transport['id'], array('data' => $transport)));
		}
	}

	function save() {
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_process['[model]']);
		return $this->saveData($this->_process['[model]'], $this->_process);
	}

	function saveProperties () {
		foreach ($this->getProcessProperties() as $property) {
			$property->save();
		}
	}

	function saveInfoProperties () {
		foreach ($this->getProcessInfoProperties() as $property) {
			$property->save();
		}
	}
	
	function saveActions () {
		foreach ($this->getProcessActions() as $action) {
			$action->save();
		}
	}

	function saveTransitions () {
		foreach ($this->getProcessTransitions() as $transition) {
			$transition->save();
		}
	}
	
	function saveTransports () {
		foreach ($this->getProcessTransports() as $transport) {
			$transport->save();
		}
	}
	
	function saveRoles () {
		foreach ($this->getProcessRoles() as $role) {
			$role->save();
		}
	}

	function exportProperties () {
		$result = "<properties>\n";
		foreach ($this->getProcessProperties() as $property) {
			$result .= $property->export();
		}
		return $result."</properties>\n";
	}

	function exportInfoProperties () {
		$result = "<infoproperties>\n";
		foreach ($this->getProcessInfoProperties() as $property) {
			$result .= $property->export();
		}
		return $result."</infoproperties>\n";
	}

	function exportActions () {
		$result = "<actions>\n";
		foreach ($this->getProcessActions() as $action) {
			$result .= $action->export();
		}
		return $result."</actions>\n";
	}

	function exportTransitions () {
		if ($this->getProperty('[transitions]')->isEmpty()) {
			$this->initTransitions();
		}
		$result = "<transitions>\n";
		foreach ($this->getProcessTransitions() as $transition) {
			$result .= $transition->export();
		}
		return $result."</transitions>\n";
	}

	function exportTransports () {
		$result = "<transports>\n";
		foreach ($this->getProcessTransports() as $transport) {
			$result .= $transport->export();
		}
		return $result."</transports>\n";
	}
	
	function exportRoles () {
		$result = "<roles>\n";
		foreach ($this->getProcessRoles() as $role) {
			$result .= $role->export();
		}
		return $result."</roles>\n";
	}

	function exportChilds () {
		$result = "<childs>\n";
		foreach ($this->getProcessChilds() as $child) {
			$result .= $child->export();
		}
		return $result."</childs>\n";
	}
	
	function saveAll() {
		$this->saveProperties();
		$this->saveInfoProperties();
		$this->saveRoles();
		$this->saveActions();
		$this->saveTransitions();
		$this->saveTransports();

		logRuntime('['.get_class($this).'.saveAll->'.$this->getProperty('name').'] all data saved');
	
		return $this->save();

	}
	
	function export() {
		return "\n<cannabis>\n<process>\n".$this->exportData($this->_process['[model]'], $this->_process)."</process>\n".$this->exportRoles().$this->exportProperties().$this->exportInfoProperties().$this->exportActions().$this->exportTransitions().$this->exportTransports().$this->exportChilds()."</cannabis>\n";
	}

	static function import($filename = NULL, $connection = NULL) {
		global $engine;

		if (file_exists($filename)) {
			logRuntime('['.get_class(self).'.import] import process from "'.$filename.'"');
			
			$document = simplexml_load_file($filename);

			$processdata = get_object_vars($document->process);

			logRuntime('['.get_class(self).'.import] import process "'.$processdata['name'].'" data');

			$process = self::importData("Process", "CsProcess", $processdata, (is_resource($connection)?$connection:$engine->getConnection()));

			if (($find = $process->getConnection()->execute('select * from '.$process->getConnection()->getTable($process->getProperty('[model]'))->getTableName().' where name = \''.trim($process->getProperty('name')).'\' and version = float4('.$process->getProperty('version').')')->fetch()) != false) {
				logRuntime('['.get_class(self).'.import] process "'.$processdata['name'].'" already exists! replacing...');
				$process->setProperty('id', $find['id']);
				$process->_id = $find['id'];
			}
			
			$process->setProperty('created_at', strftime("%Y-%m-%d %H:%M:%S", time()));
			if ($process->getProperty('is_active') == 1) {
				$process->setProperty('activated_at', strftime("%Y-%m-%d %H:%M:%S", time()));
			}
			$process->setProperty('author_id', USER_CODE);
			$process->setProperty('id', $process->save());

			logRuntime('['.get_class(self).'.import] import process roles data');
			foreach ($document->roles->role as $roledata) {
				$roledata = get_object_vars($roledata);
				$roledata['process_id'] = $process->getProperty('id');

				logRuntime('['.get_class(self).'.import] import process role "'.$roledata['name'].'" data');
				$role = ProcessRole::import($roledata, (is_resource($connection)?$connection:$engine->getConnection()));

				$roles['['.$role->getProperty('__id__').']'] = $role->getProperty('id'); 
			}

			logRuntime('['.get_class(self).'.import] import process properties data');
			foreach ($document->properties->property as $propertydata) {
				$propertydata = get_object_vars($propertydata);
				$propertydata['process_id'] = $process->getProperty('id');

				logRuntime('['.get_class(self).'.import] import process property "'.$propertydata['name'].'" data');
				$property = ProcessProperty::import($propertydata, (is_resource($connection)?$connection:$engine->getConnection()));
				
				$properties['['.$property->getProperty('__id__').']'] = $property->getProperty('id'); 
			}

			logRuntime('['.get_class(self).'.import] import process info properties data');
			foreach ($document->infoproperties->infoproperty as $infopropertydata) {
				$infopropertydata = get_object_vars($infopropertydata);
				$infopropertydata['process_id'] = $process->getProperty('id');
				$infopropertydata['property_id'] = $properties['['.$infopropertydata['property_id'].']'];

				$infoproperty = ProcessInfoProperty::import($infopropertydata, (is_resource($connection)?$connection:$engine->getConnection()));
			}

			logRuntime('['.get_class(self).'.import] import process actions data');
			foreach ($document->actions->action as $actiondata) {
				$actiondata = get_object_vars($actiondata);
				$actiondata['process_id'] = $process->getProperty('id');
				$actiondata['role_id'] = $roles['['.$actiondata['role_id'].']'];
				
				$ta = $actiondata['true_action_id']; $fa = $actiondata['false_action_id'];
				$actiondata['true_action_id'] = NULL; $actiondata['false_action_id'] = NULL;
				
				logRuntime('['.get_class(self).'.import] import process action "'.$actiondata['name'].'" data');
				$action = ProcessAction::import($actiondata, $properties, (is_resource($connection)?$connection:$engine->getConnection()));

				$actions['['.$action->getProperty('__id__').']'] = $action->getProperty('id');
				$trueactions['['.$action->getProperty('id').']'] = $ta;
				$falseactions['['.$action->getProperty('id').']'] = $fa;
				
			}
			
			logRuntime('['.get_class(self).'.import] import process transitions data');
			foreach ($document->transitions->transition as $transitiondata) {
				$transitiondata = get_object_vars($transitiondata);
				$transitiondata['process_id'] = $process->getProperty('id');
				$transitiondata['from_action_id'] = $actions['['.$transitiondata['from_action_id'].']'];
				$transitiondata['to_action_id'] = $actions['['.$transitiondata['to_action_id'].']'];

				$transition = ProcessTransition::import($transitiondata, (is_resource($connection)?$connection:$engine->getConnection()));
			}

			logRuntime('['.get_class(self).'.import] import process transports data');
			foreach ($document->transports->transport as $transportdata) {
				$transportdata = get_object_vars($transportdata);
				$transportdata['process_id'] = $process->getProperty('id');
				
				$transport = ProcessTransport::import($transportdata, (is_resource($connection)?$connection:$engine->getConnection()));
			}
		}

		$process->_process['[actions]']			= new Collection($process);
		$process->_process['[properties]']		= new Collection($process);
		$process->_process['[infoproperties]']	= new Collection($process);
		$process->_process['[transitions]']		= new Collection($process);
		$process->_process['[childs]']			= new Collection($process);
		$process->_process['[roles]']			= new Collection($process);
		$process->_process['[transports]']		= new Collection($process);

		$process->initActions();
		foreach ($process->getProcessActions() as $action) {
			$action->setProperty('true_action_id', $actions['['.$trueactions['['.$action->getProperty('id').']'].']'], true);
			$action->setProperty('false_action_id', $actions['['.$falseactions['['.$action->getProperty('id').']'].']'], true);
		}

		$process->initProperties();
		$process->initInfoProperties();
		$process->initRoles();
		$process->initTransports();
		$process->initTransitions();
		$process->initChilds();

		$process->saveAll();
		logRuntime('['.get_class(self).'.import] import process done');
		
		return $process;
	}

	function graph() {
		// TODO: Process::graph написать надо бы...
	}
}
?>