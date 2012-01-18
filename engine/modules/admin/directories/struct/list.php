<?php if (($user_permissions[getParentModule()][getParentChildModule()]['can_read']) and (defined('DIRECTORY_ID'))): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
<?php if ($user_permissions[getParentModule()][getParentChildModule()]['can_write']): ?>
	<tr>
		<th colspan="5">&nbsp;</th>
		<th><a href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("DIRECTORY_ID")?'&directory_id='.DIRECTORY_ID:(defined("X_DIRECTORY_ID")?'&directory_id='.X_DIRECTORY_ID:"")); ?>"><img src="images/create_icon.png" /></a></th>
	</tr>
<?php endif; ?>
	<tr>
		<th>Наименование</th>
		<th>Заголовок</th>
		<th>Тип значения</th>
		<th>Автоинкремент</th>
		<th colspan="2">Действия</th>
	</tr>
<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				if (defined('X_DIRECTORY_ID')) {
					$field = $connection->getTable('CsDirectoryField')->create();
					$field['directory_id'] = X_DIRECTORY_ID;
					$field['type_id'] = X_FIELD_TYPE_ID;
					$field['name'] = prepareForSave(X_FIELD_NAME);
					$field['caption'] = prepareForSave(X_FIELD_CAPTION);
					if (isNULL($field['caption'])) {
						$field['caption'] = $$field['name'];
					}
					$field['default_value'] = (defined('X_FIELD_VALUE')?prepareForSave(X_FIELD_VALUE):NULL);
					$field['autoinc'] = (X_FIELD_AUTOINC == 'on'?true:false);
					if ($field['type_id'] <> Constants::PROPERTY_TYPE_NUMBER) {
						$field['autoinc'] = false;
					}
					if (($field['autoinc']) and (isNULL($field['default_value']))) {
						$field['default_value'] = 0;
					}
					$field->save();
				}
				break;
			case "change" :
				if (defined('X_FIELD_ID')) {
					$field = $connection->getTable('CsDirectoryField')->find(X_FIELD_ID);
					$field['type_id'] = (defined('X_FIELD_TYPE_ID')?X_FIELD_TYPE_ID:(X_TYPE_ID == ""?NULL:X_TYPE_ID));
					$field['name'] = prepareForSave(X_FIELD_NAME);
					$field['caption'] = prepareForSave(X_FIELD_CAPTION);
					if (isNULL($field['caption'])) {
						$field['caption'] = $$field['name'];
					}
					$field['default_value'] = (defined('X_FIELD_VALUE')?prepareForSave(X_FIELD_VALUE):NULL);
					$field['autoinc'] = (X_FIELD_AUTOINC == 'on'?true:false);
					if ($field['type_id'] <> Constants::PROPERTY_TYPE_NUMBER) {
						$field['autoinc'] = false;
					}
					if (($field['autoinc']) and (isNULL($field['default_value']))) {
						$field['default_value'] = 0;
					}
					$field->save();
				}
				break;
			case "delete" :
				$field = $connection->getTable('CsDirectoryField')->find(FIELD_ID);
				$field->delete();
				break;
			default:
				break;
		}
	}

	$fields = $connection->execute('select cs_directory_field.*, cs_directory.name as directoryname, cs_property_type.name as typename from cs_directory_field, cs_directory, cs_property_type where cs_directory_field.directory_id = cs_directory.id and cs_directory_field.type_id = cs_property_type.id and cs_directory_field.directory_id = '.DIRECTORY_ID.' order by cs_directory_field.id, cs_directory_field.name');

	$directory = $connection->execute('select name from cs_directory where id = '.DIRECTORY_ID)->fetch();
	print "<caption class=\"caption\"><b>Наименование справочника: </b>".$directory['name']."</caption>\n";

	foreach ($fields as $field) {
		print "<tr>";
		print "<td>".$field['name']."</td>";
		print "<td>".$field['caption']."</td>";
		print "<td width=\"15%\" align=\"center\">".$field['typename']."</td>";
		print "<td width=\"5%\" align=\"center\">".(($field['autoinc'] == Constants::TRUE)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&field_id=".$field['id']."&directory_id=".$field['directory_id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
		print "<td width=\"3%\" align=\"center\"><a href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&field_id=".$field['id']."&directory_id=".$field['directory_id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		print "</tr>\n";
	}
	?>
	</td>
	</tr>
<?php if ($user_permissions[getParentModule()][getParentChildModule()]['can_write']): ?>
	<tr>
		<th colspan="5">&nbsp;</th>
		<th><a href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("DIRECTORY_ID")?'&directory_id='.DIRECTORY_ID:(defined("X_DIRECTORY_ID")?'&directory_id='.X_DIRECTORY_ID:"")); ?>"><img src="images/create_icon.png" /></a></th>
	</tr>
<?php endif; ?>
</table>
<?php endif; ?>
