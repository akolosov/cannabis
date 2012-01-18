<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<?php
require_once(MODULES_PATH.DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."request_action.php");

if (defined("ACTION")) {
	switch (ACTION) {
		case "activate_process_instance" :
			if (defined('PROCESS_INSTANCE_ID')) {
				$process = new ProcessInstanceWrapper($engine, PROCESS_INSTANCE_ID);
				if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) || ($process->getProperty('initiator_id') == USER_CODE)) {
					logMessage('активирован экземпляр процесса "'.$process->getProperty('name').'" (идентификатор экземпляра: '.PROCESS_INSTANCE_ID.')');
					$result = $connection->execute('select activate_process_instance('.PROCESS_INSTANCE_ID.');')->fetch();
				}
				$process = NULL;
			}
			break;
		case "deactivate_process_instance" :
			if (defined('PROCESS_INSTANCE_ID')) {
				$process = new ProcessInstanceWrapper($engine, PROCESS_INSTANCE_ID);
				if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) || ($process->getProperty('initiator_id') == USER_CODE)) {
					logMessage('деактивирован экземпляр процесса "'.$process->getProperty('name').'" (идентификатор экземпляра: '.PROCESS_INSTANCE_ID.')');
					$result = $connection->execute('select deactivate_process_instance('.PROCESS_INSTANCE_ID.');')->fetch();
				}
				$process = NULL;
			}
			break;
		case "delete_process_instance" :
			if (defined('PROCESS_INSTANCE_ID')) {
				$process = new ProcessInstanceWrapper($engine, PROCESS_INSTANCE_ID);
				if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) || ($process->getProperty('initiator_id') == USER_CODE)) {
					logMessage('удалён экземпляр процесса "'.$process->getProperty('name').'" (идентификатор экземпляра: '.PROCESS_INSTANCE_ID.')');
					$result = $connection->execute('select delete_process_instance('.PROCESS_INSTANCE_ID.');')->fetch();
				}
				$process = NULL;
			}
			break;
		case "terminate_process_instance" :
			if (defined('PROCESS_INSTANCE_ID')) {
				$process = new ProcessInstanceWrapper($engine, PROCESS_INSTANCE_ID);
				if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) || ($process->getProperty('initiator_id') == USER_CODE)) {
					logMessage('прерван экземпляр процесса "'.$process->getProperty('name').'" (идентификатор экземпляра: '.PROCESS_INSTANCE_ID.')');
					$result = $connection->execute('select terminate_process_instance('.PROCESS_INSTANCE_ID.');')->fetch();
				}
				$process = NULL;
			}
			break;
		case "stop_process_instance" :
			if (defined('PROCESS_INSTANCE_ID')) {
				$process = new ProcessInstanceWrapper($engine, PROCESS_INSTANCE_ID);
				if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) || ($process->getProperty('initiator_id') == USER_CODE)) {
					logMessage('завершен экземпляр процесса "'.$process->getProperty('name').'" (идентификатор экземпляра: '.PROCESS_INSTANCE_ID.')');
					$result = $connection->execute('select stop_process_instance('.PROCESS_INSTANCE_ID.');')->fetch();
				}
				$process = NULL;
			}
			break;
		case "error_process_instance" :
			if (defined('PROCESS_INSTANCE_ID')) {
				$process = new ProcessInstanceWrapper($engine, PROCESS_INSTANCE_ID);
				if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) || ($process->getProperty('initiator_id') == USER_CODE)) {
					logMessage('ошибочный экземпляр процесса "'.$process->getProperty('name').'" (идентификатор экземпляра: '.PROCESS_INSTANCE_ID.')');
					$result = $connection->execute('select error_process_instance('.PROCESS_INSTANCE_ID.');')->fetch();
				}
				$process = NULL;
			}
			break;
		case "erase_process_instance" :
			if (defined('PROCESS_INSTANCE_ID')) {
				$process = new ProcessInstanceWrapper($engine, PROCESS_INSTANCE_ID);
				if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) || ($process->getProperty('initiator_id') == USER_CODE)) {
					logMessage('стёрт СОВСЕМ экземпляр процесса "'.$process->getProperty('name').'" (идентификатор экземпляра: '.PROCESS_INSTANCE_ID.')');
					$result = $connection->execute('select erase_process_instance('.PROCESS_INSTANCE_ID.');')->fetch();
				}
				$process = NULL;
			}
			break;
		case "erase_empty_instances" :
			if ($user_permissions[getParentModule()][getChildModule()]['can_admin']) {
				$empty_docs = $connection->execute('select id from get_empty_docs where started_at < \''.strftime('%d.%m.%Y %H:%M:%S', time()-86400).'\';')->fetchAll();
				foreach ($empty_docs as $empty_doc) {
					logMessage('стёрт СОВСЕМ пустой экземпляр процесса (идентификатор экземпляра: '.$empty_doc['id'].')');
					$result = $connection->execute('select erase_process_instance('.$empty_doc['id'].');')->fetch();
				}
			}
			break;
		default:
			break;
	}
}

