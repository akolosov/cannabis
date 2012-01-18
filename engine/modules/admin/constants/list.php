<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="4" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить константу"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
	<tr>
		<th width="5%">Код</th>
		<th>Наименование</th>
		<th>Значение</th>
		<th colspan="2">Действия</th>
	</tr>
	<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				$constant = $connection->getTable('CsConstants')->create();
				$constant['name'] = prepareForSave(X_CONSTANT_NAME);
				$constant['description'] = prepareForSave(X_CONSTANT_DESCR);
				$constant['value'] = prepareForSave(X_CONSTANT_VALUE);
				$constant['fixed_name'] = (X_CONSTANT_FIXED_NAME == 'on'?true:false);
				$constant->save();
				break;
			case "change" :
				$constant = $connection->getTable('CsConstants')->find(X_CONSTANT_ID);
				$constant['name'] = prepareForSave(X_CONSTANT_NAME);
				$constant['description'] = prepareForSave(X_CONSTANT_DESCR);
				$constant['value'] = prepareForSave(X_CONSTANT_VALUE);
				$constant['fixed_name'] = (X_CONSTANT_FIXED_NAME == 'on'?true:false);
				$constant->save();
				break;
			case "delete" :
				$constant = $connection->getTable('CsConstants')->find(CONSTANT_ID);
				$constant->delete();
				break;
			default:
				break;
		}
	}

	$constants = $connection->execute('select * from cs_constants order by id');
	foreach ($constants as $constant) {
		print "<tr>";
		print "<td align=\"center\">".$constant['id']."</td>";
		print "<td title=\"".$constant['description'].($constant['name']?" (Зарезервированное имя)":"")."\">".($constant['fixed_name']?"<b>".$constant['name']."</b>":$constant['name'])."</td>";
		print "<td>".$constant['value']."</td>";
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Изменить константу\" href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&constant_id=".$constant['id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_delete']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Удалить константу\" href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&constant_id=".$constant['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		}
		print "</tr>\n";
	}
	?>
	</td>
	</tr>
	<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="4" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить константу"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
</table>
	<?php endif; ?>
