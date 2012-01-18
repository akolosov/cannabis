
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
	<tr>
		<th colspan="8" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить модуль"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("PERMISSION_ID")?'&permission_id='.PERMISSION_ID:(defined("X_PERMISSION_ID")?'&permission_id='.X_PERMISSION_ID:"")); ?>"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<tr>
		<th>Наименование модуля</th>
		<th>Чтение</th>
		<th>Запись</th>
		<th>Удаление</th>
		<th>Админист.</th>
		<th>Обзор</th>
		<th>Наблюдение</th>
		<th colspan="2">Действия</th>
	</tr>
	<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				if (defined('X_PERMISSION_ID')) {
					$permission = $connection->getTable('CsPermissionList')->create();
					$permission['permission_id'] = X_PERMISSION_ID;
					$permission['module_id'] = X_PERMISSION_MODULE_ID;
					$permission['can_read'] = (X_PERMISSION_CAN_READ == 'on'?true:false);
					$permission['can_write'] = (X_PERMISSION_CAN_WRITE == 'on'?true:false);
					$permission['can_delete'] = (X_PERMISSION_CAN_DELETE == 'on'?true:false);
					$permission['can_admin'] = (X_PERMISSION_CAN_ADMIN == 'on'?true:false);
					$permission['can_review'] = (X_PERMISSION_CAN_REVIEW == 'on'?true:false);
					$permission['can_observe'] = (X_PERMISSION_CAN_OBSERVE == 'on'?true:false);
					$permission->save();
				}
				break;
			case "change" :
				if (defined('X_PERMISSION_LIST_ID')) {
					$permission = $connection->getTable('CsPermissionList')->find(X_PERMISSION_LIST_ID);
					$permission['can_read'] = (X_PERMISSION_CAN_READ == 'on'?true:false);
					$permission['can_write'] = (X_PERMISSION_CAN_WRITE == 'on'?true:false);
					$permission['can_delete'] = (X_PERMISSION_CAN_DELETE == 'on'?true:false);
					$permission['can_admin'] = (X_PERMISSION_CAN_ADMIN == 'on'?true:false);
					$permission['can_review'] = (X_PERMISSION_CAN_REVIEW == 'on'?true:false);
					$permission['can_observe'] = (X_PERMISSION_CAN_OBSERVE == 'on'?true:false);
					$permission->save();
				}
				break;
			case "delete" :
				$permission = $connection->getTable('CsPermissionList')->findByDQL('permission_id = '.PERMISSION_ID.' and module_id = '.MODULE_ID);
				$permission->delete();
				break;
			default:
				break;
		}
	}

	$permissions = $connection->execute('select cs_permission.name as permissionname, cs_module.name as modulename, cs_module.description as moduledescr, cs_permission_list.* from cs_permission_list, cs_permission, cs_module where cs_permission_list.module_id = cs_module.id and cs_permission_list.permission_id = cs_permission.id '.(defined("PERMISSION_ID")?' and permission_id = '.PERMISSION_ID:'').' order by cs_permission_list.id, cs_permission_list.module_id');
	$permission = $connection->execute('select name from cs_permission where id = '.PERMISSION_ID)->fetch();
	print "<caption class=\"caption\"><b>Наименование права: </b>".$permission['name']."</caption>\n";
	foreach ($permissions as $permission) {
		print "<td title=\"".$permission['moduledescr']."\">".$permission['modulename']."</td>";
		print "<td width=\"6%\"  align=\"center\">".(($permission['can_read'] == Constants::TRUE)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "<td width=\"6%\"  align=\"center\">".(($permission['can_write'] == Constants::TRUE)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "<td width=\"6%\"  align=\"center\">".(($permission['can_delete'] == Constants::TRUE)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "<td width=\"6%\"  align=\"center\">".(($permission['can_admin'] == Constants::TRUE)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "<td width=\"6%\"  align=\"center\">".(($permission['can_review'] == Constants::TRUE)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "<td width=\"6%\"  align=\"center\">".(($permission['can_observe'] == Constants::TRUE)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "<td width=\"3%\" align=\"center\"><a title=\"Изменить права на модуль\" href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&permission_id=".$permission['permission_id']."&module_id=".$permission['module_id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
		print "<td width=\"3%\" align=\"center\"><a title=\"Удалить модуль\" href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&permission_id=".$permission['permission_id']."&module_id=".$permission['module_id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
        print "</tr>\n";
	}
	?>
	</td>
	</tr>
	<tr>
		<th colspan="8" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить модуль"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("PERMISSION_ID")?'&permission_id='.PERMISSION_ID:(defined("X_PERMISSION_ID")?'&permission_id='.X_PERMISSION_ID:"")); ?>"><img
			src="images/create_icon.png" /></a></th>
	</tr>
</table>
