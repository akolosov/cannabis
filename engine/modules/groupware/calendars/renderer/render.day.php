<?php
if ((strftime('%u', strtotime(CALENDAR_DAY)) == 6) or (strftime('%u', strtotime(CALENDAR_DAY)) == 7)) {
	define('IS_WEEKEND', true);
} else {
	define('IS_WEEKEND', false);
}
?>
<tr>
	<th width="5%">Время</th>
	<th width="auto">Событие</th>
</tr>
<?php
	$time = 0;
	$printedRows = 1;
	$spanPrinted = false;
	$printed = array();
	while ($time < 86400):
?>
<?php
	$subclass = (((isWorkTime(strtotime(CALENDAR_DAY." ".gmstrftime('%H:%M', $time)))) and (!isWeekEnd(strtotime(CALENDAR_DAY))))?" calendarworktime":"");
?>
<tr class="selectable<?= $subclass; ?>">
	<?php
		$haveEvents		= haveCrossEventsInTime($events, strtotime(CALENDAR_DAY." ".gmstrftime('%H:%M', $time)), &$printed);
		$timeQuantCount	= ((isNotNULL($haveEvents))?getQuantCount($haveEvents):1);
	?>
	<td id="calendartime_<?= gmstrftime('%H_%M', $time); ?>" class="bold" <?= printEventOnClick(NULL, array('view' => true, 'doubleclick' => DOUBLE_CLICK_MODE, 'start_date' => CALENDAR_DAY, 'start_time' => gmstrftime('%H:%M', $time), 'end_time' => gmstrftime('%H:%M', $time + (DEFAULT_TIME_QUANT * 60)))); ?>><?= gmstrftime('%H:%M', $time); ?></td>
	<? if (($printedRows == 1) and (isNULL($haveEvents))): ?>
		<td align="center" valign="top" class="calendarcell" <?= printEventOnClick(NULL, array('view' => true, 'doubleclick' => DOUBLE_CLICK_MODE, 'start_date' => CALENDAR_DAY, 'start_time' => gmstrftime('%H:%M', $time), 'end_time' => gmstrftime('%H:%M', $time + (DEFAULT_TIME_QUANT * 60)))); ?>>&nbsp;</td>
	<? else: ?>
		<?php if (!$spanPrinted): ?>
			<td align="center" valign="top" class="calendareventcell" <?php
			if (($printedRows == 1) and ($timeQuantCount > 1)) {
				$printedRows++;
				$spanPrinted = true;
				$totalQuantCount = $timeQuantCount; 
				print " rowspan=\"".$totalQuantCount."\"";
			} elseif (($printedRows < $totalQuantCount) and ($totalQuantCount > 1)) {
				$printedRows++;
				$spanPrinted = true;
			} else {
				$printedRows = 1;
				$totalQuantCount = 1;
				$spanPrinted = false;
			}
			$rows = 1;
			?>>
				<table class="eventcontainers">
					<tr>
					<?php foreach ($haveEvents as $event): ?>
						<?php
							$subclasses	= "action eventcontainer "; 
							if ($event->isOvertimed()) {
								$subclasses	.= " eventovertimed";
							} elseif ($event->isInProgress()) {
								$subclasses	.= " eventinprogress";
							} elseif ($event->isCompleted()) {
								$subclasses	.= " eventcompleted";
							} elseif ($event->isCanceled()) {
								$subclasses	.= " eventcanceled";
							} else {
								$subclasses	.= " eventbusy";
							}
						?>
						<td class="eventcover"><div title="<?= printEventTitle($event); ?>" class="<?= $subclasses; ?>" <?= printEventOnClick($event, array('view' => true, 'doubleclick' => true, 'start_date' => CALENDAR_DAY, 'start_time' => gmstrftime('%H:%M', $time), 'end_time' => gmstrftime('%H:%M', $time + (DEFAULT_TIME_QUANT * 60)))); ?>><?php printEventInCalendar($event); ?></div></td>
						<?php
							if (($rows == EVENTS_IN_ROW) and (count($haveEvents > $rows))) {
								$rows = 1;
								print "</tr><tr>";
							} else {
								$rows++;
							}
						?>
					<?php endforeach; ?>
					</tr>
				</table>
			</td>
		<? else: ?>
		<?php
			if (($printedRows < $totalQuantCount) and ($totalQuantCount > 1)) {
				$printedRows++;
				$spanPrinted = true;
			} else {
				$printedRows = 1;
				$totalQuantCount = 1;
				$spanPrinted = false;
			}
		?>
		<? endif; ?>
	<? endif; ?>
	<?php
	?>
</tr>
<?php
	$time += (DEFAULT_TIME_QUANT * 60);
	endwhile;
?>
