<caption class="title" id="calendar-title">
<img src="images/help.png" class="action nontransparent" onClick="showPopupWindow('legend', { height: 190, width: 450, top: 20, left: 20, title: 'Описание обозначений', center: false, modal: false, resizable: false, url: '<?= "?module=groupware/calendars/misc/legend&content_only=true"; ?>'});" title="Описание обозначений" style=" float: left; " />
<img src="images/date.png" class="action nontransparent" onClick="showPopupWindow('calendars', { height: 290, width: 450, top: 20, left: 20, title: 'Выбор календарей', center: false, modal: false, resizable: false, url: '?module=<?= getParentModule()."/".getParentChildModule(); ?>/manager/select&content_only=true<?= ((defined('CALENDAR_IDS'))?"&calendar_ids=".CALENDAR_IDS:"").((defined('CALENDAR_ID'))?"&calendar_id=".CALENDAR_ID:"").((defined('CALENDAR_MODE'))?"&calendar_mode=".CALENDAR_MODE:"").((defined('CALENDAR_START'))?"&calendar_start=".CALENDAR_START:"").((defined('CALENDAR_END'))?"&calendar_end=".CALENDAR_END:"").((defined('CALENDAR_DAY'))?"&calendar_day=".CALENDAR_DAY:""); ?>'}); " title="Управление календарями" style=" float: left; " />
<img src="images/date_edit.png" class="action nontransparent" onClick="document.location.href = '?module=<?= getParentModule()."/".getParentChildModule(); ?>/manager/list'; " title="Управление списком календарей" style=" float: left; " />
<img src="images/year.png" class="action <?= ((CALENDAR_MODE == 'year')?"selected":""); ?>" onClick="loadAJAX('content', '/index.php', '?module=<?= getParentModule()."/".getChildModule(); ?>/list&content_only=true&ajax=true&update_uri=true&calendar_mode=year<?= ((defined('CALENDAR_IDS'))?"&calendar_ids=".CALENDAR_IDS:"").((defined('CALENDAR_ID'))?"&calendar_id=".CALENDAR_ID:""); ?>&calendar_start=<?= (defined('CALENDAR_START')?CALENDAR_START:strftime('%d.%m.%Y', time())); ?>'); " title="Режим календаря: <strong>ГОД</strong>" style=" float: right; " />
<img src="images/month.png" class="action <?= ((CALENDAR_MODE == 'mounth')?"selected":""); ?>" onClick="loadAJAX('content', '/index.php', '?module=<?= getParentModule()."/".getChildModule(); ?>/list&content_only=true&ajax=true&update_uri=true&calendar_mode=mounth<?= ((defined('CALENDAR_IDS'))?"&calendar_ids=".CALENDAR_IDS:"").((defined('CALENDAR_ID'))?"&calendar_id=".CALENDAR_ID:""); ?>&calendar_start=<?= (defined('CALENDAR_START')?CALENDAR_START:strftime('%d.%m.%Y', time())); ?>'); " title="Режим календаря: <strong>МЕСЯЦ</strong>" style=" float: right; " />
<img src="images/week.png" class="action <?= ((CALENDAR_MODE == 'week')?"selected":""); ?>" onClick="loadAJAX('content', '/index.php', '?module=<?= getParentModule()."/".getChildModule(); ?>/list&content_only=true&ajax=true&update_uri=true&calendar_mode=week<?= ((defined('CALENDAR_IDS'))?"&calendar_ids=".CALENDAR_IDS:"").((defined('CALENDAR_ID'))?"&calendar_id=".CALENDAR_ID:""); ?>&calendar_start=<?= (defined('CALENDAR_START')?CALENDAR_START:strftime('%d.%m.%Y', time())); ?>'); " title="Режим календаря: <strong>НЕДЕЛЯ</strong>" style=" float: right; " />
<img src="images/day.png" class="action <?= ((CALENDAR_MODE == 'day')?"selected":""); ?>" onClick="loadAJAX('content', '/index.php', '?module=<?= getParentModule()."/".getChildModule(); ?>/list&content_only=true&ajax=true&update_uri=true&calendar_mode=day<?= ((defined('CALENDAR_IDS'))?"&calendar_ids=".CALENDAR_IDS:"").((defined('CALENDAR_ID'))?"&calendar_id=".CALENDAR_ID:""); ?>&calendar_day=<?= (defined('CALENDAR_DAY')?CALENDAR_DAY:strftime('%d.%m.%Y', time())); ?>'); " title="Режим календаря: <strong>ДЕНЬ</strong>" style=" float: right; " />
<img src="images/back.png" id="date" class="action nontransparent" onClick="loadAJAX('content', '/index.php', '?module=<?php
	print getParentModule()."/".getChildModule()."/list";
	print "&content_only=true&ajax=true&update_uri=true&calendar_mode=".CALENDAR_MODE;
	print ((defined('CALENDAR_IDS'))?"&calendar_ids=".CALENDAR_IDS:"").((defined('CALENDAR_ID'))?"&calendar_id=".CALENDAR_ID:"");
	switch (CALENDAR_MODE) {
		case "year":
			print "&calendar_start=".yearPrev(strtotime((defined('CALENDAR_START')?CALENDAR_START:beginOfYear(time()))));
			break;
		case "mounth":
			print "&calendar_start=".mounthPrev(strtotime((defined('CALENDAR_START')?CALENDAR_START:beginOfMounth(time()))));
			break;
		case "week":
			print "&calendar_start=".weekPrev(strtotime((defined('CALENDAR_START')?CALENDAR_START:beginOfWeek(time()))));
			break;
		default:
			print "&calendar_day=".dayPrev(strtotime((defined('CALENDAR_DAY')?CALENDAR_DAY:strftime('%d.%m.%Y', time()))));
			break;
	}
