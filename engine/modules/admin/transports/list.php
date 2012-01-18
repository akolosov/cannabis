<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="3" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить транспорт"
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
				$transport = $connection->getTable('CsTransport')->create();
				$transport['name'] = prepareForSave(X_TRANSPORT_NAME);
				$transport['description'] = prepareForSave(X_TRANSPORT_DESCR);
				$transport['server_address'] = prepareForSave(X_TRANSPORT_SERVER);
				$transport['server_port'] = prepareForSave(X_TRANSPORT_PORT);
				$transport['server_login'] = prepareForSave(X_TRANSPORT_LOGIN);
				$transport['server_passwd'] = prepareForSave(X_TRANSPORT_PASSWORD);
				$transport['class_name'] = prepareForSave(X_TRANSPORT_CLASS);
				$transport->save();
				break;
			case "change" :
				$transport = $connection->getTable('CsTransport')->find(X_TRANSPORT_ID);
				$transport['name'] = prepareForSave(X_TRANSPORT_NAME);
				$transport['description'] = prepareForSave(X_TRANSPORT_DESCR);
				$transport['server_address'] = prepareForSave(X_TRANSPORT_SERVER);
				$transport['server_port'] = prepareForSave(X_TRANSPORT_PORT);
				$transport['server_login'] = prepareForSave(X_TRANSPORT_LOGIN);
				$transport['server_passwd'] = prepareForSave(X_TRANSPORT_PASSWORD);
				$transport['class_name'] = prepareForSave(X_TRANSPORT_CLASS);
				$transport->save();
				break;
			case "delete" :
				$transport = $connection->getTable('CsTransport')->find(TRANSPORT_ID);
				$transport->delete();
				break;
			default:
				break;
		}
	}

	$transports = $connection->execute('select * from cs_transport order by id');
	foreach ($transports as $transport) {
		print "<tr>";
		print "<td align=\"center\">".$transport['id']."</td>";
		print "<td title=\"".$transport['description']."\">".$transport['name']."</td>";
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Изменить транспорт\" href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&transport_id=".$transport['id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_delete']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Удалить транспорт\" href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&transport_id=".$transport['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		}
		print "</tr>\n";
	}
	?>
	</td>
	</tr>
	<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="3" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить транспорт"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
</table>
	<?php endif; ?>
