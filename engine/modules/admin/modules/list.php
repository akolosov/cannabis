<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<table width="100%" align="center">
	<tr>
		<th width="99%" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить модуль"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
</table>
<?php endif; ?>
<?php
if (defined("ACTION")) {
	switch (ACTION) {
		case "add" :
			$module = $connection->getTable('CsModule')->create();
			$module['parent_id'] = (defined('X_MODULE_PARENT_ID') && X_MODULE_PARENT_ID != ""?X_MODULE_PARENT_ID:NULL);
			$module['name'] = prepareForSave(X_MODULE_NAME);
			$module['description'] = prepareForSave(X_MODULE_DESCR);
			$module['caption'] = prepareForSave(X_MODULE_CAPTION);
			$module['is_hidden'] = (X_MODULE_IS_HIDDEN == 'on'?true:false);
			$module->save();
			break;
		case "change" :
			$module = $connection->getTable('CsModule')->find(X_MODULE_ID);
			$module['parent_id'] = (defined('X_MODULE_PARENT_ID')?(X_MODULE_PARENT_ID != ""?X_MODULE_PARENT_ID:NULL):(X_PARENT_ID != ""?X_PARENT_ID:NULL));
			$module['name'] = prepareForSave(X_MODULE_NAME);
			$module['description'] = prepareForSave(X_MODULE_DESCR);
			$module['caption'] = prepareForSave(X_MODULE_CAPTION);
			$module['is_hidden'] = (X_MODULE_IS_HIDDEN == 'on'?true:false);
			$module->save();
			break;
		case "delete" :
			$module = $connection->getTable('CsModule')->find(MODULE_ID);
			$module->delete();
			break;
		default:
			break;
	}
}


function printCurrentRow($module) {
	global $user_permissions;

	print "<li class=\"treeitem\"><table width=\"auto\" cellspacing=\"1\" cellpadding=\"1\" style=\" border : 1px dotted #ccc; border-collapse : collapse; \">";
//	print "<tr><td><a href=\"#\"></a></td><td align=\"center\" width=\"5%\">".$module['id']."</td>";
	print "<tr><td><a href=\"#\"></a></td><td align=\"center\" width=\"2%\"></td>";
	print "<td width=\"94%\" title=\"".($module['description']?$module['description']:"&nbsp;")."\">".$module['name']."</td>";
	if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
		print "<td width=\"3%\" align=\"center\"><span title=\"Изменить пользователя или группу\" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&module_id=".$module['id']."'\"><img src=\"images/edit_icon.png\" /></span></td>";
	}
	if ($user_permissions[getParentModule()][getChildModule()]['can_delete']) {
		print "<td width=\"3%\" align=\"center\"><span title=\"Удалить пользователя или группу\" onClick=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&module_id=".$module['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></span></td>";
	}
	print "</tr></table>";
	printRow($module['id']);
	print "</li>\n";
}

function printRow($id = 0, $level = 0) {
	global $connection;

	$modules = $connection->execute('select * from modules_tree where parent_id = '.$id)->fetchAll();

	foreach ($modules as $module) {
		print "<ul>\n";
		printCurrentRow($module);
		print "</ul>";
	}
}

$modules = $connection->execute('select * from modules_tree where level = 0 order by id, name');
print "<ul id=\"modules_tree\" style=\" display : none; \">\n";
foreach ($modules as $module) {
	print "<li class=\"roottreeitem\"><table width=\"auto\" cellspacing=\"1\" cellpadding=\"1\" style=\" border : 1px dotted #ccc; border-collapse : collapse; \">";
//	print "<tr><td><a href=\"#\"></a></td><td align=\"center\" width=\"5%\">".$module['id']."</td>";
	print "<tr><td><a href=\"#\"></a></td><td align=\"center\" width=\"2%\"></td>";
	print "<td width=\"94%\" title=\"".($module['description']?$module['description']:"&nbsp;")."\">".$module['name']."</td>";
	if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
		print "<td width=\"3%\" align=\"center\"><span title=\"Изменить пользователя или группу\" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&module_id=".$module['id']."'\"><img src=\"images/edit_icon.png\" /></span></td>";
	}
	if ($user_permissions[getParentModule()][getChildModule()]['can_delete']) {
		print "<td width=\"3%\" align=\"center\"><span title=\"Удалить пользователя или группу\" onClick=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&module_id=".$module['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></span></td>";
	}
	print "</tr></table>";
	printRow($module['id']);
	print "</li>\n";
}
print "</ul>\n";
?>
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<table width="100%" align="center">
	<tr>
		<th width="99%" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить модуль"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
</table>
<?php endif; ?>
<script>
<!--
	var modules_tree_options = {
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
	projects_tree.initMenu('modules_tree', modules_tree_options);
//-->
</script>
<?php endif; ?>