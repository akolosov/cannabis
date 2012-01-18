<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="3" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить должность"
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
				$post = $connection->getTable('CsPost')->create();
				$post['name'] = prepareForSave(X_POST_NAME);
				$post['description'] = prepareForSave(X_POST_DESCR);
				$post->save();
				break;
			case "change" :
				$post = $connection->getTable('CsPost')->find(X_POST_ID);
				$post['name'] = prepareForSave(X_POST_NAME);
				$post['description'] = prepareForSave(X_POST_DESCR);
				$post->save();
				break;
			case "delete" :
				$post = $connection->getTable('CsPost')->find(POST_ID);
				$post->delete();
				break;
			default:
				break;
		}
	}

	$posts = $connection->execute('select * from cs_post order by id');
	foreach ($posts as $post) {
		print "<tr>";
		print "<td align=\"center\">".$post['id']."</td>";
		print "<td title=\"".$post['description']."\">".$post['name']."</td>";
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Изменить должность\" href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&post_id=".$post['id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_delete']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Удалить должность\" href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&post_id=".$post['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		}
		print "</tr>\n";
	}
	?>
	</td>
	</tr>
	<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="3" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить должность"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
</table>
	<?php endif; ?>