function getByStatus($processes = array(), $statuses = array()) {
	$result = array();
	$count = 0;
	foreach ($processes as $process) {
		if (in_array($process['status_id'], $statuses)) {
			$result[] = $process;
		}
	}
	return $result;
}

function getByParent($processes = array(), $parent_id = 0) {
	$result = array();
	foreach ($processes as $process) {
		if ($process['parent_id'] == $parent_id) {
			$result[] = $process;
		}
	}
	return $result;
}

function printActiveItem($processes = array(), $parent_id = 0) {
	global $connection, $user_permissions;

	foreach ($processes as $process) {
		print "<ul>\n";
		print "<li class=\"treeitem\">";
		print "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\" class=\"treeitem\">";
		print "<tr><td width=\"".(($user_permissions[getParentModule()][getChildModule()]['can_admin'])?"58":"60")."%\" title=\"<p style=' text-align : left !important; '>";
		$action = $connection->execute('select initiator_id, performer_id from cs_process_current_action where instance_id = '.$process['id'].' and (initiator_id = '.USER_CODE.' or performer_id = '.USER_CODE.') and status_id in ('.Constants::ACTION_STATUS_IN_PROGRESS.', '.Constants::ACTION_STATUS_WAITING.')')->fetch();
		$chrono = $connection->execute('select * from cs_chrono where instance_id = '.$process['id'])->fetchAll();
		$properties = $connection->execute('select name, value from process_instance_properties_list where instance_id = '.$process['id'].' and property_id in (select property_id from cs_process_info_property where process_id = '.$process['process_id'].') order by property_id')->fetchAll();
		foreach ($properties as $property) {
			$property['value'] = stripMacros($property['value']); 
			print "<b>".$property['name'].": </b>".htmlentities((mb_strlen($property['value']) > 50?mb_substr($property['value'], 0, 50)."...":$property['value']), ENT_COMPAT, DEFAULT_CHARSET)."<br />";
		}
		print "<b>Статус: </b>".$process['statusname']."</p>\"";
		print (((($user_permissions[getParentModule()][getChildModule()]['can_read']) and (($action['initiator_id'] == USER_CODE) or ($action['performer_id'] == USER_CODE))) and ($parent_id == 0))
				?" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR."list&action=execute&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."';\""
				:(($user_permissions[getParentModule()][getChildModule()]['can_read'])
					?" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."processes/list&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."';\""
					:"")).
			" class=\"".((($action['initiator_id'] == USER_CODE) or ($action['performer_id'] == USER_CODE))?"bold_":"")."small action\">";

		print "<a href=\"#\"></a>".$process['name']." №".$process['id']."</td>";
		print "<td align=\"center\" class=\"small\" width=\"20%\">".$process['initiatorname']."</td><td align=\"center\" class=\"small\" width=\"15%\">".($process['started_at']?strftime("%d.%m.%Y в %H:%M", strtotime($process['started_at'])):"-")."</td>";
		print "<td width=\"".(($user_permissions[getParentModule()][getChildModule()]['can_admin'])?"auto":"32")."\" align=\"right\"><nobr>";
		if ((isNotNULL($chrono)) and (($user_permissions[getParentModule()][getChildModule()]['can_read']) or ($user_permissions[getParentModule()][getParentChildModule()]['can_read']))) {
			print "<span class=\"small action\" title=\"История движения документа '".$process['name']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."history/list&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/date.png\" /></span>";
			print "&nbsp";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
			print "<span class=\"small action\" title=\"Хронология документа '".$process['name']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."chronos/list&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/time.png\" /></span>";
			print "&nbsp";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and ($parent_id == 0)) {
			print "<span class=\"small action\" title=\"Стереть СОВСЕМ (!) экземпляр процесса '".$process['name']."'\" onClick=\"confirmItMessage('Все вложенные процессы будут также СОВСЕМ УДАЛЕНЫ! Удалить СОВСЕМ (!) экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=erase_process_instance&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/erase.png\" /></span>";
			print "&nbsp";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and ($parent_id == 0)) {
			print "<span class=\"small action\" title=\"Установить признак 'Ошибка выполнения' экземпляру процесса '".$process['name']."'\" onClick=\"confirmItMessage('Все вложенные процессы будут также помечены как ошибочные! Установить признак ошибки выполнения экземпляру процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=error_process_instance&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/error_sign.png\" /></span>";
			print "&nbsp";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and ($parent_id == 0)) {
			print "<span class=\"small action\" title=\"Остановить выполнение экземпляра процесса '".$process['name']."'\" onClick=\"confirmItMessage('Все вложенные процессы будут также деактивированы! Деактивировать экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=terminate_process_instance&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/delete.png\" /></span>";
			print "&nbsp";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and ($parent_id == 0)) {
			print "<span class=\"small action\" title=\"Завершить выполнение экземпляра процесса '".$process['name']."'\" onClick=\"confirmItMessage('Все вложенные процессы будут также завершены! Завершить экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=stop_process_instance&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/cancel.png\" /></span>";
			print "&nbsp";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and ($parent_id == 0)) {
			print "<span class=\"small action\" title=\"Деактивировать экземпляр процесса '".$process['name']."'\" onClick=\"confirmItMessage('Все вложенные процессы будут также деактивированы! Деактивировать экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=deactivate_process_instance&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/close.png\" /></span>";
			print "&nbsp";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and ($parent_id == 0)) {
			print "<span class=\"small action\" title=\"Удалить экземпляр процесса '".$process['name']."'\" onClick=\"confirmItMessage('Все вложенные процессы будут также удалены! Удалить экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete_process_instance&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/trash.png\" /></span>";
			print "&nbsp";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
			print "<span class=\"small action\" title=\"Печать экземпляра процесса '".$process['name']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."processes/list&media=print&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/print.png\" /></span>";
			print "&nbsp";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
			print "<span class=\"small action\" title=\"Просмотр экземпляра процесса '".$process['name']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."processes/list&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/template.png\" /></span>";
		}
		if ((($user_permissions[getParentModule()][getChildModule()]['can_read']) and (($action['initiator_id'] == USER_CODE) or ($action['performer_id'] == USER_CODE))) and ($parent_id == 0)) {
				print "<span class=\"small action\" title=\"Запустить экземпляра процесса '".$process['name']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR."list&action=execute&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/play.png\" /></span>";
		}
		print "</nobr></td></tr></table>";
		printActiveItem(getByParent($processes, $process['id']), $process['id']);
		print "</li>\n";
		print "</ul>\n";
	}
}

 function printCompletedItem($processes = array(), $parent_id = 0) {
	global $connection, $user_permissions;

	foreach ($processes as $process) {
		print "<ul>\n";
		print "<li class=\"treeitem\">";
		print "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\" class=\"treeitem\">";
		print "<tr><td width=\"43%\" title=\"<p style=' text-align : left !important; '>";
		$properties = $connection->execute('select name, value from process_instance_properties_list where instance_id = '.$process['id'].' and property_id in (select property_id from cs_process_info_property where process_id = '.$process['process_id'].') order by property_id')->fetchAll();
		$chrono = $connection->execute('select * from cs_chrono where instance_id = '.$process['id'])->fetchAll();
		foreach ($properties as $property) {
			$property['value'] = stripMacros($property['value']); 
			print "<b>".$property['name'].": </b>".htmlentities((mb_strlen($property['value']) > 50?mb_substr($property['value'], 0, 50)."...":$property['value']), ENT_COMPAT, DEFAULT_CHARSET)."<br>";
		}
		print "</p>\"".
			(($user_permissions[getParentModule()][getChildModule()]['can_read'])
				?" class=\"small action\" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."processes/list&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."';\""
				:"").
			">";
		print "<a href=\"#\"></a>".$process['name']." №".$process['id']."</td><td align=\"center\" class=\"small\" width=\"20%\">".$process['initiatorname']."</td><td align=\"center\" class=\"small\" width=\"15%\">".($process['started_at']?strftime("%d.%m.%Y в %H:%M", strtotime($process['started_at'])):"-")."</td><td align=\"center\" class=\"small\" width=\"15%\">".strftime("%d.%m.%Y в %H:%M", strtotime($process['ended_at']))."</td>";
		print "<td width=\"60\" align=\"right\">";
		if ((isNotNULL($chrono)) and (($user_permissions[getParentModule()][getChildModule()]['can_read']) or ($user_permissions[getParentModule()][getParentChildModule()]['can_read']))) {
			print "<span class=\"small action\" title=\"История движения документа '".$process['name']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."history/list&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/date.png\" /></span>";
			print "&nbsp";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
			print "<span class=\"small action\" title=\"Хронология документа '".$process['name']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."chronos/list&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/time.png\" /></span>";
			print "&nbsp";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and ($parent_id == 0)) {
			print "<span class=\"small action\" title=\"Стереть СОВСЕМ (!) экземпляр процесса '".$process['name']."'\" onClick=\"confirmItMessage('Все вложенные процессы будут также СОВСЕМ УДАЛЕНЫ! Удалить СОВСЕМ (!) экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=erase_process_instance&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/erase.png\" /></span>";
			print "&nbsp";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
			print "<span class=\"small action\" title=\"Печать экземпляра процесса '".$process['name']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."processes/list&media=print&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/print.png\" /></span>";
			print "&nbsp";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
			print "<span class=\"small action\" title=\"Просмотр экземпляра процесса '".$process['name']."'\" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."processes/list&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."';\"><img src=\"images/template.png\" /></span>";
		}
		print "</td></tr></table>";
		printCompletedItem(getByParent($processes, $process['id']), $process['id']);
		print "</li>\n";
		print "</ul>\n";
	}
}

