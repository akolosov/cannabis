<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="4" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить право доступа"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
	<tr>
		<th width="5%">Код</th>
		<th>Наименование</th>
		<th colspan="3">Действия</th>
	</tr>
	<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				$permission = $connection->getTable('CsPermission')->create();
				$permission['name'] = prepareForSave(X_PERMISSION_NAME);
				$permission['description'] = prepareForSave(X_PERMISSION_DESCR);
				$permission->save();
				break;
			case "change" :
				$permission = $connection->getTable('CsPermission')->find(X_PERMISSION_ID);
				$permission['name'] = prepareForSave(X_PERMISSION_NAME);
				$permission['description'] = prepareForSave(X_PERMISSION_DESCR);
				$permission->save();
				break;
			case "delete" :
				$permission = $connection->getTable('CsPermission')->find(PERMISSION_ID);
				$permission->delete();
				break;
			default:
				break;
		}
	}

	$permissions = $connection->execute('select * from cs_permission order by id');
	foreach ($permissions as $permission) {
		print "<tr>";
		print "<td align=\"center\">".$permission['id']."</td>";
		print "<td title=\"".$permission['description']."\">".$permission['name']."</td>";
		if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Права доступа к модулям\" href=\"?module=admin/permissions/modules/list&permission_id=".$permission['id']."\"><img src=\"images/list.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Редактировать данные о праве доступа\" href=\"?module=admin/permissions/edit&action=change&permission_id=".$permission['id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_delete']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Удалить право доступа\" href=\"javascript:confirmIt('?module=admin/permissions/list&action=delete&permission_id=".$permission['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		}
		print "</tr>\n";
	}
	?>
	</td>
	</tr>
	<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="4" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить право доступа"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
</table>
	<?php endif; ?>
