<?php
	function printTitle() {
		global $report_params;

		print "<h5>Пользователь: ".USER_NAME." (".USER_DESCR."), Дата и время генерации: ".strftime("%d.%m.%Y в %H:%M:%S", time())."</h5>\n";
		print "<h2 onClick=\"window.print();\">Учет выполнения процедур документооборота</h2>\n";
		print "<h4 onClick=\"window.print();\">за период с ".$report_params['by_period_from']." по ".$report_params['by_period_to']."</h4>\n";
		print "<br /><table class=\"report\" width=\"100%\">\n";
	}

	function printSplitter() {
		global $report_params, $total_on_page, $first_page;
		
		print "<tr class=\"groupheader\">\n";
		print "<th class=\"groupsplitter\" colspan=\"".($report_params['by_group'] == "project"?"8":"7")."\"></th>\n";
		print "</tr>\n";

		$total_on_page = 0;
		$first_page = false;
	}

	function printHeader() {
		global $report_params, $first_page;

		if ($first_page == false) {
			printSplitter();
		}

		print "<tr>\n";
		print "<th rowspan=\"2\" colspan=\"2\" class=\"report\" width=\"20%\">Документ №</th>\n";
		if ($report_params['by_group'] <> 'initiator') {
			print "<th rowspan=\"2\" class=\"report\"".(($report_params['by_group'] == 'status')?" colspan=\"3\"":"").">Инициатор</th>\n";
		}
		if ($report_params['by_group'] <> 'status') {
			print "<th rowspan=\"2\" class=\"report\"".(($report_params['by_group'] == 'initiator')?" colspan=\"3\"":" colspan=\"2\"").">Статус</th>\n";
		}
		print "<th rowspan=\"2\" class=\"report\">Д/В создания</th>\n";
		print "<th class=\"report\">Д/В завершения</th>\n";
		print "<th rowspan=\"2\" class=\"report\">Фактическое Время выполнения</th>\n";
		print "</tr>\n";
		print "<tr>\n";
		print "<th class=\"report\">Д/В текущего действия</th>\n";
		print "</tr>\n";
	}

	function printGroupHeader() {
		global $project_name, $initiator_name, $status_name, $report_params, $process_name, $first_page, $total_on_page;

		if ($first_page == false) {
			printHeader();
			$first_page = true;
		}
		$total_on_page = 0;
		
		switch ($report_params['by_group']) {
			case 'project':
				print "<tr><td class=\"report group\" colspan=\"2\" align=\"right\">Предприятие:</td>\n";
				print "<td class=\"report group big_italic\" colspan=\"6\">".$project_name."</td></tr>\n";
				break;

			case 'initiator':
				print "<tr><td class=\"report group\" colspan=\"2\" align=\"right\">Разработчик:</td>\n";
				print "<td class=\"report group big_italic\" colspan=\"5\">".$initiator_name."</td></tr>\n";
				break;

			case 'process':
				print "<tr><td class=\"report group\" colspan=\"2\" align=\"right\">Документ:</td>\n";
				print "<td class=\"report group big_italic\" colspan=\"5\">".$process_name."</td></tr>\n";
				break;

			case 'status':
				print "<tr><td class=\"report group\" colspan=\"2\" align=\"right\">Статус:</td>\n";
				print "<td class=\"report group big_italic\" colspan=\"5\">".$status_name."</td></tr>\n";
				break;

			default:
				break;
		}
	}

	function printBody($process, $group_by) {
		global $engine, $report_params, $group_result, $total_result, $total_on_page;

		$action = $engine->getConnection()->execute('select max(ended_at) as ended_at from cs_process_current_action where instance_id = '.$process['id'].' and status_id = '.Constants::ACTION_STATUS_COMPLETED.' group by instance_id')->fetch();

		$group_result[$group_by][0] += 1; 
		$total_result[0] += 1;

		print "<tr>\n";
		print "<td class=\"report\" colspan=\"2\">";
		print "<a href=\"#\" onClick=\"openWindow('/?module=runtime/".((($process['status_id'] == Constants::PROCESS_STATUS_COMPLETED) or ($process['status_id'] == Constants::PROCESS_STATUS_CHILD_COMPLETED))?"archives":"outboxes")."/processes/list&project_instance_id=".$process['project_instance_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."&project_id=".$process['project_id']."');\">";
		print $process['name']." №".$process['id'].", создан ".strftime("%d.%m.%Y в %H:%M", strtotime($process['started_at']));
		print "</a></td>\n";
		if ($report_params['by_group'] <> 'initiator') {
			print "<td class=\"report center\"".(($report_params['by_group'] == 'status')?" colspan=\"3\"":"").">";
			print $process['initiatorname'];
			print "</td>\n";
		}
		if ($report_params['by_group'] <> 'status') {
			print "<td class=\"report center\"".(($report_params['by_group'] == 'initiator')?" colspan=\"3\"":" colspan=\"2\"").">".$process['statusname']."</td>\n";
		}
		print "<td class=\"report center\">";
		print strftime('%d.%m.%Y в %H:%M', strtotime($process['started_at']));
		print "</td>\n";
		print "<td class=\"report center\">";
		if (($process['status_id'] <> Constants::PROCESS_STATUS_COMPLETED) or ($process['status_id'] <> Constants::PROCESS_STATUS_CHILD_COMPLETED)) {
			print strftime('%d.%m.%Y в %H:%M', strtotime($action['ended_at']));
		} else {
			print strftime('%d.%m.%Y в %H:%M', strtotime($process['ended_at']));
		}
		print "</td>\n";
		// Время на принятие решения
		print "<td class=\"report center\">";
		if (($process['status_id'] <> Constants::PROCESS_STATUS_COMPLETED) or ($process['status_id'] <> Constants::PROCESS_STATUS_CHILD_COMPLETED)) {
			print formatedInterval(dateDiff(strtotime($process['started_at']), strtotime($action['ended_at'])));
			$group_result[$group_by][1] += dateDiff(strtotime($process['started_at']), strtotime($action['ended_at'])); 
			$total_result[1] += dateDiff(strtotime($process['started_at']), strtotime($action['ended_at'])); 
		} else {
			print formatedInterval(dateDiff(strtotime($process['started_at']), strtotime($process['ended_at'])));
			$group_result[$group_by][1] += dateDiff(strtotime($process['started_at']), strtotime($action['ended_at'])); 
			$total_result[1] += dateDiff(strtotime($process['started_at']), strtotime($action['ended_at'])); 
		}
		if (($process['status_id'] == Constants::PROCESS_STATUS_COMPLETED) or ($process['status_id'] == Constants::PROCESS_STATUS_CHILD_COMPLETED)) {
			$group_result[$group_by][2] += 1; 
			$total_result[2] += 1;
		} else {
			$group_result[$group_by][3] += 1; 
			$total_result[3] += 1;
		}
		
		print "</td>\n";		
		print "</tr>\n";
		$total_on_page++;
	}

	function printFooter() {
		global $total_result;

		print "<tr>\n";
		print "<td rowspan=\"2\" class=\"group report\" align=\"right\">ИТОГО ПО ВСЕМ ГРУППАМ:</td>\n";
		print "<td rowspan=\"2\" class=\"report center\">".$total_result[0]."</td>\n";
		print "<td rowspan=\"2\" class=\"group report\" align=\"right\">ИТОГО ПО ВСЕМ ГРУППАМ:</td>\n";
		print "<td class=\"group report\" align=\"right\">Завершенные:</td>\n";
		print "<td class=\"group report\" align=\"right\">Не Завершенные:</td>\n";
		print "<td rowspan=\"2\" colspan=\"2\" class=\"group report\" align=\"right\">ИТОГО ПО ВСЕМ ГРУППАМ:</td>\n";
		print "<td rowspan=\"2\" class=\"report center\">".formatedInterval($total_result[1])."</td>\n";
		print "</tr>\n";
		print "<tr>\n";
		print "<td class=\"group report\" align=\"right\">".$total_result[2]."</td>\n";
		print "<td class=\"group report\" align=\"right\">".$total_result[3]."</td>\n";
		print "</tr>\n";
		print "</table><br />\n";
	}

	function printGroupFooter($group_by) {
		global $group_result, $first_page;

		print "<tr class=\"groupfooter\">\n";
		print "<td rowspan=\"2\" class=\"group report\" align=\"right\">ИТОГО ПО ГРУППЕ:</td>\n";
		print "<td rowspan=\"2\" class=\"report center\">".$group_result[$group_by][0]."</td>\n";
		print "<td rowspan=\"2\" class=\"group report\" align=\"right\">ИТОГО ПО ГРУППЕ:</td>\n";
		print "<td class=\"group report\" align=\"right\">Завершенные:</td>\n";
		print "<td class=\"group report\" align=\"right\">Не Завершенные:</td>\n";
		print "<td rowspan=\"2\" colspan=\"2\" class=\"group report\" align=\"right\">ИТОГО ПО ГРУППЕ:</td>\n";
		print "<td rowspan=\"2\" class=\"report center\">".formatedInterval($group_result[$group_by][1])."</td>\n";
		print "</tr>\n";
		print "<tr>\n";
		print "<td class=\"group report\" align=\"right\">".$group_result[$group_by][2]."</td>\n";
		print "<td class=\"group report\" align=\"right\">".$group_result[$group_by][3]."</td>\n";
		print "</tr>\n";
		$first_page = false;
	}

	require_once(MODULES_PATH.DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."report_action.php");
	require_once(MODULES_PATH.DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."functions.php");

	if (defined('ACTION')) {
		$query = 'select distinct project_processes_instances_list.* from project_processes_instances_list, cs_process_current_action where (project_processes_instances_list.id = cs_process_current_action.instance_id)'.
			(((($user_permissions[getParentModule()][getChildModule()]['can_admin']) or
			($user_permissions[getParentModule()][getChildModule()]['can_review']) or
			($user_permissions[getParentModule()][getChildModule()]['can_observe']) or 
			($user_permissions[getParentModule()][getParentChildModule()]['can_admin']) or
			($user_permissions[getParentModule()][getParentChildModule()]['can_review']) or
			($user_permissions[getParentModule()][getParentChildModule()]['can_observe'])) and
			isNULL($inwhere))
				?''
				:' and '.(isNULL($inwhere)
							?'((cs_process_current_action.performer_id = '.USER_CODE.') or (cs_process_current_action.initiator_id = '.USER_CODE.'))'
							:'(((cs_process_current_action.performer_id = '.USER_CODE.') or (cs_process_current_action.initiator_id = '.USER_CODE.')) or ('.(implode(' and ', $inwhere)).')) and ('.(implode(' and ', $inwhere)).')')
			).(isNotNULL($where)
				?' and ('.(implode(' and ', $where)).')'
				:'').
			((($user_permissions[getParentModule()][getChildModule()]['can_admin']) or
			($user_permissions[getParentModule()][getChildModule()]['can_observe']) or 
			($user_permissions[getParentModule()][getParentChildModule()]['can_admin']) or
			($user_permissions[getParentModule()][getParentChildModule()]['can_observe']))
				?''
				:' and ((project_id in (select project_id from cs_project_role where division_id in ('.implode(', ', $engine->getAccount()->getDivisionsList()).'))) or (project_instance_id in (select project_instance_id from cs_project_process_instance where process_instance_id in (select id from project_processes_instances_list where project_instance_id = project_processes_instances_list.project_instance_id and (initiator_id = '.USER_CODE.' or (id in (select instance_id from cs_process_current_action where (instance_id = project_processes_instances_list.id) and (initiator_id = '.USER_CODE.' or performer_id = '.USER_CODE.'))))))))'
			).' order by project_processes_instances_list.status_id, project_processes_instances_list.started_at desc,
				project_processes_instances_list.ended_at desc, project_processes_instances_list.id desc,
				project_processes_instances_list.name';

		$allprocesses = $connection->execute($query)->fetchAll();
		$group_result = array();
		$total_result = array();

		if (isNotNULL($allprocesses)) {
			$total_result[0] = 0;
			$total_result[1] = 0;
			$total_result[2] = 0;
			$total_result[3] = 0;
			$total_on_page = 0;
			$first_page = true;

			printTitle();
			switch ($report_params['by_group']) {
				case 'project':
					printHeader();
					$projectslist = getProjectsList($allprocesses);
					foreach ($projectslist as $project_id => $project_name) {
						$processes = getByProject($allprocesses, $project_id);
						if (isNotNULL($processes)) {
							$group_result[$project_name][0] = 0;
							$group_result[$project_name][1] = 0;
							$group_result[$project_name][2] = 0;
							$group_result[$project_name][3] = 0;

							printGroupHeader();
							foreach ($processes as $process) {
								printBody($process, $project_name);
								if (((!$first_page) and ($total_on_page == 28)) or (($first_page) and ($total_on_page == 24))) {
									printSplitter();
								}
							}
							printGroupFooter($project_name);
						}
					}
					printFooter();
					break;

				case 'initiator':
					printHeader();
					$initiatorslist = getInitiatorsList($allprocesses);
					foreach ($initiatorslist as $initiator_id => $initiator_name) {
						$processes = getByInitiator($allprocesses, $initiator_id);
						if (isNotNULL($processes)) {
							$group_result[$initiator_name][0] = 0;
							$group_result[$initiator_name][1] = 0;
							$group_result[$initiator_name][2] = 0;
							$group_result[$initiator_name][3] = 0;

							printGroupHeader();
							foreach ($processes as $process) {
								printBody($process, $initiator_name);
								if (((!$first_page) and ($total_on_page == 28)) or (($first_page) and ($total_on_page == 24))) {
									printSplitter();
								}
							}
							printGroupFooter($initiator_name);
						}
					}
					printFooter();
					break;

				case 'process':
					printHeader();
					$processeslist = getProcessesList($allprocesses);
					foreach ($processeslist as $process_id => $process_name) {
						$processes = getByProcess($allprocesses, $process_id);
						if (isNotNULL($processes)) {
							$group_result[$process_name][0] = 0;
							$group_result[$process_name][1] = 0;
							$group_result[$process_name][2] = 0;
							$group_result[$process_name][3] = 0;

							printGroupHeader();
							foreach ($processes as $process) {
								printBody($process, $process_name);
								if (((!$first_page) and ($total_on_page == 28)) or (($first_page) and ($total_on_page == 24))) {
									printSplitter();
								}
							}
							printGroupFooter($process_name);
						}
					}
					printFooter();
					break;
	
				case 'status':
					printHeader();
					$statuseslist = getStatusesList($allprocesses);
					foreach ($statuseslist as $status_id => $status_name) {
						$processes = getByStatus($allprocesses, $status_id);
						if (isNotNULL($processes)) {
							$group_result[$status_name][0] = 0;
							$group_result[$status_name][1] = 0;
							$group_result[$status_name][2] = 0;
							$group_result[$status_name][3] = 0;

							printGroupHeader();
							foreach ($processes as $process) {
								printBody($process, $status_name);
								if (((!$first_page) and ($total_on_page == 28)) or (($first_page) and ($total_on_page == 24))) {
									printSplitter();
								}
							}
							printGroupFooter($status_name);
						}
					}
					printFooter();
					break;

				default:
					break;
			}
		} else {
			print "<h2>За период с ".$report_params['by_period_from']." по ".$report_params['by_period_to']." по выбранным параметрам нет ни одного документа!</h2>";
		}
	} else {
		require_once(MODULES_PATH.DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."report_form.php");
	}
?>
