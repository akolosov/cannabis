<?php if (($user_permissions[getParentModule()][getChildModule()]['can_read']) or ($user_permissions[getParentModule()][getParentChildModule()]['can_read'])): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<?php
require_once(MODULES_PATH.DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."request_action.php");

require_once(MODULES_PATH.DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."functions.php");

function printInboxItem($processes = array(), $parent_id = 0) {
	global $connection, $user_permissions;

	print "<ul>\n";
	print "<li class=\"treeitem\">";
	print "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"1\" border=\"0\" class=\"treeitem\">";
	foreach ($processes as $process) {
		$action = $connection->execute('select * from cs_process_current_action where instance_id = '.$process['id'].' and status_id = '.Constants::ACTION_STATUS_IN_PROGRESS)->fetch();
		$chrono = $connection->execute('select * from cs_chrono where instance_id = '.$process['id'])->fetchAll();
		print "<tr><td width=\"".(($user_permissions[getParentModule()][getChildModule()]['can_admin'])?"58":"60")."%\" title=\"<p style=' text-align : left !important; '>";
		$properties = $connection->execute('select name, value, cs_process_info_property.id as info from process_instance_properties_list left join cs_process_info_property on cs_process_info_property.property_id = process_instance_properties_list.property_id and cs_process_info_property.process_id = '.$process['process_id'].' where instance_id = '.$process['id'].' order by process_instance_properties_list.property_id')->fetchAll();
		foreach ($properties as $property) {
			if (isNotNULL($property['info'])) {
				$property['value'] = stripMacros($property['value']); 
				print "<b>".$property['name'].": </b>".htmlentities((mb_strlen($property['value']) > 50?mb_substr($property['value'], 0, 50)."...":$property['value']), ENT_COMPAT, DEFAULT_CHARSET)."<br />";
			}
		}
		print "<b>Статус: </b>".$process['statusname']."</p>\"";
		if ($action['performer_id'] == USER_CODE) {
			print " onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR.(($action['status_id'] == Constants::ACTION_STATUS_WAITING)?"processes/list":"actions/list&action=execute")."&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."';\"";
			print " class=\"small action".(($process['id'] == PROCESS_INSTANCE_ID)?" red":"")."\">";
			print "<a href=\"#\"></a><strong>".$process['name']." №".$process['id']."</strong>";
		} else {
			print " onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."processes/list&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."';\"";
			print " class=\"small action".(($process['id'] == PROCESS_INSTANCE_ID)?" red":"")."\">";
			print "<a href=\"#\"></a>".$process['name']." №".$process['id'];
		}
		$document_period_start = getPropertyValueByName($properties, 'Срок исполнения');
		$document_period_end = (isNotNULL(getPropertyValueByName($properties, 'Дата и время выполнения Исполнителем'))?strtotime(getPropertyValueByName($properties, 'Дата и время выполнения Исполнителем')):time());
		if ((isNotNULL($document_period_start)) and (strtotime($document_period_start." ".END_WORK_TIME) < $document_period_end)) {
			print " <span class=\"red\">(просрочена на ".formatedInterval(dateDiff($document_period_end, strtotime($document_period_start." ".END_WORK_TIME))).")</span>";
		}
		print "</td>";
		print "<td align=\"center\" class=\"small\" width=\"20%\">".$process['initiatorname']."</td><td align=\"center\" class=\"small\" width=\"15%\">".($process['started_at']?strftime("%d.%m.%Y в %H:%M", strtotime($process['started_at'])):"-")."</td>";
		print "<td width=\"auto\" align=\"right\"><nobr>";
		if ((isNotNULL($chrono)) and (($user_permissions[getParentModule()][getChildModule()]['can_read']) or ($user_permissions[getParentModule()][getParentChildModule()]['can_read']))) {
			print "<span class=\"small action\" title=\"История движения документа '".$process['name']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."history/list&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/date.png\" /></span>";
			print "&nbsp";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_read']) or ($user_permissions[getParentModule()][getParentChildModule()]['can_read'])) {
			print "<span class=\"small action\" title=\"Хронология документа '".$process['name']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."chronos/list&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/time.png\" /></span>";
			print "&nbsp";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_read']) or ($user_permissions[getParentModule()][getParentChildModule()]['can_read'])) {
			print "<span class=\"small action\" title=\"Печать документа '".$process['name']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."processes/list&media=print&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/print.png\" /></span>";
			print "&nbsp";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_read']) or ($user_permissions[getParentModule()][getParentChildModule()]['can_read'])) {
			print "<span class=\"small action\" title=\"Просмотр документа '".$process['name']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."processes/list&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/template.png\" /></span>";
			print "&nbsp";
		}
		if ((($user_permissions[getParentModule()][getChildModule()]['can_read']) or ($user_permissions[getParentModule()][getParentChildModule()]['can_read'])) and ($action['performer_id'] == USER_CODE)) {
			print "<span class=\"small action\" title=\"Редактировать документ '".$process['name']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."actions/list&action=execute&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."');\"><img src=\"images/play.png\" /></span>";
		}
		print "</nobr></td></tr>";
	}
	print "</table>";
	print "</li>\n";
	print "</ul>\n";
}

