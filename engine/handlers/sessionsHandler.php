<?php

$parameters = array();
$parameters['totalqueriescount']	= 0;
$parameters['selectqueriescount']	= 0;
$parameters['insertqueriescount']	= 0;
$parameters['updatequeriescount']	= 0;
$parameters['deletequeriescount']	= 0;

foreach ($_GET as $param_name => $param) {
	logDebug("[GET] ".strtoupper($param_name)." = ".$param);

	if (strtoupper($param_name) == "USER_PASSWD") {
		if (trim($param) <> '') {
			$param = md5($param);
		}
	} elseif (strtoupper($param_name) == "X_USER_PASSWD") {
		if (trim($param) <> '') {
			$param = md5($param);
		}
	} elseif (strtoupper($param_name) == "CALENDAR_START") {
		if (defined('CALENDAR_MODE')) {
			if (CALENDAR_MODE == 'week') {
				$param = beginOfWeek(strtotime($param));
			} elseif (CALENDAR_MODE == 'mounth') {
				$param = beginOfMounth(strtotime($param));
			} elseif (CALENDAR_MODE == 'year') {
				$param = beginOfYear(strtotime($param));
			}
		}
	} elseif (strtoupper($param_name) == "CALENDAR_END") {
		if (defined('CALENDAR_MODE')) {
			if (CALENDAR_MODE == 'week') {
				$param = endOfWeek(strtotime($param));
			} elseif (CALENDAR_MODE == 'mounth') {
				$param = endOfMounth(strtotime($param));
			} elseif (CALENDAR_MODE == 'year') {
				$param = endOfYear(strtotime($param));
			}
		}
	}
	define(strtoupper($param_name), $param, true);
	$parameters[strtoupper($param_name)] = $param;
}

if ($_SERVER['REQUEST_METHOD'] == "POST") {
	foreach ($_POST as $param_name => $param) {
		logDebug("[POST] ".strtoupper($param_name)." = ".$param);
		
		if (strtoupper($param_name) == "USER_PASSWD") {
			if (trim($param) <> '') {
				$param = md5($param);
			}
		} elseif (strtoupper($param_name) == "X_USER_PASSWD") {
			if (trim($param) <> '') {
				$param = md5($param);
			}
		}
		define(strtoupper($param_name), $param, true);
		$parameters[strtoupper($param_name)] = $param;
	}
}

foreach ($_COOKIE as $cookie_name => $cookie) {
	logDebug("[COOKIE] ".strtoupper($cookie_name)." = ".$cookie);

	if (!defined(strtoupper($cookie_name))) {
		define(strtoupper($cookie_name), $cookie, true);
		$parameters[strtoupper($cookie_name)] = $cookie;
	}
}

if (!defined('MENU_VISIBILITY')) {
	define('MENU_VISIBILITY', 'true');
	setcookie('menu_visibility', 'true');
}

$connection = Doctrine_Manager::connection(DATABASE_DSN);

$connection->addRecordListener(new RecordsDebugger());
$connection->addListener(new QueriesDebugger());

$statusnames = $connection->execute('select name from cs_status order by id')->fetchAll();
foreach ($statusnames as $statusname) {
	$status_names[] = trim($statusname['name']);
}

$eventnames = $connection->execute('select name from cs_event order by id')->fetchAll();
foreach ($eventnames as $eventname) {
	$event_names[] = trim($eventname['name']);
}

$mimenames = $connection->execute('select name, ext from cs_mime where is_active = true order by id')->fetchAll();
foreach ($mimenames as $mimename) {
	$mime_names[] = trim($mimename['name']);
	$mime_exts[] = trim($mimename['ext']);
}

if ((!defined("USER_CODE") || !defined("USER_PASSWD")) && (MODULE <> "common/authorize")) {
	header("Location: /?module=common/authorize".(defined('MODULE')?"&backto=".urlencode($_SERVER['REQUEST_URI']):"")."&message=Авторизуйтесь пожалуйста!");
} else {
	if (defined("USER_CODE") && defined("USER_PASSWD") && USER_CODE > 0) {
		$engine = new Engine($connection, USER_CODE);
		if ((trim(USER_PASSWD) == trim($engine->getAccount()->getProperty('passwd'))) and ($engine->getAccount()->getProperty('is_active') == Constants::TRUE)) {
			define('USER_FOUND', true);
			define('USER_NAME', $engine->getAccount()->getProperty('name'));
			define('USER_DESCR', $engine->getAccount()->getProperty('description'));
			define('USER_MAIL', $engine->getAccount()->getProperty('email'));
			define('USER_GROUPCODE', $engine->getAccount()->getProperty('parent_id'));
			define('USER_GROUPNAME', $engine->getAccount()->getProperty('parentname'));
			define('USER_ABOVEGROUPCODE', (USER_GROUPCODE <> ''?$engine->getFieldByParams('id = '.USER_GROUPCODE, 'parent_id', 'CsAccount'):'NULL'));
			define('USER_ABOVEGROUPNAME', (USER_ABOVEGROUPCODE <> ''?$engine->getFieldByParams('id = '.USER_ABOVEGROUPCODE, 'name', 'CsAccount'):'NULL'));
			define('USER_DIVISIONS', implode(',', $engine->getAccount()->getDivisionsList()));
			define('USER_POSTS', implode(',', $engine->getAccount()->getPostsList()));
			define('USER_DIVISIONCODE', $engine->getAccount()->getMainDivision()->getProperty('id'));
			define('USER_DIVISIONNAME', $engine->getAccount()->getMainDivision()->getProperty('name'));
			if (isNULL($engine->getAccount()->getMainDivision()->getProperty('boss_id'))) {
				define('USER_BOSSCODE', USER_CODE);
			} else {
				define('USER_BOSSCODE', $engine->getAccount()->getMainDivision()->getProperty('boss_id'));
			}
			if (isNULL($engine->getAccount()->getMainDivision()->getProperty('bossname'))) {
				define('USER_BOSSNAME', USER_NAME);
			} else {
				define('USER_BOSSNAME', $engine->getAccount()->getMainDivision()->getProperty('bossname'));
			}
			define('USER_ABOVEDIVISIONCODE', (isNotNull($engine->getAccount()->getHigherDivision()->getProperty('id'))?$engine->getAccount()->getHigherDivision()->getProperty('id'):'NULL'));
			define('USER_ABOVEDIVISIONNAME', (isNotNull($engine->getAccount()->getHigherDivision()->getProperty('name'))?$engine->getAccount()->getHigherDivision()->getProperty('name'):'NULL'));
			define('USER_PERMISSION', $engine->getAccount()->getProperty('permission_id'));
			logMessage("пользователь \"".USER_NAME."\" авторизовался в системе");
	
			$user_permissions = $engine->getAccount()->getPermissions();

			logMessage("права пользователя инициализированы успешно (".$engine->getAccount()->getPermissionsName().")");
			
			setcookie("user_code", USER_CODE, time()+60*60*24*30);
			setcookie("user_name", USER_NAME);
			setcookie("user_mail", USER_MAIL);
			setcookie("user_passwd", USER_PASSWD);
		
			if (($_SERVER['REQUEST_METHOD'] <> "POST") and (!defined('MODULE'))) {
				define('MODULE', "common/authorized");
			}
			$engine->getTemplate()->initDefaults();
		} else {
			header("Location: /?module=common/authorize".(defined('MODULE')?"&backto=".urlencode($_SERVER['REQUEST_URI']):"")."&message=Авторизуйтесь пожалуйста!");
		}
	}
}
?>
