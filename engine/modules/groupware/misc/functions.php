<?php

function getByParent($objects = array(), $parent_id = 0) {
	$result = array();
	foreach ($objects as $object) {
		if ($object->getProperty('parent_id') == $parent_id) {
			$result[] = $object;
		}
	}
	return $result;
}

function getByLevel($objects = array(), $level = 0) {
	$result = array();
	foreach ($objects as $object) {
		if ($object->getProperty('level') == $level) {
			$result[] = $object;
		}
	}
	return $result;
}

function setMessageParams($message = NULL) {
	global $parameters, $engine;

	if (is_a($message, 'Message')) {
		$message->clearAllRecievers();
		foreach ($parameters['X_RECIEVERS_LIST'] as $reciever) {
			if (preg_match('/^L.*$/', $reciever)) {
				$reciever = str_replace("L", "", $reciever);
				$contactlist = new ContactList($engine, $reciever);
				foreach ($contactlist->getAccountsProperty('id') as $account) {
					$message->addReciever(MessageReciever::create(array('owner' => $message,
																	'message_id' => $message->getProperty('id'),
																	'reciever_id' => $account)));
				}
			} else {
				$message->addReciever(MessageReciever::create(array('owner' => $message,
																	'message_id' => $message->getProperty('id'),
																	'reciever_id' => $reciever)));
			}
		}
		if (defined('X_SUBJECT')) {
			$message->setProperty('subject', X_SUBJECT);
		}

		if (defined('X_MESSAGE')) {
			$message->setProperty('message', X_MESSAGE);
		}
	}
}

function prepareOptions() {
	$options = array();
	
	switch (strtoupper(CALENDAR_MODE)) {
		case "WEEK":
			if (defined('CALENDAR_START')) {
				$options['startdate'] = beginOfWeek(strtotime(CALENDAR_START));
			} else {
				if (defined('CALENDAR_END')) {
					$options['startdate'] = beginOfWeek(strtotime(CALENDAR_END));
				} else {
					$options['startdate'] = beginOfWeek(time());
				}
				define('CALENDAR_START', $options['startdate']);
			}
			$options['enddate'] = endOfWeek(strtotime($options['startdate']));
			define('CALENDAR_END', $options['enddate']);
			break;
		case "MOUNTH":
			if (defined('CALENDAR_START')) {
				$options['startdate'] = beginOfMounth(strtotime(CALENDAR_START));
			} else {
				if (defined('CALENDAR_END')) {
					$options['startdate'] = beginOfMounth(strtotime(CALENDAR_END));
				} else {
					$options['startdate'] = beginOfMounth(time());
				}
				define('CALENDAR_START', $options['startdate']);
			}
			$options['enddate'] = endOfMounth(strtotime($options['startdate']));
			define('CALENDAR_END', $options['enddate']);
			break;
		case "YEAR":
			if (defined('CALENDAR_START')) {
				$options['startdate'] = beginOfYear(strtotime(CALENDAR_START));
			} else {
				if (defined('CALENDAR_END')) {
					$options['startdate'] = beginOfYear(strtotime(CALENDAR_END));
				} else {
					$options['startdate'] = beginOfYear(time());
				}
				define('CALENDAR_START', $options['startdate']);
			}
			$options['enddate'] = endOfYear(strtotime($options['startdate']));
			define('CALENDAR_END', $options['enddate']);
			break;
		default:
			if (defined('CALENDAR_DAY')) {
				$options['onlydate'] = CALENDAR_DAY;
			} elseif (defined('CALENDAR_START')) {
				$options['onlydate'] = CALENDAR_START;
			} elseif (defined('CALENDAR_END')) {
				$options['onlydate'] = CALENDAR_END;
			} else {
				$options['onlydate'] = strftime('%d.%m.%Y', time());
			}
			define('CALENDAR_DAY', $options['onlydate']);
			define('CALENDAR_START', $options['onlydate']);
			define('CALENDAR_END', $options['onlydate']);
			define('CURRENT_DAY', $options['onlydate']);
			break;
	}
	if (defined('CALENDAR_IDS')) {
		$options['ids'] = CALENDAR_IDS;
	}
	return $options;
}

