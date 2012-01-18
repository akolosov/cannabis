<?php
require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."common.php");
require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."functions.php");

$manager = new CalendarManager($engine, USER_CODE, prepareOptions());
$events = $manager->getAllEvents();

require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."calendars".DIRECTORY_SEPARATOR."renderer".DIRECTORY_SEPARATOR."renderer.php");
?>