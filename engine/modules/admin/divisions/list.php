<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<table width="100%" align="center">
	<tr>
		<th width="99%" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить подразделение"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	</table>
<?php endif; ?>
<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				$division = $connection->getTable('CsDivision')->create();
				$division['parent_id'] = (defined('X_DIVISION_PARENT_ID') && X_DIVISION_PARENT_ID != ""?X_DIVISION_PARENT_ID:NULL);
				$division['boss_id'] = (defined('X_DIVISION_BOSS_ID') && X_DIVISION_BOSS_ID != ""?X_DIVISION_BOSS_ID:NULL);
				$division['name'] = prepareForSave(X_DIVISION_NAME);
				$division['description'] = prepareForSave(X_DIVISION_DESCR);
				$division->save();
				break;
			case "change" :
				$division = $connection->getTable('CsDivision')->find(X_DIVISION_ID);
				$division['parent_id'] = (defined('X_DIVISION_PARENT_ID')?(X_DIVISION_PARENT_ID == ""?NULL:X_DIVISION_PARENT_ID):(X_PARENT_ID != ""?X_PARENT_ID:NULL));
				$division['boss_id'] = (defined('X_DIVISION_BOSS_ID')?(X_DIVISION_BOSS_ID == ""?NULL:X_DIVISION_BOSS_ID):(X_BOSS_ID != ""?X_BOSS_ID:NULL));
				$division['name'] = prepareForSave(X_DIVISION_NAME);
				$division['description'] = prepareForSave(X_DIVISION_DESCR);
				$division->save();
				break;
			case "delete" :
				$division = $connection->getTable('CsDivision')->find(DIVISION_ID);
				$division->delete();
				break;
			default:
				break;
		}
	}

	function printCurrentRow($division) {
		global $user_permissions;

		print "<li class=\"treeitem\"><table width=\"auto\" cellspacing=\"1\" cellpadding=\"1\" style=\" border : 1px dotted #ccc; border-collapse : collapse; \">";
		print "<tr><td><a href=\"#\">&nbsp;&nbsp;&nbsp;</a></td>";
		print "<td width=\"74%\" title=\"".($division['description']?$division['description']:"&nbsp;")."\">".$division['name']."</td>";
		print "<td width=\"20%\" align=\"center\">".$division['bossname']."</td>";
		if (($user_permissions[getParentModule()][getChildModule()]['can_write']) and (($division['id'] > 0) or ($user_permissions[getParentModule()][getChildModule()]['can_admin']))) {
			print "<td width=\"3%\" align=\"center\"><span title=\"Изменить пользователя или группу\" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&division_id=".$division['id']."'\"><img src=\"images/edit_icon.png\" /></span></td>";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_delete']) and (($division['id'] > 0) or ($user_permissions[getParentModule()][getChildModule()]['can_admin']))) {
			print "<td width=\"3%\" align=\"center\"><span title=\"Удалить пользователя или группу\" onClick=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&division_id=".$division['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></span></td>";
		}
		print "</tr></table>";
		printRow($division['id']);
		print "</li>\n";
	}

	function printRow($id = 0, $level = 0) {
		global $connection;

		$divisions = $connection->execute('select * from divisions_tree where parent_id = '.$id)->fetchAll();

		foreach ($divisions as $division) {
			print "<ul>";
			printCurrentRow($division);
			print "</ul>";
		}
	}

	$divisions = $connection->execute('select * from divisions_tree where level = 0 order by id, name');
	print "<ul class=\"tree\" id=\"divisions_tree\" style=\" display : none; \">\n";
	foreach ($divisions as $division) {
		print "<li class=\"roottreeitem\"><table width=\"auto\" cellspacing=\"1\" cellpadding=\"1\" style=\" border : 1px dotted #ccc; border-collapse : collapse; \">";
		print "<tr><td><a href=\"#\">&nbsp;&nbsp;&nbsp</a></td>";
		print "<td width=\"74%\" title=\"".($division['description']?$division['description']:"&nbsp;")."\">".$division['name']."</td>";
		print "<td width=\"20%\" align=\"center\">".$division['bossname']."</td>";
		if (($user_permissions[getParentModule()][getChildModule()]['can_write']) and (($division['id'] > 0) or ($user_permissions[getParentModule()][getChildModule()]['can_admin']))) {
			print "<td width=\"3%\" align=\"center\"><span title=\"Изменить пользователя или группу\" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&division_id=".$division['id']."'\"><img src=\"images/edit_icon.png\" /></span></td>";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_delete']) and (($division['id'] > 0) or ($user_permissions[getParentModule()][getChildModule()]['can_admin']))) {
			print "<td width=\"3%\" align=\"center\"><span title=\"Удалить пользователя или группу\" onClick=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&division_id=".$division['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></span></td>";
		}
		print "</tr></table>";
		printRow($division['id']);
		print "</li>\n";
	}
	print "</ul>\n";
	?>
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<table width="100%" align="center">
	<tr>
		<th width="99%" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить подразделение"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
</table>
<?php endif; ?>
<script>
<!--
	var divisions_tree_options = {
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
	projects_tree.initMenu('divisions_tree', divisions_tree_options);
//-->
</script>
<?php endif; ?>
