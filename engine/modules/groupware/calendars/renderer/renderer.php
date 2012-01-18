<?php
	print $engine->getFormManager()->useCalendars();
?>
<?php if ((defined('CALENDAR_ID')) and (isNotNULL($calendar))): ?>
<div class="caption action" onClick="document.location.href = '?module=<?= getParentModule()."/".getParentChildModule(); ?>/manager/list';">Календарь: <?= $calendar->getProperty('name'); ?> (<?= $calendar->getProperty('description') ?>)</div>
<?php endif; ?>
<table align="center" id="calendar" cellpadding="0">
<?php
	require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."calendars".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."navigation.php");
	
	if (file_exists(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."calendars".DIRECTORY_SEPARATOR."renderer".DIRECTORY_SEPARATOR."render.".CALENDAR_MODE.".php")) {
		require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."calendars".DIRECTORY_SEPARATOR."renderer".DIRECTORY_SEPARATOR."render.".CALENDAR_MODE.".php");
	}
?>
</table>
<script>
<!--
	var limit="<?= DEFAULT_TIME_QUANT ?>:00"

	if (document.images){
		var parselimit = limit.split(":");
		if (_limit = 1) {
			_limit = parselimit[0] * 60 + parselimit[1] * 1;
		}
	}

	function beginRefresh(){
		if (!document.images) {
			return false;
		}
		if (_limit == 1) {
			reloadAJAX('content', null, null);
		} else { 
			_limit -= 1;
			clearTimeout(_timeout);
			_timeout = setTimeout("beginRefresh()", 1000);
		}
	}

	clearTimeout(_timeout);
	window.onLoad = beginRefresh();
//-->
</script>