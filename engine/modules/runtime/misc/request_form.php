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
<div id="request_params" style="<?= ((($_SERVER['REQUEST_METHOD'] == "POST") and (!defined('ACTION')))?' visibility: visible; display: block; ':''); ?>">
<br />
<form onSubmit=" checkSubmit(); " method="POST" action="<?= $_SERVER['REQUEST_URI']; ?>">
<table class="rounded form">
<caption class="title">Параметры для отбора документов</caption>
<tr>
<td class="form" width="20%" align="right" title="По номеру документа (перечислять через запятую или точку с запятой)">По номеру:</td>
<td class="form" width="80%" colspan="3"><input type="text" name="x_search_num" value="<?= ((defined("X_SEARCH_NUM"))?X_SEARCH_NUM:""); ?>" size="35" style=" width : 100%; " /></td>
</tr>
<?php if (MODULE == "runtime/today/list"): ?>
<? if (USER_CODE == USER_BOSSCODE): ?>
<?= $engine->getFormManager()->formCombo(array('title' => 'По сотруднику',
												'name' => 'X_PROCESS_OWNER',
												'description' => 'Отбор документов по подчинённому сотруднику',
												'value' => X_PROCESS_OWNER,
												'size' => 0,
												'required' => false,
												'error' => false,
												'colspan' => 3,
												'nocurrentuser' => false,
												'titlewidth' => '20%',
												'datawidth' => '80%',
												'readonly' => false,
												'disabled' => false,
												'emptyline' => false,
												'reverse' => false,
												'data' => $engine->getDirectoryList(array_merge(array('directory' => "get_accounts_by_post_and_division('%%USER_POSTS%%', '%%USER_DIVISIONCODE%%')", 'valueasname' => false, 'order' => 'name')))));
?>
<? endif; ?>
<? else: ?>
<?php if ((MODULE == "runtime/archives/list") or (MODULE == "runtime/managers/list")): ?>
<tr>
<td class="form" width="20%" align="right">Количество строк:</td>
<td class="form" width="80%" colspan="3">
<select name="X_PROCESS_LIMIT">
<option value="0" <?= (CURRENT_LIMIT == '0'?"selected":""); ?> />Все
<option value="5" <?= (CURRENT_LIMIT == '5'?"selected":""); ?> />5
<option value="10" <?= (CURRENT_LIMIT == '10'?"selected":""); ?> />10
<option value="20" <?= (CURRENT_LIMIT == '20'?"selected":""); ?> />20
<option value="40" <?= (CURRENT_LIMIT == '40'?"selected":""); ?> />40
<option value="70" <?= (CURRENT_LIMIT == '70'?"selected":""); ?> />70
<option value="100" <?= (CURRENT_LIMIT == '100'?"selected":""); ?> />100
<option value="150" <?= (CURRENT_LIMIT == '150'?"selected":""); ?> />150
<option value="200" <?= (CURRENT_LIMIT == '200'?"selected":""); ?> />200
<option value="400" <?= (CURRENT_LIMIT == '400'?"selected":""); ?> />400
<option value="600" <?= (CURRENT_LIMIT == '600'?"selected":""); ?> />600
<option value="800" <?= (CURRENT_LIMIT == '800'?"selected":""); ?> />700
<option value="1000" <?= (CURRENT_LIMIT == '1000'?"selected":""); ?> />1000
<option value="1500" <?= (CURRENT_LIMIT == '1500'?"selected":""); ?> />1500
<option value="2000" <?= (CURRENT_LIMIT == '2000'?"selected":""); ?> />2000
<option value="3000" <?= (CURRENT_LIMIT == '3000'?"selected":""); ?> />3000
</select>
</td>
</tr>
<?php endif; ?>
<tr>
<td class="form" width="20%" align="right" title="Отображать только документы выбранного предприятия">По предприятию:</td>
<td class="form" width="80%" colspan="3">
<select name="X_PROJECT_NAME">
<option value=""/>Все
<?php
	$projectslist = $connection->execute('select * from projects_instances '.(($user_permissions[getParentModule()][getChildModule()]['can_admin'])?'':' where is_system = false ').' order by name')->fetchAll();
	foreach ($projectslist as $project) {
		print "<option value=\"".$project['id']."\" ".(((X_PROJECT_NAME == $project['id']) or (PROJECT_INSTANCE_ID == $project['id']))?"selected":"")." />".$project['name']." (".$project['description'].")";
	}