?>'); " />
<?php
	switch (CALENDAR_MODE) {
		case "year":
			print "<input type=\"hidden\" id=\"start-date-store\" value=\"".(defined('CALENDAR_START')?CALENDAR_START:beginOfYear(time()))."\" />";
			print "<span class=\"action\" id=\"start-date-show\" onClick=\"return createCalendar('start-date-store', '%d.%m.%Y'); \">".strftime('%Y', strtotime((defined('CALENDAR_START')?CALENDAR_START:beginOfMounth(time()))))."</span>";
			break;
		case "mounth":
			print "<input type=\"hidden\" id=\"start-date-store\" value=\"".(defined('CALENDAR_START')?CALENDAR_START:beginOfMounth(time()))."\" />";
			print "<span class=\"action\" id=\"start-date-show\" onClick=\"return createCalendar('start-date-store', '%d.%m.%Y'); \">".strftime('%B %Y', strtotime((defined('CALENDAR_START')?CALENDAR_START:beginOfMounth(time()))))."</span>";
			break;
		case "week":
			print "<input type=\"hidden\" id=\"start-date-store\" value=\"".(defined('CALENDAR_START')?CALENDAR_START:beginOfWeek(time()))."\" />";
			print "<input type=\"hidden\" id=\"end-date-store\" value=\"".(defined('CALENDAR_END')?CALENDAR_END:endOfWeek(time()))."\" />";
			print "<span class=\"action\" id=\"start-date-show\" onClick=\"return createCalendar('start-date-store', '%d.%m.%Y'); \">".(defined('CALENDAR_START')?CALENDAR_START:beginOfWeek(time()))."</span>";
			print " - <span class=\"action\" id=\"end-date-show\" onClick=\"return createCalendar('end-date-store', '%d.%m.%Y'); \">".(defined('CALENDAR_END')?CALENDAR_END:endOfWeek(time()))."</span>";
			break;
		default:
			print "<input type=\"hidden\" id=\"start-date-store\" value=\"".(defined('CALENDAR_DAY')?CALENDAR_DAY:strftime('%d.%m.%Y', time()))."\" />";
			print "<span class=\"action\" id=\"start-date-show\" onClick=\"return createCalendar('start-date-store', '%d.%m.%Y'); \">".(defined('CALENDAR_DAY')?CALENDAR_DAY:strftime('%d.%m.%Y', time()))."</span>";
			break;
	}
