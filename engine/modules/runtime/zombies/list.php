<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<?php
  require_once(MODULES_PATH.DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."request_action.php");

  if (defined("ACTION")) {
	switch (ACTION) {
		case "complete_process_instance" :
			if (defined('PROCESS_INSTANCE_ID')) {
				if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) || ($process->getProperty('initiator_id') == USER_CODE)) {
					$result = $connection->execute('update cs_process_instance set status_id = '.Constants::PROCESS_STATUS_COMPLETED.' where id = '.PROCESS_INSTANCE_ID.';')->fetch();
				}
				$process = NULL;
			}
			break;
		default:
			break;
	}
  }

  function printNotCompletedItem($processes = array(), $parent_id = 0) {
	global $connection, $user_permissions;

	foreach ($processes as $process) {
		print "<tr><td width=\"43%\" title=\"<p style=' text-align : left !important; '>";
		$properties = $connection->execute('select name, value from process_instance_properties_list where instance_id = '.$process['id'].' and property_id in (select property_id from cs_process_info_property where process_id = '.$process['process_id'].') order by property_id')->fetchAll();
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
		if ($user_permissions[getParentModule()][getChildModule()]['can_admin']) {
			print "<span class=\"small action\" title=\"Исправить документ '".$process['name']."'\" onClick=\"confirmItMessage('Исправить документ?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=complete_process_instance&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/ok.png\" /></span>";
			print "&nbsp";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
			print "<span class=\"small action\" title=\"Печать документа '".$process['name']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."processes/list&media=print&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/print.png\" /></span>";
			print "&nbsp";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
			print "<span class=\"small action\" title=\"Просмотр документа '".$process['name']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."processes/list&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/template.png\" /></span>";
		}
		print "</td></tr>";
	 }
  }

  print "<div class=\"caption\"><img src=\"images/constants.png\" style=\" float: right; z-index: 1000; \"  onClick=\"hideIt('request_params')\" title=\"Выборка процессов по параметрам\" />Зависшие документы:</div>\n";
  require_once(MODULES_PATH.DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."request_form.php");
  $query = 'select distinct project_processes_instances_list.* from project_processes_instances_list, cs_process_current_action where (project_processes_instances_list.id = cs_process_current_action.instance_id)'.
	((($user_permissions[getParentModule()][getChildModule()]['can_admin']) and
	  isNULL($inwhere))
	    ?''
	    :' and '.(isNULL($inwhere)
	               ?'((cs_process_current_action.performer_id = '.((defined('X_PROCESS_INITIATOR') and (X_PROCESS_INITIATOR <> ''))?X_PROCESS_INITIATOR:USER_CODE).') or (cs_process_current_action.initiator_id = '.((defined('X_PROCESS_INITIATOR') and (X_PROCESS_INITIATOR <> ''))?X_PROCESS_INITIATOR:USER_CODE).'))'
	               :'(((cs_process_current_action.performer_id = '.((defined('X_PROCESS_INITIATOR') and (X_PROCESS_INITIATOR <> ''))?X_PROCESS_INITIATOR:USER_CODE).') or (cs_process_current_action.initiator_id = '.((defined('X_PROCESS_INITIATOR') and (X_PROCESS_INITIATOR <> ''))?X_PROCESS_INITIATOR:USER_CODE).')) or ('.(implode(' and ', $inwhere)).')) and ('.(implode(' and ', $inwhere)).')')
	).(isNotNULL($where)
	    ?' and ('.(implode(' and ', $where)).') and '
	    :'').
	     ' and (project_processes_instances_list.status_id <> '.Constants::PROCESS_STATUS_COMPLETED.') and (project_processes_instances_list.ended_at is not null) '.
	     '  order by project_processes_instances_list.status_id, project_processes_instances_list.started_at desc,
         project_processes_instances_list.ended_at desc, project_processes_instances_list.id desc,
         project_processes_instances_list.name limit '.(CURRENT_LIMIT*(($user_permissions[getParentModule()][getChildModule()]['can_admin'])?4:2));
	    
  $processes = $connection->execute($query)->fetchAll();
	    
  if ($user_permissions[getParentModule()][getChildModule()]['can_admin']) {
	print "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\" class=\"treeitem\">";
  	printNotCompletedItem($processes);
	print "</table>";
  }
?>
<?php endif; ?>
