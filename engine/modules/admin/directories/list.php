<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="7" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить справочник"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
	<tr>
		<th width="5%">Код</th>
		<th>Наименование</th>
		<th width="10%">Чтение</th>
		<th width="10%">Системный</th>
		<th colspan="4">Действия</th>
	</tr>
	<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				$directory = $connection->getTable('CsDirectory')->create();
				$directory['name'] = prepareForSave(X_DIRECTORY_NAME);
				$directory['description'] = prepareForSave(X_DIRECTORY_DESCR);
				$directory['tablename'] = prepareForSave(X_DIRECTORY_TABLE);
				$directory['parameters'] = prepareForSave(X_DIRECTORY_PARAMETERS);
				$directory['custom'] = (X_DIRECTORY_CUSTOM == 'on'?true:false);
				if ($directory['custom']) {
					$directory['tablename'] = NULL;
					$directory['parameters'] = NULL; 
				}
				if ((preg_match('/^cs_.*/', X_DIRECTORY_TABLE)) or ($directory['custom'])) {
					$directory['readonly'] = false; 
				} else {
					$directory['readonly'] = true; 
				}
				$directory->save();

				if ($directory['custom']) {
					$field = $connection->getTable('CsDirectoryField')->create();
					$field['directory_id'] = $directory['id'];
					$field['name'] = 'id';
					$field['caption'] = '№';
					$field['type_id'] = Constants::PROPERTY_TYPE_NUMBER;
					$field['autoinc'] = true;
					$field['default_value'] = 0;
					$field->save();
					
					$field = $connection->getTable('CsDirectoryField')->create();
					$field['directory_id'] = $directory['id'];
					$field['name'] = 'name';
					$field['caption'] = 'Наименование';
					$field['type_id'] = Constants::PROPERTY_TYPE_STRING;
					$field->save();

					$field = $connection->getTable('CsDirectoryField')->create();
					$field['directory_id'] = $directory['id'];
					$field['name'] = 'description';
					$field['caption'] = 'Описание';
					$field['type_id'] = Constants::PROPERTY_TYPE_TEXT;
					$field->save();
				} else {
					$res = $connection->execute('delete from cs_directory_field where directory_id = '.$directory['id'])->fetch();
				}
				break;
			case "change" :
				$directory = $connection->getTable('CsDirectory')->find(X_DIRECTORY_ID);
				$directory['name'] = prepareForSave(X_DIRECTORY_NAME);
				$directory['description'] = prepareForSave(X_DIRECTORY_DESCR);
				$directory['tablename'] = prepareForSave(X_DIRECTORY_TABLE);
				$directory['parameters'] = prepareForSave(X_DIRECTORY_PARAMETERS);
				$directory['custom'] = (X_DIRECTORY_CUSTOM == 'on'?true:false);
				if ($directory['custom']) {
					$directory['tablename'] = NULL;
					$directory['parameters'] = NULL; 
				}
				if ((preg_match('/^cs_.*/', X_DIRECTORY_TABLE)) or ($directory['custom'])) {
					$directory['readonly'] = false; 
				} else {
					$directory['readonly'] = true; 
				}
				$directory->save();

				if ($directory['custom']) {
					$res = $connection->execute('select * from cs_directory_field where directory_id = '.$directory['id'])->fetchAll();
					if (isNULL($res)) {
						$field = $connection->getTable('CsDirectoryField')->create();
						$field['directory_id'] = $directory['id'];
						$field['name'] = 'id';
						$field['caption'] = '№';
						$field['type_id'] = Constants::PROPERTY_TYPE_NUMBER;
						$field['autoinc'] = true;
						$field['default_value'] = 0;
						$field->save();
						
						$field = $connection->getTable('CsDirectoryField')->create();
						$field['directory_id'] = $directory['id'];
						$field['name'] = 'name';
						$field['caption'] = 'Наименование';
						$field['type_id'] = Constants::PROPERTY_TYPE_STRING;
						$field->save();

						$field = $connection->getTable('CsDirectoryField')->create();
						$field['directory_id'] = $directory['id'];
						$field['name'] = 'description';
						$field['caption'] = 'Описание';
						$field['type_id'] = Constants::PROPERTY_TYPE_TEXT;
						$field->save();
					}
				}
				break;
			case "delete" :
				$directory = $connection->getTable('CsDirectory')->find(DIRECTORY_ID);
				$directory->delete();
				break;
			default:
				break;
		}
	}

	$directorys = $connection->execute('select * from cs_directory order by id');
	foreach ($directorys as $directory) {
		print "<tr>";
		print "<td align=\"center\">".$directory['id']."</td>";
		print "<td title=\"".$directory['description']."\">".$directory['name']."</td>";
		print "<td align=\"center\">".(($directory['readonly'] == Constants::TRUE)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "<td align=\"center\">".(($directory['custom'] == Constants::FALSE)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		if (($user_permissions[getParentModule()][getChildModule()]['can_write']) and ($directory['custom'] == Constants::TRUE)) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Структура справочник\" href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."struct".DIRECTORY_SEPARATOR."list&directory_id=".$directory['id']."\"><img src=\"images/template.png\" /></a></td>";
		} else {
			print "<td width=\"3%\" align=\"center\">&nbsp;</td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Содержимое справочника\" href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."content".DIRECTORY_SEPARATOR."list&directory_id=".$directory['id']."\"><img src=\"images/list.png\" /></a></td>";
		} else {
			print "<td width=\"3%\" align=\"center\">&nbsp;</td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Изменить справочник\" href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&directory_id=".$directory['id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
		} else {
			print "<td width=\"3%\" align=\"center\">&nbsp;</td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_delete']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Удалить справочник\" href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&directory_id=".$directory['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		} else {
			print "<td width=\"3%\" align=\"center\">&nbsp;</td>";
		}
		print "</tr>\n";
	}
	?>
	</td>
	</tr>
	<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="7" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить справочник"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
</table>
	<?php endif; ?>
