
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
	<tr>
		<th colspan="2">&nbsp;</th>
		<th><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("ACTION_ID")?'&action_id='.ACTION_ID:(defined("X_ACTION_ID")?'&action_id='.X_ACTION_ID:"")); ?><?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<tr>
		<th>Наименование</th>
		<th colspan="2">Действия</th>
	</tr>
	<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				if (defined('X_PROPERTY_ID')) {
					$property = $connection->getTable('CsProcessInfoProperty')->create();
					$property['process_id'] = X_PROCESS_ID;
					$property['property_id'] = X_PROPERTY_ID;
					$property->save();
				}
				break;
			case "change" :
				if (defined('X_ID')) {
					$property = $connection->getTable('CsProcessInfoProperty')->find(X_ID);
					$property['property_id'] = (defined('X_PROPERTY_ID')?X_PROPERTY_ID:(X_PROCESS_PROPERTY_ID == ""?NULL:X_PROCESS_PROPERTY_ID));
					$property->save();
				}
				break;
			case "delete" :
				if (defined('PROCESS_PROPERTY_ID')) {
					$property = $connection->getTable('CsProcessInfoProperty')->find(PROCESS_PROPERTY_ID);
					$property->delete();
				}
				break;
			default:
				break;
		}
	}

	$properties = $connection->execute('select * from process_info_properties_list where process_id = '.PROCESS_ID.' order by id, name');
	
	$process = $connection->execute('select name from cs_process where id = '.PROCESS_ID)->fetch();
	print "<caption class=\"caption\"><b>Наименование процесса: </b>".$process['name']."</caption>\n";

	foreach ($properties as $property) {
		print "<tr>";
		print "<td title=\"".$property['description']."\">".$property['name']."</td>";
		print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&process_property_id=".$property['id']."&property_id=".$property['property_id']."&process_id=".$property['process_id'].(defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"")."\"><img src=\"images/edit_icon.png\" /></a></td>";
		print "<td width=\"3%\" align=\"center\"><a href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&process_property_id=".$property['id']."&property_id=".$property['property_id']."&process_id=".$property['process_id'].(defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"")."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		print "</tr>\n";
	}
	?>
	</td>
	</tr>
	<tr>
		<th colspan="2">&nbsp;</th>
		<th><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("ACTION_ID")?'&action_id='.ACTION_ID:(defined("X_ACTION_ID")?'&action_id='.X_ACTION_ID:"")); ?><?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>"><img
			src="images/create_icon.png" /></a></th>
	</tr>
</table>
