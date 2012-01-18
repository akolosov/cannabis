<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="4" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить mime-тип"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
	<tr>
		<th width="5%">Код</th>
		<th>Наименование</th>
		<th>Активно</th>
		<th colspan="2">Действия</th>
	</tr>
	<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				$mime = $connection->getTable('CsMime')->create();
				$mime['name'] = prepareForSave(X_MIME_NAME);
				$mime['ext'] = prepareForSave(X_MIME_EXT);
				$mime['is_active'] = true;
				$mime->save();
				break;
			case "change" :
				$mime = $connection->getTable('CsMime')->find(X_MIME_ID);
				$mime['name'] = prepareForSave(X_MIME_NAME);
				$mime['ext'] = prepareForSave(X_MIME_EXT);
				$mime->save();
				break;
			case "delete" :
				$mime = $connection->getTable('CsMime')->find(MIME_ID);
				$mime['is_active'] = (!$mime['is_active']);
				$mime->save();
				break;
			default:
				break;
		}
	}

	$mimes = $connection->execute('select * from cs_mime order by id')->fetchAll();
	foreach ($mimes as $mime) {
		print "<tr>";
		print "<td align=\"center\">".$mime['id']."</td>";
		print "<td title=\"Расширение: ".$mime['ext']."\">".$mime['name']."</td>";
		print "<td align=\"center\">".(($mime['is_active'] == 1)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Изменить mime-тип\" href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&mime_id=".$mime['id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_delete']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Удалить mime-тип\" href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&mime_id=".$mime['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		}
		print "</tr>\n";
	}
	?>
	</td>
	</tr>
	<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="4" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить mime-тип"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
</table>
	<?php endif; ?>
