<? print $engine->getFormManager()->useCalendars(); ?>
<script type="text/javascript">
<!--
function checkSubmit() {
	if (isValidDate($F('x_period_from')) && isValidDate($F('x_period_to'))) { 
		return true;
	} else {
		return false;
	}
}
//-->
</script>
<form onSubmit=" checkSubmit(); " method="POST" action="<?= $_SERVER['REQUEST_URI']."&action=execute&media=print&orientation=landscape"; ?>" target="_blank">
<table class="rounded form">
<caption class="title">Параметры отбора документов для отчетов</caption>
<tr>
<td class="form" width="20%" align="right" valign="top" title="Отображать только документы начатые с ...">За период с:</td><td class="form" width="30%"><input class="calendarinput" name="x_period_from" id="x_period_from" type="text" value="<?= (defined('X_PERIOD_FROM')?X_PERIOD_FROM:beginOfMounth()); ?>" onBlur=" if (!isValidDate(this.value)) { this.value = oldDateFrom; } " onFocus=" oldDateFrom = $F('x_period_from'); " /><img valign="middle" src="images/date.png" title="Открыть календарь" onClick=" return showCalendar('x_period_from', '%d.%m.%Y'); " /></td>
<td class="form" width="20%" align="right" valign="top" title="Отображать только документы начатые по ...">За период по:</td><td class="form" width="30%"><input class="calendarinput" name="x_period_to" id="x_period_to" type="text" value="<?= (defined('X_PERIOD_TO')?X_PERIOD_TO:endOfMounth()); ?>" onBlur=" if (!isValidDate(this.value)) { this.value = oldDateTo; } " onFocus=" oldDateTo = $F('x_period_to'); " /><img valign="middle" src="images/date.png" title="Открыть календарь" onClick=" return showCalendar('x_period_to', '%d.%m.%Y'); " /></td>
</tr>
<tr>
<td class="form" width="20%" align="right" title="Отображать только документы выбранного предприятия" valign="top">По предприятию:</td>
<td class="form" width="80%" colspan="3">
<select name="X_PROJECT_ID[]" multiple="multiple" size="7">
<option value=""/>Все
<?php
	$projectslist = $connection->execute('select * from projects_instances '.(($user_permissions[getParentModule()][getChildModule()]['can_admin'])?'':' where is_system = false ').' order by name')->fetchAll();
	foreach ($projectslist as $project) {
		print "<option value=\"".$project['id']."\" ".(in_array($project['id'], $report_params['by_project_id'])?"selected":"")." />".$project['name']." (".$project['description'].")";
	}
?>
</select>
</td>
<? if (REPORT_NAME == 'second'): ?>
	<tr>
	<td class="form" width="20%" align="right" title="Отображать только выбранный тип документов" valign="top">По типу документа:</td>
	<td class="form" width="80%" colspan="3">
	<select name="X_PROCESS_ID[]" multiple="multiple" size="7">
	<option value=""/>Все
	<?php
		$processeslist = $connection->execute('select * from cs_process where (is_active = true or is_public = true or is_standalone = true) and is_system = false order by name')->fetchAll();
		foreach ($processeslist as $process) {
			print "<option value=\"".$process['id']."\" ".(in_array($process['id'], $report_params['by_process_id'])?"selected":"")." />".$process['name']." (".((mb_strlen($process['description']) > 30)?mb_substr($process['description'], 0, 30).'...':$process['description']).")";
		}
		?>
	</select>
	</td>
	</tr>
<? endif; ?>
<?= $engine->getFormManager()->formCombo(array('title' => 'По разработчику',
												'name' => 'X_INITIATOR_ID',
												'description' => 'Отбор по разработчику документа',
												'value' => implode('||', $report_params['by_initiator_id']),
												'multiple' => true,
												'required' => false,
												'error' => false,
												'colspan' => 3,
												'size' => 10,
												'nocurrentuser' => true,
												'titlewidth' => '20%',
												'datawidth' => '80%',
												'readonly' => false,
												'disabled' => false,
												'emptyline' => true,
												'reverse' => false,
												'data' => $engine->getDirectoryList(array_merge(array('directory' => 'accounts_without_groups_list', 'valueasname' => false, 'order' => 'name', 'where' => 'is_active = true')))));
?>
<?= $engine->getFormManager()->formCombo(array('title' => 'По исполнителю',
												'name' => 'X_PERFORMER_ID',
												'description' => 'Отбор по исполнителю документа',
												'value' => implode('||', $report_params['by_performer_id']),
												'multiple' => true,
												'required' => false,
												'error' => false,
												'colspan' => 3,
												'size' => 10,
												'nocurrentuser' => true,
												'titlewidth' => '20%',
												'datawidth' => '80%',
												'readonly' => false,
												'disabled' => false,
												'emptyline' => true,
												'reverse' => false,
												'data' => $engine->getDirectoryList(array_merge(array('directory' => 'accounts_without_groups_list', 'valueasname' => false, 'order' => 'name', 'where' => 'is_active = true')))));
?>
<? if ((REPORT_NAME <> 'fifth') and (REPORT_NAME <> 'sixth')): ?>
<tr>
<td class="form" width="20%" align="right" title="Отображать только документы с выбранным статусом">По статусу:</td>
<td class="form" width="30%"><select name="X_STATUS_ID">
<option value=""/>Все
<option value="completed" <?= (X_STATUS_ID == 'completed'?'selected':''); ?> />Завершенные
<option value="incompleted" <?= (X_STATUS_ID == 'incompleted'?'selected':''); ?> />Не Завершенные
</select>
</td>
<td class="form" width="20%" align="right" title="Группировать документы по определённому критерию">Группировать по:</td>
<td class="form" width="30%"><select name="X_GROUP_BY">
<option value="project" <?= (X_GROUP_BY == 'project'?'selected':''); ?> />Предприятию
<? if (REPORT_NAME == 'second'): ?>
	<option value="process" <?= (X_GROUP_BY == 'process'?'selected':''); ?> />Документу
<?php endif; ?>
<option value="initiator" <?= (X_GROUP_BY == 'initiator'?'selected':''); ?> />Разработчику
<? if (REPORT_NAME <> 'third'): ?>
<option value="status" <?= (X_GROUP_BY == 'status'?'selected':''); ?> />Статусу
<?php endif; ?>
</select>
</td>
</tr>
<?php endif; ?>
<? if ((REPORT_NAME <> 'second') and (REPORT_NAME <> 'fifth') and (REPORT_NAME <> 'sixth')): ?>
<tr>
<td class="form" width="20%" align="right" title="Только не завершенные Исполнителем">Не выполненые Исполнителем:</td>
<td class="form" width="30%" align="center"><input type="checkbox" name="x_uncompleted_only" <?= (X_UNCOMPLETED_ONLY == 'on')?"checked":""; ?> style=" width : 100%; " /></td>
<? if ((REPORT_NAME <> 'fourth') and (REPORT_NAME <> 'second') and (REPORT_NAME <> 'fifth') and (REPORT_NAME <> 'sixth')): ?>
<td class="form" width="20%" align="right" title="Выводить часть содержимого документа в отчет">Выводить содержимое:</td>
<td class="form" width="30%" align="center"><input type="checkbox" name="x_show_doc" <?= (X_SHOW_DOC == 'on')?"checked":""; ?> style=" width : 100%; " /></td>
<?php endif; ?>
</tr>
<?php endif; ?>
<tr>
<td colspan="4"><input class="button" type="submit" value="Сформировать отчет" /></td>
</tr>
</table>
</form>
