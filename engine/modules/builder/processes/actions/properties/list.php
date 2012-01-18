
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
	<tr>
		<th colspan="9">&nbsp;</th>
		<th><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=deleteinactive<?= (defined("ACTION_ID")?'&action_id='.ACTION_ID:(defined("X_ACTION_ID")?'&action_id='.X_ACTION_ID:"")); ?><?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>"><img
			src="images/delete_icon.png" /></a></th>
		<th><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("ACTION_ID")?'&action_id='.ACTION_ID:(defined("X_ACTION_ID")?'&action_id='.X_ACTION_ID:"")); ?><?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<tr>
		<th>#</th>
		<th>Наименование</th>
		<th>Тип значения</th>
		<th>Справочник</th>
		<th>Обязательный</th>
		<th>Только чтение</th>
		<th>Скрытый</th>
		<th>Активно</th>
		<th colspan="3">Действия</th>
	</tr>
	<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				if (defined('X_ACTION_ID')) {
					$property = $connection->getTable('CsProcessActionProperty')->create();
					$property['action_id'] = X_ACTION_ID;
					$property['property_id'] = X_PROPERTY_ID;
					$property['npp'] = (X_PROPERTY_NPP <> ""?X_PROPERTY_NPP:"0");
					$property['parameters'] = X_PROPERTY_PARAMETERS;
					$property['is_required'] = (X_PROPERTY_REQUIRED == 'on'?true:false);
					$property['is_readonly'] = (X_PROPERTY_READONLY == 'on'?true:false);
					$property['is_nextuser'] = (X_PROPERTY_NEXTUSER == 'on'?true:false);
					$property['is_hidden'] = (X_PROPERTY_HIDDEN == 'on'?true:false);
					$property['is_active'] = (X_PROPERTY_ACTIVE == 'on'?true:false);
					$property['is_combo'] = (X_PROPERTY_COMBO == 'on'?true:false);
					$property['is_multiple'] = (X_PROPERTY_MULTIPLE == 'on'?true:false);
					$property['is_childprocess'] = (X_PROPERTY_CHILDPROCESS == 'on'?true:false);
					if ((($property['is_childprocess']) or ($property['is_nextuser'])) and (!$property['is_readonly']) and (!$property['is_hidden'])) {
						$property['is_required'] = true;
					}
					if ($property['is_required']) {
						$property['is_readonly'] = false;
					}
					if ($property['is_multiple']) {
						$property['is_combo'] = false;
					}
					$property->save();
				}
				break;
			case "change" :
				if (defined('X_ACTION_PROPERTY_ID')) {
					$property = $connection->getTable('CsProcessActionProperty')->find(X_ACTION_PROPERTY_ID);
					$property['property_id'] = (defined('X_PROPERTY_ID')?X_PROPERTY_ID:(X_PROPERTY_PROPERTY_ID == ""?NULL:X_PROPERTY_PROPERTY_ID));
					$property['npp'] = (X_PROPERTY_NPP <> ""?X_PROPERTY_NPP:"0");
					$property['parameters'] = X_PROPERTY_PARAMETERS;
					$property['is_required'] = (X_PROPERTY_REQUIRED == 'on'?true:false);
					$property['is_readonly'] = (X_PROPERTY_READONLY == 'on'?true:false);
					$property['is_nextuser'] = (X_PROPERTY_NEXTUSER == 'on'?true:false);
					$property['is_hidden'] = (X_PROPERTY_HIDDEN == 'on'?true:false);
					$property['is_active'] = (X_PROPERTY_ACTIVE == 'on'?true:false);
					$property['is_childprocess'] = (X_PROPERTY_CHILDPROCESS == 'on'?true:false);
					$property['is_combo'] = (X_PROPERTY_COMBO == 'on'?true:false);
					$property['is_multiple'] = (X_PROPERTY_MULTIPLE == 'on'?true:false);
					if ((($property['is_childprocess']) or ($property['is_nextuser'])) and (!$property['is_readonly']) and (!$property['is_hidden'])) {
						$property['is_required'] = true;
					}
					if ($property['is_required']) {
						$property['is_readonly'] = false;
					}
					if ($property['is_multiple']) {
						$property['is_combo'] = false;
					}
					$property->save();
				}
				break;
			case "delete" :
				if (defined('ACTION_PROPERTY_ID')) {
					$property = $connection->getTable('CsProcessActionProperty')->find(ACTION_PROPERTY_ID);
					$property['is_active'] = (!$property['is_active']);
					$property->save();
				}
				break;
			case "deleteinactive" :
				if (defined('ACTION_ID')) {
					$property = $connection->execute('delete from cs_process_action_property where is_active = false and action_id='.ACTION_ID)->fetch();
				}
				break;
			case "realdelete" :
				if (defined('ACTION_PROPERTY_ID')) {
					$property = $connection->getTable('CsProcessActionProperty')->find(ACTION_PROPERTY_ID);
					$property->delete();
				}
				break;
			default:
				break;
		}
	}

	$properties = $connection->execute('select * from process_action_properties_list where action_id = '.ACTION_ID.' order by npp, id, name');
	
	$process = $connection->execute('select name from cs_process where id = '.PROCESS_ID)->fetch();
	print "<div class=\"caption\"><b>Наименование процесса: </b>".$process['name']."</div>\n";
	$action = $connection->execute('select name from cs_process_action where id = '.ACTION_ID)->fetch();
	print "<div class=\"caption\"><b>Наименование действия: </b>".$action['name']."</div>\n";
	
	foreach ($properties as $property) {
		print "<tr>";
		print "<td align=\"center\" width=\"3%\">".$property['npp']."</td>";
		print "<td title=\"".$property['description']."\">".$property['name']."</td>";
		print "<td align=\"center\" width=\"15%\">".$property['typename']."</td>";
		print "<td align=\"center\" width=\"10%\">".(($property['is_list'] == Constants::TRUE)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "<td align=\"center\" width=\"10%\">".(($property['is_required'] == Constants::TRUE)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "<td align=\"center\" width=\"10%\">".(($property['is_readonly'] == Constants::TRUE)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "<td align=\"center\" width=\"10%\">".(($property['is_hidden'] == Constants::TRUE)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "<td align=\"center\" width=\"10%\">".(($property['is_active'] == Constants::TRUE)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&action_id=".$property['action_id']."&action_property_id=".$property['id']."&property_id=".$property['property_id']."&process_id=".$property['process_id'].(defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"")."\"><img src=\"images/edit_icon.png\" /></a></td>";
		print "<td width=\"3%\" align=\"center\"><a href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&action_id=".$property['action_id']."&action_property_id=".$property['id']."&property_id=".$property['property_id']."&process_id=".$property['process_id'].(defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"")."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		print "<td width=\"3%\" align=\"center\"><a href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=realdelete&action_id=".$property['action_id']."&action_property_id=".$property['id']."&property_id=".$property['property_id']."&process_id=".$property['process_id'].(defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"")."', '_top', true);\"><img src=\"images/erase.png\" /></a></td>";
		print "</tr>\n";
	}
	?>
	</td>
	</tr>
	<tr>
		<th colspan="9">&nbsp;</th>
		<th><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=deleteinactive<?= (defined("ACTION_ID")?'&action_id='.ACTION_ID:(defined("X_ACTION_ID")?'&action_id='.X_ACTION_ID:"")); ?><?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>"><img
			src="images/delete_icon.png" /></a></th>
		<th><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("ACTION_ID")?'&action_id='.ACTION_ID:(defined("X_ACTION_ID")?'&action_id='.X_ACTION_ID:"")); ?><?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>"><img
			src="images/create_icon.png" /></a></th>
	</tr>
</table>
