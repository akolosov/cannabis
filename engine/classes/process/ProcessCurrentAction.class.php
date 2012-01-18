<?php

// $Id$

class ProcessCurrentAction extends Core { // действие экзепляра процесса

	public $_action = array();

	function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
		if (($id > 0) && ($owner <> NULL)) {

			parent::__construct($owner, $id, $owner->getConnection());

			if (is_null($options['data'])) {
				$action = $this->getConnection()->execute('select * from process_instance_actions_list where id = '.$this->_id)->fetch();
			} else {
				$action = $options['data'];
			}
			foreach ($action as $key => $data) {
				$this->_action[$key] = $data;
			}

			if ((is_a($this->_owner, 'ProcessInstanceWrapper')) or (is_a($this->_owner, 'ProcessInstance'))) {
				$this->_action['[action]']	= $this->_owner->getProperty('[process]')->getProcessAction($this->getProperty('name'));
			} else {
				$this->_action['[action]']	= new ProcessAction($this, $this->getProperty('action_id'));
			}

			$this->initPerformers();

			$this->_action['[model]']		= "CsProcessCurrentAction";

			if (($this->getProperty('is_interactive') == Constants::TRUE) or ($this->getProperty('type_id') == Constants::ACTION_TYPE_INFO)) {
				$this->_action['[form_file]']	= CACHE_PATH.DIRECTORY_SEPARATOR."form_".$this->getProperty('action_id')."_".$this->getProperty('id')."_".USER_CODE.".php";
			}
			$this->_action['[code_file]']	= CACHE_PATH.DIRECTORY_SEPARATOR."code_".$this->getProperty('action_id')."_".$this->getProperty('id')."_".USER_CODE.".php";
			$this->_action['[recipients]']	= array('[mail]' => array(), '[icq]' => array(), '[jabber]' => array(), '[cell]' => array());
		}
	}

	function getProperty($name) {
		return $this->_action[$name];
	}

	function getActionProperty($name) {
		return $this->_action['[action]']->getProperty($name);
	}

	function getAction() {
		return $this->_action;
	}
	
	function getRole() {
		return $this->_action['[action]']->getProperty('[role]');
	}
	
	function getPerformer($name) {
		return $this->_action['[performers]']->getElement($name);
	}

	function getPerformers() {
		return $this->_action['[performers]']->getElements();
	}

	function initPerformers() {
		$this->_action['[performers]']	= NULL;
		$this->_action['[performers]']	= new Collection($this);
		
		$performers = $this->getConnection()->execute('select * from process_instance_actions_performers_list where instance_action_id = '.$this->_id)->fetchAll();
		foreach($performers as $performer) {
			$this->_action['[performers]']->setElement(($performer['performername']?$performer['performername']:$performer['initiatorname']), new ProcessCurrentActionPerformer($this, $performer['id'], array('data' => $performer)));
		}
	}

	function setProperty($name, $value, $lazy = false) {
		$this->_action[$name] = $value;
		if ($lazy == false) {
			$this->save();
		}
		logRuntime('['.get_class($this).'.setProperty->'.$this->getProperty('name').'] set property '.$name.' value to '.($value?$value:'NULL').' for action '.$this->getProperty('name'));
	}

	private function initRecipients() {
		$this->_action['[recipients]']	= array('[mail]' => array(), '[icq]' => array(), '[jabber]' => array(), '[cell]' => array());
		
		$account = $this->getConnection()->execute('select * from accounts_tree where id = '.$this->getProperty('performer_id').';')->fetch();
		if (isNotNULL($account['email'])) {
			$this->_action['[recipients]']['[mail]']['['.$account['name'].']'] = $account['email'];
		}
		if (isNotNULL($account['icq'])) {
			$this->_action['[recipients]']['[icq]']['['.$account['name'].']'] = $account['icq'];
		}
		if (isNotNULL($account['jabber'])) {
			$this->_action['[recipients]']['[jabber]']['['.$account['name'].']'] = $account['jabber'];
		}
		if ((isNotNULL($account['cell'])) and (isNotNULL($account['cellopgate']))) {
			$account['cell'] = preg_replace('/[^\d]/', '', preg_replace('/^(8)(.*)/', '7$2', $account['cell']));
			$this->_action['[recipients]']['[cell]']['['.$account['name'].']'] = $account['cell'].'@'.$account['cellopgate'];
		}

		if ($this->getProperty('performer_id') <> $this->getProperty('initiator_id')) {
			$account = $this->getConnection()->execute('select * from accounts_tree where id = '.$this->getProperty('initiator_id').';')->fetch();
			if (isNotNULL($account['email'])) {
				$this->_action['[recipients]']['[mail]']['['.$account['name'].']'] = $account['email'];
			}
			if (isNotNULL($account['icq'])) {
				$this->_action['[recipients]']['[icq]']['['.$account['name'].']'] = $account['icq'];
			}
			if (isNotNULL($account['jabber'])) {
				$this->_action['[recipients]']['[jabber]']['['.$account['name'].']'] = $account['jabber'];
			}
			if ((isNotNULL($account['cell'])) and (isNotNULL($account['cellopgate']))) {
				$account['cell'] = preg_replace('/[^\d]/', '', preg_replace('/^(8)(.*)/', '7$2', $account['cell']));
				$this->_action['[recipients]']['[cell]']['['.$account['name'].']'] = $account['cell'].'@'.$account['cellopgate'];
			}
		}
		
		$account = $this->getConnection()->execute('select * from accounts_tree where id = '.$this->_owner->getProperty('initiator_id').';')->fetch();
		if (isNotNULL($account['email'])) {
			$this->_action['[recipients]']['[mail]']['['.$account['name'].']'] = $account['email'];
		}
		if (isNotNULL($account['icq'])) {
			$this->_action['[recipients]']['[icq]']['['.$account['name'].']'] = $account['icq'];
		}
		if (isNotNULL($account['jabber'])) {
			$this->_action['[recipients]']['[jabber]']['['.$account['name'].']'] = $account['jabber'];
		}
		if ((isNotNULL($account['cell'])) and (isNotNULL($account['cellopgate']))) {
			$account['cell'] = preg_replace('/[^\d]/', '', preg_replace('/^(8)(.*)/', '7$2', $account['cell']));
			$this->_action['[recipients]']['[cell]']['['.$account['name'].']'] = $account['cell'].'@'.$account['cellopgate'];
		}
	}

	function setPerformer($performer, $lazy = false) {
		$account = $this->getConnection()->execute('select * from cs_account where id = '.$performer.';')->fetch();
		if (isNotNULL($this->getProperty('performer_id')) and ($this->getProperty('performer_id') <> $performer)) {
			if (isNULL($childaction = $this->_action['[performers]']->findElementByPropertyValue('performer_id', $performer))) {
				$childaction = $this->createChildAction($performer);
			}
			$childaction->setPerformer($performer, $lazy);
		} else {
			if (isNULL($this->getProperty('initiator_id'))) {
				$this->setInitiator($performer, $lazy);
			}
			$delegate = $this->getConnection()->execute('select * from cs_account where id = (select get_real_performer('.$this->getProperty('initiator_id').'));')->fetch();
			if ((isNotNULL($delegate)) and ($this->getProperty('initiator_id') <> USER_CODE) and ($delegate['id'] <> $this->getProperty('initiator_id'))) {
				$this->setProperty('performer_id', $delegate['id'], $lazy);
				$this->setProperty('performername', $delegate['name'], true);
				logRuntime('['.get_class($this).'.setPerformer->'.$this->getProperty('name').'] set performer to '.($performer?$performer:'NULL').' for action '.$this->getProperty('name').' (delegated by '.$account['name'].')');
			} elseif (isNULL($this->getProperty('performer_id'))) {
				$this->setProperty('performer_id', $performer, $lazy);
				$this->setProperty('performername', $account['name'], true);
				logRuntime('['.get_class($this).'.setPerformer->'.$this->getProperty('name').'] set performer to '.($performer?$performer:'NULL').' for action '.$this->getProperty('name'));
			}
		}
	}

	function setInitiator($initiator, $lazy = false) {
		$account = $this->getConnection()->execute('select * from cs_account where id = '.$initiator.';')->fetch();
		if (isNotNULL($this->getProperty('initiator_id')) and (($this->getProperty('initiator_id') <> $initiator) and  ($this->getProperty('performer_id') <> $initiator))) {
			if (isNULL($childaction = $this->_action['[performers]']->findElementByPropertyValue('initiator_id', $initiator))) {
				$childaction = $this->createChildAction($initiator);
			}
			$childaction->setInitiator($initiator, $lazy);
		} elseif (isNULL($this->getProperty('initiator_id'))) {
			$this->setProperty('initiator_id', $initiator, $lazy);
			$this->setProperty('initiatorname', $account['name'], true);
			logRuntime('['.get_class($this).'.setInitiator->'.$this->getProperty('name').'] set initiator to '.($initiator?$initiator:'NULL').' for action '.$this->getProperty('name'));
		}
	}
	
	function save() {
		// сохранение действия
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] saving data to '.$this->_action['[model]']);
		return $this->saveData($this->_action['[model]'], $this->_action);
	}

	function view($print = false) {
		// установка формы по умолчанию для просмотра процесса
		if (!$this->getProperty('form')) {
			$this->setProperty('form', $this->getFormManager()->generateForm(array('action' => $this, 'print' => $print)), true);
		}

		// сохранение формы в файл 
		$file = fopen($this->getProperty('[form_file]'), 'w+');
		flock($file, LOCK_EX);
		fwrite($file, $this->getProperty('form'));
		flock($file, LOCK_UN);
		fclose($file);
		logRuntime('['.get_class($this).'.execute->'.$this->getProperty('name').'] '.$this->getProperty('name').' action form cached');
	}

	private function executeAction($performer = NULL) {
		global $status_names;
		// запуск действия
		if ($this->getProperty('status_id') <> Constants::ACTION_STATUS_IN_PROGRESS) {
			if (ACTION == 'execute') {
				// отправка уведомления о начале запуска действия
				$this->sendMessage(Constants::EVENT_BEFORE_ANY_ACTION_START);
				if ($this->isInteractive() == Constants::TRUE) {
					$this->sendMessage(Constants::EVENT_BEFORE_INT_ACTION_START);
				} else {
					$this->sendMessage(Constants::EVENT_BEFORE_NOT_ACTION_START);
				}
			}
			// действие не запущено
			// установка признака запуска
			$this->setProperty('status_id', Constants::ACTION_STATUS_IN_PROGRESS, true);
			$this->setProperty('statusname', $status_names[$this->getProperty('status_id')-1], true);

			// установка даты и времени запуска действия
			if ((EXECUTE_IMMEDIATELY === true) or (!$this->isInteractive())) {
				$this->setProperty('started_at', strftime("%Y-%m-%d %H:%M:%S", time()), true);
			} else {
				if ($this->getProperty('is_interactive') == Constants::TRUE)  {
					$this->setProperty('started_at', NULL, true);
				} else {
					if ($this->canPerform()) {
						$this->setProperty('started_at', strftime("%Y-%m-%d %H:%M:%S", time()), true);
					}
				}
			}

			// сброс даты и времени окончания действия
			$this->setProperty('ended_at', NULL, true);
			// установка инициатора действия
			if (isNULL($this->getProperty('initiator_id'))) {
				$this->setInitiator((isNULL($this->getProperty('performer_id'))?(isNULL($performer)?(isNULL($this->getRole()->getProperty('account_id'))?USER_CODE:$this->getRole()->getProperty('account_id')):$performer):$this->getProperty('performer_id')), true);
			}

			// установка исполнителя действия
			if (isNULL($this->getProperty('performer_id'))) {
				$this->setPerformer((isNULL($performer)?(isNULL($this->getRole()->getProperty('account_id'))?USER_CODE:$this->getRole()->getProperty('account_id')):$performer), true);
			}
			$this->save();
			logRuntime('['.get_class($this).'.execute->'.$this->getProperty('name').'] '.$this->getProperty('name').' action executed');

			if (ACTION == 'execute') {
				// отправка уведомления о запуске действия
				$this->sendMessage(Constants::EVENT_AFTER_ANY_ACTION_START);
				if ($this->isInteractive() == Constants::TRUE) {
					$this->sendMessage(Constants::EVENT_AFTER_INT_ACTION_START);
				} else {
					$this->sendMessage(Constants::EVENT_AFTER_NOT_ACTION_START);
				}
			}
		} else {
			// действие уже запущено
			// установка даты и времени запуска действия
			if (isNULL($this->getProperty('started_at'))) {
				$this->setProperty('started_at', strftime("%Y-%m-%d %H:%M:%S", time()), true);
				$this->save();

				if (ACTION == 'execute') {
					// отправка уведомления о запуске действия
					$this->sendMessage(Constants::EVENT_AFTER_ANY_ACTION_START);
					if ($this->isInteractive() == Constants::TRUE) {
						$this->sendMessage(Constants::EVENT_AFTER_INT_ACTION_START);
					} else {
						$this->sendMessage(Constants::EVENT_AFTER_NOT_ACTION_START);
					}
				}
			}
			logRuntime('['.get_class($this).'.execute->'.$this->getProperty('name').'] '.$this->getProperty('name').' already in progress');
		}

		// установка формы по умолчанию, если не указана в интерактивном действии
		if ($this->isInteractive() == Constants::TRUE) {
			if (!$this->getProperty('form')) {
				$this->setProperty('form', "<?php\n  \$this->generateForm();\n?>", true);
			} else {
				$this->setProperty('form', (str_ireplace('%%ACTIONFORM%%', "<?php\n  \$this->generateForm();\n?>", $this->getProperty('form'))));
				$this->setProperty('form', (str_ireplace('%%FORM%%', "<?php\n  \$this->generateForm();\n?>", $this->getProperty('form'))));
			}
		}

		// сохранение формы в файл 
		if ($this->isInteractive() == Constants::TRUE) {
			$file = fopen($this->getProperty('[form_file]'), 'w+');
			flock($file, LOCK_EX);
			fwrite($file, $this->getProperty('form'));
			flock($file, LOCK_UN);
			fclose($file);
			logRuntime('['.get_class($this).'.execute->'.$this->getProperty('name').'] '.$this->getProperty('name').' action form cached');
		}

		// установка кода по умолчанию, если не указано
		if (!$this->getProperty('code')) {
			$this->setProperty('code', "<?php\n".$this->getFormManager()->generateFullCode(array('process' => $this->_owner))."?>", true);
		} else {
			$this->setProperty('code', (str_ireplace('%%VALIDATIONCODE%%', $this->getFormManager()->generateValidationCode(array('process' => $this->_owner)), $this->getProperty('code'))));
			$this->setProperty('code', (str_ireplace('%%FINALCODE%%', $this->getFormManager()->generateFinalCode(array('process' => $this->_owner)), $this->getProperty('code'))));
			$this->setProperty('code', (str_ireplace('%%FULLCODE%%', "<?php\n".$this->getFormManager()->generateFullCode(array('process' => $this->_owner))."?>", $this->getProperty('code'))));
			$this->setProperty('code', (str_ireplace('%%CODE%%', "<?php\n".$this->getFormManager()->generateFullCode(array('process' => $this->_owner))."?>", $this->getProperty('code'))));
		}

		// сохранение кода в файл
		$file = fopen($this->getProperty('[code_file]'), 'w+');
		flock($file, LOCK_EX);
		fwrite($file, $this->getProperty('code'));
		flock($file, LOCK_UN);
		fclose($file);
		logRuntime('['.get_class($this).'.execute->'.$this->getProperty('name').'] '.$this->getProperty('name').' action code cached');
	}
	
	function createChildAction($performer = NULL) {
		if ((isNotNULL($performer) and ($performer <> $this->getProperty('performer_id')))) {
			$childaction = new ProcessCurrentActionPerformer($this, 0);
			if (isNotNULL($childaction)) {
				$account = $this->getConnection()->execute('select * from cs_account where id = '.$performer.';')->fetch();
				$childaction->setProperty('performer_id', $performer, true);
				$childaction->setProperty('initiator_id', $performer, true);
				$childaction->setProperty('performername', $account['name'], true);
				$childaction->setProperty('initiatorname', $account['name'], true);
				$childaction->setProperty('instance_action_id', $this->_id, true);
				$childaction->setProperty('id', $childaction->save());
				$this->_action['[performers]']->setElement(($account['name']?$account['name']:"childaction".$childaction['id']), $childaction);				
			}
			return $childaction;
		} else {
			return NULL;
		}
	}

	function execute($performer = NULL) {
		// запуск действия
		if ($this->isMultiple()) {
			// многопользовательское действие
			if (isNULL($childaction = $this->_action['[performers]']->findElementByPropertyValue('performer_id', $performer))) {
				// исполнитель пока отсутствует
				$childaction = $this->createChildAction($performer);
			}
			// и запускаем дочернее действие
			if (isNotNULL($childaction)) {
				$childaction->execute($performer);
			} elseif ($this->getProperty('performer_id') == $performer) {
				$this->executeAction($performer);
			}
		} else {
			// простое действие
			$this->executeAction($performer);
		}
	}

	function isInteractive() {
		// интерактивное ли действие
		return ($this->getProperty('is_interactive') == Constants::TRUE);
	}
	
	function isMultiple() {
		// многопользовательское ли действие
		return (isNotNULL($this->getPerformers()));
	}

	private function completeAction() {
		global $status_names;
		// завершение действия

		// отправка уведомления перед окончанием действия
		$this->sendMessage(Constants::EVENT_BEFORE_ANY_ACTION_END);
		if ($this->isInteractive() == Constants::TRUE) {
			$this->sendMessage(Constants::EVENT_BEFORE_INT_ACTION_END);
		} else {
			$this->sendMessage(Constants::EVENT_BEFORE_NOT_ACTION_END);
		}
		
		logMessage('попытка завершить действие "'.$this->getProperty('name').'"');
		// установка признака завершения действия
		$this->setProperty('status_id', Constants::ACTION_STATUS_COMPLETED, true);
		$this->setProperty('statusname', $status_names[$this->getProperty('status_id')-1], true);
		// установка даты и времени окончания действия
		$this->setProperty('ended_at', strftime("%Y-%m-%d %H:%M:%S", time()), true);
		$this->save();
		logRuntime('['.get_class($this).'.complete->'.$this->getProperty('name').'] set current status of '.$this->getProperty('name').' to '.$this->getProperty('status_id').' ('.$status_names[$this->getProperty('status_id')-1].')');

		if ($this->getFormManager()->formIsValid()) {
			$this->getFormManager()->formPassed();
		} else {
			$this->getFormManager()->formNotPassed();
		}

		logMessage('действие "'.$this->getProperty('name').'" завершено');

		// отправка уведомления об окончании действия
		$this->sendMessage(Constants::EVENT_AFTER_ANY_ACTION_END);
		if ($this->isInteractive() == Constants::TRUE) {
			$this->sendMessage(Constants::EVENT_AFTER_INT_ACTION_END);
		} else {
			$this->sendMessage(Constants::EVENT_AFTER_NOT_ACTION_END);
		}
	}

	function complete() {
		// завершение действия
		if (($this->isMultiple()) and (!$this->isPerformersComplete())) {
			// многопользовательское действие
			if (isNotNULL($childaction = $this->_action['[performers]']->findElementByPropertyValue('performer_id', USER_CODE))) {
				$childaction->complete();
			} elseif ($this->getProperty('performer_id') == $performer) {
				if ($this->isPerformersComplete()) {
					$this->completeAction();
				} else {
					$this->waitAction();
				}
			}
		} else {
			// простое действие
			$this->completeAction();
		}
	}

	private function skipAction($performer = NULL) {
		// пропуск действия
		global $status_names;

		logMessage('попытка пропустить действие "'.$this->getProperty('name').'"');
		
		// установка признаков (инициатор, исполнитель, статус, дата и время начала и окончания) пропущенного действия
		$this->setPerformer((isNULL($this->getProperty('performer_id'))?(isNULL($performer)?(isNULL($this->getRole()->getProperty('account_id'))?USER_CODE:$this->getRole()->getProperty('account_id')):$performer):$this->getProperty('performer_id')));
		$this->setInitiator((isNULL($this->getProperty('performer_id'))?(isNULL($this->getProperty('initiator_id'))?(isNULL($performer)?(isNULL($this->getRole()->getProperty('account_id'))?USER_CODE:$this->getRole()->getProperty('account_id')):$performer):$this->getProperty('initiator_id')):$this->getProperty('performer_id')));
		$this->setProperty('status_id', Constants::ACTION_STATUS_SKIPED, true);
		$this->setProperty('statusname', $status_names[$this->getProperty('status_id')-1], true);
		$this->setProperty('started_at', strftime("%Y-%m-%d %H:%M:%S", time()), true);
		$this->setProperty('ended_at', strftime("%Y-%m-%d %H:%M:%S", time()), true);
		$this->save();
		
		logRuntime('['.get_class($this).'.skip->'.$this->getProperty('name').'] set current status of '.$this->getProperty('name').' to '.$this->getProperty('status_id').' ('.$status_names[$this->getProperty('status_id')-1].')');

		logMessage('действие "'.$this->getProperty('name').'" пропущено');
	}

	function skip($performer = NULL) {
		// запуск действия
		if (($this->isMultiple()) and (!$this->isPerformersComplete())) {
			// многопользовательское действие
			if (isNULL($childaction = $this->_action['[performers]']->findElementByPropertyValue('performer_id', $performer))) {
				// исполнитель пока отсутствует
				$childaction = $this->createChildAction($performer);
			}
			// и запускаем дочернее действие
			if (isNotNULL($childaction)) {
				$childaction->skip($performer);
			} elseif ($this->getProperty('performer_id') == $performer) {
				$this->skipAction($performer);
			}
		} else {
			// простое действие
			$this->skipAction($performer);
		}
	}
	
	private function waitAction() {
		// приостановить действие
		global $status_names;

		logMessage('попытка установить режим ожидания для действия "'.$this->getProperty('name').'"');
		// установка признака ожидания
		$this->setProperty('status_id', Constants::ACTION_STATUS_WAITING, true);
		$this->setProperty('statusname', $status_names[$this->getProperty('status_id')-1], true);
		// установка даты и времени входа в режим ожидания
		$this->setProperty('ended_at', strftime("%Y-%m-%d %H:%M:%S", time()), true);
		$this->save();

		logRuntime('['.get_class($this).'.wait->'.$this->getProperty('name').'] set current status of '.$this->getProperty('name').' to '.$this->getProperty('status_id').' ('.$status_names[$this->getProperty('status_id')-1].')');

		logMessage('режим ожидания для действия "'.$this->getProperty('name').'" установлен');
	}

	function wait() {
		// завершение действия
		if (($this->isMultiple()) and (!$this->isPerformersComplete())) {
			// многопользовательское действие
			if (isNotNULL($childaction = $this->_action['[performers]']->findElementByPropertyValue('performer_id', USER_CODE))) {
				$childaction->wait();
			} elseif ($this->getProperty('performer_id') == $performer) {
				if ($this->isPerformersComplete()) {
					$this->completeAction();
				} else {
					$this->waitAction();
				}
			}
		} else {
			// простое действие
			$this->waitAction();
		}
	}
	
	function isComplete() {
		// действие завершено?
		global $status_names;

		logRuntime('['.get_class($this).'.isCompeted->'.$this->getProperty('name').'] '.$this->getProperty('name').' current status is '.$this->getProperty('status_id').' ('.($this->getProperty('status_id')?$status_names[$this->getProperty('status_id')-1]:'NULL').')');
		return ((((($this->getProperty('status_id') == Constants::ACTION_STATUS_COMPLETED) or ($this->getProperty('status_id') == Constants::ACTION_STATUS_SKIPED)) and ($this->isPerformersComplete()))) or ($this->getProperty('type_id') == Constants::ACTION_TYPE_INFO));
	}

	function isPerformersComplete() {
		foreach ($this->getPerformers() as $performer) {
			if (($performer->getProperty('status_id') <> Constants::ACTION_STATUS_COMPLETED) and ($performer->getProperty('status_id') <> Constants::ACTION_STATUS_SKIPED) and ($this->getProperty('type_id') == Constants::ACTION_TYPE_INFO)) {
				return false;
			}
		}
		return true;
	}

	function isWaiting() {
		// действие в режиме ожидания?
		global $status_names;

		logRuntime('['.get_class($this).'.isWaiting->'.$this->getProperty('name').'] '.$this->getProperty('name').' current status is '.$this->getProperty('status_id').' ('.($this->getProperty('status_id')?$status_names[$this->getProperty('status_id')-1]:'NULL').')');
		return ($this->getProperty('status_id') == Constants::ACTION_STATUS_WAITING);
	}

	function canPerform() {
		// может текущий пользователь выполнить действие?

		return (($this->getProperty('performer_id') == USER_CODE) or (isNotNULL(($performer = $this->getPerformer(USER_NAME)))) or ($this->isInteractive() == false));
	}

	function getTemplate() {
		$result = array();
		$result['actionname']				= $this->getProperty('name');
		$result['actiondescription']		= $this->getProperty('description');
		$result['actioninstanceid']			= $this->getProperty('id');
		$result['actionprocessinstanceid']	= $this->getProperty('instance_id');
		$result['actionparentid']			= $this->getProperty('parent_id');
		$result['actionid']					= $this->getProperty('action_id');
		$result['actioninitiatorid']		= $this->getProperty('initiator_id');
		$result['actioninitiatorname']		= $this->getProperty('initiatorname');
		$result['actionperformerid']		= $this->getProperty('performer_id');
		$result['actionperformername']		= $this->getProperty('performername');
		$result['actionstatusid']			= $this->getProperty('status_id');
		$result['actionstatusname']			= $this->getProperty('statusname');
		$result['actiontypeid']				= $this->getProperty('type_id');
		$result['actiontypename']			= $this->getProperty('typename');
		$result['actionplaned']				= $this->getProperty('planed');
		$result['actionstartedat']			= $this->getProperty('started_at');
		$result['actionendedat']			= $this->getProperty('ended_at');

		return $result;
	}
	
	function initTemplate() {
		// инициализация шаблона для родительского процесса и текущего действия
		$this->getEngine()->getTemplate()->setTemplate(array_merge($this->_owner->getTemplate(), $this->getTemplate()));
	}
	
	function haveObjectProperty() {
		foreach ($this->getProperty('[action]')->getProperty('[properties]')->getElements() as $property) {
			if (($property->getProperty('type_id') == Constants::PROPERTY_TYPE_OBJECT) and ($property->getProperty('is_active') == Constants::TRUE)) {
				return true;
			}
		}
		return false;
	}

	function haveNonReadonlyProperty() {
		foreach ($this->getProperty('[action]')->getProperty('[properties]')->getElements() as $property) {
			if (($property->getProperty('is_readonly') == Constants::FALSE) and ($property->getProperty('is_hidden') == Constants::FALSE) and ($property->getProperty('is_active') == Constants::TRUE)) {
				return true;
			}
		}
		return false;
	}
	
	function sendMessage($event = 0) {
		global $event_names;
		
		foreach ($this->getProperty('[action]')->getTransports() as $transport) {
			if ($transport->getProperty('event_id') == $event) {
				if (isNULL($transport->getProperty('recipients_template'))) { 
					$this->initRecipients();
				} else {
					$this->_action['[recipients]'] = array('[mail]' => array(), '[icq]' => array(), '[jabber]' => array(), '[cell]' => array());
				}
				$this->initTemplate();
				
				logRuntime('['.get_class($this).'.sendMessage->'.$this->getProperty('name').'] sending message for event '.$event_names[$event].' by transport '.$transport->getProperty('class_name'));
				
				if (preg_match('/mail/i', $transport->getProperty('class_name'))) {
					$transport->send(array('to' => $this->_action['[recipients]']['[mail]']));
				} elseif (preg_match('/icq/i', $transport->getProperty('class_name'))) {
					$transport->send(array('to' => $this->_action['[recipients]']['[icq]']));
				} elseif (preg_match('/jabber/i', $transport->getProperty('class_name'))) {
					$transport->send(array('to' => $this->_action['[recipients]']['[jabber]']));
				} elseif (preg_match('/sms/i', $transport->getProperty('class_name'))) {
					$transport->send(array('to' => $this->_action['[recipients]']['[cell]']));
				}
			}
		}
		$this->_action['[recipients]'] = NULL;

		$this->_owner->sendMessage($event);
	}
}
?>