function isWeekEnd($date = NULL) {
	if (is_null($date)) {
		$date = time();
	}

	return ((strftime('%u', $date) == 6) or (strftime('%u', $date) == 7));
}

function isWorkTime($time = NULL) {
	if (is_null($time)) {
		$time = time();
	}

	return (($time >= strtotime(strftime('%d.%m.%Y', $time)." ".BEGIN_WORK_TIME)) and
			($time <= strtotime(strftime('%d.%m.%Y', $time)." ".END_WORK_TIME)));
}

function isCurrentTime($time = NULL) {
	if (is_null($time)) {
		$time = time();
	}

	return ((time() >= $time) and (time() <= $time));
}

function haveEvent($events = array(), $time = NULL) {
	if (is_null($time)) {
		$time = time();
	}

	foreach ($events as $event) {
		if (($time >= strtotime($event->getProperty('started_at'))) and
			($time < strtotime($event->getProperty('ended_at'))) and
			($event->userCanSeeEvent(USER_CODE))) {
				$event->setProperty('[QC]', ((strtotime($event->getProperty('ended_at')) - strtotime($event->getProperty('started_at'))) / (DEFAULT_TIME_QUANT * 60)));
				return $event;
			}
	}
	return false;
}

function haveEvents($events = array(), $time = NULL) {
	if (is_null($time)) {
		$time = time();
	}
	$result = array();

	foreach ($events as $event) {
		if ((strtotime(strftime('%d.%m.%Y', $time).' 00:00') <= strtotime($event->getProperty('started_at'))) and
			(strtotime(strftime('%d.%m.%Y', $time).' 23:59') >= strtotime($event->getProperty('ended_at'))) and
			($event->userCanSeeEvent(USER_CODE))) {
				$event->setProperty('[QC]', ((strtotime($event->getProperty('ended_at')) - strtotime($event->getProperty('started_at'))) / (DEFAULT_TIME_QUANT * 60)));
				$result[] = $event;
			}
	}
	return $result;
}

function haveEventsInTime($events = array(), $time = NULL, $exclude = array()) {
	$max = 0;
	if (is_null($time)) {
		$time = time();
	}
	$result = array();

	foreach ($events as $event) {
		if (((strtotime($event->getProperty('started_at')) <= $time) and
			(strtotime($event->getProperty('ended_at')) >= $time)) and
			($event->userCanSeeEvent(USER_CODE)) and
			(!in_array($event->getProperty('id'), $exclude))) {
				$exclude[] = $event->getProperty('id');
				$event->setProperty('[QC]', ((strtotime($event->getProperty('ended_at')) - strtotime($event->getProperty('started_at'))) / (DEFAULT_TIME_QUANT * 60)));
				$result[] = $event;
			}
	}

	return $result;
}

function haveCrossEventsInTime($events = array(), $time = NULL, $exclude = array()) {
	$max = 0;
	if (is_null($time)) {
		$time = time();
	}
	$result = haveEventsInTime($events, $time, &$exclude);

	$allresult = $result;
	foreach ($result as $event) {
		$allresult = array_merge($allresult, haveEventsBetween($events, strtotime($event->getProperty('started_at')), strtotime($event->getProperty('ended_at')), &$exclude));
	}

	return $allresult;
}

