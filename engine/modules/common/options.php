<?php

// some usefull defines

$actions_icons			= array("start", "action", "switch", "split", "join", "stop", "standalone", "info");

$calendar_options		= array('lang'		=> 'ru',
								'theme'		=> 'system',
								'stripped'	=> false);

$status_names			= array();

$event_names			= array();

$mime_names				= array();

$mime_exts				= array();

$css_files				= array(
								'css/common.css' => "screen",
								'css/printer.css' => "print"
								);

$js_files				= array(
								JAVASCRIPT_PATH.'/common/detect.js',
								JAVASCRIPT_PATH.'/common/common.js',
								JAVASCRIPT_PATH.'/tooltips/tooltips.js'
								);

$prototypeJS_required	= array(
								'groupware/calendars/list',
								'groupware/calendars/events/list',
								'groupware/calendars/events/edit'
								);

$no_JS_required			= array('groupware/calendars/misc/legend');
?>