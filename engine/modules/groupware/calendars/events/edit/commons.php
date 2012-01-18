<table width="100%" class="noborders intabspanel" style="table-layout: fixed;" >
	<tr>
		<td align="right" valign="top" width="15%">Календарь:</td>
		<td align="left" valign="top" width="auto" colspan="5">
			<input name="x_old_calendar_id" type="hidden" value="<?= $event['calendar_id']; ?>" />
			<select name="x_calendar_id" id="x_calendar_id" style=" width : 100%; " <?= ((($readonly) or (defined('CALENDAR_ID')))?"disabled":""); ?> >
				<?php foreach ($calendars as $calendar): ?>
				<option value="<?= $calendar['id']; ?>" <?= (($event['calendar_id'] == $calendar['id'])?"selected":""); ?> /><?= $calendar['name']; ?>
				<?php endforeach; ?> 
			</select>
		</td>
	</tr>
	<tr>
		<td align="right" valign="top" width="15%">Наименование:</td>
		<td align="left" valign="top" width="auto" colspan="5"><input class="isValidRequired" <?= ($readonly?"readonly":""); ?> type="text" id="x_subject" name="x_subject" value="<?= $event['subject']; ?>" size="100" style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top" width="15%">Описание:</td>
		<td align="left" valign="top" width="auto" colspan="5"><textarea class="isValidRequired" <?= ($readonly?"readonly":""); ?> name="x_event" id="x_event" rows="20" style=" width : 100%; " /><?= $event['event']; ?></textarea></td>
	</tr>
	<tr>
		<td align="right" valign="top" width="15%">Дата события:</td>
		<td align="left" valign="top" width="auto"><input readonly type="text" id="x_start_date" name="x_start_date" value="<?= strftime('%d.%m.%Y', strtotime($event['started_at'])); ?>" size="15" style=" width : 80%; " /><img src="images/date.png" title="Открыть календарь" onClick="<?= ($readonly?"":"return showCalendar('x_start_date', '%d.%m.%Y');"); ?>" /></td>
		<td align="right" valign="top" width="15%">Начало в:</td>
		<td align="left" valign="top" width="auto"><select name="x_start_time" style=" width : 100% !important; " <?= ($readonly?"disabled":""); ?>>
<?php
	$time = 0;
	while ($time < 86400) {
		print "<option ".((strftime('%H:%M', strtotime($event['started_at'])) == gmstrftime('%H:%M', $time))?"selected":"")." value=\"".gmstrftime('%H:%M', $time)."\" />".gmstrftime('%H:%M', $time)."\n";
		$time += (DEFAULT_TIME_QUANT * 60);
	}
?>
		</select></td>
		<td align="right" valign="top" width="15%">Конец в:</td>
		<td align="left" valign="top" width="auto"><select name="x_end_time" style=" width : 100% !important; " <?= ($readonly?"disabled":""); ?>>
<?php
	$time = 0;
	while ($time < 86400) {
		print "<option ".((strftime('%H:%M', strtotime($event['ended_at'])) == gmstrftime('%H:%M', $time))?"selected":"")." value=\"".gmstrftime('%H:%M', $time)."\" />".gmstrftime('%H:%M', $time)."\n";
		$time += (DEFAULT_TIME_QUANT * 60);
	}
?>
		</select></td>
	</tr>
</table>