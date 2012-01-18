<?php

require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php");

if ((defined('PROJECT_INSTANCE_ID')) and (defined('PROCESS_INSTANCE_ID'))) {
	if ((is_null($project)) or ($project->getProperty('id') <> PROJECT_INSTANCE_ID)) {
		$project = new ProjectInstanceWrapper($engine, PROJECT_INSTANCE_ID, array('onlyprocess' => PROCESS_INSTANCE_ID));
		$process = $project->getProperty('[processes]')->findElementByID(PROCESS_INSTANCE_ID);
	} else {
		logRuntime('[Controllers.Actions->List] reinitialize process instance by id '.PROCESS_INSTANCE_ID);
		$process = $project->reinitProcess(PROCESS_INSTANCE_ID);
	}
		
	if (defined("ACTION") and ($project) and ($process) and ($process->canPerform()))  {
		switch (ACTION) {
			case 'pause':
				$process->pauseCurrentToday();
				break;

			case "saveform" :
			case "execute" :
				if ((($process->getProperty('status_id') == Constants::PROCESS_STATUS_IN_PROGRESS) or ($process->getProperty('status_id') == Constants::PROCESS_STATUS_CHILD_IN_PROGRESS)) and (is_null($process->getProperty('started_at')))) {
					$process->sendMessage(Constants::EVENT_BEFORE_PROCESS_START);

					logMessage('установка даты и времени запуска документа "'.$process->getProperty('name').'"');
					$process->setProperty('started_at', strftime("%Y-%m-%d %H:%M:%S", time()), true);
					$process->save();

					$process->sendMessage(Constants::EVENT_AFTER_PROCESS_START);
				}
				if (($process->getCurrentAction()) and ($process->getCurrentAction()->isInteractive()) and (is_null($process->getCurrentAction()->getProperty('started_at')))) {
					if (ACTION == 'execute') {
						$process->getCurrentAction()->sendMessage(Constants::EVENT_BEFORE_ANY_ACTION_START);
						if ($process->getCurrentAction()->isInteractive()) {
							$process->getCurrentAction()->sendMessage(Constants::EVENT_BEFORE_INT_ACTION_START);
						} else {
							$process->getCurrentAction()->sendMessage(Constants::EVENT_BEFORE_NOT_ACTION_START);
						}
					}
					logMessage('установка даты и времени запуска действия "'.$process->getCurrentAction()->getProperty('name').'"');
					$process->getCurrentAction()->setProperty('started_at', strftime("%Y-%m-%d %H:%M:%S", time()), true);
					$process->getCurrentAction()->save();

					if (ACTION == 'execute') {
						$process->getCurrentAction()->sendMessage(Constants::EVENT_AFTER_ANY_ACTION_START);
						if ($process->getCurrentAction()->isInteractive()) {
							$process->getCurrentAction()->sendMessage(Constants::EVENT_AFTER_INT_ACTION_START);
						} else {
							$process->getCurrentAction()->sendMessage(Constants::EVENT_AFTER_NOT_ACTION_START);
						}
					}
					
				}
				$process->execute();
				if (($process->getCurrentAction()->isInteractive()) and (!$process->getCurrentAction()->isWaiting())) {
					define('IS_INTERACTIVE', true);
				}
				break;

			case "view" :
				$process->view(((MEDIA <> 'print')?false:true));
				break;

			case "goto" :
				break;

			default:
				break;
		}

		require_once(MODULES_PATH.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.'misc'.DIRECTORY_SEPARATOR.'change.php');
				
	} else {

	}

	if (!defined('IS_INTERACTIVE') and ((is_null($process) or ($process->getCurrentAction()->isWaiting())))) {
		if (!is_null($process)) {
			if (($process->getCurrentPerformerID() <> USER_CODE) and ($process->getProperty('status_id') == Constants::PROCESS_STATUS_IN_PROGRESS)) {
//				if (ACTION == 'execute') {
//					// Переход к просмотру отправленного документа
//					setLocation('/?module='.getParentModule().DIRECTORY_SEPARATOR."outboxes".DIRECTORY_SEPARATOR."processes".DIRECTORY_SEPARATOR."list".getURIParams());
//				} else {
				// Переход к просмотру журнала отправленных документа
				setLocation('/?module='.getParentModule().DIRECTORY_SEPARATOR."outboxes".DIRECTORY_SEPARATOR."list".getURIParams());
//				}
			} elseif (($process->getCurrentPerformerID() == USER_CODE) and ($process->getProperty('status_id') == Constants::PROCESS_STATUS_IN_PROGRESS) and (!$process->getCurrentAction()->isWaiting())) {
				setLocation('/?module='.getParentModule().DIRECTORY_SEPARATOR."inboxes".DIRECTORY_SEPARATOR."list".getURIParams()."&action=".ACTION);
			} elseif (($process->getCurrentAction()->isWaiting()) and (defined('CHILD_PROCESS_INSTANCE_ID'))) {
				setLocation('/?module='.getParentModule().DIRECTORY_SEPARATOR."inboxes".DIRECTORY_SEPARATOR."list".getURIParams(true)."&action=".ACTION);
			}
		} else {
			setLocation('/?module=common/authorized');
		}
	} else {
		if ((($process->getCurrentPerformerID() <> USER_CODE) or ($process->getProperty('status_id') <> Constants::PROCESS_STATUS_IN_PROGRESS) or (ACTION == 'saveform')) and defined("ACTION") and defined('PROJECT_INSTANCE_ID') and defined('PROCESS_INSTANCE_ID')) {
			if (ACTION <> 'saveform') {
				if ($process->getCurrentPerformerID() <> USER_CODE) {
//					if (ACTION == 'execute') {
//						// Переход к просмотру отправленного документа
//						setLocation('/?module='.getParentModule().DIRECTORY_SEPARATOR."outboxes".DIRECTORY_SEPARATOR."processes".DIRECTORY_SEPARATOR."list".getURIParams());
//					} else {
					// Переход к просмотру журнала отправленных документа
					setLocation('/?module='.getParentModule().DIRECTORY_SEPARATOR."outboxes".DIRECTORY_SEPARATOR."list".getURIParams());
//					}
				} elseif (($process->getCurrentPerformerID() == USER_CODE) and ($process->getProperty('status_id') == Constants::PROCESS_STATUS_IN_PROGRESS)) {
					setLocation('/?module='.getParentModule().DIRECTORY_SEPARATOR."inboxes".DIRECTORY_SEPARATOR."list".getURIParams()."&action=".ACTION);
				} elseif ($process->getProperty('status_id') == Constants::PROCESS_STATUS_COMPLETED) {
					setLocation('/?module='.getParentModule().DIRECTORY_SEPARATOR."archives".DIRECTORY_SEPARATOR."list".getURIParams());
				}
			} else {
				if ($process->getFormManager()->formIsValid()) {
					if ($process->getCurrentAction()->getProperty('npp') == 0) {
						setLocation('/?module='.getParentModule().DIRECTORY_SEPARATOR."drafts".DIRECTORY_SEPARATOR."list".getURIParams());
					} else {
						setLocation('/?module='.getParentModule().DIRECTORY_SEPARATOR."inboxes".DIRECTORY_SEPARATOR."list".getURIParams());
					}
				}			
			}
		}
	}
}

?>