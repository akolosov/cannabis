<?php

// $Id$

class ProcessInstance extends Core { // экземпляр процесса

	public $_instance = array();

	function __construct($owner = NULL, $id = 0, $options = array('ownedby' => NULL, 'data' => NULL)) {
		if (($id > 0) && ($owner <> NULL)) {

			parent::__construct($owner, $id, $owner->getConnection());

			if (is_null($options['data'])) {
				$instance = $this->getConnection()->execute('select * from process_instances_list where id = '.$this->_id)->fetch();
			} else {
				$instance = $options['data'];
			}
			foreach ($instance as $key => $data) {
				$this->_instance[$key] = $data;
			}

			$this->_instance['[security]']			= NULL;
			$this->_instance['[prevaction]']		= NULL;
			$this->_instance['[currentaction]']		= NULL;
			$this->_instance['[nextaction]']		= NULL;
			$this->_instance['[prevperformer]']		= NULL;
			$this->_instance['[currentperformer]']	= NULL;
			$this->_instance['[nextperformer]']		= NULL;
			$this->_instance['[actions]']			= NULL;
			$this->_instance['[properties]']		= NULL;
			$this->_instance['[infoproperties]']	= NULL;
			$this->_instance['[childs]']			= NULL;
			$this->_instance['[recipients]']		= array('[mail]' => array(), '[icq]' => array(), '[jabber]' => array(), '[cell]' => array());
			$this->_instance['[inside_project]']	= false;
			$this->_instance['[inside_process]']	= false;
			$this->_instance['[code_included]']		= false;
			$this->_instance['[form_included]']		= false;
			$this->_instance['[nextactionset]']		= false;
			$this->_instance['[ownedby]']			= $options['ownedby'];
			$this->_instance['[process]']			= new Process($this, $this->getProperty('process_id'));
			$this->_instance['[model]']				= "CsProcessInstance";

			$this->checkOwner();

			$this->_instance['[security]']	= $this->_owner->getSecurity();

			$this->initActions();
			$this->initProperties();
			$this->initInfoProperties();
			$this->initChilds();

			$this->checkForExecute();
		}
	}

	function getProperty($name) {
		return $this->_instance[$name];
	}

	function setProperty($name, $value, $lazy = false) {
		$this->_instance[$name] = $value;
		if ($lazy == false) {
			$this->save();
		}
	}

	function insideProject() {
		// процесс внутри проекта
		return $this->getProperty('[inside_project]');
	}

	function insideProcess() {
		// дочерний ли процесс
		return $this->getProperty('[inside_process]');
	}

	private function initActions() {
		// инициализация действий экземпляра процесса
		$this->_instance['[actions]'] = NULL;
		$this->_instance['[actions]'] = new Collection($this);

		if ($this->getProperty('[ownedby]')) {
			$ownedby = ' and (initiator_id = '.$this->getProperty('[ownedby]').' or performer_id = '.$this->getProperty('[ownedby]').')';
		} else {
			$ownedby = '';
		}

		$actions = $this->getConnection()->execute('select * from process_instance_actions_list where instance_id = '.$this->_id.$ownedby.' order by npp')->fetchAll();
		foreach ($actions as $action) {
			$this->_instance['[actions]']->setElement($action['name'], new ProcessCurrentAction($this, $action['id'], array('data' => $action)));
		}

		logRuntime('['.get_class($this).'.initActions->'.$this->getProperty('name').'] actions initialized');

		$this->setCurrentAction($this->getLastActiveAction());
		$this->setNextAction($this->getLastInactiveAction());
		$this->setPrevAction($this->getLastCompleteAction());
	}

