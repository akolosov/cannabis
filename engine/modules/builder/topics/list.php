<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
	<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="5" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить тему"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
	<tr>
		<th width="5%">Код</th>
		<th>Наименование</th>
		<th>Описание</th>
		<th colspan="3">Действия</th>
	</tr>
	<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				$topic = $connection->getTable('CsPublicTopic')->create();
				$topic['name'] = prepareForSave(X_TOPIC_NAME);
				$topic['description'] = prepareForSave(X_TOPIC_DESCR);
				$topic['is_active'] = (X_TOPIC_IS_ACTIVE == 'on'?true:false);
				$topic->save();
				break;
			case "change" :
				$topic = $connection->getTable('CsPublicTopic')->find(X_TOPIC_ID);
				$topic['name'] = prepareForSave(X_TOPIC_NAME);
				$topic['description'] = prepareForSave(X_TOPIC_DESCR);
				$topic['is_active'] = (X_TOPIC_IS_ACTIVE == 'on'?true:false);
				$topic->save();
				break;
			case "delete" :
				$topic = $connection->getTable('CsPublicTopic')->find(TOPIC_ID);
				$topic['is_active'] = (!$topic['is_active']);
				$topic->save();
				break;
			case "erase" :
				$topic = $connection->getTable('CsPublicTopic')->find(TOPIC_ID);
				$topic->delete();
				break;
			default:
				break;
		}
	}

	$topics = $connection->execute('select * from cs_public_topic order by id');
	foreach ($topics as $topic) {
		print "<tr>";
		print "<td align=\"center\">".$topic['id']."</td>";
		print "<td ".(!$topic['is_active']?"class=\"strike\"":"").">".$topic['name']."</td>";
		print "<td ".(!$topic['is_active']?"class=\"strike\"":"").">".$topic['description']."</td>";
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Изменить тему\" href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&topic_id=".$topic['id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_delete']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Удалить тему\" href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&topic_id=".$topic['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_admin']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Удалить СОВСЕМ тему\" href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=erase&topic_id=".$topic['id']."', '_top', true);\"><img src=\"images/erase.png\" /></a></td>";
		}
		print "</tr>\n";
	}
	?>
	</td>
	</tr>
	<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="5" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить тему"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
</table>
<?php endif; ?>