function printErrorItem($processes = array(), $parent_id = 0) {
	global $connection, $user_permissions;

	foreach ($processes as $process) {
		print "<ul>\n";
		print "<li class=\"treeitem\">";
		print "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\" class=\"treeitem\">";
		print "<tr><td width=\"35%\" title=\"<p style=' text-align : left !important; '>";
		$properties = $connection->execute('select name, value from process_instance_properties_list where instance_id = '.$process['id'].' and property_id in (select property_id from cs_process_info_property where process_id = '.$process['process_id'].') order by property_id')->fetchAll();
		foreach ($properties as $property) {
			$property['value'] = stripMacros($property['value']); 
			print "<b>".$property['name'].": </b>".htmlentities((mb_strlen($property['value']) > 50?mb_substr($property['value'], 0, 50)."...":$property['value']), ENT_COMPAT, DEFAULT_CHARSET)."<br>";
		}
		print "</p>\">";
		print "<a href=\"#\"></a>".$process['name']." №".$process['id']."</td><td align=\"center\" class=\"small\" width=\"10%\">[".($process['statusname']?$process['statusname']:"не активен")."]</td><td align=\"center\" class=\"small\" width=\"20%\">".$process['initiatorname']."</td><td align=\"center\" class=\"small\" width=\"14%\">".($process['started_at']?strftime("%d.%m.%Y в %H:%M", strtotime($process['started_at'])):"-")."</td><td align=\"center\" class=\"small\" width=\"14%\">".strftime("%d.%m.%Y в %H:%M", strtotime($process['ended_at']))."</td>";
		print "<td width=\"40\" align=\"right\">";
		if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and ($parent_id == 0)) {
			print "<span class=\"small action\" title=\"Стереть СОВСЕМ (!) экземпляр процесса '".$process['name']."'\" onClick=\"confirmItMessage('Все вложенные процессы будут также СОВСЕМ УДАЛЕНЫ! Удалить СОВСЕМ (!) экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=erase_process_instance&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/erase.png\" /></span>";
			print "&nbsp";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and ($parent_id == 0)) {
			print "<span class=\"small action\" title=\"Активировать экземпляр процесса '".$process['name']."'\" onClick=\"confirmItMessage('Все вложенные процессы будут также активированы! Активировать экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=activate_process_instance&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/check.png\" /></span>";
		}
		print "</td></tr></table>";
		printErrorItem(getByParent($processes, $process['id']), $process['id']);
		print "</li>\n";
		print "</ul>\n";
	}
}

