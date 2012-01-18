<?php
	$statuses			= $connection->execute('select * from cs_event_status')->fetchAll();
	$transports			= $connection->execute('select * from cs_transport')->fetchAll();
	$priorities			= $connection->execute('select * from cs_event_period')->fetchAll();
	$periods			= $connection->execute('select * from cs_event_period')->fetchAll();
	$periodconditions	= $connection->execute('select * from cs_event_period_condition')->fetchAll();
	$calendars			= $connection->execute('(select * from calendars_list where owner_id = '.USER_CODE.' and is_deleted = false) union'.
												'(select * from calendars_list where is_public = false and is_deleted = false and id in (select calendar_id from cs_calendar_permission where account_id = '.USER_CODE.' and permission_id > 2)) union'.
												'(select * from calendars_list where is_public = true and is_deleted = false and owner_id <> '.USER_CODE.')')->fetchAll();

	if (((ACTION == 'view') or (ACTION == 'edit')) and (defined('EVENT_ID'))) {
		$event = $connection->execute('select * from calendar_events_list where id = '.EVENT_ID)->fetch();
		if ($event['is_periodic']) {
			$period = $connection->execute('select * from calendar_event_periods_list where event_id = '.EVENT_ID)->fetch();
		} else {
			$period = array('period_id' => Constants::EVENT_PERIOD_DAILY);
		}
	} elseif (ACTION == 'add') {
		$event = array('started_at' => START_DATE." ".START_TIME, 'ended_at' => START_DATE." ".END_TIME);
		if (defined('CALENDAR_ID')) {
			$event['calendar_id'] = CALENDAR_ID;
		}
		$period = array('period_id' => Constants::EVENT_PERIOD_DAILY);
	}
	if (ACTION == 'view') {
		$readonly = true;
	}
?>
<div class="tabs_panel">
<ul id="event_edit_tabs" class="subsection_tabs">
	<li><a class="active action" href="#commons">Общие</a></li>
	<li><a class="action" href="#repeats">Повторение</a></li>
	<li><a class="action" href="#accounts">Участники</a></li>
	<li><a class="action" href="#alerts">Уведомление</a></li>
	<li><a class="action" href="#attachements">Вложения</a></li>
</ul>
<form style=" height: 95%; width: 99%; " id="event_edit_form" method="POST">
<div class="panel active" id="commons"><?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."calendars".DIRECTORY_SEPARATOR."events".DIRECTORY_SEPARATOR."edit".DIRECTORY_SEPARATOR."commons.php"); ?></div>
<div style=" display : none; " class="panel" id="repeats"><?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."calendars".DIRECTORY_SEPARATOR."events".DIRECTORY_SEPARATOR."edit".DIRECTORY_SEPARATOR."repeats.php"); ?></div>
<div style=" display : none; " class="panel" id="accounts"><?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."calendars".DIRECTORY_SEPARATOR."events".DIRECTORY_SEPARATOR."edit".DIRECTORY_SEPARATOR."accounts.php"); ?></div>
<div style=" display : none; " class="panel" id="alerts"><?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."calendars".DIRECTORY_SEPARATOR."events".DIRECTORY_SEPARATOR."edit".DIRECTORY_SEPARATOR."alerts.php"); ?></div>
<div style=" display : none; " class="panel" id="attachements"><?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."calendars".DIRECTORY_SEPARATOR."events".DIRECTORY_SEPARATOR."edit".DIRECTORY_SEPARATOR."attachements.php"); ?></div>
<div style=" position: relative; top: 10px; "><table><tr><?php if (ACTION <> 'view'): ?><td><input class="button" type="button" value="Сохранить" onClick="$('event_edit_form').validateBeforeSubmit(); " /></td><?php endif; ?><td><input class="button" type="button" onClick="Windows.close('<?= WINDOW_ID; ?>', event)" value="<?= ((ACTION <> 'view')?"Отмена":"Закрыть") ?>" /></td></tr></table></div>
</form>
</div>
<script>
<!--
	new Control.Tabs('event_edit_tabs');
//-->
</script>