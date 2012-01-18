<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="5" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить ответственность"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
	<tr>
		<th width="5%">Код</th>
		<th>Наименование</th>
		<th>Ответственный</th>
		<th colspan="3">Действия</th>
	</tr>
	<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				$responser = $connection->getTable('CsResponser')->create();
				$responser['name'] = prepareForSave(X_RESPONSER_NAME);
				$responser['description'] = prepareForSave(X_RESPONSER_DESCR);
				$responser['account_id'] = X_RESPONSER_ACCOUNT;
				$responser->save();
				break;
			case "change" :
				$responser = $connection->getTable('CsResponser')->find(X_RESPONSER_ID);
				$responser['name'] = prepareForSave(X_RESPONSER_NAME);
				$responser['description'] = prepareForSave(X_RESPONSER_DESCR);
				$responser['account_id'] = ((defined('X_RESPONSER_ACCOUNT') and trim(X_RESPONSER_ACCOUNT) <> '')?X_RESPONSER_ACCOUNT:X_ACCOUNT_ID);
				$responser->save();
				break;
			case "delete" :
				$responser = $connection->getTable('CsResponser')->find(RESPONSER_ID);
				$responser['is_active'] = (!$responser['is_active']);
				$responser->save();
				break;
			case "erase" :
				$responser = $connection->getTable('CsResponser')->find(RESPONSER_ID);
				$responser->delete();
				break;
			default:
				break;
		}
	}

	$responsers = $connection->execute('select * from responsers_list order by id');
	foreach ($responsers as $responser) {
		print "<tr>";
		print "<td align=\"center\">".$responser['id']."</td>";
		print "<td title=\"".$responser['description']."\">".($responser['is_active']?'':'<strike>').$responser['name'].($responser['is_active']?'':'</strike>')."</td>";
		print "<td title=\"".$responser['accountdescr']."\">".($responser['is_active']?'':'<strike>').$responser['accountname'].($responser['is_active']?'':'</strike>')."</td>";
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Изменить ответственность\" href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&responser_id=".$responser['id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_delete']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Удалить ответственность\" href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&responser_id=".$responser['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_delete']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Удалить СОВСЕМ ответственность\" href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=erase&responser_id=".$responser['id']."', '_top', true);\"><img src=\"images/erase.png\" /></a></td>";
		}
		print "</tr>\n";
	}
	?>
	</td>
	</tr>
	<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="5" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить ответственность"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
</table>
	<?php endif; ?>
