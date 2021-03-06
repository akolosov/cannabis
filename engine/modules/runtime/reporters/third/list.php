<?php
	function printTitle() {
		global $report_params;

		print "<h5>Пользователь: ".USER_NAME." (".USER_DESCR."), Дата и время генерации: ".strftime("%d.%m.%Y в %H:%M:%S", time())."</h5>\n";
		print "<h2 onClick=\"window.print();\">Учет сроков выполнения Служебных записок</h2>\n";
		print "<h4 onClick=\"window.print();\">за период с ".$report_params['by_period_from']." по ".$report_params['by_period_to']."</h4>\n";
		print "<br /><h5 onClick=\"window.print();\">звёздочкой (*) отмечены документы выполненные, но еще не подтверждённые</h5>\n";
		print "<table class=\"report\" width=\"100%\">\n";
	}

	function printSplitter() {
		global $report_params, $total_on_page, $first_page;
		
		print "<tr class=\"groupheader\">\n";
		print "<th class=\"groupsplitter\" colspan=\"".($report_params['by_group'] == "project"?"7":"5")."\"></th>\n";
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
		print "<th class=\"report\">Документ №</th>\n";
		if ($report_params['by_group'] <> 'initiator') {
			print "<th class=\"report\">Разработчик</th>\n";
			print "<th class=\"report\">Инициатор</th>\n";
		}
		print "<th class=\"report\">Желаемый срок исполнения</th>\n";
		print "<th class=\"report\">Назначенный срок исполнения</th>\n";
		print "<th class=\"report\">Дата и время выполнения</th>\n";
		print "<th class=\"report\">Разница между текущим и назначенным временем</th>\n";
		print "</tr>\n";
	}

	function printGroupHeader() {
		global $project_name, $initiator_name, $status_name, $report_params, $first_page, $total_on_page;

		if ($first_page == false) {
			printHeader();
			$first_page = true;
		}
		$total_on_page = 0;

		switch ($report_params['by_group']) {
			case 'project':
				print "<tr><td class=\"report group\" align=\"right\">Предприятие:</td>\n";
				print "<td class=\"report group big_italic\" colspan=\"7\">".$project_name."</td></tr>\n";
				break;

			case 'initiator':
				print "<tr><td class=\"report group\" align=\"right\">Разработчик:</td>\n";
				print "<td class=\"report group big_italic\" colspan=\"5\">".$initiator_name."</td></tr>\n";
				break;

			default:
				break;
		}
	}

	function printBody($process, $group_by) {
		global $engine, $report_params, $group_result, $total_result, $total_on_page;

		$properties = $engine->getConnection()->execute('select * from process_instance_properties_list where instance_id = '.$process['id'])->fetchAll();

		if (isNotNULL(getPropertyValueByName($properties, 'Срок исполнения'))) {
			if ((X_UNCOMPLETED_ONLY == 'on') and (trim(getPropertyValueByName($properties, 'Дата и время выполнения Исполнителем')) == '')) {
				$print_this_line = true;
			} elseif (X_UNCOMPLETED_ONLY <> 'on') {
				$print_this_line = true;
			} else {
				$print_this_line = false;
			}
			if ($print_this_line) {
				print "<tr>\n";
				print "<td class=\"report\">";
				print "<a href=\"#\" onClick=\"openWindow('/?module=runtime/".((($process['status_id'] == Constants::PROCESS_STATUS_COMPLETED) or ($process['status_id'] == Constants::PROCESS_STATUS_CHILD_COMPLETED))?"archives":"outboxes")."/processes/list&project_instance_id=".$process['project_instance_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."&project_id=".$process['project_id']."');\">";
				print $process['name']." №".$process['id'].", создан ".strftime("%d.%m.%Y в %H:%M", strtotime($process['started_at']));
				print "</a></td>\n";
				if ($report_params['by_group'] <> 'initiator') {
					print "<td ".((X_SHOW_DOC == 'on')?" rowspan=\"7\"":"")." class=\"report center\">";
					print $process['initiatorname'];
					print "</td>\n";
					print "<td ".((X_SHOW_DOC == 'on')?" rowspan=\"7\"":"")." class=\"report center\">";
					if ($process['version'] == 1) {
						print $process['initiatorname'];
					} elseif ($process['version'] >= 2) {
						print getPropertyValueByName($properties, 'Инициатор');
					}
					print "</td>\n";
				}
				print "<td ".((X_SHOW_DOC == 'on')?" rowspan=\"7\"":"")." class=\"report center\">";
				if ($process['version'] == 1) {
					print getPropertyValueByName($properties, 'Предполагаемый Срок исполнения')." ".END_WORK_TIME;
				} elseif ($process['version'] >= 2) {
					print getPropertyValueByName($properties, 'Желаемый срок исполнения')." ".END_WORK_TIME;
				}
				print "<td ".((X_SHOW_DOC == 'on')?" rowspan=\"7\"":"")." class=\"report center\">".getPropertyValueByName($properties, 'Срок исполнения').(getPropertyValueByName($properties, 'Срок исполнения')?" ".END_WORK_TIME:"")."</td>\n";
	
				$time = (isNULL(getPropertyValueByName($properties, 'Дата и время выполнения Исполнителем'))?time():strtotime(getPropertyValueByName($properties, 'Дата и время выполнения Исполнителем')));
				$timestr = (isNULL(getPropertyValueByName($properties, 'Дата и время выполнения Исполнителем'))?"-":strftime('%d.%m.%Y %H:%M', strtotime(getPropertyValueByName($properties, 'Дата и время выполнения Исполнителем'))));
				print "<td ".((X_SHOW_DOC == 'on')?" rowspan=\"7\"":"")." class=\"report center\">".$timestr."</td>\n";
	
				// Разница между текущим и планом
				print "<td ".((X_SHOW_DOC == 'on')?" rowspan=\"7\"":"")." class=\"report center";
				if ($time > strtotime(getPropertyValueByName($properties, 'Срок исполнения')." ".END_WORK_TIME)) {
					print " red\">- ".formatedInterval(dateDiff($time, strtotime(getPropertyValueByName($properties, 'Срок исполнения')." ".END_WORK_TIME))).(isNotNULL(getPropertyValueByName($properties, 'Дата и время выполнения Исполнителем'))?" *":"");
					$group_result[$group_by][0] += dateDiff($time, strtotime(getPropertyValueByName($properties, 'Срок исполнения')." ".END_WORK_TIME)); 
					$total_result[0] += dateDiff($time, strtotime(getPropertyValueByName($properties, 'Срок исполнения')." ".END_WORK_TIME)); 
				} else {
					print " blue\">+ ".formatedInterval(dateDiff(strtotime(getPropertyValueByName($properties, 'Срок исполнения')." ".END_WORK_TIME), $time)).(isNotNULL(getPropertyValueByName($properties, 'Дата и время выполнения Исполнителем'))?" *":"");
					$group_result[$group_by][1] += dateDiff(strtotime(getPropertyValueByName($properties, 'Срок исполнения')." ".END_WORK_TIME), $time); 
					$total_result[1] += dateDiff(strtotime(getPropertyValueByName($properties, 'Срок исполнения')." ".END_WORK_TIME), $time); 
				}
				print "</td>\n";		
				print "</tr>\n";
				$total_on_page++;
				if (X_SHOW_DOC == 'on') {
					print "<tr>\n";
					print "<td class=\"report\"><strong>Разработчик: </strong>";
					if ($process['version'] == 1) {
						print $process['initiatorname'];
					} elseif ($process['version'] >= 2) {
						print getPropertyValueByName($properties, 'Разработчик');
					}
					print "</td>\n";
					print "</tr>\n";
					print "<td class=\"report\"><strong>Инициатор: </strong>";
					if ($process['version'] == 1) {
						print $process['initiatorname'];
					} elseif ($process['version'] >= 2) {
						print getPropertyValueByName($properties, 'Инициатор');
					}
					print "<tr>\n<td class=\"report\"><strong>Получатель: </strong>".getPropertyValueByName($properties, 'Получатель')."</td>\n</tr>\n";
					print "<tr>\n<td class=\"report\"><strong>Исполнитель: </strong>".getPropertyValueByName($properties, 'Исполнитель')."</td>\n</tr>\n";
					print "</td>\n";
					print "</tr>\n";
					print "<tr>\n<td class=\"report\"><strong>Статус: </strong>".$process['statusname']."</td>\n</tr>\n";
					print "<tr>\n";
					print "<td class=\"report\"><strong>Текст: </strong>";
					print str_replace("\n", "<br />", getPropertyValueByName($properties, 'Текст'))."<br /><br />";
					print "</td>\n";
					print "</tr>\n";
				}
			}
		}
	}
	
	function printFooter() {
		global $report_params, $total_result;

		print "<tr>\n";
		print "<td rowspan=\"2\" colspan=\"".(($report_params['by_group']) == 'project'?"6":"4")."\" class=\"group report\" align=\"right\">ИТОГО ПО ВСЕМ ГРУППАМ:</td>\n";
		print "<td class=\"report center red\">".($total_result[0]?"- ".formatedInterval($total_result[0]):"&nbsp;")."</td>\n";
		print "</tr>\n";
		print "<tr>\n";
		print "<td class=\"report center blue\">".($total_result[1]?"+ ".formatedInterval($total_result[1]):"&nbsp;")."</td>\n";
		print "</tr>\n";
		print "</table>\n";
	    print "<h5 onClick=\"window.print();\">звёздочкой (*) отмечены документы выполненные, но еще не подтверждённые</h5><br />\n";
	}
	
	function printGroupFooter($group_by) {
		global $report_params, $group_result, $first_page;

		print "<tr class=\"groupfooter\">\n";
		print "<td rowspan=\"2\" colspan=\"".(($report_params['by_group']) == 'project'?"6":"4")."\" class=\"group report\" align=\"right\">ИТОГО ПО ГРУППЕ:</td>\n";
		print "<td class=\"report center red\">".($group_result[$group_by][0]?"- ".formatedInterval($group_result[$group_by][0]):"&nbsp;")."</td>\n";
		print "</tr>\n";
		print "<tr>\n";
		print "<td class=\"report center blue\">".($group_result[$group_by][1]?"+ ".formatedInterval($group_result[$group_by][1]):"&nbsp;")."</td>\n";
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
			$first_page = true;
			$total_on_page = 0;

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
								if (((!$first_page) and ($total_on_page == 28)) or (($first_page) and ($total_on_page == 23))) {
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
								if (((!$first_page) and ($total_on_page == 28)) or (($first_page) and ($total_on_page == 23))) {
									printSplitter();
								}
							}
							printGroupFooter($initiator_name);
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
								if (((!$first_page) and ($total_on_page == 28)) or (($first_page) and ($total_on_page == 23))) {
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
