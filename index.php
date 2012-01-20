<?php
	define('ENGINE_NAME', "cannabis");
	define('ENGINE_DESCR', "Objectives, Resources and Processes System"); // Цели, Ресурсы и Система Процессов
	define('ENGINE_DESCR_SHORT', "ORP System");
	define('ENGINE_VERSION', "1.3.0.0");
	define('ENGINE_BUILD', "sativa");
	define('DEBUG_MODE', true);
	define('RUNTIME_DEBUG_MODE', DEBUG_MODE);
	define('ADVANCED_DEBUG_MODE', false); // Жуткая информация обо всех варнингах и всякой фигне
	
	# кофигурация системы
	require_once("config.php");
	setlocale(LC_ALL, "ru_RU.UTF-8");
	mb_internal_encoding(DEFAULT_CHARSET);
	
	# дополнительные параметры и общие функции
	require_once(COMMON_MODULES_PATH."/options.php");
	require_once(HANDLERS_PATH."/commonFunctions.php");
	
	# библиотеки Doctrine ORM
	if (USE_FULL_DOCTRINE) {
		require_once(LIBRARY_PATH."/doctrine/Doctrine.php");
	} else {
		require_once(LIBRARY_PATH."/doctrine/Doctrine.PgSQL.compiled.php");
	}
	
	# обработчики запросов, отладчик и пр.
	require_once(HANDLERS_PATH."/errorsHandler.php");
	require_once(HANDLERS_PATH."/debugsHandler.php");
	require_once(HANDLERS_PATH."/sessionsHandler.php");
	
	# дополнительная кофигурация системы
	require_once("override.config.php");
	# дополнительные параметры системы
	require_once(COMMON_MODULES_PATH."/override.options.php");
?>
<?php if (file_exists("stop.me")): // stop.me exists ?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html>
	<head>
	  <meta http-equiv="pragma" content="no-cache">
	  <meta http-equiv="cache-control" content="no-cache">
	  <meta http-equiv="content-type" content="text/html; charset=<?= DEFAULT_CHARSET; ?>">
	  <link rel="icon" href="/images/favicon.png" type="image/x-png" />
	  <link rel="shortcut icon" href="/images/favicon.png" type="image/x-png" />
	<?php foreach ($css_files as $css_file => $css_media): ?>
	<? if (defined('MEDIA')): ?>
	  <? if ($css_media == MEDIA): ?> 
	  <link rel="stylesheet" href="<?= $css_file; ?>" type="text/css" media="screen" />
	  <link rel="stylesheet" href="<?= $css_file; ?>" type="text/css" media="<?= $css_media; ?>" />
	  <? endif; ?>
	<? else: ?>
	  <link rel="stylesheet" href="<?= $css_file; ?>" type="text/css" media="<?= $css_media; ?>" />
	<? endif; ?>
	<?php endforeach; ?>
	<?php require_once(HANDLERS_PATH."/titlesHandler.php"); ?>
	</head>
	<body>
		<div style=" border : 2px red; text-align : center; font-weight : bold; color : red; font-size: 15pt; ">
			<pre><br />ВНИМАНИЕ! На сервере ведуться технические работы!<br />За дополнительной информацией обращайтесь к Системному Администратору.<br />Спасибо за понимание!</pre>
		</div>
	</body>
	</html>