?>
</select>
</td>
<tr>
<td class="form" width="20%" align="right" title="Отображать только выбранный тип документов">По типу документа:</td>
<td class="form" width="80%" colspan="3"><select name="X_PROCESS_NAME">
<option value=""/>Все
<?php
	$processeslist = $connection->execute('select * from cs_process order by name')->fetchAll();
	foreach ($processeslist as $process) {
		print "<option value=\"".$process['id']."\" ".(X_PROCESS_NAME == $process['id']?"selected":"")." />".$process['name']." (".$process['description'].")";
	}
	?>
</select>
</td>
</tr>
<?= $engine->getFormManager()->formCombo(array('title' => 'По инициатору',
												'name' => 'X_PROCESS_INITIATOR',
												'description' => 'Отбор по инициатору документа',
												'value' => X_PROCESS_INITIATOR,
												'size' => 0,
												'required' => false,
												'error' => false,
												'colspan' => 3,
												'nocurrentuser' => true,
												'titlewidth' => '20%',
												'datawidth' => '80%',
												'readonly' => false,
												'disabled' => false,
												'emptyline' => true,
												'reverse' => false,
												'data' => $engine->getDirectoryList(array_merge(array('directory' => 'accounts_without_groups_list', 'valueasname' => false, 'order' => 'name')))));
?>
<?php if (MODULE <> "runtime/inboxes/list"): ?>
<?= $engine->getFormManager()->formCombo(array('title' => 'По исполнителю',
												'name' => 'X_PROCESS_PERFORMER',
												'description' => 'Отбор по исполнителю документа',
												'value' => X_PROCESS_PERFORMER,
												'size' => 0,
												'required' => false,
												'error' => false,
												'colspan' => 3,
												'nocurrentuser' => true,
												'titlewidth' => '20%',
												'datawidth' => '80%',
												'readonly' => false,
												'disabled' => false,
												'emptyline' => true,
												'reverse' => false,
												'data' => $engine->getDirectoryList(array_merge(array('directory' => 'accounts_without_groups_list', 'valueasname' => false, 'order' => 'name')))));
?>
<?php endif; ?>
<?php endif; ?>
<? print $engine->getFormManager()->useCalendars(); ?>
<tr>
<td class="form" width="20%" align="right" title="По содержанию в любом из свойств документа">По содержанию:</td>
<td class="form" width="80%" colspan="3"><input type="text" name="x_search_value" value="<?= ((defined("X_SEARCH_VALUE"))?X_SEARCH_VALUE:""); ?>" size="35" style=" width : 100%; " /></td>
</tr>
<tr>
<td class="form" width="20%" align="right" title="Отображать только документы начатые с ...">За период с:</td><td class="form" width="30%"><input class="calendarinput" name="x_period_from" id="x_period_from" type="text" value="<?= (defined('X_PERIOD_FROM')?X_PERIOD_FROM:beginOfMounth()); ?>" onBlur=" if (!isValidDate(this.value)) { this.value = oldDateFrom; } " onFocus=" oldDateFrom = $F('x_period_from'); " /><img valign="middle" src="images/date.png" title="Открыть календарь" onClick=" return showCalendar('x_period_from', '%d.%m.%Y'); " /></td>
<td class="form" width="20%" align="right" title="Отображать только документы начатые по ...">За период по:</td><td class="form" width="30%"><input class="calendarinput" name="x_period_to" id="x_period_to" type="text" value="<?= (defined('X_PERIOD_TO')?X_PERIOD_TO:endOfMounth()); ?>" onBlur=" if (!isValidDate(this.value)) { this.value = oldDateTo; } " onFocus=" oldDateTo = $F('x_period_to'); " /><img valign="middle" src="images/date.png" title="Открыть календарь" onClick=" return showCalendar('x_period_to', '%d.%m.%Y'); " /></td>
</tr>
<tr><td colspan="4"><input class="button" type="submit" value="Выборка по параметрам" /></td></tr>
</table>
</form>
<br />
</div>
