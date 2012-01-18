<?php
if (defined('CALENDAR_ID')) {
	require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."common.php");
	require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."functions.php");

	$calendar = new Calendar($engine, CALENDAR_ID, prepareOptions());
	$events = $calendar->getAllEvents();
	
	require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."calendars".DIRECTORY_SEPARATOR."renderer".DIRECTORY_SEPARATOR."renderer.php");
}
?>