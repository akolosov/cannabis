<?php

// $Id$

class ProcessCurrentActionPerformer extends Core {

	public $_performer = array();

	function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
		if ($owner <> NULL) {
			parent::__construct($owner, $id, $owner->getConnection());
	
			if ($id > 0) {
				if (is_null($options['data'])) {
					$performer = $this->getConnection()->execute('select * from process_instance_actions_performers_list where id = '.$this->_id)->fetch();
				} else {
					$performer = $options['data'];
				}
				foreach ($performer as $key => $data) {
					$this->_performer[$key] = $data;
				}
			}
	
			$this->_performer['[model]']	= "CsProcessCurrentActionPerformer";
		}
	}

	function setPerformer($performer, $lazy = false) {
		$account = $this->getConnection()->execute('select * from cs_account where id = '.$performer.';')->fetch();
		if (isNULL($this->getProperty('initiator_id'))) {
			$this->setInitiator($performer, $lazy);
		}
		$delegate = $this->getConnection()->execute('select * from cs_account where id = (select get_real_performer('.$this->getProperty('initiator_id').'));')->fetch();
		if ((isNotNULL($delegate)) and ($this->getProperty('initiator_id') <> USER_CODE) and ($delegate['id'] <> $this->getProperty('initiator_id'))) {
			$this->setProperty('performer_id', $delegate['id'], $lazy);
			$this->setProperty('performername', $delegate['name'], true);
			logRuntime('['.get_class($this).'.setPerformer->'.$this->getProperty('name').'] set performer to '.($performer?$performer:'NULL').' for action '.$this->getProperty('name').' (delegated by '.$account['name'].')');
		} elseif (isNULL($this->getProperty('performer_id')))  {
			$this->setProperty('performer_id', $performer, $lazy);
			$this->setProperty('performername', $account['name'], true);
			logRuntime('['.get_class($this).'.setPerformer->'.$this->getProperty('name').'] set performer to '.($performer?$performer:'NULL').' for action '.$this->getProperty('name'));
		}
	}

	function setInitiator($initiator, $lazy = false) {
		if (isNULL($this->getProperty('initiator_id'))) { 
			$account = $this->getConnection()->execute('select * from cs_account where id = '.$initiator.';')->fetch();
			$this->setProperty('initiator_id', $initiator, $lazy);
			$this->setProperty('initiatorname', $account['name'], true);
			logRuntime('['.get_class($this).'.setInitiator->'.$this->getProperty('name').'] set child initiator to '.($initiator?$initiator:'NULL').' for action '.$this->getProperty('name'));
		}
	}
	
	function execute($performer = NULL) {
		global $status_names;
		// запуск действия
		if ($this->getProperty('status_id') <> Constants::ACTION_STATUS_IN_PROGRESS) {
			// действие не запущено
			// установка признака запуска
			$this->setProperty('status_id', Constants::ACTION_STATUS_IN_PROGRESS, true);
			$this->setProperty('statusname', $status_names[$this->getProperty('status_id')-1], true);

			// установка даты и времени запуска действия
			if ((EXECUTE_IMMEDIATELY === true) or (!$this->_owner->isInteractive())) {
				$this->setProperty('started_at', strftime("%Y-%m-%d %H:%M:%S", time()), true);
			} else {
				if ($this->_owner->getProperty('is_interactive') == Constants::TRUE)  {
					$this->setProperty('started_at', NULL, true);
				} else {
					if ($this->_owner->canPerform()) {
						$this->setProperty('started_at', strftime("%Y-%m-%d %H:%M:%S", time()), true);
					}
				}
			}

			// сброс даты и времени окончания действия
			$this->setProperty('ended_at', NULL, true);
			// установка инициатора действия
			if (isNULL($this->getProperty('initiator_id'))) {
				$this->setInitiator((isNULL($this->getProperty('performer_id'))?(isNULL($performer)?(isNULL($this->_owner->getRole()->getProperty('account_id'))?USER_CODE:$this->_owner->getRole()->getProperty('account_id')):$performer):$this->getProperty('performer_id')), true);
			}

			// установка исполнителя действия
			if (isNULL($this->getProperty('performer_id'))) {
				$this->setPerformer((isNULL($performer)?(isNULL($this->_owner->getRole()->getProperty('account_id'))?USER_CODE:$this->_owner->getRole()->getProperty('account_id')):$performer), true);
			}
			$this->save();
			logRuntime('['.get_class($this).'.execute->'.$this->getProperty('name').'] '.$this->getProperty('name').' child action executed');
		} else {
			// действие уже запущено
			// установка даты и времени запуска действия
			if (isNULL($this->getProperty('started_at'))) {
				$this->setProperty('started_at', strftime("%Y-%m-%d %H:%M:%S", time()), true);
				$this->save();
			}
			logRuntime('['.get_class($this).'.execute->'.$this->getProperty('name').'] '.$this->getProperty('name').' child already in progress');
		}

		// установка формы по умолчанию, если не указана в интерактивном действии
		if ($this->_owner->isInteractive() == Constants::TRUE) {
			if (!$this->getProperty('form')) {
				$this->setProperty('form', "<?php\n  \$this->generateForm();\n?>", true);
			} else {
				$this->setProperty('form', (str_ireplace('%%ACTIONFORM%%', "<?php\n  \$this->generateForm();\n?>", $this->getProperty('form'))));
				$this->setProperty('form', (str_ireplace('%%FORM%%', "<?php\n  \$this->generateForm();\n?>", $this->getProperty('form'))));
			}
		}

		// сохранение формы в файл 
		if ($this->_owner->isInteractive() == Constants::TRUE) {
			$file = fopen($this->_owner->getProperty('[form_file]'), 'w+');
			flock($file, LOCK_EX);
			fwrite($file, $this->getProperty('form'));
			flock($file, LOCK_UN);
			fclose($file);
			logRuntime('['.get_class($this).'.execute->'.$this->getProperty('name').'] '.$this->getProperty('name').' child action form cached');
		}

		// установка кода по умолчанию, если не указано
		if (!$this->getProperty('code')) {
			$this->setProperty('code', "<?php\n".$this->getFormManager()->generateFullCode(array('process' => $this->getOwnerByClass('ProcessInstanceWrapper')))."?>", true);
		} else {
			$this->setProperty('code', (str_ireplace('%%VALIDATIONCODE%%', $this->getFormManager()->generateValidationCode(array('process' => $this->getOwnerByClass('ProcessInstanceWrapper'))), $this->getProperty('code'))));
			$this->setProperty('code', (str_ireplace('%%FINALCODE%%', $this->getFormManager()->generateFinalCode(array('process' => $this->getOwnerByClass('ProcessInstanceWrapper'))), $this->getProperty('code'))));
			$this->setProperty('code', (str_ireplace('%%FULLCODE%%', "<?php\n".$this->getFormManager()->generateFullCode(array('process' => $this->getOwnerByClass('ProcessInstanceWrapper')))."?>", $this->getProperty('code'))));
			$this->setProperty('code', (str_ireplace('%%CODE%%', "<?php\n".$this->getFormManager()->generateFullCode(array('process' => $this->getOwnerByClass('ProcessInstanceWrapper')))."?>", $this->getProperty('code'))));
		}

		// сохранение кода в файл
		$file = fopen($this->_owner->getProperty('[code_file]'), 'w+');
		flock($file, LOCK_EX);
		fwrite($file, $this->getProperty('code'));
		flock($file, LOCK_UN);
		fclose($file);
		logRuntime('['.get_class($this).'.execute->'.$this->getProperty('name').'] '.$this->getProperty('name').' child action code cached');
	}

	function complete() {
		global $status_names;
		// завершение действия

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
	}

	function skip($performer = NULL) {
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

	function wait() {
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

	function getProperty($name) {
		return $this->_performer[$name];
	}

	function setProperty($name, $value, $lazy = false) {
		$this->_performer[$name] = $value;
		if ($lazy == false) {
			$this->save();
		}
	}

	function isComplete() {
		// действие завершено?
		global $status_names;

		logRuntime('['.get_class($this).'.isCompeted->'.$this->getProperty('name').'] '.$this->getProperty('name').' current status is '.$this->getProperty('status_id').' ('.($this->getProperty('status_id')?$status_names[$this->getProperty('status_id')-1]:'NULL').')');
		return ((($this->getProperty('status_id') == Constants::ACTION_STATUS_COMPLETED) or ($this->getProperty('status_id') == Constants::ACTION_STATUS_SKIPED)) or ($this->_owner->getProperty('type_id') == Constants::ACTION_TYPE_INFO));
	}

	function save() {
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('performername').'] data saved to '.$this->_performer['[model]']);
		return $this->saveData($this->_performer['[model]'], $this->_performer);
	}

}
?>