<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="3" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить роль"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
	<tr>
		<th width="5%">Код</th>
		<th>Наименование</th>
		<th colspan="2">Действия</th>
	</tr>
	<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				$role = $connection->getTable('CsRole')->create();
				$role['name'] = prepareForSave(X_ROLE_NAME);
				$role['description'] = prepareForSave(X_ROLE_DESCR);
				$role->save();
				break;
			case "change" :
				$role = $connection->getTable('CsRole')->find(X_ROLE_ID);
				$role['name'] = prepareForSave(X_ROLE_NAME);
				$role['description'] = prepareForSave(X_ROLE_DESCR);
				$role->save();
				break;
			case "delete" :
				$role = $connection->getTable('CsRole')->find(ROLE_ID);
				$role->delete();
				break;
			default:
				break;
		}
	}

	$roles = $connection->execute('select * from cs_role order by id')->fetchAll();
	foreach ($roles as $role) {
		print "<tr>";
		print "<td align=\"center\">".$role['id']."</td>";
		print "<td title=\"".$role['description']."\">".$role['name']."</td>";
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Изменить роль\" href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&role_id=".$role['id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_delete']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Удалить роль\" href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&role_id=".$role['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		}
		print "</tr>\n";
	}
	?>
	</td>
	</tr>
	<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="3" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить роль"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
</table>
	<?php endif; ?>