?>
<?php
if (defined("ACTION") && defined('PROJECT_INSTANCE_ID') && defined('PROCESS_INSTANCE_ID')) {
	require_once("actions/list.php");
} else {
	$query = 'select distinct project_processes_instances_list.* from project_processes_instances_list, cs_process_current_action, cs_process_action where (project_processes_instances_list.id = cs_process_current_action.instance_id) and (cs_process_action.id = cs_process_current_action.action_id) and '.
				(((defined('PROJECT_INSTANCE_ID')) and (PROJECT_INSTANCE_ID <> ''))?'(project_processes_instances_list.project_instance_id = '.PROJECT_INSTANCE_ID.') and ':'').
				'(((cs_process_current_action.performer_id = '.USER_CODE.') or (cs_process_current_action.initiator_id = '.USER_CODE.')) and '.
				' (cs_process_current_action.ended_at is null) and '.
				' (cs_process_action.npp >= 0) and '.
				' (cs_process_current_action.status_id in ('.implode(', ', array(Constants::ACTION_STATUS_IN_PROGRESS, Constants::ACTION_STATUS_WAITING)).')))'.
				(isNotNULL($inprop)?" and ".$inprop:"").
				(isNotNULL($where)?' and ('.(implode(' and ', $where)).')':'').
					' and (project_processes_instances_list.status_id in ('.implode(', ', array(Constants::PROCESS_STATUS_IN_PROGRESS, Constants::PROCESS_STATUS_WAITING, Constants::PROCESS_STATUS_CHILD_IN_PROGRESS, Constants::PROCESS_STATUS_CHILD_WAITING)).'))'.
					' order by project_processes_instances_list.project_id, project_processes_instances_list.status_id, project_processes_instances_list.started_at desc,'.
					'  project_processes_instances_list.ended_at desc, project_processes_instances_list.id desc,'.
				'  project_processes_instances_list.name';

	$processes = $connection->execute($query)->fetchAll();
	
	print "<div class=\"caption\"><img src=\"images/constants.png\" style=\" float: right; z-index: 1000; \" onClick=\"hideIt('request_params')\" title=\"Выборка документов по параметрам\" />Входящие документы".(defined('PROJECT_INSTANCE_ID')?": ".$projectslist[PROJECT_INSTANCE_ID]:"")."</div>\n";
	require_once(MODULES_PATH.DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."request_form.php");

	$projectslist = getProjectsList($processes);
	if (isNotNULL($projectslist)) {
		print "<ul class=\"tree\" id=\"projects_tree\" style=\" display : none; \">\n";
		foreach ($projectslist as $project_id => $project_name) {
			print "<li class=\"roottreeitem\"><a href=\"#\"></a>Предприятие: ".$project_name;
			if (($user_permissions[getParentModule()][getChildModule()]['can_read']) or ($user_permissions[getParentModule()][getParentChildModule()]['can_read'])) {
				printInboxItem(getByProject($processes, $project_id));
			}
			print "</li>\n";
		}
		print "</ul>\n";
	}
}
?>
<?php if (defined("ACTION") && defined('PROJECT_INSTANCE_ID') && defined('PROCESS_INSTANCE_ID')): ?>
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
