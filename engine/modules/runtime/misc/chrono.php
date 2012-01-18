<?php if (($user_permissions[getParentModule()][getParentChildModule()]['can_read']) and (defined('PROCESS_INSTANCE_ID'))): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<?php
  function printChronedItem($processes = array(), $parent_id = 0) {
	global $connection, $user_permissions, $total_time;

	$npp = 1;
	foreach ($processes as $process) {
		print "<tr>";
		print "<td class=\"small\" width=\"3%\" align=\"right\">".$npp."</td>";
		print "<td align=\"center\" class=\"small".(($process['confirm'] == Constants::FALSE)?" strike":"")."\" width=\"30%\" title=\"".$process['actiondescr']."\">".$process['actionname']."</td>";
		print "<td align=\"center\" class=\"small".(($process['confirm'] == Constants::FALSE)?" strike":"")."\" width=\"15%\">".strftime("%d.%m.%Y в %H:%M:%S", strtotime($process['started_at']))."</td>";
		print "<td align=\"center\" class=\"small".(($process['confirm'] == Constants::FALSE)?" strike":"")."\" width=\"15%\">".(isNotNULL($process['ended_at'])?strftime("%d.%m.%Y в %H:%M:%S", strtotime($process['ended_at'])):"-")."</td>";
		print "<td align=\"center\" class=\"small".(($process['confirm'] == Constants::FALSE)?" strike":"")."\" width=\"15%\" title=\"".$process['accountdescr']."\">".$process['accountname']."</td>";
		print "<td align=\"center\" class=\"small".(($process['confirm'] == Constants::FALSE)?" strike":"")."\" width=\"10%\" title=\"".$process['statusdescr']."\">".$process['statusname']."</td>";
		print "<td align=\"center\" class=\"small".(($process['confirm'] == Constants::FALSE)?" strike":"")."\" width=\"15%\">".formatedInterval(dateDiff(strtotime($process['started_at']), strtotime($process['ended_at'])))."</td>";
		print "<td align=\"center\">".(($process['confirm'] == Constants::TRUE)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "</tr>";
		$total_time[$process['status_id']] += dateDiff(strtotime($process['started_at']), strtotime($process['ended_at']));
		$npp++;
	 }
  }

  $query = 'select * from account_today_list where process_instance_id = '.PROCESS_INSTANCE_ID.' order by npp, id';    
  $processes = $connection->execute($query)->fetchAll();

  print "<div class=\"caption\">Хронология документа ".$processes[0]['processname']." №".$processes[0]['process_instance_id']."</div>\n";

  $total_time = array();
  
  if ($user_permissions[getParentModule()][getParentChildModule()]['can_read']) {
	print "<br /><table width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\" class=\"treeitem\">";
	print "<tr>";
	print "<th>#</th>";
	print "<th>Наименование действия</th>";
	print "<th>Начало</th>";
	print "<th>Окончание</th>";
	print "<th>Пользователь</th>";
	print "<th>Статус</th>";
	print "<th>Общее время</th>";
	print "<th>OK</th>";
	print "</tr>";

	printChronedItem($processes);
	
	foreach ($total_time as $key => $value) {
		print "<tr>";
		print "<th colspan=\"6\" align=\"right\">Общее время на ".(($key == 1)?"выполнение":"ожидание").":&nbsp;</th>";
		print "<th colspan=\"2\">".formatedInterval($value)."</th>";
		print "</tr>";
		$total += $value;
	}

	print "<tr>";
	print "<th colspan=\"6\" align=\"right\">Общее время всего:&nbsp;</th>";
	print "<th colspan=\"2\">".formatedInterval($total)."</th>";
	print "</tr>";
	
	print "</table><br />";
  }
?>
<? endif; ?>