function printInactiveItem($processes = array(), $parent_id = 0) {
	global $connection, $user_permissions;

	foreach ($processes as $process) {
		print "<ul>\n";
		print "<li class=\"treeitem\">";
		print "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\" class=\"treeitem\">";
		print "<tr><td width=\"60%\" title=\"<p style=' text-align : left !important; '>";
		$properties = $connection->execute('select name, value from process_instance_properties_list where instance_id = '.$process['id'].' and property_id in (select property_id from cs_process_info_property where process_id = '.$process['process_id'].') order by property_id')->fetchAll();
		foreach ($properties as $property) {
			$property['value'] = stripMacros($property['value']); 
			print "<b>".$property['name'].": </b>".htmlentities((mb_strlen($property['value']) > 50?mb_substr($property['value'], 0, 50)."...":$property['value']), ENT_COMPAT, DEFAULT_CHARSET)."<br>";
		}
		print "</p>\">";
		print "<a href=\"#\"></a>".$process['name']." №".$process['id']."</td><td align=\"center\" class=\"small\" width=\"20%\">".$process['initiatorname']."</td><td align=\"center\" class=\"small\" width=\"15%\">".($process['started_at']?strftime("%d.%m.%Y в %H:%M", strtotime($process['started_at'])):"-")."</td>";
		print "<td width=\"60\" align=\"right\"><nobr>";
		if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and ($parent_id == 0)) {
			print "<span class=\"small action\" title=\"Стереть СОВСЕМ (!) экземпляр процесса '".$process['name']."'\" onClick=\"confirmItMessage('Все вложенные процессы будут также СОВСЕМ УДАЛЕНЫ! Удалить СОВСЕМ (!) экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=erase_process_instance&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/erase.png\" /></span>";
			print "&nbsp";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and ($parent_id == 0)) {
			print "<span class=\"small action\" title=\"Удалить экземпляр процесса '".$process['name']."'\" onClick=\"confirmItMessage('Все вложенные процессы будут также удалены! Удалить экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete_process_instance&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/trash.png\" /></span>";
			print "&nbsp";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and ($parent_id == 0)) {
			print "<span class=\"small action\" title=\"Активировать экземпляр процесса '".$process['name']."'\" onClick=\"confirmItMessage('Все вложенные процессы будут также активированы! Активировать экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=activate_process_instance&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/check.png\" /></span>";
		}
		print "</nobr></td></tr></table>";
		printInactiveItem(getByParent($processes, $process['id']), $process['id']);
		print "</li>\n";
		print "</ul>\n";
	}
}


