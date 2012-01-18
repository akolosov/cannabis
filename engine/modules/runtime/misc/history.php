<?php if (($user_permissions[getParentModule()][getParentChildModule()]['can_read']) and (defined('PROCESS_INSTANCE_ID'))): ?>
<?php
require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php");

function printChronedItem($processes = array(), $parent_id = 0) {
	global $connection, $user_permissions;

	$npp = 1;
	foreach ($processes as $process) {
		print "<tr>";
		print "<td class=\"small\" width=\"5%\" align=\"right\" title=\"<p style=' text-align : left !important; '>";
		$properties = $connection->execute('select name, value from chrono_process_instance_properties_list where chrono_id = '.$process['id'].' and property_id in (select property_id from cs_process_info_property where process_id = '.$process['process_id'].') order by property_id')->fetchAll();
		foreach ($properties as $property) {
			$property['value'] = stripMacros($property['value']); 
			print "<b>".$property['name'].": </b>".htmlentities((mb_strlen($property['value']) > 50?mb_substr($property['value'], 0, 50)."...":$property['value']), ENT_COMPAT, DEFAULT_CHARSET)."<br>";
		}
		print "</p>\">";
		print "<a href=\"#\"></a>".$npp."</td>";
		print "<td align=\"center\" class=\"small\" width=\"15%\" title=\"Пользователь, сделавший откат документа\">".$process['accountname']."</td>";
		print "<td align=\"center\" class=\"small\" width=\"15%\" title=\"Дата и время отката\">".strftime("%d.%m.%Y в %H:%M", strtotime($process['chrono_at']))."</td>";
		print "<td align=\"center\" class=\"small\" width=\"30%\" title=\"Откат от действия\">".$process['fromactionname']."</td>";
		print "<td align=\"center\" class=\"small\" width=\"30%\" title=\"Откат к действию\">".$process['toactionname']."</td>";
		print "<td width=\"35\" align=\"right\">";
		if ($user_permissions[getParentModule()][getParentChildModule()]['can_read']) {
			print "<span class=\"small action\" title=\"Просмотр состояния документа '".$process['processname']."' на момент до отката\" onClick=\"document.location.href = '/?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."processes/list&chrono_instance_id=".$process['id']."&process_instance_id=".$process['instance_id']."&process_id=".$process['process_id']."';\"><img src=\"images/template.png\" /></span>";
			print "&nbsp";
			print "<span class=\"small action\" title=\"Печать состояния документа '".$process['processname']."' на момент до отката\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."processes/list&media=print&chrono_instance_id=".$process['id']."&process_instance_id=".$process['instance_id']."&process_id=".$process['process_id']."');\"><img src=\"images/print.png\" /></span>";
		}
		print "</td></tr>";
		$npp++;
	}
}

$query = 'select * from chrono_process_instances_list where instance_id = '.PROCESS_INSTANCE_ID.' order by id';    
$processes = $connection->execute($query)->fetchAll();

print "<div class=\"caption\">История движения документа ".$processes[0]['processname']." №".$processes[0]['instance_id']."</div>\n";

if ($user_permissions[getParentModule()][getParentChildModule()]['can_read']) {
	print "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\" class=\"treeitem\">";
	printChronedItem($processes);
	print "</table>";
}
?>
<? endif; ?>