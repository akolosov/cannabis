<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
	<tr>
		<th colspan="5" align="center">&nbsp;</th>
		<th align="center"><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add&project_id=<?= PROJECT_ID; ?>"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<tr>
		<th width="7%">Код</th>
		<th width="40%">Наименование</th>
		<th width="12%">Автор</th>
		<th width="15%">Создан</th>
		<th colspan="2" width="21%">Действия</th>
	</tr>
	<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				$process = $connection->getTable('CsProjectProcess')->create();
				$process['project_id'] = X_PROJECT_ID;
				$process['process_id'] = X_PROJECT_PROCESS_ID;
				$process->save();
				break;
			case "change" :
				$process = $connection->getTable('CsProjectProcess')->find(X_PROCESS_ID);
				$process['process_id'] = (defined('X_PROJECT_PROCESS_ID')?X_PROJECT_PROCESS_ID:(X_OLD_PROJECT_PROCESS_ID == ""?NULL:X_OLD_PROJECT_PROCESS_ID));
				$process->save();
				break;
			case "delete" :
				$process = $connection->getTable('CsProjectProcess')->find(PROCESS_ID);
				$process->delete();
				break;
			case "export":
				if (defined('PROCESS_ID')) {
					$process = new Process($engine, PROCESS_ID, true);
					file_put_contents(CACHE_PATH.'/'.$process->getProperty('name').'.xml', '<?xml version="1.0" encoding="'.DEFAULT_CHARSET.'"?>'.$process->export());
					print "<script>\n<!--\n";
					print "  openWindow('/".CACHE_PATH.'/'.$process->getProperty('name').".xml', '', this);\n";
					print "//-->\n</script>\n";
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
		print "<td align=\"center\" width=\"7%\">".$process['id']."</td>";
		print "<td width=\"40%\" title=\"".$process['description']."\"";
		if ($currentHaveChilds) {
			print " onClick=\"hideIt('table_".$process['id']."'); changeImage('table_".$process['id']."', 'image_".$process['id']."');\"><b>".$process['name']."</b>&nbsp;&nbsp;<img class=\"expand\" id=\"image_".$process['id']."\" src=\"images/tree_expand.png\" />";
		} else {
			print ">".$process['name'];
		}
		print "</td>";
		print "<td width=\"12%\" align=\"center\" class=\"small\">".$process['authorname']."</td>";
		print "<td width=\"12%\" align=\"center\" class=\"small\" ".($process['activated_at']?"title=\"Активирован: ".$process['activated_at']."\"":"&nbsp;").">".$process['created_at']."</td>";
		print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&project_id=".PROJECT_ID."&process_id=".$process['project_process_id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
		print "<td width=\"3%\" align=\"center\"><a href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&project_id=".PROJECT_ID."&process_id=".$process['project_process_id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		print "</tr>\n";
	}

	function printRow($id = 0, $level = 0) {
		global $connection;

		$processs = $connection->execute('select * from project_processes_tree where parent_id = '.$id)->fetchAll();

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
				<th colspan="2" width="21%">Действия</th>
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

	$processes = $connection->execute('select * from project_processes_tree where project_id = '.PROJECT_ID.' order by id, name');
	$project = $connection->execute('select * from cs_project where id = '.PROJECT_ID.' limit 1')->fetch();
	print "<caption class=\"caption\"><b>Наименование проекта: </b>".$project['name']."</caption>\n";
	foreach ($processes as $process) {
		printCurrentRow($process);
		printRow($process['id']);
	}
	?>
	</td>
	</tr>
	<tr>
		<th colspan="5" align="center">&nbsp;</th>
		<th align="center"><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add&project_id=<?= PROJECT_ID; ?>"><img
			src="images/create_icon.png" /></a></th>
	</tr>
</table>
