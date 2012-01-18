<?php if (($user_permissions[getParentModule()][getChildModule()]['can_read']) or ($user_permissions[getParentModule()][getParentChildModule()]['can_read'])): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<?php
  require_once(MODULES_PATH.DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."functions.php");

  if (defined("ACTION")) {
	switch (ACTION) {
		case "erase" :
			if ((defined('PROCESS_INSTANCE_ID')) and ($user_permissions[getParentModule()][getChildModule()]['can_write'])) {
				$process = new ProcessInstanceWrapper($engine, PROCESS_INSTANCE_ID);
				if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) || ($process->getProperty('initiator_id') == USER_CODE)) {
					logMessage('стёрт СОВСЕМ экземпляр документа "'.$process->getProperty('name').'" (идентификатор экземпляра: '.PROCESS_INSTANCE_ID.')');
					$result = $connection->execute('select erase_process_instance('.PROCESS_INSTANCE_ID.');')->fetch();
				}
				$process = NULL;
			}
			break;

		default:
			break;
	}
  }

  function printDraftItem($processes = array(), $parent_id = 0) {
	global $connection, $user_permissions;

	print "<ul>\n";
	print "<li class=\"treeitem\">";
	print "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"1\" border=\"0\" class=\"treeitem\">";
	foreach ($processes as $process) {
		print "<tr><td width=\"60%\" class=\"small action".(($process['id'] == PROCESS_INSTANCE_ID)?" red":"")."\" title=\"<p style=' text-align : left !important; '>";
		$properties = $connection->execute('select name, value from process_instance_properties_list where instance_id = '.$process['id'].' and property_id in (select property_id from cs_process_info_property where process_id = '.$process['process_id'].') order by property_id')->fetchAll();
		foreach ($properties as $property) {
			$property['value'] = stripMacros($property['value']); 
			print "<b>".$property['name'].": </b>".htmlentities((mb_strlen($property['value']) > 50?mb_substr($property['value'], 0, 50)."...":$property['value']), ENT_COMPAT, DEFAULT_CHARSET)."<br />";
		}
		print "<b>Статус: </b>".$process['statusname']."</p>\"";
		print " onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."actions/list&action=execute&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."';\" class=\"small action\">";
		print "<a href=\"#\"></a>".$process['name']." №".$process['id']."</td>";
		print "<td align=\"center\" class=\"small\" width=\"20%\">".$process['initiatorname']."</td><td align=\"center\" class=\"small\" width=\"15%\">".($process['started_at']?strftime("%d.%m.%Y в %H:%M", strtotime($process['started_at'])):"-")."</td>";
		print "<td width=\"auto\" align=\"center\"><nobr>";
		if (($user_permissions[getParentModule()][getChildModule()]['can_write']) or ($user_permissions[getParentModule()][getParentChildModule()]['can_write'])) {
			print "<span class=\"small action\" title=\"Удалить документ '".$process['name']."'\" onClick=\"confirmItMessage('Все вложенные документы будут также СОВСЕМ УДАЛЕНЫ! Удалить СОВСЕМ (!) документ?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=erase&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/trashcan.png\" /></span>";
			print "&nbsp";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_write']) or ($user_permissions[getParentModule()][getParentChildModule()]['can_write'])) {
			print "<span class=\"small action\" title=\"Редактировать документ '".$process['name']."'\" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."actions/list&action=execute&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."';\"><img src=\"images/play.png\" /></span>";
		}
		print "</nobr></td></tr>";
	}
	print "</table>";
	print "</li>\n";
	print "</ul>\n";		
  }
?>
<?php
  if (defined("ACTION") and defined('PROJECT_INSTANCE_ID') and defined('PROCESS_INSTANCE_ID')) {
  	require_once("actions/list.php");
  } else {
	  $query = 'select distinct project_processes_instances_list.* from project_processes_instances_list, cs_process_current_action, cs_process_action where (project_processes_instances_list.id = cs_process_current_action.instance_id) and (cs_process_action.id = cs_process_current_action.action_id) and '.
			   '((cs_process_current_action.performer_id = '.((defined('X_PROCESS_PERFORMER') and (X_PROCESS_PERFORMER <> ''))?X_PROCESS_PERFORMER:USER_CODE).') and '.
		       ' (cs_process_current_action.ended_at is null) and '.
		       ' (cs_process_action.npp = 0) and '.
	  		   ' (cs_process_current_action.status_id in ('.implode(', ', array(Constants::ACTION_STATUS_IN_PROGRESS, Constants::ACTION_STATUS_WAITING)).')))'.
		       ' and (project_processes_instances_list.status_id in ('.implode(', ', array(Constants::PROCESS_STATUS_IN_PROGRESS, Constants::PROCESS_STATUS_WAITING, Constants::PROCESS_STATUS_CHILD_IN_PROGRESS, Constants::PROCESS_STATUS_CHILD_WAITING)).'))'.
		       ' order by project_processes_instances_list.project_id, project_processes_instances_list.status_id, project_processes_instances_list.started_at desc,'.
	           '  project_processes_instances_list.ended_at desc, project_processes_instances_list.id desc,'.
	           '  project_processes_instances_list.name';
	
	  $processes = $connection->execute($query)->fetchAll();
	
	  print "<div class=\"caption\">Черновики документов</div>\n";
	  print "<ul class=\"tree\" id=\"projects_tree\" style=\" display : none; \">\n";
	  print "<li class=\"roottreeitem\"><a href=\"#\"></a>Черновики документов: ".$project_name;
	  if (($user_permissions[getParentModule()][getChildModule()]['can_read']) or ($user_permissions[getParentModule()][getParentChildModule()]['can_read'])) {
		printDraftItem($processes);
	  }
	  print "</li>\n";
	  print "</ul>\n";
  }
?>
<?php if (defined("ACTION") and defined('PROJECT_INSTANCE_ID') and defined('PROCESS_INSTANCE_ID')): ?>
<?php else: ?>
<script>
<!--
	var projects_tree_options = {
			'theme' : { 'name' : 'SimpleTree' },
			closeSiblings : false,
			maxOpenDepth : 2,
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
<?php endif; ?>
