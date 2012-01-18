<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
	<?php if ($user_permissions[getParentModule()][getChildModule()]['can_admin']): ?>
		<th colspan="14" align="center">&nbsp;</th>
		<th align="center"><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/import"><img
			src="images/import.png" /></a></th>
	<?php else: ?>
		<th colspan="15" align="center">&nbsp;</th>
	<?php endif; ?>
		<th align="center"><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
	<tr>
		<th width="7%">Код</th>
		<th width="40%">Наименование</th>
		<th width="12%">Автор</th>
		<th width="16%">Создан</th>
		<th width="6%">Активен</th>
		<th width="6%">Общий</th>
		<th colspan="10" width="21%">Действия</th>
	</tr>
	<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				$process = $connection->getTable('CsProcess')->create();
				$process['parent_id'] = (defined('X_PROCESS_PARENT_ID') && X_PROCESS_PARENT_ID != ""?X_PROCESS_PARENT_ID:NULL);
				$process['name'] = prepareForSave(X_PROCESS_NAME);
				$process['description'] = prepareForSave(X_PROCESS_DESCR);
				$process['version'] = 1.0;
				$process['created_at'] = strftime("%Y-%m-%d %H:%M:%S", time());
				$process['is_active'] = false;
				$process['is_standalone'] = (X_PROCESS_STANDALONE == 'on'?true:false);
				$process['is_public'] = (X_PROCESS_PUBLIC == 'on'?true:false);
				$process['is_hidden'] = (X_PROCESS_HIDDEN == 'on'?true:false);
				$process['is_system'] = (X_PROCESS_SYSTEM == 'on'?true:false);
				if (($process['is_system']) or ($process['is_hidden'])) {
					$process['is_public'] = false;
				}
				$process['author_id'] = USER_CODE;
				$process->save();
				break;
			case "change" :
				$process = $connection->getTable('CsProcess')->find(X_PROCESS_ID);
				$process['parent_id'] = (defined('X_PROCESS_PARENT_ID')?(X_PROCESS_PARENT_ID != ""?X_PROCESS_PARENT_ID:NULL):(X_PARENT_ID == ""?NULL:X_PARENT_ID));
				$process['name'] = prepareForSave(X_PROCESS_NAME);
				$process['description'] = prepareForSave(X_PROCESS_DESCR);
				$process['is_active'] = (X_PROCESS_ACTIVE == 'on'?true:false);
				$process['is_standalone'] = (X_PROCESS_STANDALONE == 'on'?true:false);
				$process['is_public'] = (X_PROCESS_PUBLIC == 'on'?true:false);
				$process['is_hidden'] = (X_PROCESS_HIDDEN == 'on'?true:false);
				$process['is_system'] = (X_PROCESS_SYSTEM == 'on'?true:false);
				if (($process['is_system']) or ($process['is_hidden'])) {
					$process['is_public'] = false;
				}
				if ($process['is_active']) {
					$process['activated_at'] = strftime("%Y-%m-%d %H:%M:%S", time());
				} else {
					$process['activated_at'] = NULL;
				}
				$process->save();
				break;
			case "delete" :
				$process = $connection->getTable('CsProcess')->find(PROCESS_ID);
				$process['is_active'] = (!$process['is_active']);
				$process->save();
				break;
			case "export":
				if (defined('PROCESS_ID')) {
					$process = new Process($engine, PROCESS_ID, true);
					file_put_contents(CACHE_PATH.'/'.str_replace(' ', '_', $process->getProperty('name')).'.xml', '<?xml version="1.0" encoding="'.DEFAULT_CHARSET.'"?>'.$process->export());
					print "<script>\n<!--\n";
					print "  openWindow('/".CACHE_PATH.'/'.str_replace(' ', '_', $process->getProperty('name')).".xml', '', this);\n";
					print "//-->\n</script>\n";
				}
				break;
			case "import":
				if (is_array($_FILES['x_filename']) and ($_FILES['x_filename']['type'] == 'text/xml') and ($_FILES['x_filename']['size'] <= MAX_FILE_SIZE) and (is_uploaded_file($_FILES['x_filename']['tmp_name']))) {
					Process::import($_FILES['x_filename']['tmp_name'], $engine->getConnection());
				}
				break;
			default:
				break;
		}
	}


	function printCurrentRow($process) {
		global $user_permissions;

		$currentHaveChilds = (haveChilds('cs_process', $process['id']));

		print "<tr>";
		print "<td width=\"7%\" align=\"center\">".$process['id']."</td>";
		print "<td width=\"40%\" title=\"".$process['description']."\"";
		if ($currentHaveChilds) {
			print " onClick=\"hideIt('table_".$process['id']."'); changeImage('table_".$process['id']."', 'image_".$process['id']."');\"><b>".$process['name']."</b>&nbsp;&nbsp;<img class=\"expand\" id=\"image_".$process['id']."\" src=\"images/tree_expand.png\" />";
		} else {
			print ">".$process['name'];
		}
		print "</td>";
		print "<td width=\"12%\" align=\"center\" class=\"small\">".$process['authorname']."</td>";
		print "<td width=\"16%\" align=\"center\" class=\"small\" ".($process['activated_at']?"title=\"Активирован: ".$process['activated_at']."\"":"&nbsp;").">".$process['created_at']."</td>";
		print "<td width=\"6%\" align=\"center\">".(($process['is_active'] == 1)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "<td width=\"6%\" align=\"center\">".(($process['is_public'] == 1)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."transports/list&process_id=".$process['id']."\"><img src=\"images/transports.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."roles/list&process_id=".$process['id']."\"><img src=\"images/roles.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."properties/list&process_id=".$process['id']."\"><img src=\"images/list.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."infoprops/list&process_id=".$process['id']."\"><img src=\"images/infoprops.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."actions/list&process_id=".$process['id']."\"><img src=\"images/action.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."transitions/list&process_id=".$process['id']."\"><img src=\"images/transition.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."graph&process_id=".$process['id']."\"><img src=\"images/memory.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_admin']) {
			print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=export&process_id=".$process['id']."\"><img src=\"images/export.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&process_id=".$process['id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_delete']) {
			print "<td width=\"3%\" align=\"center\"><a href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&process_id=".$process['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		}
		print "</tr>\n";
	}

	function printRow($id = 0, $level = 0) {
		global $connection;

		$processs = $connection->execute('select * from processes_tree where parent_id = '.$id)->fetchAll();

		if ($level <> $processs[0]['level']) {
			if (!is_null($processs[0]['id'])) {
				print "<tr class=\"prehidden\"><td colspan=\"".(14+($processs[0]['level']-1))."\" class=\"prehidden\"><table align=\"center\" id=\"table_".$processs[0]['parent_id']."\" class=\"hidden\" cellspacing=\"1\"><caption class=\"header\" onClick=\"hideIt('table_".$processs[0]['parent_id']."'); changeImage('table_".$processs[0]['parent_id']."', 'image_".$processs[0]['parent_id']."');\">".$processs[0]['parentname']."</caption>";
				print <<<EOT
     <tr>
      <th width="7%">Код</th>
				<th width="21%">Наименование</th>
				<th width="24%">Описание</th>
				<th width="10%">Автор</th>
				<th width="15%">Создан</th>
				<th width="6%">Активен</th>
				<th width="6%">Общий</th>
				<th colspan="9" width="21%">Действия</th>
				</tr>
EOT;
			}
		}

		foreach ($processs as $process) {
			printCurrentRow($process);
			$a_level = printRow($process['id'], $process['level']);
		}

		if ($a_level <> $processs[0]['level']) {
			print "</table></td></tr>";
		}

		return $process['level'];
	}


	$processes = $connection->execute('select * from processes_tree where level = 0 order by id, name');
	foreach ($processes as $process) {
		printCurrentRow($process);
		printRow($process['id']);
	}
	?>
	</td>
	</tr>
	<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
	<?php if ($user_permissions[getParentModule()][getChildModule()]['can_admin']): ?>
		<th colspan="14" align="center">&nbsp;</th>
		<th align="center"><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/import"><img
			src="images/import.png" /></a></th>
	<?php else: ?>
		<th colspan="15" align="center">&nbsp;</th>
	<?php endif; ?>
		<th align="center"><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
</table>
	<?php endif; ?>
