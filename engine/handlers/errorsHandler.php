<?php

// $Id$

if (DEBUG_MODE) {
	$other_messages = array();
	$debug_messages = array();
	if (LOG_EXCEPTIONS || LOG_ERRORS || ADVANCED_DEBUG_MODE) {
		$error_messages = array();
		$exception_messages = array();
	}
}

ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);

if (USE_XDEBUG) {
    ini_set("xdebug.show_exception_trace", 1);
    ini_set("xdebug.show_local_vars", 1);
    ini_set("xdebug.show_mem_delta", 1);
}

if (ADVANCED_DEBUG_MODE) {
	error_reporting(E_ALL);
} else {
	error_reporting(E_ALL ^ E_STRICT ^ E_NOTICE ^ E_WARNING);
}

set_error_handler("errorsHandler");
set_exception_handler("exceptionsHandler");

function errorsHandler($errno, $errmsg, $file, $line) {
	if (LOG_ERRORS) {
		logError("#".$errno." in file ".$file." in line ".$line.", message: ".$errmsg);
	}
}

function exceptionsHandler($e) {
	if (LOG_EXCEPTIONS) {
		logExcept("in file ".$e->getFile()." in line ".$e->getLine().", message: ".$e->getMessage());
	}
}

function logDebug($a_str) {
	if (LOG_DEBUGS || DEBUG_MODE) {
		logMessage("DEBUG: ".$a_str);
	}
}

function logRuntime($a_str) {
	if (RUNTIME_DEBUG_MODE) {
		logMessage("RUNTIME: ".$a_str);
	}
}

function logExcept($a_str) {
	global $have_exceptions;

	logMessage("EXCEPTION: ".$a_str);
	if (!$have_exceptions) {
		$have_exceptions = true;
	}
}

function logError($a_str) {
	global $have_errors;

	logMessage("ERROR: ".$a_str);
	if (!$have_errors) {
		$have_errors = true;
	}
}

function logMessage($a_str) {
	global $debug_messages, $error_messages, $exception_messages, $other_messages;

	if (defined("LOG_FILE_NAME")) {
		$logfile = LOG_FILE_NAME."-[".(defined('USER_NAME')?USER_NAME:"COMMON")."]".(preg_match("/^debug.*/i", $a_str)?".debugs":"").(preg_match("/^error.*/i", $a_str)?".errors":"").(preg_match("/^except.*/i", $a_str)?".exceptions":"").".log";
		$logmessage = strftime("%H:%M:%S", time())." - ".$a_str." (IP: ".getenv("REMOTE_ADDR").")";
		$fp = fopen($logfile,  "a+");
		flock($fp, LOCK_SH);
		fwrite($fp, $logmessage."\n");

		if (!USE_XDEBUG) {
			$tracemessage = "-----------[ trace info begin ]------------\n".getTrace()."\n------------[ trace info end ]-------------\n\n";

			if ((preg_match("/^error.*/i", $a_str)) && (LOG_ERRORS)) {
				fwrite($fp, $tracemessage);
			}

			if ((preg_match("/^except.*/i", $a_str)) && (LOG_EXCEPTIONS)) {
				fwrite($fp, $tracemessage);
				if (EMAIL_ERRORS) {
					mail(EMAIL_FOR_ERRORS, "Серьёзная ошибка", "Пользователь: ".USER_NAME."\n"."Модуль: ".MODULE."\n"."Адрес: ".$_SERVER['REQUEST_URI']."\n\n".$logmessage."\n\n".$tracemessage);
				}
			}

			fclose($fp);
		}

		if ((preg_match("/^except.*/i", $a_str)) && (DISPLAY_EXCEPTIONS) && (!USE_XDEBUG)) {
			print "<div id=\"error\"><div class=\"error\">.-=[ ERROR OR EXCEPTION ]=-.</div><pre>".$logmessage."<br />";
			print "<font color=\"red\">".str_replace("\n", "<br />", str_replace(" ", "&nbsp;", $tracemessage))."</font></pre>";
			print "</div>";
		}

		if ((preg_match("/^error.*/i", $a_str)) && (DISPLAY_ERRORS) && (!USE_XDEBUG)) {
			print "<div id=\"error\"><div class=\"error\">.-=[ ERROR OR EXCEPTION ]=-.</div><pre>".$logmessage."<br />";
			print "<font color=\"red\">".str_replace("\n", "<br />", str_replace(" ", "&nbsp;", $tracemessage))."</font></pre>";
			print "</div>";
		}

		if (preg_match("/^debug.*/i", $a_str)) {
			if (preg_match("/\[sql\:/i", $a_str)) {
				$debug_messages[] = "<font color=\"green\">".$logmessage."</font>";
			} elseif (preg_match("/\[autoload\]/i", $a_str)) {
				$debug_messages[] = "<font color=\"blue\">".$logmessage."</font>";
			} elseif (preg_match("/\[(get|post|cookie)\]/i", $a_str)) {
				$debug_messages[] = "<font color=\"gray\">".$logmessage."</font>";
			} else {
				$debug_messages[] = $logmessage;
			}
		} elseif (preg_match("/^error.*/i", $a_str)) {
			$error_messages[] = $logmessage;
			if (!USE_XDEBUG) {
				$error_messages[] = "<font color=\"red\">".$tracemessage."</font>";
			}
		} elseif (preg_match("/^except.*/i", $a_str)) {
			$exception_messages[] = $logmessage;
			if (!USE_XDEBUG) {
				$exception_messages[] = "<font color=\"red\">".$tracemessage."</font>";
			}
		} else {
			$other_messages[] = (preg_match("/^runtime.*/i", $a_str)?"<font color=\"green\">".$logmessage."</font>":$logmessage);
		}
	}
}

function getTrace($a_level = 3) {
	$vDebug = debug_backtrace();
	$vFiles = array();
	for ($i = 0; $i < count($vDebug); $i++) {
		if ($i < $a_level) {
			continue;
		}
		$aFile = $vDebug[$i];

		$aFile['file']		= empty($aFile['file'])?"UNKNOWN":$aFile['file'];
		$aFile['line']		= empty($aFile['line'])?"UNKNOWN":$aFile['line'];
		$aFile['class']		= empty($aFile['class'])?"ROOT":$aFile['class'];
		$aFile['function']	= empty($aFile['function'])?"UNKNOWN":$aFile['function'];
		$aFile['type']		= empty($aFile['type'])?"void":$aFile['type'];

		$vFiles[] = "file: ".basename($aFile['file'])."; line: ".$aFile['line']."; function: ".$aFile['class']."->".$aFile['function']."()";
	}
	return implode("\n", $vFiles);
}
?>