function printTerminatedItem($processes = array(), $parent_id = 0) {
	global $connection, $user_permissions;

	foreach ($processes as $process) {
		print "<ul>\n";
		print "<li class=\"treeitem\">";
		print "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\" class=\"treeitem\">";
		print "<tr><td width=\"36%\" title=\"<p style=' text-align : left !important; '>";
		$properties = $connection->execute('select name, value from process_instance_properties_list where instance_id = '.$process['id'].' and property_id in (select property_id from cs_process_info_property where process_id = '.$process['process_id'].') order by property_id')->fetchAll();
		foreach ($properties as $property) {
			$property['value'] = stripMacros($property['value']); 
			print "<b>".$property['name'].": </b>".htmlentities((mb_strlen($property['value']) > 50?mb_substr($property['value'], 0, 50)."...":$property['value']), ENT_COMPAT, DEFAULT_CHARSET)."<br>";
		}
		print "<hr /><b>".$process['statusname']."</b></p>\">";
		print "<a href=\"#\"></a>".$process['name']." №".$process['id']."</td><td align=\"center\" class=\"small\" width=\"20%\">".$process['initiatorname']."</td><td align=\"center\" class=\"small\" width=\"14%\">".($process['started_at']?strftime("%d.%m.%Y в %H:%M", strtotime($process['started_at'])):"-")."</td><td align=\"center\" class=\"small\" width=\"14%\">".strftime("%d.%m.%Y в %H:%M", strtotime($process['ended_at']))."</td>";
		print "<td width=\"40\" align=\"right\"><nobr>";
		if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and ($parent_id == 0)) {
			print "<span class=\"small action\" title=\"Стереть СОВСЕМ (!) экземпляр процесса '".$process['name']."'\" onClick=\"confirmItMessage('Все вложенные процессы будут также СОВСЕМ УДАЛЕНЫ! Удалить СОВСЕМ (!) экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=erase_process_instance&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/erase.png\" /></span>";
			print "&nbsp";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and ($parent_id == 0)) {
			print "<span class=\"small action\" title=\"Активировать экземпляр процесса '".$process['name']."'\" onClick=\"confirmItMessage('Все вложенные процессы будут также активированы! Активировать экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=activate_process_instance&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/check.png\" /></span>";
		}
		print "</nobr></td></tr></table>";
		printTerminatedItem(getByParent($processes, $process['id']), $process['id']);
		print "</li>\n";
		print "</ul>\n";
	}
}


