<div class="titlesmall" style=" width: 99% !important; ">Доступные календари:</div>
<?php
	function calendarChecked($calendar_id = 0, $calendar_ids = array()) {
		if (($calendar_id == CALENDAR_ID) or (in_array($calendar_id, $calendar_ids))) {
			print " checked ";
		}
	}

	$calendars = $connection->execute('(select * from calendars_list where owner_id = '.USER_CODE.' and is_deleted = false) union'.
										'(select * from calendars_list where is_public = false and is_deleted = false and id in (select calendar_id from cs_calendar_permission where account_id = '.USER_CODE.' and permission_id > 2)) union'.
										'(select * from calendars_list where is_public = true and is_deleted = false and owner_id <> '.USER_CODE.')')->fetchAll();

	if (defined('CALENDAR_IDS')) {
		$ids = split("\,", CALENDAR_IDS);
	} else {
		$ids = array();
		if (defined('CALENDAR_ID')) {
			$ids[] = CALENDAR_ID;
		} else {
			foreach ($calendars as $calendar) {
				$ids[] = $calendar['id'];
			}
		}
	}
?>
<div id="calendarsList" name="calendarsList" class="scrolablebox dotborders" style=" height: 80% !important; ">
	<table>
	<?php foreach ($calendars as $calendar): ?>
		<tr>
			<td width="5%" align="center"><input type="checkbox" id="cb_<?= $calendar['id']; ?>" value="<?= $calendar['id']; ?>" name="checkboxes" style=" width: auto !important; " <?php calendarChecked($calendar['id'], $ids); ?> /></td>
			<td><label for="cb_<?= $calendar['id']; ?>"><?= $calendar['name']." (Владелец: ".$calendar['ownername'].")"; ?></label></td>
		</tr>
	<?php endforeach; ?>
	</table>
</div>
<table style=" margin-top: 10px; ">
	<tr>
		<td><input type="button" class="thinbutton" value="Показать отмеченные" onClick="loadAJAX('content', '/index.php', 'module=groupware/calendars/list&content_only=true&ajax=true&update_uri=true<?= ((defined('CALENDAR_MODE'))?"&calendar_mode=".CALENDAR_MODE:"").((defined('CALENDAR_START'))?"&calendar_start=".CALENDAR_START:"").((defined('CALENDAR_END'))?"&calendar_end=".CALENDAR_END:"").((defined('CALENDAR_DAY'))?"&calendar_day=".CALENDAR_DAY:""); ?>&calendar_ids='+getCheckBoxesList($('calendarsList'))); " /></td>
		<td>&nbsp;</td>
		<td><input type="button" class="thinbutton" value="Показать все" onClick="loadAJAX('content', '/index.php', '?module=groupware/calendars/list&content_only=true&ajax=true&update_uri=true<?= ((defined('CALENDAR_MODE'))?"&calendar_mode=".CALENDAR_MODE:"").((defined('CALENDAR_START'))?"&calendar_start=".CALENDAR_START:"").((defined('CALENDAR_END'))?"&calendar_end=".CALENDAR_END:"").((defined('CALENDAR_DAY'))?"&calendar_day=".CALENDAR_DAY:""); ?>'); setCheckBoxesValue($('calendarsList'), true); " /></td>
	</tr>
</table>