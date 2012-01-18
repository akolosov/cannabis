<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<table width="100%" align="center">
	<tr>
		<th width="99%" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить пользователя или группу"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
</table>
<?php endif; ?>
<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				$user = $connection->getTable('CsAccount')->create();
				$user['parent_id'] = (defined('X_USER_PARENT_ID') && X_USER_PARENT_ID != ""?X_USER_PARENT_ID:NULL);
				$user['name'] = prepareForSave(X_USER_NAME);
				$user['description'] = prepareForSave(X_USER_DESCR);
				$user['passwd'] = (defined('X_USER_PASSWD') && X_USER_PASSWD <> ""?prepareForSave(X_USER_PASSWD):NULL);
				$user['email'] = prepareForSave(X_USER_MAIL);
				$user['icq'] = prepareForSave(X_USER_ICQ);
				$user['jabber'] = prepareForSave(X_USER_JABBER);
				$user['cell'] = prepareForSave(X_USER_CELL);
				$user['is_active'] = (X_USER_ACTIVE == 'on'?true:false);
				$user['permission_id'] = (defined('X_USER_PERMISSION') && X_USER_PERMISSION <> ""?X_USER_PERMISSION:NULL);
				$user['cellop_id'] = (defined('X_USER_CELLOP') && X_USER_CELLOP <> ""?X_USER_CELLOP:NULL);
				$user['division_id'] = (defined('X_USER_DIVISION') && X_USER_DIVISION <> ""?X_USER_DIVISION:NULL);
				$user->save();

				foreach ($parameters['X_USER_DIVISIONS'] as $division_id) {
					$division = $connection->getTable('CsAccountDivision')->create();
					$division['account_id'] = $user['id'];
					$division['division_id'] = $division_id;
					$division->save();
				}
				break;
			case "change" :
				$user = $connection->getTable('CsAccount')->find(X_USER_ID);
				$user['parent_id'] = (defined('X_USER_PARENT_ID')?(X_USER_PARENT_ID != ""?X_USER_PARENT_ID:NULL):(X_PARENT_ID == ""?NULL:X_PARENT_ID));
				$user['name'] = prepareForSave(X_USER_NAME);
				$user['description'] = prepareForSave(X_USER_DESCR);
				if (trim(X_USER_PASSWD) <> trim(md5(X_USER_OLD_PASSWD))) {
					$user['passwd'] = (defined('X_USER_PASSWD') && X_USER_PASSWD <> ""?trim(prepareForSave(X_USER_PASSWD)):NULL);
				}
				$user['email'] = prepareForSave(X_USER_MAIL);
				$user['icq'] = prepareForSave(X_USER_ICQ);
				$user['jabber'] = prepareForSave(X_USER_JABBER);
				$user['cell'] = prepareForSave(X_USER_CELL);
				$user['is_active'] = (X_USER_ACTIVE == 'on'?true:false);
				$user['permission_id'] = (defined('X_USER_PERMISSION') && X_USER_PERMISSION <> ""?X_USER_PERMISSION:NULL);
				$user['cellop_id'] = (defined('X_USER_CELLOP') && X_USER_CELLOP <> ""?X_USER_CELLOP:(X_CELLOP_ID <> ''?X_CELLOP_ID:NULL));
				$user['division_id'] = (defined('X_USER_DIVISION') && X_USER_DIVISION <> ""?X_USER_DIVISION:(X_DIVISION_ID <> ''?X_DIVISION_ID:NULL));
				$user->save();

				$divisions = $connection->execute('delete from cs_account_division where account_id = '.X_USER_ID)->fetch();
				foreach ($parameters['X_USER_DIVISIONS'] as $division_id) {
					$division = $connection->getTable('CsAccountDivision')->create();
					$division['account_id'] = $user['id'];
					$division['division_id'] = $division_id;
					$division->save();
				}
				break;
			case "delete" :
				$user = $connection->getTable('CsAccount')->find(USER_ID);
				$user['is_active'] = (!$user['is_active']);
				$user->save();
				break;
			default:
				break;
		}
	}

	function printCurrentRow($user) {
		global $user_permissions;

		print "<li class=\"treeitem\"><table width=\"auto\" cellspacing=\"1\" cellpadding=\"1\" style=\" border : 1px dotted #ccc; border-collapse : collapse; \">";
		print "<tr><td width=\"1%\"><a href=\"#\">&nbsp;&nbsp;&nbsp;</a></td><td align=\"center\" width=\"5%\" title=\"Код пользователя\">".$user['id']."</td>";
		print "<td width=\"94%\" title=\"".($user['description']?$user['description'].($user['is_active']?'':' (НЕАКТИВЕН)'):"&nbsp;")."\">".($user['is_active']?'':'<strike>').$user['name'].($user['is_active']?'':'</strike>')."</td>";
		if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and (isNotNull($user['passwd']) and isNotNULL($user['permission']))) {
			print "<td width=\"3%\" align=\"center\"><span title=\"Должности пользователя\" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."posts/list&user_id=".$user['id']."'\"><img src=\"images/posts.png\" /></span></td>";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_write']) and (($user['id'] > 0) or ($user_permissions[getParentModule()][getChildModule()]['can_admin']))) {
			print "<td width=\"3%\" align=\"center\"><span title=\"Изменить пользователя или группу\" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&user_id=".$user['id']."'\"><img src=\"images/edit_icon.png\" /></span></td>";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_delete']) and (($user['id'] > 0) or ($user_permissions[getParentModule()][getChildModule()]['can_admin']))) {
			print "<td width=\"3%\" align=\"center\"><span title=\"Активировать/Деактивировать пользователя или группу\" onClick=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&user_id=".$user['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></span></td>";
		}
		print "</tr></table>";
		printRow($user['id']);
		print "</li>\n";
	}

	function printRow($id = 0, $level = 0) {
		global $connection;

		$users = $connection->execute('select * from accounts_tree where parent_id = '.$id)->fetchAll();

		foreach ($users as $user) {
			print "<ul>\n";
			printCurrentRow($user);
			print "</ul>";
		}
	}

	$users = $connection->execute('select * from accounts_tree where level = 0 order by id, name');
	print "<ul class=\"tree\" id=\"accounts_tree\" style=\" display : none; \">\n";
	foreach ($users as $user) {
		print "<li class=\"roottreeitem\"><table width=\"auto\" cellspacing=\"1\" cellpadding=\"1\" style=\" border : 1px dotted #ccc; border-collapse : collapse; \">";
		print "<tr><td width=\"1%\"><a href=\"#\"></a></td><td align=\"center\" width=\"5%\" title=\"Код пользователя\">".$user['id']."</td>";
		print "<td width=\"94%\" title=\"".($user['description']?$user['description']:"&nbsp;")."\">".$user['name']."</td>";
		if (($user_permissions[getParentModule()][getChildModule()]['can_write']) and (($user['id'] > 0) or ($user_permissions[getParentModule()][getChildModule()]['can_admin']))) {
			print "<td width=\"3%\" align=\"center\"><span title=\"Изменить пользователя или группу\" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&user_id=".$user['id']."'\"><img src=\"images/edit_icon.png\" /></span></td>";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_delete']) and (($user['id'] > 0) or ($user_permissions[getParentModule()][getChildModule()]['can_admin']))) {
			print "<td width=\"3%\" align=\"center\"><span title=\"Удалить пользователя или группу\" onClick=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&user_id=".$user['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></span></td>";
		}
		print "</tr></table>";
		printRow($user['id']);
		print "</li>\n";
	}
	print "</ul>\n";
?>
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<table width="100%" align="center">
	<tr>
		<th width="99%" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить пользователя или группу"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
</table>
<?php endif; ?>
<script>
<!--
	var accounts_tree_options = {
			'theme' : { 'name' : 'SimpleTree' },
			closeSiblings : false,
			maxOpenDepth : 1,
			flagClosedClass : 'close',
			toggleMenuOnClick : true,
			incrementalConvert : false,
			openTimeout : 0,
			closeTimeout: 0
	}
	var projects_tree = new CompleteMenuSolution;
	projects_tree.initMenu('accounts_tree', accounts_tree_options);
//-->
</script>
<?php endif; ?>