function printDeletedItem($processes = array(), $parent_id = 0) {
	global $connection, $user_permissions;

	foreach ($processes as $process) {
		print "<ul>\n";
		print "<li class=\"treeitem\">";
		print "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\" class=\"treeitem\">";
		print "<tr><td width=\"36%\" title=\"<p style=' text-align : left !important; '>";
		$properties = $connection->execute('select name, value from process_instance_properties_list where instance_id = '.$process['id'].' and property_id in (select property_id from cs_process_info_property where process_id = '.$process['process_id'].') order by property_id')->fetchAll();
		foreach ($properties as $property) {
			$property['value'] = stripMacros($property['value']); 
			print "<b>".$property['name'].": </b>".htmlentities((mb_strlen($property['value']) > 50?mb_substr($property['value'], 0, 50)."...":$property['value']), ENT_COMPAT, DEFAULT_CHARSET)."<br>";
		}
		print "<b>Статус: </b>".$process['statusname']."</p>\">";
		print "<a href=\"#\"></a>".$process['name']." №".$process['id']."</td><td align=\"center\" class=\"small\" width=\"20%\">".$process['initiatorname']."</td><td align=\"center\" class=\"small\" width=\"14%\">".($process['started_at']?strftime("%d.%m.%Y в %H:%M", strtotime($process['started_at'])):"-")."</td><td align=\"center\" class=\"small\" width=\"14%\">".strftime("%d.%m.%Y в %H:%M", strtotime($process['ended_at']))."</td>";
		print "<td width=\"40\" align=\"right\"><nobr>";
		if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and ($parent_id == 0)) {
			print "<span class=\"small action\" title=\"Стереть СОВСЕМ (!) экземпляр процесса '".$process['name']."'\" onClick=\"confirmItMessage('Все вложенные процессы будут также СОВСЕМ УДАЛЕНЫ! Удалить СОВСЕМ (!) экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=erase_process_instance&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/erase.png\" /></span>";
			print "&nbsp";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and ($parent_id == 0)) {
			print "<span class=\"small action\" title=\"Активировать экземпляр процесса '".$process['name']."'\" onClick=\"confirmItMessage('Все вложенные процессы будут также активированы! Активировать экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=activate_process_instance&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/check.png\" /></span>";
		}
		print "</nobr></td></tr></table>";
		printDeletedItem(getByParent($processes, $process['id']), $process['id']);
		print "</li>\n";
		print "</ul>\n";
	}
}


function printTemplateItem($project_id, $parent_id = 0, $instance_id = 0) {
	global $connection, $user_permissions;

	$processes = $connection->execute('select * from project_active_processes_tree where (project_id = '.$project_id.' and parent_id'.($parent_id == 0?" is null":" = ".$parent_id).') order by id, name')->fetchAll();
	foreach ($processes as $process) {
		print "<ul>\n";
		print "<li class=\"treeitem\">";
		print "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\" class=\"treeitem\">";
		print "<tr><td width=\"98%\" title=\"".$process['description']."\"".(($user_permissions[getParentModule()][getChildModule()]['can_write'])?" class=\"bold_small action\" onClick=\"confirmItMessage('Экземпляр процесса будет добавлен в \'Активные процессы\'! Создать экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=create_process_instance&project_instance_id=".$instance_id."&project_id=".$process['project_id']."&process_id=".$process['id']."');\"":"")."><a href=\"#\"></a>".$process['name']."</td>";
		print "<td width=\"16\" align=\"right\">";
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
 			print "<span class=\"small action\" title=\"Создать экземпляр процесса '".$process['name']."'\" onClick=\"confirmItMessage('Экземпляр процесса будет добавлен в \'Активные процессы\'! Создать экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=create_process_instance&project_instance_id=".$instance_id."&project_id=".$process['project_id']."&process_id=".$process['id']."');\"><img src=\"images/add.png\" /></span>";
 		}
		print "</td></tr></table>";
		printTemplateItem($project_id, $process['id'], $instance_id);
		print "</li>\n";
		print "</ul>\n";
	}
}

  
function printPublicTemplateItem($project_id = 0, $parent_id = 0, $instance_id = 0) {
	global $connection, $user_permissions;

	$processes = $connection->execute('select * from public_active_processes_tree where (parent_id'.($parent_id == 0?" is null":" = ".$parent_id).') order by id, name')->fetchAll();
	foreach ($processes as $process) {
		print "<ul>\n";
		print "<li class=\"treeitem\">";
		print "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\" class=\"treeitem\">";
		print "<tr><td width=\"98%\" title=\"".$process['description']."\"".(($user_permissions[getParentModule()][getChildModule()]['can_write'])?" class=\"bold_small action\" onClick=\"confirmItMessage('Экземпляр процесса будет добавлен в \'Активные процессы\'! Создать экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=create_process_instance&project_instance_id=".$instance_id."&project_id=".$process['project_id']."&process_id=".$process['id']."');\"":"")."><a href=\"#\"></a>".$process['name']."</td>";
		print "<td width=\"16\" align=\"right\">";
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
 			print "<span class=\"small action\" title=\"Создать экземпляр процесса '".$process['name']."'\" onClick=\"confirmItMessage('Экземпляр процесса будет добавлен в \'Активные процессы\'! Создать экземпляр процесса?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=create_process_instance&project_instance_id=".$instance_id."&project_id=".$project_id."&process_id=".$process['id']."');\"><img src=\"images/add.png\" /></span>";
 		}
		print "</td></tr></table>";
		printPublicTemplateItem($project_id, $process['id'], $instance_id);
		print "</li>\n";
		print "</ul>\n";
	}
}

