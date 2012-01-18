<?php
if (USE_PROTOTYPE == true) {
	$js_files[]		= JAVASCRIPT_PATH.'/prototype/prototype.js';
}

if ((USE_PROTOTYPE == true) and (USE_SCRIPTACULOUS == true)) {
	$js_files[]		= JAVASCRIPT_PATH.'/scriptaculous/scriptaculous.js?load=effects';
}

if ((USE_PROTOTYPE == true) and (USE_SCRIPTACULOUS == true) and (USE_WINDOWS == true)) {
	$js_files[]		= JAVASCRIPT_PATH.'/prototype/window/window.js';
	$js_files[]		= JAVASCRIPT_PATH.'/prototype/tabs/control.tabs.js';
	$js_files[]		= JAVASCRIPT_PATH.'/prototype/validate/validate.js';

	if (USE_WINDOWS_EXT == true) {
		$js_files[]		= JAVASCRIPT_PATH.'/prototype/window/window_ext.js';
		$js_files[]		= JAVASCRIPT_PATH.'/prototype/window/window_effects.js';
	}

	$css_files['css/calendars.css']			= 'screen';
	$css_files['css/tabs.css']				= 'screen';
	$css_files['css/themes/default.css']	= 'screen';
	$css_files['css/themes/alert.css']		= 'screen';

	if ((RUNTIME_DEBUG_MODE) or (DEBUG_MODE)) {
		$js_files[]		= JAVASCRIPT_PATH.'/prototype/window/debug.js';
		$js_files[]		= JAVASCRIPT_PATH.'/prototype/window/extended_debug.js';

		$css_files['css/themes/debug.css']		= 'screen';
	}
}

if ((USE_PROTOTYPE == false) and (USE_SCRIPTACULOUS == false) and (USE_WINDOWS == false)) {
	$js_files[]		= JAVASCRIPT_PATH.'/cms/cms_packed.js';
}
?>