?>
<img src="images/forward.png" class="action nontransparent" onClick="loadAJAX('content', '/index.php', '?module=<?php
	print getParentModule()."/".getChildModule()."/list";
	print "&content_only=true&ajax=true&update_uri=true&calendar_mode=".CALENDAR_MODE;
	print ((defined('CALENDAR_IDS'))?"&calendar_ids=".CALENDAR_IDS:"").((defined('CALENDAR_ID'))?"&calendar_id=".CALENDAR_ID:"");
	switch (CALENDAR_MODE) {
		case "year":
			print "&calendar_start=".yearNext(strtotime((defined('CALENDAR_START')?CALENDAR_START:beginOfYear(time()))));
			break;
		case "mounth":
			print "&calendar_start=".mounthNext(strtotime((defined('CALENDAR_START')?CALENDAR_START:beginOfMounth(time()))));
			break;
		case "week":
			print "&calendar_start=".weekNext(strtotime((defined('CALENDAR_START')?CALENDAR_START:beginOfWeek(time()))));
			break;
		default:
			print "&calendar_day=".dayNext(strtotime((defined('CALENDAR_DAY')?CALENDAR_DAY:strftime('%d.%m.%Y', time()))));
			break;
	}
?>'); " />
</caption>
<script type="text/javascript">
<!--
	function closeCalendar(cal) {
		cal.hide();
		cal.destroy();
		_calendar = null;
	}

	function dateChanged(cal, date) {
		if (cal.dateClicked) {
			var year = cal.date.getFullYear();
			var mounth = cal.date.getMonth();
			var day = cal.date.getDate();

			if (mounth.toString().length < 2) {
				mounth = "0"+(mounth + 1).toString();
			} else {
				mounth = (mounth + 1);
			}

			if (day.toString().length < 2) {
				day = "0"+day.toString();
			}

			if (cal.sel.id == 'start-date-store') {
				calendar_from = 'start';
			} else {
				calendar_from = 'end';
			}

			if (_ajaxURI) {
				uri = _ajaxURI.split('&');
				url = uri[0];
				for (i = 1; i < uri.length; i++) {
					if ((!uri[i].match(/^calendar_start/)) && (!uri[i].match(/^calendar_end/)) && (!uri[i].match(/^calendar_day/))) {
						url += '&'+uri[i];
					}
				}
				url += "&calendar_" + calendar_from + "=" + day + "." + mounth + "." + year;
			} else {
				url = "?module=<?= getParentModule()."/".getChildModule(); ?>/list&content_only=true&ajax=true&update_uri=true&calendar_mode=<?= CALENDAR_MODE; ?><?= ((defined('CALENDAR_IDS'))?"&calendar_ids=".CALENDAR_IDS:"").((defined('CALENDAR_ID'))?"&calendar_id=".CALENDAR_ID:""); ?>&calendar_" + calendar_from + "=" + day + "." + mounth + "." + year;
			}

			loadAJAX('content', '/index.php', url);
		}
	};

	function createCalendar(id, format) {
		var el = document.getElementById(id);

		if (_calendar != null) {
			_calendar.callCloseHandler();
		} else {
			cal = new Calendar(1, null, dateChanged, closeCalendar);
			cal.weekNumbers = false;
			cal.showsTime = false;
			cal.showsOtherMonths = true;
			cal.setRange(2000, 2100);
			cal.create();
			_calendar = cal;
			_calendar.setDateFormat(format);
			_calendar.parseDate(el.value);
			_calendar.sel = el;
			_calendar.showAtElement($('calendar-title'), "BC");
		}
		return false;
	}
//-->
</script>
