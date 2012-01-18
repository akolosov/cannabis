<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="6" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить ответственность"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
	<tr>
		<th>Доверяющий</th>
		<th>Ответственный</th>
		<th>Период с</th>
		<th>Период по</th>
		<th colspan="3">Действия</th>
	</tr>
	<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				$delegate = $connection->getTable('CsDelegate')->create();
				$delegate['account_id'] = USER_CODE;
				$delegate['delegate_id'] = X_DELEGATE_ACCOUNT;
				$delegate['is_active'] = true;
				$delegate['started_at'] = strftime("%Y-%m-%d %H:%M", strtotime(X_PERIOD_FROM));
				$delegate['ended_at'] = strftime("%Y-%m-%d %H:%M", strtotime(X_PERIOD_TO));
				$delegate->save();
				break;
			case "change" :
				$delegate = $connection->getTable('CsDelegate')->find(X_DELEGATE_ID);
				$delegate['delegate_id'] = ((defined('X_DELEGATE_ACCOUNT') and trim(X_DELEGATE_ACCOUNT) <> '')?X_DELEGATE_ACCOUNT:X_ACCOUNT_ID);
				$delegate['started_at'] = strftime("%Y-%m-%d %H:%M", strtotime(X_PERIOD_FROM));
				$delegate['ended_at'] = strftime("%Y-%m-%d %H:%M", strtotime(X_PERIOD_TO));
				$delegate->save();
				break;
			case "delete" :
				$delegate = $connection->getTable('CsDelegate')->find(DELEGATE_ID);
				$delegate['is_active'] = (!$delegate['is_active']);
				$delegate->save();
				break;
			case "erase" :
				$delegate = $connection->getTable('CsDelegate')->find(DELEGATE_ID);
				$delegate->delete();
				break;
			default:
				break;
		}
	}

	$delegates = $connection->execute('select * from delegates_list where account_id = '.USER_CODE.' or delegate_id = '.USER_CODE.' order by started_at desc');
	foreach ($delegates as $delegate) {
		print "<tr>";
		print "<td>".(((!$delegate['is_active']) or ((strtotime($delegate['started_at']) < time()) and (strtotime($delegate['ended_at']) < time())))?'<strike>':'').$delegate['accountname'].(((!$delegate['is_active']) or ((strtotime($delegate['started_at']) < time()) and (strtotime($delegate['ended_at']) < time())))?'</strike>':'')."</td>";
		print "<td>".(((!$delegate['is_active']) or ((strtotime($delegate['started_at']) < time()) and (strtotime($delegate['ended_at']) < time())))?'<strike>':'').$delegate['delegatename'].(((!$delegate['is_active']) or ((strtotime($delegate['started_at']) < time()) and (strtotime($delegate['ended_at']) < time())))?'</strike>':'')."</td>";
		print "<td align=\"center\">".(((!$delegate['is_active']) or ((strtotime($delegate['started_at']) < time()) and (strtotime($delegate['ended_at']) < time())))?'<strike>':'').strftime("%d.%m.%Y в %H:%M", strtotime($delegate['started_at'])).(((!$delegate['is_active']) or ((strtotime($delegate['started_at']) < time()) and (strtotime($delegate['ended_at']) < time())))?'</strike>':'')."</td>";
		print "<td align=\"center\">".(((!$delegate['is_active']) or ((strtotime($delegate['started_at']) < time()) and (strtotime($delegate['ended_at']) < time())))?'<strike>':'').strftime("%d.%m.%Y в %H:%M", strtotime($delegate['ended_at'])).(((!$delegate['is_active']) or ((strtotime($delegate['started_at']) < time()) and (strtotime($delegate['ended_at']) < time())))?'</strike>':'')."</td>";
		if (($user_permissions[getParentModule()][getChildModule()]['can_write']) and ($delegate['account_id'] == USER_CODE)) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Изменить делигирование\" href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&delegate_id=".$delegate['id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
		} else {
			print "<td width=\"3%\" align=\"center\">&nbsp;</td>";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_delete']) and ($delegate['account_id'] == USER_CODE)) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Удалить делигирование\" href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&delegate_id=".$delegate['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		} else {
			print "<td width=\"3%\" align=\"center\">&nbsp;</td>";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and ($delegate['account_id'] == USER_CODE)) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Удалить СОВСЕМ делигирование\" href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=erase&delegate_id=".$delegate['id']."', '_top', true);\"><img src=\"images/erase.png\" /></a></td>";
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
		<th colspan="6" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить ответственность"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
</table>
	<?php endif; ?>
