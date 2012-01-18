<?php if (defined('PROCESS_ID')): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
	<tr>
		<th colspan="9">&nbsp;</th>
		<th><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<tr>
		<th width="16">#</th>
		<th>Наименование</th>
		<th width="16"></th>
		<th>Тип</th>
		<th>Вес</th>
		<th>Интерактивно</th>
		<th colspan="4">Действия</th>
	</tr>
	<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				$action = $connection->getTable('CsProcessAction')->create();
				$action['process_id'] = X_PROCESS_ID;
				$action['type_id'] = X_ACTION_TYPE_ID;
				$action['weight'] = str_replace(",", ".", X_ACTION_WEIGHT);
				$action['planed'] = (defined('X_ACTION_PLANED')?X_ACTION_PLANED:NULL);
				$action['name'] = prepareForSave(X_ACTION_NAME);
				$action['description'] = prepareForSave(X_ACTION_DESCR);
				$action['form'] = (defined('X_ACTION_FORM')?prepareForSave(X_ACTION_FORM):NULL);
				$action['code'] = (defined('X_ACTION_CODE')?prepareForSave(X_ACTION_CODE):NULL);
				$action['is_interactive'] = (X_ACTION_INTERACTIVE == 'on'?true:false);
				$action['role_id'] = (defined('X_ACTION_ROLE_ID')?X_ACTION_ROLE_ID:NULL);
				$action['true_action_id'] = (defined('X_ACTION_TRUE_ID')?X_ACTION_TRUE_ID:NULL);
				$action['false_action_id'] = (defined('X_ACTION_FALSE_ID')?X_ACTION_FALSE_ID:NULL);
				$action->save();

				if ($action['type_id'] == Constants::ACTION_TYPE_SWITCH) {
					$transition = $connection->getTable('CsProcessTransition')->create();
					$transition['process_id'] = X_PROCESS_ID;
					$transition['from_action_id'] = $action['id'];
					$transition['to_action_id'] =$action['true_action_id'];
					$transition->save(); 

					$transition = $connection->getTable('CsProcessTransition')->create();
					$transition['process_id'] = X_PROCESS_ID;
					$transition['from_action_id'] = $action['id'];
					$transition['to_action_id'] =$action['false_action_id'];
					$transition->save(); 
				}
				
				$connection->execute('select sort_process_actions('.$action['process_id'].');');

				// добавить выбранные свойства
				$npp = 0;
				foreach ($parameters['X_PROPERTIES'] as $property_id) {
					$actionproperty = $connection->getTable('CsProcessActionProperty')->create();
					$actionproperty['action_id'] = $action['id'];
					$actionproperty['property_id'] = $property_id;
					$actionproperty['npp'] = $npp;
					$actionproperty['is_readonly'] = (($action['type_id'] == Constants::ACTION_TYPE_INFO)?true:false);
					$actionproperty['is_active'] = true;
					$actionproperty->save();
					$npp++;
				}
				break;

			case "change" :
				$action = $connection->getTable('CsProcessAction')->find(X_ACTION_ID);
				$action['type_id'] = (defined('X_ACTION_TYPE_ID')?X_ACTION_TYPE_ID:(X_TYPE_ID == ""?NULL:X_TYPE_ID));
				$action['weight'] = str_replace(",", ".", X_ACTION_WEIGHT);
				$action['planed'] = (defined('X_ACTION_PLANED')?X_ACTION_PLANED:NULL);
				$action['name'] = prepareForSave(X_ACTION_NAME);
				$action['description'] = prepareForSave(X_ACTION_DESCR);
				$action['form'] = (defined('X_ACTION_FORM')?prepareForSave(X_ACTION_FORM):NULL);
				$action['code'] = (defined('X_ACTION_CODE')?prepareForSave(X_ACTION_CODE):NULL);
				$action['is_interactive'] = (X_ACTION_INTERACTIVE == 'on'?true:false);
				$action['role_id'] = (defined('X_ACTION_ROLE_ID')?X_ACTION_ROLE_ID:(X_OLD_ROLE_ID == ""?NULL:X_OLD_ROLE_ID));
				$action['true_action_id'] = (defined('X_ACTION_TRUE_ID')?X_ACTION_TRUE_ID:NULL);
				$action['false_action_id'] = (defined('X_ACTION_FALSE_ID')?X_ACTION_FALSE_ID:NULL);
				$action->save();

				if ($action['type_id'] == Constants::ACTION_TYPE_SWITCH) {
					$test = $connection->execute('select * from cs_process_transition where from_action_id = '.$action['id'].' and to_action_id = '.$action['true_action_id'])->fetch();
					if (isNULL($test)) {
						$transition = $connection->getTable('CsProcessTransition')->create();
						$transition['process_id'] = X_PROCESS_ID;
						$transition['from_action_id'] = $action['id'];
						$transition['to_action_id'] =$action['true_action_id'];
						$transition->save(); 
					}

					$test = $connection->execute('select * from cs_process_transition where from_action_id = '.$action['id'].' and to_action_id = '.$action['false_action_id'])->fetch();
					if (isNULL($test)) {
						$transition = $connection->getTable('CsProcessTransition')->create();
						$transition['process_id'] = X_PROCESS_ID;
						$transition['from_action_id'] = $action['id'];
						$transition['to_action_id'] =$action['false_action_id'];
						$transition->save(); 
					}
				}

				// добавить выбранные свойства
				$npp = 0;
				$actprop = $connection->execute('delete from cs_process_action_property where action_id = '.$action['id'].' and property_id not in ('.implode(',', $parameters['X_PROPERTIES']).')')->fetch();
				foreach ($parameters['X_PROPERTIES'] as $property_id) {
					$actprop = $connection->execute('select * from cs_process_action_property where action_id = '.$action['id'].' and property_id = '.$property_id)->fetch();
					if (!$actprop) {  
						$actionproperty = $connection->getTable('CsProcessActionProperty')->create();
						$actionproperty['action_id'] = $action['id'];
						$actionproperty['property_id'] = $property_id;
						$actionproperty['npp'] = $npp;
						$actionproperty['is_readonly'] = (($action['type_id'] == Constants::ACTION_TYPE_INFO)?true:false);
						$actionproperty['is_active'] = true;
						$actionproperty->save();
						$npp++;
					}
				}
				$connection->execute('select sort_process_actions('.$action['process_id'].');');
				break;

			case "delete" :
				$action = $connection->getTable('CsProcessAction')->find(ACTION_ID);
				$action->delete();
				$connection->execute('select sort_process_actions('.$action['process_id'].');');
				break;

			default:
				break;
		}
	}

	$actions = $connection->execute('select cs_process_action.*, cs_process.name as processname, cs_action_type.name as typename from cs_process_action, cs_process, cs_action_type where cs_process_action.process_id = cs_process.id and cs_process_action.type_id = cs_action_type.id and cs_process_action.process_id = '.PROCESS_ID.' order by cs_process_action.npp, cs_process_action.type_id, cs_process_action.id, cs_process_action.name');

	$process = $connection->execute('select name from cs_process where id = '.PROCESS_ID)->fetch();
	print "<div class=\"caption\"><b>Наименование процесса: </b>".$process['name']."</div>\n";

	foreach ($actions as $action) {
		print "<tr>";
		print "<td width=\"16\">".$action['npp']."</td>";
		print "<td title=\"".$action['description']."\">".$action['name']."</td>";
		print "<td width=\"16\"><img src=\"images/actions/".($action['is_interactive'] == Constants::TRUE?"i_":"a_").$actions_icons[$action['type_id']-1].".gif\" /></td>";
		print "<td align=\"center\">".$action['typename']."</td>";
		print "<td align=\"center\">".$action['weight']."</td>";
		print "<td width=\"5%\"  align=\"center\">".(($action['is_interactive'] == Constants::TRUE)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."properties/list&action_id=".$action['id']."&process_id=".$action['process_id'].(defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"")."\"><img src=\"images/list.png\" /></a></td>";
		print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."transports/list&action_id=".$action['id']."&process_id=".$action['process_id'].(defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"")."\"><img src=\"images/transports.png\" /></a></td>";
		print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&action_id=".$action['id']."&process_id=".$action['process_id'].(defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"")."\"><img src=\"images/edit_icon.png\" /></a></td>";
		print "<td width=\"3%\" align=\"center\"><a href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&action_id=".$action['id']."&process_id=".$action['process_id'].(defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"")."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		print "</tr>\n";
	}
	?>
	</td>
	</tr>
	<tr>
		<th colspan="9">&nbsp;</th>
		<th><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>"><img
			src="images/create_icon.png" /></a></th>
	</tr>
</table>
<?php endif; ?>