<?php
if ((defined('USER_NAME')) and (file_exists(CONFIG_PATH.DIRECTORY_DELIMITER.USER_NAME.".config.php"))) {
	require_once(CONFIG_PATH.DIRECTORY_DELIMITER.USER_NAME.".config.php");
}

// Некоторые JS удобства и красивости (эффекты, AJAX, drag'n'drop)
if ((defined('MODULE')) and (in_array(MODULE, $prototypeJS_required))) {
	define('USE_PROTOTYPE', true);
	define('USE_SCRIPTACULOUS', true);
	define('USE_WINDOWS', true);
	define('USE_WINDOWS_EXT', false);
} else {
	define('USE_PROTOTYPE', false);
	define('USE_SCRIPTACULOUS', false);
	define('USE_WINDOWS', false);
	define('USE_WINDOWS_EXT', false);
}

// Минимальный квант времени в минутах
if (!defined('DEFAULT_TIME_QUANT'))
	define('DEFAULT_TIME_QUANT', 30);

// Режим календаря по умолчанию
if (!defined('DEFAULT_CALENDAR_MODE'))
	define('DEFAULT_CALENDAR_MODE', 'day');

// Рабочее время с ...
if (!defined('BEGIN_WORK_TIME'))
	define('BEGIN_WORK_TIME', '08:00');

// Рабочее время по ...
if (!defined('END_WORK_TIME'))
	define('END_WORK_TIME', '17:00');

// Открытие по двойному клику на ячейку календаря
if (!defined('DOUBLE_CLICK_MODE'))
	define('DOUBLE_CLICK_MODE', true);

?>