	private function initProperties() {
		// инициализация свойств экземпляра процесса
		$this->_instance['[properties]'] = NULL;
		$this->_instance['[properties]'] = new Collection($this);

		$properties = $this->getConnection()->execute('select * from process_instance_properties_list where instance_id = '.$this->_id.' order by id, name, type_id')->fetchAll();
		foreach ($properties as $property) {
			$this->_instance['[properties]']->setElement($property['name'], new ProcessInstanceProperty($this, $property['id'], array('data' => $property)));
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

	private function initChilds($force = false) {
		// инициализация потомков (дочерних экземпляров) процесса
		$this->_instance['[childs]'] = NULL;
		$this->_instance['[childs]'] = new Collection($this);

		$childs = $this->getConnection()->execute('select * from process_instances_tree where parent_id = '.$this->_id)->fetchAll();
		foreach ($childs as $child) {
			$this->_instance['[childs]']->setElement($child['name'].'-'.$child['id'], new ProcessInstanceWrapper($this, $child['id'], array('data' => $child)));
			logRuntime('['.get_class($this).'.initChilds->'.$this->getProperty('name').'] '.$child['name'].'-'.$child['id'].' child initialized');
		}

		logRuntime('['.get_class($this).'.initChilds->'.$this->getProperty('name').'] childs initialized'.($force?' (forced)':''));
	}

	private function initHistory() {
		// инициализация свойств экземпляра процесса
		$this->_instance['[history]'] = NULL;
		$this->_instance['[history]'] = new Collection($this);

		$chronos = $this->getConnection()->execute('select * from chrono_process_instances_list where instance_id = '.$this->_id.' order by id')->fetchAll();
		foreach ($chronos as $chrono) {
			$this->_instance['[history]']->setElement($chrono['processname'].'-'.$chrono['chrono_at'], new Chrono($this, $chrono['id'], array('data' => $chrono)));
		}

		logRuntime('['.get_class($this).'.initHistory->'.$this->getProperty('name').'] history initialized');
	}

	function reinitProcess() {
		// переинициализация ВСЕГО экземпляра процесса
		logRuntime('['.get_class($this).'.reinitProcess->'.$this->getProperty('name').'] try to reinitialize process');
		$this->initActions();
		$this->initProperties();
		$this->initInfoProperties();
		if (isNotNULL($this->_instance['[history]'])) {
			$this->initHistory();
		}
		logRuntime('['.get_class($this).'.reinitProcess->'.$this->getProperty('name').'] process reinitialized');
		return $this;
	}

	private function checkForExecute() {
		// проверка на возможность запуска кода и формы текущего действия
		if (!isNULL($this->getCurrentAction()) and ($this->getCurrentAction()->isComplete())) {
			logRuntime('['.get_class($this).'.checkForExecute] code and form for '.$this->getCurrentAction()->getProperty('name').' already included');

			$this->_instance['[code_included]']	= true;
			$this->_instance['[form_included]']	= true;
		}
	}

	private function checkOwner() {
		// проверка класса предка
		if ((is_a($this->_owner, 'ProjectInstance')) or (is_a($this->_owner, 'ProjectInstanceWrapper'))) {
			$this->_instance['[inside_project]']	= true;
		} elseif ((is_a($this->_owner, 'ProcessInstance')) or (is_a($this->_owner, 'ProcessInstanceWrapper')) or ($this->getProperty('parent_id') > 0)) {
			$this->_instance['[inside_process]']	= true;
		}
	}

	function haveHistory() {
		return (isNotNULL($this->getConnection()->execute('select * from cs_chrono where instance_id = '.$this->getProperty('id'))->fetchAll()));
	}

	function save() {
		// сохранение экземпляра процесса
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_instance['[model]']);
		return $this->saveData($this->_instance['[model]'], $this->_instance);
	}

	private function saveProperties () {
		// сохранение свойств экземпляра процесса
		foreach ($this->getProperties() as $property) {
			$property->saveValue();
		}
	}

	private function saveActions () {
		// сохранение действий экземпляра процесса
		foreach ($this->getActions() as $action) {
			$action->save();
		}
	}

	private function saveAll() {
		// сохранение ВСЕГО экземпляра процесса
		$this->saveProperties();
		$this->saveActions();

		logRuntime('['.get_class($this).'.saveAll->'.$this->getProperty('name').'] all data saved');

		return $this->save();
	}

	function saveForm() {
		// сохранение формы текущего действия экземпляра процесса
		$this->saveProperties();
		$this->getCurrentAction()->save();

		$this->pauseCurrentToday();

		logRuntime('['.get_class($this).'.saveForm->'.$this->getProperty('name').'] all form data saved');

		return $this->save();
	}
	
	function getSecurity() {
		return $this->_owner->getSecurity();
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

	function getActions() {
		return $this->_instance['[actions]']->getElements();
	}

	function getHistory() {
		if (isNULL($this->_instance['[history]'])) {
			$this->initHistory();
		}
		return $this->_instance['[history]']->getElements();
	}

	function getOwnerPropertyValue($name) {
		return (is_callable($this->_owner, 'getPropertyValue')?$this->_owner->getPropertyValue($name):NULL);
	}

	function getOwnerPropertyMimeType($name) {
		return (is_callable($this->_owner, 'getPropertyMimeType')?$this->_owner->getPropertyMimeType($name):NULL);
	}

	function setOwnerPropertyValue($name, $value, $force = false) {
		// установка значения свойства предка
		$this->checkOwner();
		if (($this->insideProcess()) or ($this->getProperty('parent_id') > 0)) {
			logRuntime('['.get_class($this).'.setOwnerPropertyValue->'.$this->getProperty('name').'] set owner process property '.$name.' value to '.(($value)?$value:'NULL').($force?" (forced)":""));

			if (is_a($this->_owner, 'ProcessInstance') or is_a($this->_owner, 'ProcessInstanceWrapper')) {
				$this->_owner->setPropertyValue($name, $value, $force);
			} else {
				$process = new ProcessInstanceWrapper($this->_owner, $this->getProperty('parent_id'));
				$process->setPropertyValue($name, $value, $force);
			}
		} else {
			logRuntime('['.get_class($this).'.setOwnerPropertyValue->'.$this->getProperty('name').'] set owner project property '.$name.' value to '.(($value)?$value:'NULL').($force?" (forced)":""));

			if (($this->insideProject()) and ((is_a($this->_owner, 'ProjectInstance')) or (is_a($this->_owner, 'ProjectInstanceWrapper')))) {
				$this->_owner->setPropertyValue($name, $value, $force);
			}
		}
	}

	function setOwnerPropertyMimeType($name, $value, $force = false) {
		// установка MIME-типа свойства предка
		$this->checkOwner();
		if (($this->insideProcess()) or ($this->getProperty('parent_id') > 0)) {
			logRuntime('['.get_class($this).'.setOwnerPropertyMimeType->'.$this->getProperty('name').'] set owner process property '.$name.' mimetype to '.(($value)?$value:'NULL').($force?" (forced)":""));

			if (is_a($this->_owner, 'ProcessInstance') or is_a($this->_owner, 'ProcessInstanceWrapper')) {
				$this->_owner->setPropertyMimeType($name, $value, $force);
			} else {
				$process = new ProcessInstanceWrapper($this->_owner, $this->getProperty('parent_id'));
				$process->setPropertyMimeType($name, $value, $force);
			}
		} else {
			logRuntime('['.get_class($this).'.setOwnerPropertyMimeType->'.$this->getProperty('name').'] set owner project property '.$name.' mimetype to '.(($value)?$value:'NULL').($force?" (forced)":""));

			if (($this->insideProject()) and ((is_a($this->_owner, 'ProjectInstance')) or (is_a($this->_owner, 'ProjectInstanceWrapper')))) {
				$this->_owner->setPropertyMimeType($name, $value, $force);
			}
		}
	}

	function setPropertyValue($name, $value, $force = false) {
		// установка значения свойства
		if ($this->propertyExists($name)) {
			logRuntime('['.get_class($this).'.setPropertyValue->'.$this->getProperty('name').'] set property '.$name.' value to '.(($value)?$value:'NULL').($force?" (forced)":""));
			$this->_instance['[properties]']->getElement($name)->setPropertyValue($value, $force);
		} else {
			logRuntime('['.get_class($this).'.setPropertyValue->'.$this->getProperty('name').'] set owner property '.$name.' value to '.(($value)?$value:'NULL').($force?" (forced)":""));
			// у нас такого свойства нет - пинаем предка
			$this->setOwnerPropertyValue($name, $value, $force);
		}
	}

	function setPropertyMimeType($name, $value, $force = false) {
		// установка MIME-типа свойства
		if ($this->propertyExists($name)) {
			logRuntime('['.get_class($this).'.setPropertyMimeType->'.$this->getProperty('name').'] set property '.$name.' mimetype to '.(($value)?$value:'NULL').($force?" (forced)":""));
			$this->_instance['[properties]']->getElement($name)->setPropertyMimeType($value, $force);
		} else {
			logRuntime('['.get_class($this).'.setPropertyMimeType->'.$this->getProperty('name').'] set owner property '.$name.' mimetype to '.(($value)?$value:'NULL').($force?" (forced)":""));
			// у нас такого свойства нет - пинаем предка
			$this->setOwnerPropertyMimeType($name, $value, $force);
		}
	}

	private function getLastActiveAction() {
		foreach ($this->getProperty('[actions]')->getElements() as $action) {
			if ((($action->getProperty('status_id') == Constants::ACTION_STATUS_IN_PROGRESS) or ($action->getProperty('status_id') == Constants::ACTION_STATUS_WAITING)) and ($action->getProperty('status_id') <> Constants::ACTION_STATUS_SKIPED) and ($action->getProperty('type_id') <> Constants::ACTION_TYPE_INFO)) {
				return $action;
			}
		}

		return $this->getProperty('[actions]')->getFirstElement();
	}

	private function getInfoAction() {
		foreach ($this->getProperty('[actions]')->getElements() as $action) {
			if ($action->getProperty('type_id') == Constants::ACTION_TYPE_INFO) {
				return $action;
			}
		}

		return NULL;
	}

	private function getEndAction() {
		foreach ($this->getProperty('[actions]')->getElements() as $action) {
			if ($action->getProperty('type_id') == Constants::ACTION_TYPE_END) {
				return $action;
			}
		}

		return NULL;
	}

	private function getBeginAction() {
		foreach ($this->getProperty('[actions]')->getElements() as $action) {
			if ($action->getProperty('type_id') == Constants::ACTION_TYPE_START) {
				return $action;
			}
		}

		return NULL;
	}

	private function getLastCompleteAction() {
		$prev = $this->getProperty('[actions]')->getFirstElement();

		foreach ($this->getProperty('[actions]')->getElements() as $action) {
			if ((($action->getProperty('status_id') == Constants::ACTION_STATUS_COMPLETED) or ($action->getProperty('status_id') == Constants::ACTION_STATUS_SKIPED)) and ($action->getProperty('type_id') <> Constants::ACTION_TYPE_INFO)) {
				$prev = $action;
			} else {
				return $prev;
			}
		}

		return $this->getProperty('[actions]')->getFirstElement();
	}

	private function getLastInactiveAction() {
		$transition = $this->getProperty('[process]')->getProperty('[transitions]')->findElementByPropertyValue('from_action_id', $this->getCurrentAction()->getProperty('action_id'));
		if (isNotNULL($transition)) {
			$action = $transition->getProperty('to_action_id');
			if ($action > 0) {
				return $this->getProperty('[actions]')->findElementByPropertyValue('action_id', $action);
			}
		}
		foreach ($this->getProperty('[actions]')->getElements() as $action) {
			if ((($action->getProperty('status_id') == Constants::ACTION_STATUS_NONE) or ($action->getProperty('status_id') == Constants::ACTION_STATUS_WAITING)) and ($action->getProperty('status_id') <> Constants::ACTION_STATUS_SKIPED) and ($action->getProperty('type_id') <> Constants::ACTION_TYPE_INFO)) {
				return $action;
			}
		}

		return $this->getProperty('[actions]')->getFirstElement();
	}

	function getCurrentAction() {
		return $this->_instance['[currentaction]'];
	}

	private function setCurrentAction($action) {
		$this->_instance['[currentaction]'] = $action;
		logRuntime('['.get_class($this).'.setCurrentAction->'.$this->getProperty('name').'] set current action to '.(($action)?$action->getProperty('name'):'NULL'));
	}

	function getNextAction() {
		if (($this->_instance['[nextaction]']) and ($this->_instance['[nextaction]']->getProperty('status_id') <> Constants::ACTION_STATUS_SKIPED)) {
			return $this->_instance['[nextaction]'];
		} else {
			return $this->getLastInactiveAction();
		}
	}

	function getPrevAction() {
		if (($this->_instance['[prevaction]']->getProperty('status_id') == Constants::ACTION_STATUS_COMPLETED) or ($this->_instance['[prevaction]']->getProperty('status_id') == Constants::ACTION_STATUS_SKIPED)) {
			return $this->_instance['[prevaction]'];
		} else {
			return $this->getLastCompleteAction();
		}
	}

	private function setNextAction($action) {
		$this->_instance['[nextaction]'] = $action;
		logRuntime('['.get_class($this).'.setNextAction->'.$this->getProperty('name').'] set next action to '.(($action)?$action->getProperty('name'):'NULL'));
	}

	private function setPrevAction($action) {
		$this->_instance['[prevaction]'] = $action;
		logRuntime('['.get_class($this).'.setPrevAction->'.$this->getProperty('name').'] set prev action to '.(($action)?$action->getProperty('name'):'NULL'));
	}

	function view($print = false) {
		// просмотр процесса если есть действие типа Информация
		$action = $this->getInfoAction();
		if ($action) {
			$action->view($print);
			include_once($action->getProperty('[form_file]'));
			if (!$this->_debug) {
				unlink($action->getProperty('[form_file]'));
			}
		}
	}

	function execute() {
		global $actions_icons, $status_names, $mime_names, $mime_exts, $parameters;

		// инициализация, запуск и завершение текущего действия или всего экземпляра процесса
		if (($this->getProperty('status_id') == Constants::PROCESS_STATUS_IN_PROGRESS) or ($this->getProperty('status_id') == Constants::PROCESS_STATUS_CHILD_IN_PROGRESS)) {
			// процесс уже запущен
			// если необходимо устанавливаем дату и время старта экземпляра процесса (экземпляр процесса запущен только что)
			if (isNULL($this->getProperty('started_at'))) {
				$this->setProperty('started_at', strftime("%Y-%m-%d %H:%M:%S", time()), true);
				$this->save();
				$this->sendMessage(Constants::EVENT_AFTER_PROCESS_START);
			}

			logRuntime('['.get_class($this).'.execute->'.$this->getProperty('name').'] try to execute '.$this->getCurrentAction()->getProperty('name').' action');
			// все ли действия и дочерние экземпляры процессов завершены
			if ($this->isAllComplete()) {
				// экземпляр процесса кончил...
				logRuntime('['.get_class($this).'.execute->'.$this->getProperty('name').'] all complete in '.$this->getProperty('name'));
				logMessage('процесс "'.$this->getProperty('name').'" и все дочерние процессы завершены');
				$this->allComplete();
				// ...и уснул...
			} else {
				logMessage('попытка запустить действие "'.$this->getCurrentAction()->getProperty('name').'" (процесс: "'.$this->getProperty('name').'"');
				if ($this->getCurrentAction()->isComplete()) {
					// текущее действие кончило...
					logRuntime('['.get_class($this).'.execute->'.$this->getProperty('name').'] current action '.$this->getCurrentAction()->getProperty('name').' is completed');
					logMessage('действие "'.$this->getCurrentAction()->getProperty('name').'" уже завершено (процесс: "'.$this->getProperty('name').'"');
					$this->complete();
					// ...и уснуло...
				} else {
					if (!$this->getCurrentAction()->isWaiting() and $this->isAllChildsComplete()) {
						// запуск текущего действия
						$this->getCurrentAction()->execute($this->getCurrentPerformerID());
						logMessage('действие "'.$this->getCurrentAction()->getProperty('name').'" запущено (процесс: "'.$this->getProperty('name').'"');
						$this->checkForExecute();
						if (file_exists($this->getCurrentAction()->getProperty('[code_file]')) and (!$this->_instance['[code_included]']) and ($this->canPerform())) {
							// запуск кода текущего действия 
							logRuntime('['.get_class($this).'.execute->'.$this->getProperty('name').'] including '.$this->getCurrentAction()->getProperty('name').' code');

							logMessage('запуск кода действия "'.$this->getCurrentAction()->getProperty('name').'"');
							include_once($this->getCurrentAction()->getProperty('[code_file]'));
							if (!$this->_debug) {
								unlink($this->getCurrentAction()->getProperty('[code_file]'));
							}
							$this->_instance['[code_included]'] = true;
						}

						$this->checkForExecute();
						if ((!$this->getFormManager()->formIsValid()) and (file_exists($this->getCurrentAction()->getProperty('[form_file]'))) and (!$this->_instance['[form_included]']) and ($this->canPerform())) {
							// запуск формы (если есть) текущего действия
							include_once(MODULES_PATH.'/runtime/misc/process_prop_popup.php');

							logRuntime('['.get_class($this).'.execute->'.$this->getProperty('name').'] including '.$this->getCurrentAction()->getProperty('name').' form');

							if (isNULL($this->getCurrentAction()->getProperty('started_at'))) {
								$this->getCurrentAction()->setProperty('started_at', strftime("%Y-%m-%d %H:%M:%S", time()), true);
								$this->getCurrentAction()->save();
							}
						
							logMessage('запуск формы действия "'.$this->getCurrentAction()->getProperty('name').'"');
							include_once($this->getCurrentAction()->getProperty('[form_file]'));
							if (!$this->_debug) {
								unlink($this->getCurrentAction()->getProperty('[form_file]'));
							}
							$this->_instance['[form_included]'] = true;
							$this->startCurrentToday();
						} elseif ((!$this->canPerform()) and ($this->getCurrentAction()->isInteractive())) {
							$this->pauseCurrentToday();
						}
					}
				}
				logRuntime('['.get_class($this).'.execute->'.$this->getProperty('name').'] '.$this->getCurrentAction()->getProperty('name').' action executed');
			}
		} else {
			// процесс ещё не запущен - запускаем
			$this->sendMessage(Constants::EVENT_BEFORE_PROCESS_START);
			$this->setProperty('status_id', Constants::PROCESS_STATUS_IN_PROGRESS, true);
			$this->setProperty('statusname', $status_names[$this->getProperty('status_id')-1], true);

			// если необходимо устанавливаем дату и время старта экземпляра процесса (экземпляр процесса запущен только что)
			if (isNULL($this->getProperty('started_at'))) {
				$this->setProperty('started_at', strftime("%Y-%m-%d %H:%M:%S", time()), true);
			}
			$this->save();
			$this->sendMessage(Constants::EVENT_AFTER_PROCESS_START);
		}
	}

	function stopAllToday() {
		$this->getConnection()->execute('update cs_account_today set ended_at = now() where process_instance_id = '.$this->getProperty('id').' and ended_at is null')->fetch();
	}

	function stopCurrentToday($time = NULL) {
		$status = $this->getConnection()->execute('select * from cs_account_today where account_id = '.$this->getCurrentAction()->getProperty('performer_id').' and process_instance_id = '.$this->getProperty('id').' and action_instance_id = '.$this->getCurrentAction()->getProperty('id').' and ended_at is null')->fetch();
		if (($status != false) and ((time() - strtotime($status['started_at'])) > CHRONOLOGY_TIMEOUT)) {
			$this->getConnection()->execute('update cs_account_today set ended_at = '.(is_null($time)?'now()':"'".strftime("%Y-%m-%d %H:%M:%S", $time)."'").' where process_instance_id = '.$this->getProperty('id').' and action_instance_id = '.$this->getCurrentAction()->getProperty('id').' and ended_at is null')->fetch();
		}
	}

	function startCurrentToday($time = NULL) {
		if ($this->getCurrentAction()->isInteractive()) {
			if ($this->isAlreadyStarted()) {
				$started = $this->getConnection()->execute('select * from cs_account_today where account_id = '.$this->getCurrentAction()->getProperty('performer_id').' and process_instance_id = '.$this->getProperty('id').' and action_instance_id = '.$this->getCurrentAction()->getProperty('id').' and status_id = '.Constants::ACTION_STATUS_IN_PROGRESS.' and ended_at is null')->fetch();
				if (todayIsNextDay(strtotime($started['started_at']))) {
					$this->stopCurrentToday(endOfWorkTime(strtotime($started['started_at'])));
					$this->pauseCurrentToday(endOfWorkTime(strtotime($started['started_at'])));
				} else {
					$this->stopCurrentToday(strtotime($started['started_at']) + PAUSE_TIMEOUT);
					$this->pauseCurrentToday(strtotime($started['started_at']) + PAUSE_TIMEOUT);
				}
			}
			$this->stopCurrentToday();
			if ($this->isAlreadySetStatus() == false) {
				$this->getConnection()->execute('insert into cs_account_today (account_id, process_instance_id, action_instance_id, status_id, started_at, ended_at) values ('.$this->getCurrentAction()->getProperty('performer_id').', '.$this->getProperty('id').', '.$this->getCurrentAction()->getProperty('id').', '.Constants::ACTION_STATUS_IN_PROGRESS.',  '.(is_null($time)?'now()':"'".strftime("%Y-%m-%d %H:%M:%S", $time)."'").', null)')->fetch();
			}
		}
	}

	function pauseCurrentToday($time = NULL) {
		if ($this->getCurrentAction()->isInteractive()) {
			$this->stopCurrentToday();
			if ($this->isAlreadySetStatus() == false) {
				$this->getConnection()->execute('insert into cs_account_today (account_id, process_instance_id, action_instance_id, status_id, started_at, ended_at) values ('.$this->getCurrentAction()->getProperty('performer_id').', '.$this->getProperty('id').', '.$this->getCurrentAction()->getProperty('id').', '.Constants::ACTION_STATUS_WAITING.', '.(is_null($time)?'now()':"'".strftime("%Y-%m-%d %H:%M:%S", $time)."'").', null)')->fetch();
			}
		}
	}

	function isAlreadyPaused() {
		$paused = $this->getConnection()->execute('select * from cs_account_today where account_id = '.$this->getCurrentAction()->getProperty('performer_id').' and process_instance_id = '.$this->getProperty('id').' and action_instance_id = '.$this->getCurrentAction()->getProperty('id').' and status_id = '.Constants::ACTION_STATUS_WAITING.' and ended_at is null')->fetch();
		if (($paused != false) and ((time() - strtotime($paused['started_at'])) < CHRONOLOGY_TIMEOUT)) {
			return true;
		} else {
			return (($paused)?true:false);
		}
	}

	function isAlreadySetStatus() {
		$status = $this->getConnection()->execute('select * from cs_account_today where account_id = '.$this->getCurrentAction()->getProperty('performer_id').' and process_instance_id = '.$this->getProperty('id').' and action_instance_id = '.$this->getCurrentAction()->getProperty('id').' and ended_at is null')->fetch();
		if (($status != false) and ((time() - strtotime($status['started_at'])) < CHRONOLOGY_TIMEOUT)) {
			return true;
		} else {
			return (($status)?true:false);
		}
	}

	function isAlreadyStarted() {
		$started = $this->getConnection()->execute('select * from cs_account_today where account_id = '.$this->getCurrentAction()->getProperty('performer_id').' and process_instance_id = '.$this->getProperty('id').' and action_instance_id = '.$this->getCurrentAction()->getProperty('id').' and status_id = '.Constants::ACTION_STATUS_IN_PROGRESS.' and ended_at is null')->fetch();
		if (($started != false) and ((time() - strtotime($started['started_at'])) < CHRONOLOGY_TIMEOUT)) {
			return true;
		} else {
			return (($started)?true:false);
		}
	}

	function complete() {
		// обработка и завершение (если возможно) текущего действия и переход на следующее
		if (($this->getProperty('status_id') == Constants::PROCESS_STATUS_IN_PROGRESS) or ($this->getProperty('status_id') == Constants::PROCESS_STATUS_CHILD_IN_PROGRESS)) {
			if ($this->getCurrentAction()->isWaiting()) {
				logMessage('действие "'.$this->getCurrentAction()->getProperty('name').'" в режиме ожидания');
				if ($this->isAllChildsComplete()) {
					if ($this->getCurrentAction()->isPerformersComplete()) {
						// все дочерние экземпляры процессов завершены
						logMessage('попытка завершить действие "'.$this->getCurrentAction()->getProperty('name').'"');
						logRuntime('['.get_class($this).'.complete->'.$this->getProperty('name').'] current action '.$this->getCurrentAction()->getProperty('name').' complete after childs in '.$this->getProperty('name'));
						// завершение текущего действия
						$this->getCurrentAction()->complete();
						$this->stopCurrentToday();
						$this->complete();
					} else {
						// многопользовательское действие
					}
				} else {
					logRuntime('['.get_class($this).'.complete->'.$this->getProperty('name').'] childs are not complete in '.$this->getProperty('name'));
				}
			} else {
				if (!$this->getCurrentAction()->isComplete()) {
					if ($this->getCurrentAction()->isPerformersComplete()) {
						// текущее действие не кончило... но уже кончает...
						logMessage('действие "'.$this->getCurrentAction()->getProperty('name').'" не завершено');
						if ($this->getPrevAction()->getProperty('type_id') == Constants::ACTION_TYPE_SWITCH) {
							// пропускаем действия того-же уровня
							logMessage('пропуск действий не входящих в условия прохождения процесса');
							$npp = $this->getCurrentAction()->getProperty('npp');
							$id  = $this->getCurrentAction()->getProperty('id');
							foreach ($this->getProperty('[actions]')->getElements() as $action) {
								if (($action->getProperty('npp') == $npp) and ($action->getProperty('id') <> $id)) {
									logRuntime('['.get_class($this).'.complete->'.$this->getProperty('name').'] '.$action->getProperty('name').' skiped');
									$action->skip($this->getCurrentPerformerID());
								}
							}
						}
						// пропускаем ненужные действия
						$this->skipOther($this->getCurrentAction()->getProperty('id'), $this->getCurrentAction()->getProperty('action_id'), $this->getCurrentPerformerID());
						
						logRuntime('['.get_class($this).'.complete->'.$this->getProperty('name').'] current action '.$this->getCurrentAction()->getProperty('name').' complete');
						// текущее действие кончило...
						$this->getCurrentAction()->complete();
						$this->stopCurrentToday();
						// ...и уснуло...
					} else {
						// многопользовательское действие
					}
				}
				if (!$this->isAllComplete()) {
					if ($this->getCurrentAction()->isPerformersComplete()) {
						logMessage('попытка инициализации предыдущего, следующего и текущего действий и исполнителей по плану');
						// инициализируем предыдущее, следующее и текущее действия для продолжения банкета
						$this->setPrevAction($this->getLastCompleteAction());
						// устанавливаем следующее действие по плану
						if ($this->_instance['[nextactionset]']) {
							$this->setNextAction($this->getNextAction());
							$this->_instance['[nextactionset]'] = false;
						} else {
							$this->setNextAction($this->getLastInactiveAction());
						}
						// устанавливаем следующего исполнителя по плану
						$this->setNextPerformer($this->getNextPerformerID());
						logMessage('предыдущее, следующее и текущее действия и исполнители по плану установлены');
						// сдвигаем действия, текущее -> предыдущее, следующее -> текущее
						$this->setCurrentAction($this->getNextAction());
						logRuntime('['.get_class($this).'.complete->'.$this->getProperty('name').'] set next action to '.$this->getCurrentAction()->getProperty('name'));
						// ... и запускаем новое текущее действие
						$this->execute();
					} else {
						// многопользовательское действие
					}
				} else {
					if ($this->isAllChildsComplete()) {
						$this->allComplete();
					}
				}
			}
		}
	}

	private function skipOther($id = 0, $action_id = 0, $performer = USER_CODE) {
		$trans = $this->getProperty('[process]')->getProcessTransitionsList($action_id);
		
		foreach ($this->getProperty('[actions]')->getElements() as $action) {
			if (($action->getProperty('status_id') <> Constants::ACTION_STATUS_COMPLETED) and
				 ($action->getProperty('status_id') <> Constants::ACTION_STATUS_SKIPED) and
				 ($action->getProperty('status_id') <> Constants::ACTION_STATUS_WAITING) and
				 ($action->getProperty('type_id') <> Constants::ACTION_TYPE_INFO) and
				 ($action->getProperty('type_id') <> Constants::ACTION_TYPE_STANDALONE) and
				 ($action->getProperty('id') <> $id) and (!in_array($action->getProperty('action_id'), $trans))) {
				logRuntime('['.get_class($this).'.skipOther->'.$this->getProperty('name').'] '.$action->getProperty('name').' skiped');
				$action->skip($performer);
			}
		}
	}

	private function allComplete() {
		global $status_names;
		// полное завершение процесса
		if (($this->getProperty('status_id') == Constants::PROCESS_STATUS_IN_PROGRESS) or ($this->getProperty('status_id') == Constants::PROCESS_STATUS_CHILD_IN_PROGRESS)) {
			$this->setProperty('status_id', Constants::PROCESS_STATUS_COMPLETED, true);
			$this->setProperty('statusname', $status_names[(Constants::PROCESS_STATUS_COMPLETED)-1], true);
			$this->setProperty('ended_at', strftime("%Y-%m-%d %H:%M:%S", time()), true);
			$this->save();

			$this->stopAllToday();

			// отправка уведомления о завершении процесса
			$this->sendMessage(Constants::EVENT_AFTER_PROCESS_END);

			if (($this->insideProcess()) or ($this->getProperty('parent_id') > 0)) {
				// это дечерний экземпляр процесса...
				if (is_a($this->_owner, 'ProcessInstance') or is_a($this->_owner, 'ProcessInstanceWrapper')) {
					// ... и родитель присутствует
					logRuntime('['.get_class($this).'.allComplete->'.$this->getProperty('name').'] in process '.$this->_owner->getProperty('name'));
					if ($this->_owner->isAllChildsComplete()) {
						// все дочерние процессы у предка завершены
						logRuntime('['.get_class($this).'.allComplete->'.$this->getProperty('name').'] owner process '.$this->_owner->getProperty('name').' all childs are completed');
						// завершаем текущее действие ожидающего родительского процесса
						$this->_owner->complete();
					}
				} else {
					// ... и родитель отсутствует
					$process = new ProcessInstanceWrapper($this->_owner, $this->getProperty('parent_id'));
					logRuntime('['.get_class($this).'.allComplete->'.$this->getProperty('name').'] created owner process '.$process->getProperty('name'));
					if ($process->isAllChildsComplete()) {
						// все дочерние процессы у предка завершены
						logRuntime('['.get_class($this).'.allComplete->'.$this->getProperty('name').'] owner process '.$process->getProperty('name').' all childs are completed');
						// завершаем текущее действие ожидающего родительского процесса
						$process->complete();
					}
				}
			}

			// с первого раз не сохраняет статус! глюк-c... :(
			$this->setProperty('status_id', Constants::PROCESS_STATUS_COMPLETED, true);
			$this->setProperty('statusname', $status_names[(Constants::PROCESS_STATUS_COMPLETED)-1], true);
			$this->save();
		}
	}

	function setNextStep($name, $users = NULL) {
		// установка следующего действия
		$this->setNextActionByName($name);
		if (isNotNULL($users)) {
			$this->setNextUser($users);
		}
	}

	function setNextUser($names) {
		// установка следующего исполнителя по имени или нескольких исполнителей
		if (mb_strpos($names, '||')) {
			foreach(explode('||', $names) as $name) {
				$this->setNextPerformerByName($name);
			}
		} else {
			$this->setNextPerformerByName($names);
		}
	}

	function setNextUserID($ids) {
		// установка следующего исполнителя по ID или нескольких исполнителей
		if (mb_strpos($ids, '||')) {
			foreach(explode('||', $ids) as $id) {
				$this->setNextPerformer($id);
			}
		} else {
			$this->setNextPerformer($ids);
		}
	}

	function setNextActionByName($name) {
		logRuntime('['.get_class($this).'.setNextActionByName->'.$this->getProperty('name').'] try to set next action to '.$name);
		$action = $this->getProperty('[actions]')->findElementByName($name);
		if ($action) {
			$this->setNextAction($action);
			if ($this->getNextAction()->getProperty('name') == $name) {
				logRuntime('['.get_class($this).'.setNextActionByName->'.$this->getProperty('name').'] next action is '.$this->getNextAction()->getProperty('name').' (success)');
				$this->_instance['[nextactionset]'] = true;
			} else {
				logRuntime('['.get_class($this).'.setNextActionByName->'.$this->getProperty('name').'] next action is '.$this->getNextAction()->getProperty('name').' (failed)');
			}
		} else {
			logRuntime('['.get_class($this).'.setNextActionByName->'.$this->getProperty('name').'] action '.$name.' does not exists!');
		}
	}

	function isAllComplete() {
		// все ли действия и дочерние процессы завершены
		$actions = $this->getProperty('[actions]')->getElements();
		foreach ($actions as $action) {
			if (!$action->isComplete()) {
				return false;
			}
		}

		return $this->isAllChildsComplete();
	}

	function isAllChildsComplete() {
		// все ли дочерние процессы завершены
		return ($this->haveIncomletedChilds()?false:true);
	}

	function isOwnerAllChildsComplete() {
		// все ли дочерние процессы предка завершены
		if (is_a($this->_owner, 'ProcessInstance') or is_a($this->_owner, 'ProcessInstanceWrapper')) {
			return $this->_owner->isAllChildsComplete();
		} else {
			$process = new ProcessInstanceWrapper($this->_owner, $this->getProperty('parent_id'));
			return $process->isAllChildsComplete();
		}
	}

	function haveIncomletedChilds() {
		// есть незавершенные дочерние процессы
		if ($this->haveChilds()) {
			foreach ($this->getProperty('[childs]')->getElements() as $child) {
				logRuntime('['.get_class($this).'.haveIncompletedChilds->'.$this->getProperty('name').'] '.$this->getProperty('name').' searching for incompleted childs');
				if (!$child->isAllComplete()) {
					logRuntime('['.get_class($this).'.haveIncompletedChilds->'.$this->getProperty('name').'] '.$this->getProperty('name').' have incompleted child '.$child->getProperty('name'));
					return true;
				}
			}
		}
		logRuntime('['.get_class($this).'.haveIncompletedChilds->'.$this->getProperty('name').'] '.$this->getProperty('name').' have no incompleted childs');
		return false;
	}

	function isSecured() {
		return (isNotNULL($this->getProperty('password')));
	}

	function getCurrentPerformerName() {
		return $this->getCurrentAction()->getProperty('performername');
	}

	function getCurrentPerformerID() {
		if (isNULL($this->getCurrentAction()->getProperty('performer_id'))) {
			if (isNULL($this->getCurrentAction()->getRole()) or isNULL($this->getCurrentAction()->getRole()->getProperty('account_id'))) {
				if (isNULL($this->getPrevPerformerID())) {
					logRuntime('['.get_class($this).'.getCurrentPerformerID->'.$this->getProperty('name').'] set current performer_id to '.USER_CODE.' (current)');
					return USER_CODE;
				} else {
					logRuntime('['.get_class($this).'.getCurrentPerformerID->'.$this->getProperty('name').'] set current performer_id to '.$this->getPrevPerformerID().' (previous)');
					return $this->getPrevPerformerID();
				}
			} else {
				logRuntime('['.get_class($this).'.getCurrentPerformerID->'.$this->getProperty('name').'] set current performer_id to '.$this->getCurrentAction()->getRole()->getProperty('account_id').' (by role)');
				return $this->getCurrentAction()->getRole()->getProperty('account_id');
			}
		} else {
			logRuntime('['.get_class($this).'.getCurrentPerformerID->'.$this->getProperty('name').'] set current performer_id to '.$this->getCurrentAction()->getProperty('performer_id').' (set before)');
			return $this->getCurrentAction()->getProperty('performer_id');
		}
	}

	private function setCurrentPerformerByName($name) {
		$account = $this->getConnection()->execute('select * from cs_account where name = \''.$name.'\';')->fetch();
		if (count($account) > 0) {
			logRuntime('['.get_class($this).'.setCurrentPerformerByName->'.$this->getProperty('name').'] set current performer name '.$name.' for action '.$this->getCurrentAction()->getProperty('name'));
			$this->getCurrentAction()->setProperty('performername', $name);
			$this->getCurrentAction()->setProperty('initiatorname', $name);
			$this->setCurrentPerformer($account['id']);
		}
	}

	private function setCurrentPerformer($id = NULL) {
		if (isNULL($id)) {
			if (isNULL($this->getCurrentAction()->getRole()->getProperty('account_id'))) {
				logRuntime('['.get_class($this).'.setCurrentPerformer->'.$this->getProperty('name').'] set current performer_id to '.USER_CODE.' (current)');
				$this->getCurrentAction()->setInitiator(USER_CODE, true);
				$this->getCurrentAction()->setPerformer(USER_CODE, true);
			} else {
				logRuntime('['.get_class($this).'.setCurrentPerformer->'.$this->getProperty('name').'] set current performer_id to '.$this->getCurrentAction()->getRole()->getProperty('account_id').' (by role)');
				$this->getCurrentAction()->setInitiator($this->getCurrentAction()->getRole()->getProperty('account_id'), true);
				$this->getCurrentAction()->setPerformer($this->getCurrentAction()->getRole()->getProperty('account_id'), true);
			}
		} else {
			logRuntime('['.get_class($this).'.setCurrentPerformer->'.$this->getProperty('name').'] set current performer_id to '.$id.' (by parameter)');
			$this->getCurrentAction()->setInitiator($id, true);
			$this->getCurrentAction()->setPerformer($id, true);
		}
		$this->getCurrentAction()->save();
	}

	function getNextPerformerName() {
		return $this->getNextAction()->getProperty('performername');
	}

	function getNextPerformerID() {
		if (isNULL($this->getNextAction()->getProperty('performer_id'))) {
			if (((isNULL($this->getNextAction()->getRole()) or isNULL($this->getNextAction()->getRole()->getProperty('account_id'))) or ($this->getCurrentPerformerID())) and ($this->getCurrentPerformerID() == $this->getPrevPerformerID())) {
				logRuntime('['.get_class($this).'.getNextPerformerID->'.$this->getProperty('name').'] set next performer_id to '.$this->getCurrentPerformerID().' (current)');
				return $this->getCurrentPerformerID();
			} else {
				if ($this->getPrevPerformerID()) {
					logRuntime('['.get_class($this).'.getNextPerformerID->'.$this->getProperty('name').'] set next performer_id to '.$this->getPrevPerformerID().' (previous)');
					return $this->getPrevPerformerID();
				} else {
					logRuntime('['.get_class($this).'.getNextPerformerID->'.$this->getProperty('name').'] set next performer_id to '.$this->getNextAction()->getRole()->getProperty('account_id').' (by role)');
					return $this->getNextAction()->getRole()->getProperty('account_id');
				}
			}
		} else {
			logRuntime('['.get_class($this).'.getNextPerformerID->'.$this->getProperty('name').'] set next performer_id to '.$this->getNextAction()->getProperty('performer_id').' (set before)');
			return $this->getNextAction()->getProperty('performer_id');
		}
	}

	private function setNextPerformerByName($name) {
		$account = $this->getConnection()->execute('select * from cs_account where name = \''.$name.'\';')->fetch();
		if (count($account) > 0) {
			logRuntime('['.get_class($this).'.setNextPerformerByName->'.$this->getProperty('name').'] set next performer name '.$name.' for action '.$this->getNextAction()->getProperty('name'));
			$this->getNextAction()->setProperty('performername', $name, true);
			$this->getNextAction()->setProperty('initiatorname', $name, true);
			$this->setNextPerformer($account['id']);
		}
	}

	private function initRecipients() {
		$this->_instance['[recipients]'] = array('[mail]' => array(), '[icq]' => array(), '[jabber]' => array(), '[cell]' => array());

		$account = $this->getConnection()->execute('select * from accounts_tree where id = '.$this->getProperty('initiator_id').';')->fetch();
		if (isNotNULL($account['email'])) {
			$this->_instance['[recipients]']['[mail]']['['.$account['name'].']'] = $account['email'];
		}
		if (isNotNULL($account['icq'])) {
			$this->_instance['[recipients]']['[icq]']['['.$account['name'].']'] = $account['icq'];
		}
		if (isNotNULL($account['jabber'])) {
			$this->_instance['[recipients]']['[jabber]']['['.$account['name'].']'] = $account['jabber'];
		}
		if ((isNotNULL($account['cell'])) and (isNotNULL($account['cellopgate']))) {
			$account['cell'] = preg_replace('/[^\d]/', '', preg_replace('/^(8)(.*)/', '7$2', $account['cell']));
			$this->_instance['[recipients]']['[cell]']['['.$account['name'].']'] = $account['cell'].'@'.$account['cellopgate'];
		}

		$account = $this->getConnection()->execute('select * from accounts_tree where id = '.$this->getCurrentAction()->getProperty('performer_id').';')->fetch();
		if (isNotNULL($account['email'])) {
			$this->_instance['[recipients]']['[mail]']['['.$account['name'].']'] = $account['email'];
		}
		if (isNotNULL($account['icq'])) {
			$this->_instance['[recipients]']['[icq]']['['.$account['name'].']'] = $account['icq'];
		}
		if (isNotNULL($account['jabber'])) {
			$this->_instance['[recipients]']['[jabber]']['['.$account['name'].']'] = $account['jabber'];
		}
		if ((isNotNULL($account['cell'])) and (isNotNULL($account['cellopgate']))) {
			$account['cell'] = preg_replace('/[^\d]/', '', preg_replace('/^(8)(.*)/', '7$2', $account['cell']));
			$this->_instance['[recipients]']['[cell]']['['.$account['name'].']'] = $account['cell'].'@'.$account['cellopgate'];
		}
	}

	private function setNextPerformer($id = NULL) {
		if ($this->getCurrentAction()->getProperty('name') == $this->getNextAction()->getProperty('name')) {
			logRuntime('['.get_class($this).'.setNextPerformerID->'.$this->getProperty('name').'] next and current action are equal! reset next action');
			$this->setNextAction($this->getLastInactiveAction());
		} else {
			logRuntime('['.get_class($this).'.setNextPerformerID->'.$this->getProperty('name').'] next and current action are not equal! continue...');
		}
		if (isNULL($id)) {
			if (isNULL($this->getCurrentAction()->getRole()->getProperty('account_id'))) {
				logRuntime('['.get_class($this).'.setNextPerformer->'.$this->getProperty('name').'] set current performer_id to '.USER_CODE.' (current)');
				$this->getNextAction()->setInitiator(USER_CODE, true);
				$this->getNextAction()->setPerformer(USER_CODE, true);
			} else {
				logRuntime('['.get_class($this).'.setNextPerformer->'.$this->getProperty('name').'] set current performer_id to '.$this->getCurrentAction()->getRole()->getProperty('account_id').' (by role)');
				$this->getNextAction()->setInitiator($this->getCurrentAction()->getRole()->getProperty('account_id'), true);
				$this->getNextAction()->setPerformer($this->getCurrentAction()->getRole()->getProperty('account_id'), true);
			}
		} else {
			logRuntime('['.get_class($this).'.setNextPerformer->'.$this->getProperty('name').'] set next performer_id to '.$id.' (by parameter)');
			$this->getNextAction()->setInitiator($id, true);
			$this->getNextAction()->setPerformer($id, true);
		}
		$this->getNextAction()->save();
	}

	function getPrevPerformerName() {
		return $this->getPrevAction()->getProperty('performername');
	}

	function getPrevPerformerID() {
		if (isNULL($this->getPrevAction()->getProperty('performer_id'))) {
			if ((isNULL($this->getPrevAction()->getRole()) or isNULL($this->getPrevAction()->getRole()->getProperty('account_id'))) or ($this->getCurrentPerformerID())) {
				return $this->getCurrentPerformerID();
			} else {
				return $this->getPrevAction()->getRole()->getProperty('account_id');
			}
		} else {
			return $this->getPrevAction()->getProperty('performer_id');
		}
	}

	function haveChilds() {
		// имеются ли дочерние процессы
		if ($this->getProperty('[childs]')->isEmpty()) {
			logRuntime('['.get_class($this).'.haveChilds->'.$this->getProperty('name').'] have no childs');
			return false;
		} else {
			logRuntime('['.get_class($this).'.haveChilds->'.$this->getProperty('name').'] have childs');
			return true;
		}
	}

	function createChildProcess($name, $performer = NULL) {
		// создание дочернего процесса
		logRuntime('['.get_class($this).'.createChildProcess->'.$this->getProperty('name').'] create child process '.$name);
		$result = 0;
		if ((is_a($this->_owner, 'ProjectInstance')) or (is_a($this->_owner, 'ProjectInstanceWrapper'))) {
			$process = $this->getConnection()->execute('select id from cs_process where name = \''.$name.'\'')->fetch();
			if ($process['id'] > 0) {
				$instance = $this->_owner->createProcessInstance($process['id'], (isNULL($performer)?$this->getCurrentPerformerID():$performer), $this->getProperty('id'));
				if ($instance['create_process_instance'] > 0) {
					$result = $instance['create_process_instance'];
					$this->initChilds();
					$this->getCurrentAction()->wait();
					$child = $this->getConnection()->getTable('CsProcessActionChild')->create();
					$child['action_id'] = $this->getCurrentAction()->getProperty('id'); 
					$child['process_id'] = $result;
					$child->save();
					define('CHILD_PROCESS_INSTANCE_ID', $result);
				}
			}
		} else {
			$this->_owner->createChildProcess($name, (isNULL($performer)?$this->getCurrentPerformerID():$performer));
		}
		return $result;
	}

	function addProperty(array $options = array()) {
		$options = array_merge(array('name' => 'added', 'description' => 'added from runtime', 'sign_id' => 1, 'type_id' => 1, 'default_value' => NULL, 'mime_type' => NULL, 'value' => NULL), $options);

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
			$property = $this->getConnection()->getTable('CsProcessProperty')->create();
			$property['name']			= $options['name'];
			$property['description']	= $options['description'];
			$property['process_id']		= $this->getProperty('[process]')->getProperty('id');
			$property['sign_id']		= $options['sign_id'];
			$property['type_id']		= $options['type_id'];
			$property['default_value']	= $options['default_value'];
			$property->save();

			$propertyvalue = $this->getConnection()->getTable('CsPropertyValue')->create();
			$propertyvalue['mime_type']	= $options['mime_type'];
			$propertyvalue['value']		= ($options['value']?$options['value']:$options['default_value']);
			$propertyvalue->save();

			$propertyinstance = $this->getConnection()->getTable('CsProcessPropertyValue')->create();
			$propertyinstance['instance_id']	= $this->getProperty('id');
			$propertyinstance['property_id']	= $property['id'];
			$propertyinstance['value_id']		= $propertyvalue['id'];
			$propertyinstance->save();

			$this->initProperties();
		}
	}

	function restartFromAction($name = NULL) {
		// перезапуск процесса с указанного действия или с начала (если не указано действие)
		if ($this->getProperty('id') > 0) {
			if (isNULL($this->getProperty('[actions]'))) {
				$this->initActions();
			}
			if (isNULL($name)) {
				$action = $this->getProperty('[actions]')->getFirstElement(); 
			} else {
				$action = $this->getProperty('[actions]')->findElementByName($name); 
			}

			if ($action->getProperty('id') > 0) {
				$result = $this->getConnection()->execute('select make_chrono_snapshot('.$this->getProperty('id').', '.$this->getCurrentAction()->getProperty('id').', '.$action->getProperty('id').', '.USER_CODE.')')->fetch();
				$result = $this->getConnection()->execute('select restart_from_action('.$this->getProperty('id').', '.$action->getProperty('id').')')->fetch();
				$this->initActions();
			}
		}
	}

	function propertyExists($name) {
		return $this->getProperty('[properties]')->elementExists($name);
	}

	function canPerform() {
		return ($this->getCurrentAction()->canPerform());
	}

	function getTemplate() {
		$result = array();
		$result['processname']						= $this->getProperty('name');
		$result['processdescription']				= $this->getProperty('description');
		$result['processinstanceid']				= $this->getProperty('id');
		$result['processparentid']					= $this->getProperty('parent_id');
		$result['processid']						= $this->getProperty('process_id');
		$result['processauthorid']					= $this->getProperty('[process]')->getProperty('author_id');
		$result['processauthorname']				= $this->getProperty('[process]')->getProperty('authorname');
		$result['processinitiatorid']				= $this->getProperty('initiator_id');
		$result['processinitiatorname']				= $this->getProperty('initiatorname');
		$result['processstatusid']					= $this->getProperty('status_id');
		$result['processstatusname']				= $this->getProperty('statusname');
		$result['processstartedat']					= $this->getProperty('started_at');
		$result['processendedat']					= $this->getProperty('ended_at');
		$result['processcurrentactionname']			= $this->getCurrentAction()->getProperty('name');
		$result['processcurrentinitiatorname']		= $this->getCurrentAction()->getProperty('initiatorname');
		$result['processcurrentperformername']		= $this->getCurrentAction()->getProperty('performername');
		$result['processcurrentstartedat']			= $this->getCurrentAction()->getProperty('started_at');
		$result['processcurrentendedat']			= $this->getCurrentAction()->getProperty('ended_at');
		$result['processnextactionname']			= $this->getNextAction()->getProperty('name');
		$result['processnextinitiatorname']			= $this->getNextAction()->getProperty('initiatorname');
		$result['processnextperformername']			= $this->getNextAction()->getProperty('performername');
		$result['processnextstartedat']				= $this->getNextAction()->getProperty('started_at');
		$result['processnextendedat']				= $this->getNextAction()->getProperty('ended_at');
		$result['processprevactionname']			= $this->getPrevAction()->getProperty('name');
		$result['processprevinitiatorname']			= $this->getPrevAction()->getProperty('initiatorname');
		$result['processprevperformername']			= $this->getPrevAction()->getProperty('performername');
		$result['processprevstartedat']				= $this->getPrevAction()->getProperty('started_at');
		$result['processprevendedat']				= $this->getPrevAction()->getProperty('ended_at');

		$project = $this->getOwnerByClass('ProjectInstanceWrapper');
		if (isNotNULL($project)) {
			$result['processinstanceviewuri']		= $this->getEngine()->getTemplate()->simpleProcess("%%SERVER_URI%%?module=%%INBOX_PROCESS_MODULE%%")."&project_instance_id=".$project->getProperty('id')."&process_instance_id=".$this->getProperty('id')."&process_id=".$this->getProperty('process_id');
			$result['processinstanceprinturi']		= $this->getEngine()->getTemplate()->simpleProcess("%%SERVER_URI%%?module=%%INBOX_PROCESS_MODULE%%")."&media=print&project_instance_id=".$project->getProperty('id')."&process_instance_id=".$this->getProperty('id')."&process_id=".$this->getProperty('process_id');
			$result['processinstanceexecuteuri']	= $this->getEngine()->getTemplate()->simpleProcess("%%SERVER_URI%%?module=%%INBOX_ACTION_MODULE%%")."&action=execute&project_instance_id=".$project->getProperty('id')."&process_instance_id=".$this->getProperty('id')."&process_id=".$this->getProperty('process_id');
		}

		foreach ($this->getProperties() as $property) {
			$result['processproperty['.$property->getProperty('name').']'] =  $property->getPropertyValue();
		}

		return $result;
	}

	function initTemplate() {
		// инициализация шаблона для процесса
		$this->getEngine()->getTemplate()->setTemplate($this->getTemplate());
	}

	function initFullTemplate() {
		// инициализация шаблона для процесса и текущего действия
		$this->getCurrentAction()->initTemplate();
	}

	function generateForm() {
		$this->getFormManager()->printGeneratedForm(array('process' => $this));
	}

	function generateValidationCode() {
		$this->getFormManager()->printValidationCode(array('process' => $this));
	}

	function generateFinalCode() {
		$this->getFormManager()->printFinalCode(array('process' => $this));
	}

	function generateFullCode() {
		$this->getFormManager()->printFullCode(array('process' => $this));
	}

	function sendMessage($event = 0) {
		global $event_names;

		foreach ($this->getProperty('[process]')->getProcessTransports() as $transport) {
			if ($transport->getProperty('event_id') == $event) { 
				if (isNULL($transport->getProperty('recipients_template'))) { 
					$this->initRecipients();
				} else {
					$this->_instance['[recipients]'] = array('[mail]' => array(), '[icq]' => array(), '[jabber]' => array(), '[cell]' => array());
				}
				$this->initFullTemplate();
				
				logRuntime('['.get_class($this).'.sendMessage->'.$this->getProperty('name').'] sending message for event '.$event_names[$event].' by transport '.$transport->getProperty('class_name'));
				
				if (preg_match('/mail/i', $transport->getProperty('class_name'))) {
					$transport->send(array('to' => $this->_instance['[recipients]']['[mail]']));
				} elseif (preg_match('/icq/i', $transport->getProperty('class_name'))) {
					$transport->send(array('to' => $this->_instance['[recipients]']['[icq]']));
				} elseif (preg_match('/jabber/i', $transport->getProperty('class_name'))) {
					$transport->send(array('to' => $this->_instance['[recipients]']['[jabber]']));
				} elseif (preg_match('/sms/i', $transport->getProperty('class_name'))) {
					$transport->send(array('to' => $this->_instance['[recipients]']['[cell]']));
				}
			}
		}

		$this->_instance['[recipients]'] = NULL;
	}

	function undoToAction($action_id = NULL) {
		if (isNotNULL($action_id)) {
			$found_it = false;
			foreach ($this->getProperty('[actions]')->getElements() as $action) {
				if ($action->getProperty('action_id') == $action_id) {
					$action->setProperty('status_id', Constants::ACTION_STATUS_IN_PROGRESS);
					$action->setProperty('started_at', NULL);
					$action->setProperty('ended_at', NULL);
					$action->save();
					$found_it = true;
				} elseif ($found_it) {
					$action->setProperty('performer_id', NULL);
					$action->setProperty('initiator_id', NULL);
					$action->setProperty('status_id', NULL);
					$action->setProperty('started_at', NULL);
					$action->setProperty('ended_at', NULL);
					$action->save();
				}
			}
			if ($found_it) {
				$this->initActions();
			}
		}
	}
}
?>