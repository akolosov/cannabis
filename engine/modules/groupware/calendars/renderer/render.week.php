<tr>
	<th>Время</th>
	<? for ($i = 0; $i < 7; $i++): ?>
		<?php
			$currentDay		= strftime('%d.%m.%Y', (strtotime(CALENDAR_START)+($i * 86400)));
		?>
		<th width="14%" class="action selectable" onClick="loadAJAX('content', '/index.php', '?module=<?= getParentModule()."/".getChildModule(); ?>/list&content_only=true&ajax=true&update_uri=true&calendar_mode=day<?= ((defined('CALENDAR_IDS'))?"&calendar_ids=".CALENDAR_IDS:"").((defined('CALENDAR_ID'))?"&calendar_id=".CALENDAR_ID:""); ?>&calendar_day=<?= $currentDay; ?>'); "><?= $weekDays[$i]."<br />".$currentDay; ?></th>
	<?php endfor; ?>
</tr>
<?php
	$day = array();
	$time = 0;
	while ($time < 86400):
?>
<tr class="selectable">
	<td align="center" id="calendartime_<?= gmstrftime('%H_%M', $time); ?>" class="bold"><?= gmstrftime('%H:%M', $time); ?></td>
	<?php for ($i = 0; $i < 7; $i++): ?>
		<?php
			$currentDay							= strftime('%d.%m.%Y', (strtotime(CALENDAR_START)+($i * 86400)));
			$day[$currentDay]['haveEvents']		= haveCrossEventsInTime($events, strtotime($currentDay." ".gmstrftime('%H:%M', $time)), &$day[$currentDay]['printed']);
			$day[$currentDay]['timeQuantCount']	= ((isNotNULL($day[$currentDay]['haveEvents']))?getQuantCount($day[$currentDay]['haveEvents']):1);
			if ($day[$currentDay]['printedRows'] == false) {
				$day[$currentDay]['printedRows'] = 1;
			}
		?>
		<? if (($day[$currentDay]['printedRows'] == 1) and (isNULL($day[$currentDay]['haveEvents']))): ?>
			<td align="center" valign="top" class="calendarcell" <?= printEventOnClick(NULL, array('view' => true, 'doubleclick' => DOUBLE_CLICK_MODE, 'start_date' => $currentDay, 'start_time' => gmstrftime('%H:%M', $time), 'end_time' => gmstrftime('%H:%M', $time + (DEFAULT_TIME_QUANT * 60)))); ?>>&nbsp;</td>
		<? else: ?>
			<?php if (!$day[$currentDay]['spanPrinted']): ?>
				<td align="center" valign="top" class="calendareventcell" <?php
				if (($day[$currentDay]['printedRows'] == 1) and ($day[$currentDay]['timeQuantCount'] > 1)) {
					$day[$currentDay]['printedRows']++;
					$day[$currentDay]['spanPrinted'] = true;
					$day[$currentDay]['totalQuantCount'] = $day[$currentDay]['timeQuantCount']; 
					print " rowspan=\"".$day[$currentDay]['totalQuantCount']."\"";
				} elseif (($day[$currentDay]['printedRows'] < $day[$currentDay]['totalQuantCount']) and ($day[$currentDay]['totalQuantCount'] > 1)) {
					$day[$currentDay]['printedRows']++;
					$day[$currentDay]['spanPrinted'] = true;
				} else {
					$day[$currentDay]['printedRows'] = 1;
					$day[$currentDay]['totalQuantCount'] = 1;
					$day[$currentDay]['spanPrinted'] = false;
				}
				$rows = 1;
				?>>
					<table class="eventcontainers">
						<tr>
							<?php foreach ($day[$currentDay]['haveEvents'] as $event): ?>
								<?php
									$day[$currentDay]['subclasses']	= "action eventcontainer "; 
									if ($event->isOvertimed()) {
										$day[$currentDay]['subclasses']	.= " eventovertimed";
									} elseif ($event->isInProgress()) {
										$day[$currentDay]['subclasses']	.= " eventinprogress";
									} elseif ($event->isCompleted()) {
										$day[$currentDay]['subclasses']	.= " eventcompleted";
									} elseif ($event->isCanceled()) {
										$day[$currentDay]['subclasses']	.= " eventcanceled";
									} else {
										$day[$currentDay]['subclasses']	.= " eventbusy";
									}
								?>
								<td class="eventcover"><div title="<?= printEventTitle($event); ?>" class="<?= $day[$currentDay]['subclasses']; ?>" <?= printEventOnClick($event, array('view' => true, 'doubleclick' => DOUBLE_CLICK_MODE, 'start_date' => $currentDay, 'start_time' => gmstrftime('%H:%M', $time), 'end_time' => gmstrftime('%H:%M', $time + (DEFAULT_TIME_QUANT * 60)))); ?>><?php printEventInCalendar($event); ?></div></td>
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
				if (($day[$currentDay]['printedRows'] < $day[$currentDay]['totalQuantCount']) and ($day[$currentDay]['totalQuantCount'] > 1)) {
					$day[$currentDay]['printedRows']++;
					$day[$currentDay]['spanPrinted'] = true;
				} else {
					$day[$currentDay]['printedRows'] = 1;
					$day[$currentDay]['totalQuantCount'] = 1;
					$day[$currentDay]['spanPrinted'] = false;
				}
			?>
			<? endif; ?>
		<? endif; ?>
	<?php endfor; ?>
</tr>
<?php
	$time += (DEFAULT_TIME_QUANT * 60);
	endwhile;
?>