function haveEventsBetween($events = array(), $st_time = NULL, $end_time = NULL, $exclude = array()) {
	$max = 0;
	if (is_null($st_time)) {
		$st_time = time();
	}
	if (is_null($end_time)) {
		$end_time = time();
	}
	$result = array();

	foreach ($events as $event) {
		if (((strtotime($event->getProperty('started_at')) >= $st_time) and
			(strtotime($event->getProperty('ended_at')) <= $end_time)) and
			($event->userCanSeeEvent(USER_CODE)) and
			(!in_array($event->getProperty('id'), $exclude))) {
				$exclude[] = $event->getProperty('id');
				$event->setProperty('[QC]', ((strtotime($event->getProperty('ended_at')) - strtotime($event->getProperty('started_at'))) / (DEFAULT_TIME_QUANT * 60)));
				$result[] = $event;
			}
	}
	return $result;
}

function getMaxQuantCount($events = array()) {
	$max = 0;
	foreach ($events as $event) {
		$qc	= ((strtotime($event->getProperty('ended_at')) - strtotime($event->getProperty('started_at'))) / (DEFAULT_TIME_QUANT * 60));
		if ($qc > $max) {
			$max = $qc;
		}
	}
	return $max;
}

function getQuantCount($events = array()) {
	$min = time();
	$max = NULL;
	foreach ($events as $event) {
		if (strtotime($event->getProperty('ended_at')) > $max) {
			$max = strtotime($event->getProperty('ended_at'));
		}
		if (strtotime($event->getProperty('started_at')) < $min) {
			$min = strtotime($event->getProperty('started_at'));
		}
	}
	return (($max - $min) / (DEFAULT_TIME_QUANT * 60));
}

function getMinQuantCount($events = array()) {
	$min = 0;
	foreach ($events as $event) {
		$qc	= ((strtotime($event->getProperty('ended_at')) - strtotime($event->getProperty('started_at'))) / (DEFAULT_TIME_QUANT * 60));
		if ($qc < $max) {
			$min = $qc;
		}
	}
	return $min;
}

function printEventTitle($event = NULL) {
	if ((isNotNULL($event)) and (is_a($event, 'CalendarEvent'))) {
		print "<p style=' text-align : left !important; '>".
			"<strong>Статус: </strong>".printEventStatus($event)."<br />".
			"<strong>Владелец: </strong>".$event->getProperty('authorname')." (".$event->getProperty('authordescr').")<br />".
			"<strong>Тема: </strong>".$event->getProperty('subject')."<br />".
			"<strong>Описание: </strong>".str_replace("\n", "<br />", $event->getProperty('event'))."<br />".
			"<strong>Начало: </strong>".strftime('%d.%m.%Y в %H:%M', strtotime($event->getProperty('started_at')))."<br />".
			"<strong>Конец: </strong>".strftime('%d.%m.%Y в %H:%M', strtotime($event->getProperty('ended_at')))."<br />".
			"<strong>Календарь: </strong>".$event->getProperty('calendarname')." (".$event->getProperty('calendardescr').")<br />".
			"</p>";
	}
}

function printEventStatus($event = array(), $printtitle = false) {
	if ((isNotNULL($event)) and (is_a($event, 'CalendarEvent'))) {
		$result .= ($event->isInProgress()?" [Текущее]":"").
					($event->isOvertimed()?" [Просроченое]":"").
					" ".$event->getProperty('statusname')." (".$event->getProperty('statusdescr').")";
		if ($printtitle) {
			print $result;
		} else {
			return $result; 
		}
	}
}

function printEventsTitle($events = array(), $printstatus = true, $printtitle = false) {
	if (isNotNULL($events)) {
		$result .= "<p style=' text-align : left !important; '>";
		foreach ($events as $event) {
			if ((isNotNULL($event)) and (is_a($event, 'CalendarEvent'))) {
					$result .= "<strong>[".strftime('%H:%M', strtotime($event->getProperty('started_at')))."-".strftime('%H:%M', strtotime($event->getProperty('ended_at')))."]</strong> ".((mb_strlen($event->getProperty('subject')) > 20)?mb_substr($event->getProperty('subject'), 0, 20)."...":$event->getProperty('subject')).(($printstatus)?printEventStatus($event):"")."<br />";
			}
		}
		$result .= "</p>";
		if ($printtitle) {
			print $result;
		} else {
			return $result; 
		}
	}
}