?>
<?php
$projects = $connection->execute('select * from projects_instances where is_active = true'.(defined('PROJECT_INSTANCE_ID')?' and id = '.PROJECT_INSTANCE_ID:'').(($user_permissions[getParentModule()][getChildModule()]['can_admin'])?'':' and is_system = false').((($user_permissions[getParentModule()][getChildModule()]['can_admin']) or ($user_permissions[getParentModule()][getChildModule()]['can_observe']))?'':' and ((project_id in (select project_id from cs_project_role where division_id in ('.implode(', ', $engine->getAccount()->getDivisionsList()).'))) or (id in (select project_instance_id from cs_project_process_instance where process_instance_id in (select id from project_processes_instances_list where project_instance_id = projects_instances.id and (initiator_id = '.USER_CODE.' or (id in (select instance_id from cs_process_current_action where (instance_id = project_processes_instances_list.id) and (initiator_id = '.USER_CODE.' or performer_id = '.USER_CODE.'))))))))').' order by id')->fetchAll();
print "<div class=\"caption\">".(($user_permissions[getParentModule()][getChildModule()]['can_admin'])?"<img src=\"images/trash.png\" style=\" float: right; z-index: 1000; \"  onClick=\"confirmItMessage('Все пустые экземпляры процессов будут безвозвратно удалены! Удалить СОВСЕМ пустые экземпляры процессов?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=erase_empty_instances');\" title=\"Удалить СОВСЕМ пустые экземпляры процессов\" />":"")."<img src=\"images/constants.png\" style=\" float: right; z-index: 1000; \"  onClick=\"hideIt('request_params')\" title=\"Выборка процессов по параметрам\" />".(defined('PROJECT_INSTANCE_ID')?"Предприятие: ".$projects[0]['name']:"Предприятия")."</div>\n";

require_once(MODULES_PATH.DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."request_form.php");