<?php else: // stop.me not exists ?>
	<?php if (!defined('AJAX')): ?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html>
	<head>
	<script type="text/javascript">
	<!--
		var oldDateFrom	= '<?= ((defined('X_PERIOD_FROM') and (trim(X_PERIOD_FROM) <> ''))?X_PERIOD_FROM:beginOfMounth()); ?>';
		var oldDateTo	= '<?= ((defined('X_PERIOD_TO') and (trim(X_PERIOD_TO) <> ''))?X_PERIOD_TO:endOfMounth()); ?>';
	//-->
	</script>
	  <meta http-equiv="pragma" content="no-cache">
	  <meta http-equiv="cache-control" content="no-cache">
	  <meta http-equiv="content-type" content="text/html; charset=<?= DEFAULT_CHARSET; ?>">
	  <link rel="icon" href="/images/favicon.png" type="image/x-png" />
	  <link rel="shortcut icon" href="/images/favicon.png" type="image/x-png" />
	<?php foreach ($css_files as $css_file => $css_media): ?>
	<? if (defined('MEDIA')): ?>
	  <? if ($css_media == MEDIA): ?> 
	  <link rel="stylesheet" href="<?= $css_file; ?>" type="text/css" media="screen" />
	  <link rel="stylesheet" href="<?= $css_file; ?>" type="text/css" media="<?= $css_media; ?>" />
	  <? endif; ?>
	<? else: ?>
	  <link rel="stylesheet" href="<?= $css_file; ?>" type="text/css" media="<?= $css_media; ?>" />
	<? endif; ?>
	<?php endforeach; ?>
	<?php if ((defined('MODULE')) and (!in_array(MODULE, $no_JS_required))): ?>
	<?php foreach ($js_files as $js_file): ?>
	  <script type="text/javascript" src="<?= $js_file; ?>"></script>
	<?php endforeach; ?>
	<?php else: ?>
	  <script type="text/javascript" src="<?= JAVASCRIPT_PATH.'/common/common.js'; ?>"></script>
	<?php endif; ?>
	<?php require_once(HANDLERS_PATH."/titlesHandler.php"); ?>
	</head>
	<body<?= ((defined('CONTENT_ONLY'))?" style=' margin-top: 5px !important; '":""); ?>>
	<? elseif (defined('UPDATE_URI')): ?>
	<script type="text/javascript">
	<!--
		_ajaxURI = "<?= $_SERVER['QUERY_STRING'] ?>";
	-->
	</script>
	<?php endif; // (!defined('AJAX')) ?>
	<? if (defined('MEDIA') and (MEDIA == 'print')): ?>
	<?
		print "<div id=\"print\"".((ORIENTATION == 'landscape')?" class=\"landscape\"":" class=\"portrait\"").">";
		require_once(MODULES_PATH."/".MODULE.".php");
		print "</div>";
	?>
	<? elseif (defined('CONTENT_ONLY')): ?>
	<div id="loading_content_<?= $start_time; ?>" class="loading"><img src="images/spinner.gif" /><span class=" very_small blink bold black "><br />ЗАГРУЗКА ДАННЫХ...</span></div>
	<div style=" left: -91px; top: -91px; visibility: hidden; " id="tooltip"></div>
	<?php
	if ((defined("MODULE")) && (file_exists(MODULES_PATH."/".MODULE.".php"))) {
		require_once(MODULES_PATH."/".MODULE.".php");
	}
	?>
	<? else: ?>
	<div id="loading_content" class="loading"><img src="images/spinner.gif" /><span class=" very_small blink bold black "><br />ЗАГРУЗКА ДАННЫХ...</span></div>
	<div style=" left: -91px; top: -91px; visibility: hidden; " id="tooltip"></div>
	<div id="container">
	<div id="header" style=" font-weight: bolder; text-align: center; ">
	<?php if (defined('USER_NAME')): ?>[Пользователь: <?= USER_NAME; ?>]&nbsp;-&nbsp;<?php endif; ?>[сегодня: <?= strftime("%d.%m.%Y"); ?>]&nbsp;-
	<?php
		if (MODULE == 'common/authorize') {
			print "[Общие/Авторизация]";
		} elseif (MODULE == 'common/authorized') {
			print "[Общие/Описание]";
		} elseif (MODULE == 'common/forum') {
			print "[Общие/Форум]";
		} else {
			print "[".$user_permissions[getParentModule()]['display']."/".$user_permissions[getParentModule()][getParentChildModule()]['display'].($user_permissions[getParentModule()][getModuleByLevel(3)]['display']?"/".$user_permissions[getParentModule()][getModuleByLevel(3)]['display']:"")."]";
		}
	?>
	</div>
	<div id="navigation">
	<?php 
		if (file_exists(MODULES_PATH."/help/".MODULE.".help.php")) {
			print "<img src=\"images/help.png\" style=\" float: right; z-index: 1000; \"  onClick=\"hideIt('help_data')\" title=\"Помощь!\" />";
			print "<div class=\"help_info\" id=\"help_data\"><img src=\"images/close.png\" style=\" float: right; \"  onClick=\"hideIt('help_data')\" title=\"Закрыть помощь\" />";
			require_once(MODULES_PATH."/help/".MODULE.".help.php");
			print "</div>";
		}
		require_once(COMMON_MODULES_PATH."/navigate.php"); ?></div>
	<?php
	if ((defined("MODULE")) && (file_exists(MODULES_PATH."/".MODULE.".php"))) {
		print "<div id=\"body\">";
		if (MODULE <> "common/authorize") {
			print "<div id=\"menu\" style=\" ".((MENU_VISIBILITY == "true")?" display: block; visibility: visible; ":" display: none; visibility: hidden; ")." \">";
			require_once(COMMON_MODULES_PATH."/menu.php");
			print "</div>";
		}
		print "<div id=\"content\" ".((MODULE <> "common/authorize")?((MENU_VISIBILITY == "true")?"style=\" width : 84%; \"":"style=\" width : 99%; \""):"style=\" width : 99% !important; overflow : hidden !important; \"")." ".(MODULE <> "common/forum"?"":"style=\" height : 1220px; \"").">";
		require_once(MODULES_PATH."/".MODULE.".php");
		print "</div>";
		print "</div>";
	} else {
		print "<div id=\"content\" style=\" width : 100% !important; \">";
		require_once(COMMON_MODULES_PATH."/authorize.php");
		print "</div>";
	}
	?>
	<div id="footer">
	<h6 class="very_small"><?= printStatistics(); ?></h6>
	</div>
	</div>
	<?php if (!defined('AJAX')): ?>
		<?php if (RUNTIME_DEBUG_MODE): ?>
		<div id="other_log" onClick="hideIt('other_log'); return true;"><?php foreach ($other_messages as $message) {
			print str_replace("\n", "<br />", $message)."<br />";
		}
		?></div>
		<?php endif; ?>
		<?php if (DEBUG_MODE): ?>
		<div id="debug_log" onClick="hideIt('debug_log'); return true;"><?php foreach ($debug_messages as $message) {
			print str_replace("\n", "<br />", $message)."<br />";
		}
		?></div>
		<?php endif; ?>
		<?php if (LOG_ERRORS || $have_errors): ?>
		<div id="error_log" onClick="hideIt('error_log'); return true;"><?php foreach ($error_messages as $message) {
			print str_replace("\n", "<br />", $message)."<br />";
		}
		?></div>
		<?php endif; ?>
		<?php if (LOG_EXCEPTIONS || $have_exceptions): ?>
		<div id="except_log" onClick="hideIt('except_log'); return true;"><?php foreach ($exception_messages as $message) {
			print str_replace("\n", "<br />", $message)."<br />";
		}
		?></div>
		<?php endif; ?>
		<a title="<?= ENGINE_NAME.' v'.ENGINE_VERSION.'/'.ENGINE_BUILD; ?>" onClick="hideIt('debug_info'); return true;"	style=" cursor: pointer; "><img id="logo" src="images/blank.gif" /></a>
		<ul class="infomenu" id="debug_info">
			<?php if (RUNTIME_DEBUG_MODE): ?>
			<li class="menuitem"><a class="button"
				onClick="hideIt('other_log'); return true;" style=" cursor: pointer; "><img
				src="images/info.png" align="top" /> others log</a></li>
				<?php endif; ?>
				<?php if (DEBUG_MODE || $have_exceptions || $have_errors): ?>
				<?php if (DEBUG_MODE): ?>
			<li class="menuitem"><a class="button"
				onClick="hideIt('debug_log'); return true;" style=" cursor: pointer; "><img
				src="images/warning.png" align="top" /> debugs log</a></li>
				<?php endif; ?>
				<?php if (LOG_ERRORS || $have_errors): ?>
			<li class="menuitem"><a class="button"
				onClick="hideIt('error_log'); return true;" style=" cursor: pointer; "><img
				src="images/error.png" align="top" /> errors log</a></li>
				<?php endif; ?>
				<?php if (LOG_EXCEPTIONS || $have_exceptions): ?>
			<li class="menuitem"><a class="button"
				onClick="hideIt('except_log'); return true;"
				style=" cursor: pointer; "><img src="images/error_sign.png"
				align="top" /> exceptions log</a></li>
				<?php endif; ?>
		</ul>
			<?php endif; ?>
		<? endif; ?>
	<?php endif; // (!defined('AJAX')) ?>
	<script>
	<!--
	<? if (defined('CONTENT_ONLY')): ?>
		tooltip.d();
	<? endif; ?>
		hideNow('loading_content');
		hideNow('loading_content_<?= $start_time; ?>');
	//-->
	</script>
	<?php if (!defined('AJAX')): ?>
	</body>
	</html>
	<?php endif; // (!defined('AJAX')) ?>
<?php endif; // stop.me not exists ?>