function printEventOnClick($event = NULL, $options = array('view' => true, 'doubleclick' => true)) {
	if ((isNotNULL($event)) and (is_a($event, 'CalendarEvent'))) {
		$url_params['window_id']	= "window_event_".$event->getProperty('id');
		$url_params['start_date']	= strftime('%d.%m.%Y', strtotime($event->started_at));
		$url_params['start_time']	= strftime('%H:%M', strtotime($event->started_at));
		$url_params['end_time']		= strftime('%H:%M', strtotime($event->ended_at));

		$subject = "Событие: ".$event->getProperty('subject')." (".(($options['view'])?"Только просмотр":"Редактирование").")";
		$url = "?module=".getParentModule()."/".getParentChildModule()."/events/edit&action=".(($options['view'])?"view":"edit")."&event_id=".$event->getProperty('id')."&calendar_id=".$event->getProperty('calendar_id');
	} else {
		$url_params['window_id']	= "window_event_".time();
		$url_params['start_date']	= $options['start_date'];
		$url_params['start_time']	= $options['start_time'];
		$url_params['end_time']		= $options['end_time'];

		$subject = "Создать новое событие";
		$url = "?module=".getParentModule()."/".getParentChildModule()."/events/edit&action=add".((defined('CALENDAR_ID'))?"&calendar_id=".CALENDAR_ID:"");
	}
	print " on".($options['doubleclick']?"Dbl":"")."Click=\"showPopupWindow('".$url_params['window_id']."', { title: '".$subject."', url: '".$url.arrayToURI($url_params)."&content_only=true', resizable: false, width: 800 }); \"";
}

function printEventInCalendar($event = NULL) {
	if ((isNotNULL($event)) and (is_a($event, 'CalendarEvent'))) {
		print "<div class='small bold eventheader'>";
		print ((mb_strlen($event->getProperty('subject')) > 20)?mb_substr($event->getProperty('subject'), 0, 20)."...":$event->getProperty('subject'));
		print "</div>";
		print "<div class='eventstatus'>";
		if ($event->isPeriodic()) {
			print "<img style=' float: right; vertical-align: top; ' title='Переодическое событие' src='images/repeat.png' />";
		}
		if ($event->haveNotifiers()) {
			print "<img style=' float: right; vertical-align: top; ' title='Событие с уведомлением' src='images/timer.png' />";
		}
		if ($event->isGroupEvent()) {
			print "<img style=' float: right; vertical-align: top; ' title='Групповое событие' src='images/group.png' />";
		}
		if ($event->getProperty('priority_id') >= Constants::EVENT_PRIORITY_HIGH) {
			print "<img style=' float: right; vertical-align: top; ' title='Событие с высоким приоритетом' src='images/priority.png' />";
		}
		print "</div>";
		print "<div class='small eventbody'>";
		print ((mb_strlen($event->getProperty('event')) > 30)?mb_substr($event->getProperty('event'), 0, 30)."...":$event->getProperty('event'));
		print "</div>";
	}
}