print "<ul class=\"tree\" id=\"projects_tree\" style=\" display : none; \">\n";
foreach ($projects as $project) {

	print "<li class=\"roottreeitem\">";
	print "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\" class=\"treeitem\">";
	print "<tr><td><a href=\"#\"></a>Предприятие: ".$project['name']." (".$project['description'].")</td>";
	if (!defined('PROJECT_INSTANCE_ID')) {
		print "<td width=\"16\" align=\"center\">";
		if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
			print "<span class=\"small action\" title=\"Информация о проекте '".$project['name']."'\" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&project_instance_id=".$project['id']."';\"><img src=\"images/template.png\" /></span>";
		}
		print "</td>";
	}
	print "</tr></table>";
	
	$query = 'select distinct project_processes_instances_list.* from project_processes_instances_list, cs_process_current_action where (project_processes_instances_list.project_instance_id = '.$project['id'].') and (project_processes_instances_list.id = cs_process_current_action.instance_id)'.
	(((($user_permissions[getParentModule()][getChildModule()]['can_admin']) or
	($user_permissions[getParentModule()][getChildModule()]['can_review']) or
	($user_permissions[getParentModule()][getChildModule()]['can_observe'])) and
	isNULL($inwhere))
		?''
		:' and '.(isNULL($inwhere)
					?'((cs_process_current_action.performer_id = '.((defined('X_PROCESS_INITIATOR') and (X_PROCESS_INITIATOR <> ''))?X_PROCESS_INITIATOR:USER_CODE).') or (cs_process_current_action.initiator_id = '.((defined('X_PROCESS_INITIATOR') and (X_PROCESS_INITIATOR <> ''))?X_PROCESS_INITIATOR:USER_CODE).'))'
					:'(((cs_process_current_action.performer_id = '.((defined('X_PROCESS_INITIATOR') and (X_PROCESS_INITIATOR <> ''))?X_PROCESS_INITIATOR:USER_CODE).') or (cs_process_current_action.initiator_id = '.((defined('X_PROCESS_INITIATOR') and (X_PROCESS_INITIATOR <> ''))?X_PROCESS_INITIATOR:USER_CODE).')) or ('.(implode(' and ', $inwhere)).')) and ('.(implode(' and ', $inwhere)).')')
	).
	(isNotNULL($inprop)
		?" and ".$inprop
		:"").
	(isNotNULL($where)
		?' and ('.(implode(' and ', $where)).')'
		:'').' order by project_processes_instances_list.status_id, project_processes_instances_list.started_at desc,
		project_processes_instances_list.ended_at desc, project_processes_instances_list.id desc,
		project_processes_instances_list.name limit '.(CURRENT_LIMIT*(($user_permissions[getParentModule()][getChildModule()]['can_admin'])?4:2));

	$processes = $connection->execute($query)->fetchAll();
		
	if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
		print "<ul>\n";
		print "<li class=\"preroottreeitem\"><a href=\"#\"></a><span title=\"Процессы, выполняемые в текущий момент.\">[Процессы: активные]</span>";
		printActiveItem(getByStatus($processes, array(Constants::PROCESS_STATUS_IN_PROGRESS, Constants::PROCESS_STATUS_WAITING, Constants::PROCESS_STATUS_CHILD_IN_PROGRESS, Constants::PROCESS_STATUS_CHILD_WAITING)));
		print "</li>\n";
		print "</ul>\n";
	}
	if ($user_permissions[getParentModule()][getChildModule()]['can_admin']) {
		print "<ul>\n";
		print "<li class=\"preroottreeitem\"><a href=\"#\"></a><span title=\"Процессы, неактивные в данный момент или декативированные пользователями.\">[Процессы: неактивные]</span>";
		printInactiveItem(getByStatus($processes, array(Constants::PROCESS_STATUS_NONE)));
		print "</li>\n";
		print "</ul>\n";
	}
	if ($user_permissions[getParentModule()][getChildModule()]['can_admin']) {
		print "<ul>\n";
		print "<li class=\"preroottreeitem\"><a href=\"#\"></a><span  title=\"Процессы, прерванные, удалённые или завершенные с ошибкой. Возможна повторная активация.\">[Процессы: прерванные, удалённые и завершенные с ошибкой]</span>";
		printTerminatedItem(getByStatus($processes, array(Constants::PROCESS_STATUS_TERMINATED, Constants::PROCESS_STATUS_CHILD_TERMINATED)));
		printDeletedItem(getByStatus($processes, array(Constants::PROCESS_STATUS_DELETED, Constants::PROCESS_STATUS_CHILD_DELETED)));
		printErrorItem(getByStatus($processes, array(Constants::PROCESS_STATUS_ERROR, Constants::PROCESS_STATUS_CHILD_ERROR)));
		print "</li>\n";
		print "</ul>\n";
	}
	if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
		print "<ul>\n";
		print "<li class=\"preroottreeitem\"><a href=\"#\"></a><span title=\"Процессы, успешно завершенные и полностью выполненные.\">[Процессы: завершеные]</span>";
		printCompletedItem(getByStatus($processes, array(Constants::PROCESS_STATUS_COMPLETED, Constants::PROCESS_STATUS_CHILD_COMPLETED)));
		print "</li>\n";
		print "</ul>\n";
	}
	print "</li>\n";
}
print "</ul>\n";

require_once(MODULES_PATH.DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."project_prop.php");
?>
<script>
<!--
	var projects_tree_options = {
			'theme' : { 'name' : 'SimpleTree' },
			closeSiblings : false,
			maxOpenDepth : <?= (((defined('PROJECT_INSTANCE_ID')) or (count($projects) == 1))?3:0); ?>,
			flagClosedClass : 'close',
			toggleMenuOnClick : true,
			incrementalConvert : false,
			openTimeout : 0,
			closeTimeout: 0
	}
	var projects_tree = new CompleteMenuSolution;
	projects_tree.initMenu('projects_tree', projects_tree_options);
//-->
</script>
<?php endif; ?>
