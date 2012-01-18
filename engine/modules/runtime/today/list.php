<?php if (($user_permissions[getParentModule()][getChildModule()]['can_read']) or ($user_permissions[getParentModule()][getParentChildModule()]['can_read'])): ?>
<?php
	require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php");
	require_once(MODULES_PATH.DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."request_action.php");
	require_once(MODULES_PATH.DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."functions.php");
	
	if ((defined('ACTION')) and (defined('PROJECT_INSTANCE_ID')) and (defined('PROCESS_INSTANCE_ID'))) {
		switch (ACTION) {
			case 'pause':
			case 'execute':
				require_once(MODULES_PATH.DIRECTORY_SEPARATOR.getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."actions/list.php");
				break;

			default:
				break;
		}
	}

	function printTodayItem(array $processes = array()) {
		global $connection, $user_permissions;

		if (isNotNULL($processes)) {
			print "<ul>\n";
			print "<li class=\"treeitem\">";
			print "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"1\" border=\"0\" class=\"treeitem\">";
			print "<tr>";
			print "<th>Документ №</th>";
			print "<th>Наименование текущего действия</th>";
			print "<th>Дата установки статуса</th>";
			print "<th>Действия</th>";
			print "</tr>";
			
			foreach ($processes as $process) {
				$properties = $connection->execute('select name, value from process_instance_properties_list where instance_id = '.$process['process_instance_id'].' and property_id in (select property_id from cs_process_info_property where process_id = '.$process['process_id'].') order by property_id')->fetchAll();
				$chrono = $connection->execute('select * from cs_chrono where instance_id = '.$process['process_instance_id'])->fetchAll();
				print "<tr>";
				print "<td title=\"<p style=' text-align : left !important; '>";
				print "<b>Автор:</b>".$process['initiatorname']."<br />";
				print "<b>Предприятие:</b>".$process['projectname']."<br />";
				foreach ($properties as $property) {
					$property['value'] = stripMacros($property['value']); 
					print "<b>".$property['name'].": </b>".htmlentities((mb_strlen($property['value']) > 50?mb_substr($property['value'], 0, 50)."...":$property['value']), ENT_COMPAT, DEFAULT_CHARSET)."<br />";
				}
				if (isNotNULL($process['parent_id'])) {
					print "<b>Родительский документ: </b>".$process['parentname']." №".$process['parent_id']."<br />";
				}
				print "</p>\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."processes/list&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['process_instance_id']."&process_id=".$process['process_id']."';\">";
				print $process['processname']." №".$process['process_instance_id'];
				print "</td>";
				print "<td title=\"".stripslashes(htmlentities($process['actiondescr'], ENT_COMPAT, DEFAULT_CHARSET))."\">";
				print $process['actionname'];
				print "</td>";
				print "<td align=\"center\">";
				print strftime('%d.%m.%Y в %H:%M', strtotime($process['started_at']));
				print "</td>";
				print "<td align=\"right\">";
				if ((isNotNULL($chrono)) and (($user_permissions[getParentModule()][getChildModule()]['can_read']) or ($user_permissions[getParentModule()][getParentChildModule()]['can_read']))) {
					print "<span class=\"small action\" title=\"История движения документа '".$process['processname']." №".$process['process_instance_id']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."history/list&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['process_instance_id']."&process_id=".$process['process_id']."');\"><img src=\"images/date.png\" /></span>";
					print "&nbsp";
				}
				if (($user_permissions[getParentModule()][getChildModule()]['can_read']) or ($user_permissions[getParentModule()][getParentChildModule()]['can_read'])) {
					print "<span class=\"small action\" title=\"Хронология документа '".$process['processname']." №".$process['process_instance_id']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."chronos/list&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['process_instance_id']."&process_id=".$process['process_id']."');\"><img src=\"images/time.png\" /></span>";
					print "&nbsp";
				}
				if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
					print "<span class=\"small action\" title=\"Печать документа '".$process['processname']." №".$process['process_instance_id']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."processes/list&media=print&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['process_instance_id']."&process_id=".$process['process_id']."');\"><img src=\"images/print.png\" /></span>";
					print "&nbsp";
				}
				if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
					print "<span class=\"small action\" title=\"Просмотр документа '".$process['processname']." №".$process['process_instance_id']."'\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."processes/list&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['process_instance_id']."&process_id=".$process['process_id']."');\"><img src=\"images/template.png\" /></span>";
					print "&nbsp";
				}
				if ((($user_permissions[getParentModule()][getChildModule()]['can_read']) or ($user_permissions[getParentModule()][getParentChildModule()]['can_read'])) and ($process['account_id'] == USER_CODE)) {
					if ($process['status_id'] == Constants::ACTION_STATUS_WAITING) {
						print "<span class=\"small action\" title=\"Продолжить выполнение документа '".$process['processname']." №".$process['process_instance_id']."'\" onClick=\"confirmItMessage('Вы точно хотите продолжить выполнение документа?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."actions/list&action=execute&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['process_instance_id']."&process_id=".$process['process_id']."');\"><img src=\"images/play.png\" /></span>";
					} elseif ($process['status_id'] == Constants::ACTION_STATUS_IN_PROGRESS) {
						print "<span class=\"small action\" title=\"Приостановить выполнение документа '".$process['processname']." №".$process['process_instance_id']."'\" onClick=\"confirmItMessage('Вы точно хотите приостановить выполнение документа?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=pause&project_instance_id=".$process['project_instance_id']."&project_id=".$process['project_id']."&process_instance_id=".$process['process_instance_id']."&process_id=".$process['process_id']."');\"><img src=\"images/pause.png\" /></span>";
					}
				}
				print "</td>";
				print "</tr>";
			}
			print "</table>";
			print "</li>\n";
			print "</ul>\n";
		}
		
	}

	if (ACTION <> "execute") {
		print "<div class=\"caption\"><img src=\"images/constants.png\" style=\" float: right; z-index: 1000; \" onClick=\"hideIt('request_params')\" title=\"Выборка документов по параметрам\" />Текущие документы</div>\n";
		require_once(MODULES_PATH.DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."request_form.php");
		
		$query = 'select * from account_today_list'.
			(isNotNULL($where)
				?' where ('.(implode(' and ', $where)).')'
				:'').
			(isNotNULL($inprop)
				?" and ".$inprop
				:"").
			' and ended_at is null';
	
		$processes = $connection->execute($query)->fetchAll();
	
	    $statuseslist = getStatusesList($processes);
		print "<ul class=\"tree\" id=\"today_tree\" style=\" display : none; \">\n";
	    foreach ($statuseslist as $status_id => $status_name) {
			print "<li class=\"roottreeitem\"><a href=\"#\"></a>Статус: ".$status_name;
	    	if (isNotNULL($processes)) {
	   			printTodayItem(getByStatus($processes, $status_id));
	    	}
	    }
	    print "</ul>\n";
	}
?>
	<script>
	<!--
		var today_tree_options = {
				'theme' : { 'name' : 'SimpleTree' },
				closeSiblings : false,
				maxOpenDepth : 2,
				flagClosedClass : 'close',
				toggleMenuOnClick : true,
				incrementalConvert : false,
				openTimeout : 0,
				closeTimeout: 0
		}
		var today_tree = new CompleteMenuSolution;
		today_tree.initMenu('today_tree', today_tree_options);
	//-->
	</script>
<?php endif; ?>