function printMounthCalendar($date = NULL, $events = array(), $day_name_length = 20, $cellheight = '120px', $printevents = true, $printcalendar = true, $first_day = 1, $dblclick = DOUBLE_CLICK_MODE) {

	if (is_null($date)) {
		$first_of_month = strtotime(beginOfMounth(time()));
	} else {
		$first_of_month = strtotime(beginOfMounth($date));
	}

	$day_names = array(); #generate all the day names according to the current locale

	for ($n = 0, $t = (3 + $first_day) * 86400; $n < 7; $n++, $t += 86400) { #January 4, 1970 was a Sunday
		$day_names[$n] = ucfirst(strftime('%A', $t)); #%A means full textual day name
	}

	list($month, $year, $month_name, $weekday) = explode(',', strftime('%m,%Y,%B,%w', $first_of_month));

	$weekday = (($weekday + 7) - $first_day) % 7;

	$calendar  = "<tr>";
	$calendar .= '<th height="20" width="1%">&nbsp;</th>';
	foreach($day_names as $d) {
		$calendar .= '<th abbr="'.$d.'" height="20" width="14%">'.($day_name_length < 4 ? mb_substr($d, 0, $day_name_length) : $d).'</th>';
	}
	$calendar .= "</tr>\n<tr class='selectable'>";
	$calendar .= '<td class="action calendarcell calendarinprogress" onClick="loadAJAX(\'content\', \'/index.php\', \'?module='.getParentModule()."/".getChildModule().'/list&content_only=true&ajax=true&update_uri=true&calendar_mode=week&'.((defined('CALENDAR_IDS'))?"&calendar_ids=".CALENDAR_IDS:"").((defined('CALENDAR_ID'))?"&calendar_id=".CALENDAR_ID:"").'calendar_start=01.'.((strlen(($month+1)) == 1)?"0".($month+1):($month+1)).'.'.$year.'\');">&nbsp;</td>';

	if ($weekday > 0) {
		$calendar .= '<td colspan="'.$weekday.'">&nbsp;</td>'; #initial 'empty' days
	}

	for ($day = 1, $days_in_month = date('t', $first_of_month); $day <= $days_in_month; $day++, $weekday++) {

		$date_str = ((strlen($day) == 1)?"0".$day:$day).'.'.((strlen($month) == 1)?"0".$month:$month).'.'.$year;

		if ($weekday == 7) {
			$weekday = 0;
			$calendar .= "</tr>\n<tr class='selectable'>";
			$calendar .= '<td class="action calendarcell calendarinprogress" onClick="loadAJAX(\'content\', \'/index.php\', \'?module='.getParentModule()."/".getChildModule().'/list&content_only=true&ajax=true&update_uri=true&calendar_mode=week'.((defined('CALENDAR_IDS'))?"&calendar_ids=".CALENDAR_IDS:"").((defined('CALENDAR_ID'))?"&calendar_id=".CALENDAR_ID:"").'&calendar_start='.$date_str.'\');">';
			$calendar .= '</td>';
		}

		$haveEvents = haveEvents($events, strtotime($date_str));
		$subClasses = ((strftime('%d.%m.%Y', time()) == $date_str)?" calendarcurrent":"");
		if ($haveEvents) {
			if (haveOvertimed($haveEvents)) {
				$subClasses .= " calendarovertimed";
			} elseif (haveInProgress($haveEvents)) {
				$subClasses .= " calendarinprogress";
			} elseif (haveCompleted($haveEvents)) {
				$subClasses .= " calendarcompleted";
			} else {
				$subClasses .= " calendarbusy";
			}
		}

		$calendar .= '<td style=" height: '.$cellheight.' !important; vertical-align: top !important; " class="action calendarcell'.$subClasses.'" on'.($dblclick?"Dbl":"").'Click="loadAJAX(\'content\', \'/index.php\', \'?module='.getParentModule()."/".getChildModule().'/list&content_only=true&ajax=true&update_uri=true&calendar_mode=day'.((defined('CALENDAR_IDS'))?"&calendar_ids=".CALENDAR_IDS:"").((defined('CALENDAR_ID'))?"&calendar_id=".CALENDAR_ID:"").'&calendar_day='.$date_str.'\');" title="'.printEventsTitle($haveEvents).'">';
		$calendar .= '<div class="event'.(($printevents)?"day":"").'header bold" style=" width: 100% !important; ">'.$day.'</div>';
		if ($printevents) {
			$calendar .= '<div class="eventdaybody" style=" text-align: left !important; width: 100% !important; ">';
			foreach ($haveEvents as $event) {
				$calendar .= "<span class='calendarevent small'>[".strftime('%H:%M', strtotime($event->getProperty('started_at')))."-".strftime('%H:%M', strtotime($event->getProperty('ended_at')))."] ".((mb_strlen($event->getProperty('subject')) > 20)?mb_substr($event->getProperty('subject'), 0, 20)."...":$event->getProperty('subject'))."</span><br />";
			}
			$calendar .= '&nbsp;</div>';
		}
		$calendar .= '</td>';
	}

	if ($weekday != 7) {
		$calendar .= '<td colspan="'.(7-$weekday).'">&nbsp;</td>'; #remaining "empty" days
	}

	if ($printcalendar) {
		print $calendar."</tr>\n";
	} else {
		return $calendar;
	}
}

