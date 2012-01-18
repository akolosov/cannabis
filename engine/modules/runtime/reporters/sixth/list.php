<?php
	function printTitle() {
		global $report_params;

		print "<h5>Пользователь: ".USER_NAME." (".USER_DESCR."), Дата и время генерации: ".strftime("%d.%m.%Y в %H:%M:%S", time())."</h5>\n";
		print "<h2 onClick=\"window.print();\">Отчет о состоянии Телефонных заявок</h2>\n";
		print "<h4 onClick=\"window.print();\">за период с ".$report_params['by_period_from']." по ".$report_params['by_period_to']."</h4>\n";
		print "<br /><table class=\"report\" width=\"auto\">\n";
	}

	function printSplitter() {
		global $report_params, $total_on_page, $first_page;
		
		$total_on_page = 0;
		$first_page = false;
	}

	function printHeader() {
		global $report_params, $first_page;

		if ($first_page == false) {
			printSplitter();
		}

		print "<tr>\n";
		print "<th class=\"report\" width=\"auto\" colspan=\"5\">Заявка</th>\n";
		print "<th class=\"report\" width=\"auto\" rowspan=\"2\">Желаемый срок исполнения</th>\n";
		print "<th class=\"report\" width=\"auto\" rowspan=\"2\">Фактический срок исполнения</th>\n";
		print "<th class=\"report\" width=\"auto\" rowspan=\"2\">Исполнитель</th>\n";
		print "<th class=\"report\" width=\"auto\" rowspan=\"2\">Оценка выполнения</th>\n";
		print "</tr>\n";
		print "<tr>\n";
		print "<th class=\"report\" width=\"auto\">Дата и время поступления</th>\n";
		print "<th class=\"report\" width=\"auto\">Заказчик (ФИО, должность)</th>\n";
		print "<th class=\"report\" width=\"auto\">Форма обращения</th>\n";
		print "<th class=\"report\" width=\"auto\">Суть вопроса</th>\n";
		print "<th class=\"report\" width=\"auto\">Статус</th>\n";
		print "</tr>\n";
		print "<tr>\n";
		print "<th class=\"noborders\" width=\"auto\" colspan=\"8\">&nbsp;</th>\n";
		print "</tr>\n";
	}

	function printGroupHeader() {
		global $project_name, $initiator_name, $status_name, $report_params, $first_page, $total_on_page;
	}

	function printBody($process, $group_by) {
		global $engine, $report_params, $group_result, $total_result, $total_on_page;

		$properties = $engine->getConnection()->execute('select * from process_instance_properties_list where instance_id = '.$process['id'])->fetchAll();

		print "<tr>\n";
		print "<td class=\"report center\">".getPropertyValueByName($properties, 'Когда поступил звонок')."</td>\n";
		print "<td class=\"report center\">".getPropertyValueByName($properties, 'Инициатор')."</td>\n";
		print "<td class=\"report center\">";
		print "<a href=\"#\" onClick=\"openWindow('/?module=runtime/".((($process['status_id'] == Constants::PROCESS_STATUS_COMPLETED) or ($process['status_id'] == Constants::PROCESS_STATUS_CHILD_COMPLETED))?"archives":"outboxes")."/processes/list&project_instance_id=".$process['project_instance_id']."&process_instance_id=".$process['id']."&process_id=".$process['process_id']."&project_id=".$process['project_id']."');\">";
		print "Телефонная заявка №".$process['id'];
		print "</a></td>\n";
		print "<td class=\"report\">".getPropertyValueByName($properties, 'Текст')."</td>";
		print "<td class=\"report\">".$process['statusname']."</td>";
		print "<td class=\"report\">".getPropertyValueByName($properties, 'Срок исполнения')."</td>\n";
		print "<td class=\"report\">".getPropertyValueByName($properties, 'Когда Исполнитель выполнил заявку')."</td>\n";
		print "<td class=\"report center\">".getPropertyValueByName($properties, 'Исполнитель')."</td>\n";
		print "<td class=\"report center\">".getPropertyValueByName($properties, 'Оценка работы')."</td>\n";
		print "</tr>\n";
		$total_on_page++;
	}

	function printFooter() {
		global $report_params, $total_result;

		print "</table><br />\n";
	}

	function printGroupFooter($group_by) {
		global $report_params, $group_result, $first_page;
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
		
		if (isNotNULL($allprocesses)) {
			$first_page = true;
			$total_on_page = 0;
			
			printTitle();
			printHeader();
			$projectslist = getProjectsList($allprocesses);
			foreach ($projectslist as $project_id => $project_name) {
				$processes = getByProject($allprocesses, $project_id);
				if (isNotNULL($processes)) {
					foreach ($processes as $process) {
						printBody($process, $project_name);
					}
				}
			}
			printFooter();
		} else {
			print "<h2>За период с ".$report_params['by_period_from']." по ".$report_params['by_period_to']." по выбранным параметрам нет ни одного документа!</h2>";
		}
	} else {
		require_once(MODULES_PATH.DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."report_form.php");
	}
?>
