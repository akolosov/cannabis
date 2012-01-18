
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
	<tr>
		<th colspan="5">&nbsp;</th>
		<th><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<tr>
		<th>Наименование</th>
		<th>Признак</th>
		<th>Тип значения</th>
		<th>Справочник</th>
		<th colspan="2">Действия</th>
	</tr>
	<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				if (defined('X_PROCESS_ID')) {
					$property = $connection->getTable('CsProcessProperty')->create();
					$property['process_id'] = X_PROCESS_ID;
					$property['sign_id'] = X_PROPERTY_SIGN_ID;
					$property['type_id'] = X_PROPERTY_TYPE_ID;
					$property['name'] = prepareForSave(X_PROPERTY_NAME);
					$property['description'] = prepareForSave(X_PROPERTY_DESCR);
					$property['default_value'] = (defined('X_PROPERTY_VALUE')?prepareForSave(X_PROPERTY_VALUE):NULL);
					$property['is_list'] = (X_PROPERTY_LIST == 'on'?true:false);
					$property['is_name_as_value'] = (X_PROPERTY_NAME_AS_VALUE == 'on'?true:false);
					$property['value_field'] = prepareForSave(X_PROPERTY_FIELD);
					if ($property['is_list']) {
						$property['directory_id'] = (X_PROPERTY_DIRECTORY_ID == ""?NULL:X_PROPERTY_DIRECTORY_ID);
					} else {
						$property['directory_id'] = NULL;
					}
					$property->save();
					// добавить все свойство НЕАКТИВНЫМ к действиям
					$actions = $connection->execute('select * from cs_process_action where process_id = '.$property['process_id'].' order by id, name')->fetchAll();
					foreach ($actions as $action) {
						$actionproperty = $connection->getTable('CsProcessActionProperty')->create();
						$actionproperty['action_id'] = $action['id'];
						$actionproperty['property_id'] = $property['id'];
						$actionproperty['npp'] = 0;
						$actionproperty['is_active'] = ($action['type_id'] == Constants::ACTION_TYPE_INFO?true:false);
						$actionproperty['is_readonly'] = ($action['type_id'] == Constants::ACTION_TYPE_INFO?true:false);
						$actionproperty->save();
					}
				}
				break;
			case "change" :
				if (defined('X_PROPERTY_ID')) {
					$property = $connection->getTable('CsProcessProperty')->find(X_PROPERTY_ID);
					$property['sign_id'] = (defined('X_PROPERTY_SIGN_ID')?X_PROPERTY_SIGN_ID:(X_SIGN_ID == ""?NULL:X_SIGN_ID));
					$property['type_id'] = (defined('X_PROPERTY_TYPE_ID')?X_PROPERTY_TYPE_ID:(X_TYPE_ID == ""?NULL:X_TYPE_ID));
					$property['name'] = prepareForSave(X_PROPERTY_NAME);
					$property['description'] = prepareForSave(X_PROPERTY_DESCR);
					$property['default_value'] = (defined('X_PROPERTY_VALUE')?prepareForSave(X_PROPERTY_VALUE):NULL);
					$property['is_list'] = (X_PROPERTY_LIST == 'on'?true:false);
					$property['is_name_as_value'] = (X_PROPERTY_NAME_AS_VALUE == 'on'?true:false);
					$property['value_field'] = prepareForSave(X_PROPERTY_FIELD);
					if ($property['is_list']) {
						$property['directory_id'] = (defined('X_PROPERTY_DIRECTORY_ID')?X_PROPERTY_DIRECTORY_ID:(X_DIRECTORY_ID == ""?NULL:X_DIRECTORY_ID));
					} else {
						$property['directory_id'] = NULL;
					}
					$property->save();
				}
				break;
			case "delete" :
				$property = $connection->getTable('CsProcessProperty')->find(PROPERTY_ID);
				$property->delete();
				break;
			default:
				break;
		}
	}

	$properties = $connection->execute('select cs_process_property.*, cs_process.name as processname, cs_sign.name as signname, cs_property_type.name as typename from cs_process_property, cs_process, cs_sign, cs_property_type where cs_process_property.process_id = cs_process.id and cs_process_property.sign_id = cs_sign.id and cs_process_property.type_id = cs_property_type.id and cs_process_property.process_id = '.PROCESS_ID.' order by cs_process_property.id, cs_process_property.name');

	$transition = $connection->execute('select name from cs_process where id = '.PROCESS_ID)->fetch();
	print "<caption class=\"caption\"><b>Наименование процесса: </b>".$transition['name']."</caption>\n";

	foreach ($properties as $property) {
		print "<tr>";
		print "<td title=\"".$property['description']."\">".$property['name']."</td>";
		print "<td width=\"15%\" align=\"center\">".$property['signname']."</td>";
		print "<td width=\"15%\" align=\"center\">".$property['typename']."</td>";
		print "<td width=\"5%\" align=\"center\">".(($property['is_list'] == Constants::TRUE)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&property_id=".$property['id']."&process_id=".$property['process_id'].(defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"")."\"><img src=\"images/edit_icon.png\" /></a></td>";
		print "<td width=\"3%\" align=\"center\"><a href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&property_id=".$property['id']."&process_id=".$property['process_id'].(defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"")."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		print "</tr>\n";
	}
	?>
	</td>
	</tr>
	<tr>
		<th colspan="5">&nbsp;</th>
		<th><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>"><img
			src="images/create_icon.png" /></a></th>
	</tr>
</table>