function printYearCalendar($date = NULL, $events = array()){

	if (is_null($date)) {
		$first_of_year = strtotime(beginOfYear(time()));
	} else {
		$first_of_year = strtotime(beginOfYear($date));
	}

	$year = strftime('%Y', $first_of_year);

	$split = 1;
	$calendar .= "<tr>\n";
	for ($mounth = 1; $mounth <= 12; $mounth++, $split++) {
		$current_mounth = strtotime('01.'.((strlen($mounth) == 1)?"0".$mounth:$mounth).'.'.$year);
		$month_name = strftime('%B', $current_mounth);
		if ($split > 4) {
			$calendar .= "</tr>\n<tr>\n";
			$split = 1;
		}
		$calendar .= "<td style=\" vertical-align: top !important; padding: 5px 5px 5px 5px !important; ".((strftime('%d.%m.%Y', $current_mounth) == strftime('01.%m.%Y', time()))?" background: #ffeac5 !important; border: 1px solid !important; ":" background: inherit !important; border: none !important; ")." \">\n";
		$calendar .= "<table id=\"calendar_".strftime('%m_%Y', $current_mounth)."\">\n";
		$calendar .= "<caption onClick=\"loadAJAX('content', '/index.php', '?module=".getParentModule()."/".getChildModule()."/list&content_only=true&ajax=true&update_uri=true&calendar_mode=mounth".((defined('CALENDAR_IDS'))?"&calendar_ids=".CALENDAR_IDS:"").((defined('CALENDAR_ID'))?"&calendar_id=".CALENDAR_ID:"")."&calendar_start=".strftime('%d.%m.%Y', $current_mounth)."');\" class=\"title action\">".$month_name."</caption>\n";
		$calendar .= printMounthCalendar($current_mounth, $events, 3, 'auto', false, false);
		$calendar .= "</table>\n</td>\n";
	}
	$calendar .= "</tr>\n";

	print $calendar;
}

function haveOvertimed($events = array()) {
	foreach ($events as $event) {
		if ((isNotNULL($event)) and (is_a($event, 'CalendarEvent')) and ($event->isOvertimed())) {
			return true;
		}
	}
	return false;
}

function haveCompleted($events = array()) {
	foreach ($events as $event) {
		if ((isNotNULL($event)) and (is_a($event, 'CalendarEvent')) and ($event->isCompleted())) {
			return true;
		}
	}
	return false;
}

function haveInProgress($events = array()) {
	foreach ($events as $event) {
		if ((isNotNULL($event)) and (is_a($event, 'CalendarEvent')) and ($event->isInProgress())) {
			return true;
		}
	}
	return false;
}

function compareEventStartedAt($a, $b) {
	if ((is_a($a, 'CalendarEvent')) and (is_a($b, 'CalendarEvent'))) {
		if (strtotime($a->getProperty('started_at')) == strtotime($b->getProperty('started_at'))) {
			return 0;
		}
		return (strtotime($a->getProperty('started_at')) < strtotime($b->getProperty('started_at'))) ? -1 : 1;
	} else {
		return 0;
	}
}

?>