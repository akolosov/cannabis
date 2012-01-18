<?php

// $Id$

$start_time = getMicroTime();

function __autoload($class) {
	logDebug("[AUTOLOAD] load class \"".$class."\"");

	if (USE_FULL_DOCTRINE) {
		if (preg_match("/^Doctrine.*$/", $class)) {
			Doctrine::autoload($class);
		}
	}

	if (preg_match("/^Cs.*$/", $class)) {
		$filename = MODELS_PATH."/".$class.".class.php";
		if (file_exists($filename)) {
			require_once($filename);
		}
	} else {
		if (preg_match("/^Process.*$/", $class)) {
			$filename = CLASSES_PATH."/process/".$class.".class.php";
		} elseif (preg_match("/^Project.*$/", $class)) {
			$filename = CLASSES_PATH."/project/".$class.".class.php";
		} elseif (preg_match("/^Chrono.*$/", $class)) {
			$filename = CLASSES_PATH."/chrono/".$class.".class.php";
		} elseif (preg_match("/^TransportProtocol.*$/", $class)) {
			$filename = CLASSES_PATH."/transport/protocol/".$class.".class.php";
		} elseif (preg_match("/^TransportProtocol.*$/", $class)) {
			$filename = CLASSES_PATH."/transport/protocol/".$class.".class.php";
		} elseif (preg_match("/^Transport.*$/", $class)) {
			$filename = CLASSES_PATH."/transport/".$class.".class.php";
		} elseif (preg_match("/^Calendar.*$/", $class)) {
			$filename = CLASSES_PATH."/calendar/".$class.".class.php";
		} elseif (preg_match("/^Contact.*$/", $class)) {
			$filename = CLASSES_PATH."/contact/".$class.".class.php";
		} elseif (preg_match("/^Message.*$/", $class)) {
			$filename = CLASSES_PATH."/message/".$class.".class.php";
		} elseif (preg_match("/^File.*$/", $class)) {
			$filename = CLASSES_PATH."/file/".$class.".class.php";
		} elseif (preg_match("/^FCKeditor$/", $class)) {
			$filename = JAVASCRIPT_PATH."/fckeditor/fckeditor.php";
		} elseif (preg_match("/^Date_.*$/", $class)) {
			$filename = LIBRARY_PATH."/".str_replace("_", "/", $class).".php";
		} else {
			$filename = CLASSES_PATH."/common/".$class.".class.php";
		}
		if (file_exists($filename)) {
			require_once($filename);
		}
	}
}

function setLocation($location) {
	print "\n<script><!--\n"; 
	print "  document.location.href='".$location."';\n"; 
	print "--></script>\n";  

}

function arrayToURI($params = array()) {
	$result = '';
	foreach ($params as $key => $value) {
		$result .= "&".$key."=".$value;
	}
	return $result;
}

function printStatistics() {
	global $start_time, $parameters; 

	$msg = '[Время генерации: '.getProcessTime($start_time).'сек] [Использовано памяти: '.round((memory_get_peak_usage()/1024)/1024, 2).'Мб] [SELECT-запросов: '.$parameters['selectqueriescount'].'] [INSERT-запросов: '.$parameters['insertqueriescount'].'] [UPDATE-запросов: '.$parameters['updatequeriescount'].'] [DELETE-запросов: '.$parameters['deletequeriescount'].'] [Всего запросов: '.$parameters['totalqueriescount'].']';
	print $msg;	logMessage($msg);	
}

function getURIParams($child_as_instance = false) {
	$result .= (defined('PERMISSION_ID')?"&permission_id=".PERMISSION_ID:"");
	$result .= (defined('PROCESS_ID')?"&process_id=".PROCESS_ID:"");
	$result .= (defined('ACTION_ID')?"&action_id=".ACTION_ID:"");
	$result .= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"");
	if (($child_as_instance) and (defined('CHILD_PROCESS_INSTANCE_ID'))) {
		$result .= (defined('PROCESS_INSTANCE_ID')?"&process_instance_id=".CHILD_PROCESS_INSTANCE_ID:"");
	} else {
		$result .= (defined('PROCESS_INSTANCE_ID')?"&process_instance_id=".PROCESS_INSTANCE_ID:"");
	}
	if (!$child_as_instance) {
		$result .= (defined('CHILD_PROCESS_INSTANCE_ID')?"&child_process_instance_id=".CHILD_PROCESS_INSTANCE_ID:"");
	}
	$result .= (defined('PROJECT_INSTANCE_ID')?"&project_instance_id=".PROJECT_INSTANCE_ID:"");
	$result .= (defined('DIRECTORY_ID')?"&directory_id=".DIRECTORY_ID:"");
	$result .= (defined('RECORD_ID')?"&record_id=".RECORD_ID:"");
	$result .= (defined('TOPIC_ID')?"&topic_id=".TOPIC_ID:"");
	$result .= (defined('DOCUMENT_ID')?"&document_id=".DOCUMENT_ID:"");
	$result .= (defined('FILE_ID')?"&file_id=".FILE_ID:"");
	$result .= (defined('MESSAGE_ID')?"&message_id=".MESSAGE_ID:"");
	$result .= (defined('CONTACTLIST_ID')?"&contactlist_id=".CONTACTLIST_ID:"");
	$result .= (defined('CONTACT_ID')?"&contact_id=".CONTACT_ID:"");
	$result .= (defined('CALENDAR_ID')?"&calendar_id=".CALENDAR_ID:"");
	$result .= (defined('CALENDAR_MODE')?"&calendar_mode=".CALENDAR_MODE:"");
	$result .= (defined('CALENDAR_DAY')?"&calendar_day=".CALENDAR_DAY:"");
	$result .= (defined('CALENDAR_START')?"&calendar_start=".CALENDAR_START:"");
	$result .= (defined('CALENDAR_END')?"&calendar_end=".CALENDAR_END:"");
	$result .= (defined('CALENDAR_IDS')?"&calendar_ids=".CALENDAR_IDS:"");
	$result .= (defined('EVENT_ID')?"&event_id=".EVENT_ID:"");

	return $result;
}

function getMicroTime(){
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}

function getProcessTime($a_start_time){
	$t = (getMicroTime() - $a_start_time);
	return substr($t, 0, 5);
}

function prepareForView(array $array = array()) {
	foreach (array_keys($array) as $key) {
		$array[$key] = htmlentities($array[$key], ENT_COMPAT, DEFAULT_CHARSET);
	}
	return $array;
}

function prepareForSave($str) {
	return stripslashes(html_entity_decode($str, ENT_COMPAT, DEFAULT_CHARSET));
}

function prepareForShow($str) {
	return preg_replace("/^(.*)(\.\d+)$/", "\$1", $str);
}

function getModule() {
	return preg_replace("/\/(list|edit)/", "", MODULE);
}

function getParentModule() {
	return getModuleByLevel(1);
}

function getChildModule() {
	if (getModuleChildCount() == 2) {
		return getModuleByLevel(2);
	} elseif (getModuleChildCount() == 3) {
		return getModuleByLevel(2)."/".getModuleByLevel(3);
	} elseif (getModuleChildCount() == 4) {
		return getModuleByLevel(2)."/".getModuleByLevel(3)."/".getModuleByLevel(4);
	}
}

function getParentChildModule() {
	return getModuleByLevel(2);
}

function getModuleByLevel($level) {
	$matches = array();
	if (preg_match_all("/(\w+)\/?/", MODULE, $matches)) {
		return $matches[1][$level-1];
	} else {
		return "";
	}
}

function getModuleChildCount() {
	return substr_count(MODULE, DIRECTORY_SEPARATOR);
}

function str_pad_html($strInput = "", $intPadLength, $strPadString = "&nbsp;&nbsp;", $intPadType = STR_PAD_RIGHT) {
	if (strlen(trim(strip_tags($strInput))) < intval($intPadLength)) {
		switch ($intPadType) {
			// STR_PAD_LEFT
			case 0:
				$offsetLeft = intval($intPadLength - strlen(trim(strip_tags($strInput))));
				$offsetRight = 0;
				break;
				// STR_PAD_RIGHT
			case 1:
				$offsetLeft = 0;
				$offsetRight = intval($intPadLength - strlen(trim(strip_tags($strInput))));
				break;
				// STR_PAD_BOTH
			case 2:
				$offsetLeft = intval(($intPadLength - strlen(trim(strip_tags($strInput)))) / 2);
				$offsetRight = round(($intPadLength - strlen(trim(strip_tags($strInput)))) / 2, 0);
				break;
				// STR_PAD_RIGHT
			default:
				$offsetLeft = 0;
				$offsetRight = intval($intPadLength - strlen(trim(strip_tags($strInput))));
				break;
		}

		$strPadded = str_repeat($strPadString, $offsetLeft) . $strInput . str_repeat($strPadString, $offsetRight);
		unset($strInput, $offsetLeft, $offsetRight);

		return $strPadded;
	} else {
		return $strInput;
	}
}

function haveChilds($tableName, $id) {
	if ($id) {
		return (getChildsCount($tableName, $id) > 0);
	} else {
		return false;
	}
}

function isChildOf($tableName, $child_id, $parent_id) {
	global	$connection;

	$result = $connection->execute("select is_child_of('".$tableName."', ".$child_id.", ".$parent_id.") as result")->fetch();
	return $result['result'];
}

function isParentOf($tableName, $parent_id, $child_id) {
	global	$connection;

	$result = $connection->execute("select is_parent_of('".$tableName."', ".$parent_id.", ".$child_id.") as result")->fetch();
	return $result['result'];
}

function getChildsCount($tableName, $id) {
	global	$connection;

	if ($id) {
		$count = $connection->execute("select count(id) as count from ".$tableName." where parent_id = ".$id)->fetch();
		return $count['count'];
	} else {
		return 0;
	}
}

function isNotNULL($var) {
	if (is_array($var)) {
		return (count($var) > 0);
	} else {
		return ((!is_null($var)) and (trim(str_replace("'", "", str_replace('"', '', stripslashes($var)))) <> ''));
	}
}

function isNULL($var) {
	return (!isNotNULL($var));
}


function encodeEMail($in_str, $charset = DEFAULT_CHARSET) {
	$out_str = $in_str;

	if ($out_str && $charset) {

		// define start delimimter, end delimiter and spacer
		$end = "?=";
		$start = "=?" . $charset . "?B?";
		$spacer = $end . "\r\n " . $start;

		// determine length of encoded text within chunks
		// and ensure length is even
		$length = 75 - strlen($start) - strlen($end);
		$length = floor($length/2) * 2;

		// encode the string and split it into chunks
		// with spacers after each chunk
		$out_str = base64_encode($out_str);
		$out_str = chunk_split($out_str, $length, $spacer);

		// remove trailing spacer and
		// add start and end delimiters
		$spacer = preg_quote($spacer);
		$out_str = preg_replace("/" . $spacer . "/", "", $out_str);
		$out_str = $start . $out_str . $end;
	}
	return $out_str;
}

function isUTF8($str) {
	return preg_match("/([\x09\x0A\x0D\x20-\x7E]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})*/x", $str);
}

function mb_str_ireplace($co, $naCo, $wCzym) {
	$wCzymM	= mb_strtolower($wCzym);
	$coM	= mb_strtolower($co);
	$offset	= 0;

	while(($poz = mb_strpos($wCzymM, $coM, $offset)) !== false) {
		$offset = $poz + mb_strlen($naCo);
		$wCzym = mb_substr($wCzym, 0, $poz). $naCo .mb_substr($wCzym, $poz+mb_strlen($co));
	$wCzymM = mb_strtolower($wCzym);
	}

	return $wCzym;
}

function stripMacros($str) {
	return preg_replace('/(%%[а-яa-z0-9\.\s\-_\[\]]*%%)/ui', '', $str);
}

function stripNonAlpha($str) {
	return preg_replace('/[^а-яa-z0-9]/ui', '', $str);
}

function storeToCache($value_id = 0, $file = NULL) {
	global $connection;

	if (($value_id > 0) and isNotNULL($file)) {
		if (file_exists($file)) {
			return true;
		} else {
			$blob = $connection->execute('select blob from cs_blob where value_id = '.$value_id)->fetch();
			return file_put_contents($file, base64_decode($blob['blob']));
		}
	} else {
		return false;
	}
}

function storeChronoToCache($value_id = 0, $file = NULL) {
	global $connection;

	if (($value_id > 0) and isNotNULL($file)) {
		if (file_exists($file)) {
			return true;
		} else {
			$blob = $connection->execute('select blob from cs_chrono_blob where value_id = '.$value_id)->fetch();
			return file_put_contents($file, base64_decode($blob['blob']));
		}
	} else {
		return false;
	}
}

function storeDirectoryToCache($value_id = 0, $file = NULL) {
	global $connection;

	if (($value_id > 0) and isNotNULL($file)) {
		if (file_exists($file)) {
			return true;
		} else {
			$blob = $connection->execute('select blob from cs_directory_blob where value_id = '.$value_id)->fetch();
			return file_put_contents($file, base64_decode($blob['blob']));
		}
	} else {
		return false;
	}
}

function storePublicToCache($file_id = 0, $file = NULL) {
	global $connection;

	if (($file_id > 0) and isNotNULL($file)) {
		if (file_exists($file)) {
			return true;
		} else {
			$blob = $connection->execute('select blob from cs_public_blob where file_id = '.$file_id)->fetch();
			return file_put_contents($file, base64_decode($blob['blob']));
		}
	} else {
		return false;
	}
}

function storeFileToCache($file_id = 0, $file = NULL) {
	global $connection;

	if (($file_id > 0) and isNotNULL($file)) {
		if (file_exists($file)) {
			return true;
		} else {
			$blob = $connection->execute('select blob from cs_file_blob where file_id = '.$file_id)->fetch();
			return file_put_contents($file, base64_decode($blob['blob']));
		}
	} else {
		return false;
	}
}

function storeMessageBlobToCache($blob_id = 0, $file = NULL) {
	global $connection;

	if (($blob_id > 0) and isNotNULL($file)) {
		if (file_exists($file)) {
			return true;
		} else {
			$blob = $connection->execute('select blob from cs_message_blob where id = '.$blob_id)->fetch();
			return file_put_contents($file, base64_decode($blob['blob']));
		}
	} else {
		return false;
	}
}

function storeMessageBlobsToCache($message_id = 0, $path = NULL) {
	global $connection;

	if (($message_id > 0) and isNotNULL($path)) {
		$blobs = $connection->execute('select blob from cs_message_blob where message_id = '.$message_id)->fetchAll();
		foreach ($blob as $blob) {
			if (!file_exists(addbs($path).$blob['name'])) {
				file_put_contents(addbs($path).$blob['name'], base64_decode($blob['blob']));
			}
		}
		return true;
	} else {
		return false;
	}
}

function beginOfYear($date = NULL) {
	return preg_replace("/^(\d\d)/", "01", preg_replace("/\.(\d\d)\./", ".01.", strftime("%d.%m.%Y", (is_null($date)?time():$date))));
}

function endOfYear($date = NULL) {
	return preg_replace("/^(\d\d)/", "31", preg_replace("/\.(\d\d)\./", ".12.", strftime("%d.%m.%Y", (is_null($date)?time():$date))));
}

function beginOfMounth($date = NULL) {
	return preg_replace("/^(\d\d)/", "01", strftime("%d.%m.%Y", (is_null($date)?time():$date)));
}

function endOfMounth($date = NULL) {
	$date = (is_null($date)?time():$date);
	return preg_replace("/^(\d\d)/", ((strftime("%m", $date) == "02")?((((strftime("%Y", $date) % 4) == 0) && (((strftime("%Y", $date) % 400) != 0) || ((strftime("%Y", $date) % 100) != 0)))?"29":"28"):(in_array(strftime("%m", $date), array('04', '06', '09', '11'))?"30":"31")), strftime("%d.%m.%Y", $date));
}

function beginOfWeek($date = NULL) {
	$date = (is_null($date)?time():$date);
	$dow = strftime('%u', $date);
	return strftime("%d.%m.%Y", ($date - (((6 + $dow) % 7)*86400)));
}


function endOfWeek($date = NULL) {
	$date = (is_null($date)?time():$date);
	$dow = strftime('%u', $date);
	return strftime("%d.%m.%Y", ($date + (((7 - $dow) % 7)*86400)));
}

function dayNext($date = NULL) {
	$date = (is_null($date)?time():$date);

	return strftime("%d.%m.%Y", ($date + 86400));
}

function dayPrev($date = NULL) {
	$date = (is_null($date)?time():$date);

	return strftime("%d.%m.%Y", ($date - 86400));
}

function weekNext($date = NULL) {
	$date = (is_null($date)?time():$date);

	return strftime("%d.%m.%Y", ($date + (7 * 86400)));
}

function weekPrev($date = NULL) {
	$date = (is_null($date)?time():$date);

	return strftime("%d.%m.%Y", ($date - (7 * 86400)));
}

function mounthNext($date = NULL) {
	$date = (is_null($date)?time():$date);

	$mounth = strftime("%m", $date);
	$year = strftime("%Y", $date);

	if ($mounth == 12) {
		$mounth = '01';
		$year++;
	} else {
		$mounth++;
	}

	return strftime("%d.", $date).((strlen($mounth) == 1)?"0".$mounth:$mounth).".".$year;
}

function mounthPrev($date = NULL) {
	$date = (is_null($date)?time():$date);

	$mounth = strftime("%m", $date);
	$year = strftime("%Y", $date);

	if ($mounth == 1) {
		$mounth = '12';
		$year--;
	} else {
		$mounth--;
	}

	return strftime("%d.", $date).((strlen($mounth) == 1)?"0".$mounth:$mounth).".".$year;
}

function yearNext($date = NULL) {
	$date = (is_null($date)?time():$date);

	return strftime("%d.%m.%Y", strtotime(strftime("%d.%m.", $date).(strftime("%Y", $date) + 1)));
}

function yearPrev($date = NULL) {
	$date = (is_null($date)?time():$date);

	return strftime("%d.%m.%Y", strtotime(strftime("%d.%m.", $date).(strftime("%Y", $date) - 1)));
}

function adjustDateTime($datetime = NULL) {
	if (is_null($datetime)) {
		$datetime = time();
	}
	$date	= strftime('%d.%m.%Y', $datetime);
	$hour	= strftime('%H', $datetime);
	$minute	= strftime('%M', $datetime);

	if (($minute >= '00') and ($minute <= '07')) {
		$minute = '00';
	} elseif ((($minute >= '08') and ($minute <= '15')) or (($minute >= '16') and ($minute <= '22'))) {
		$minute = '15';
	} elseif ((($minute >= '23') and ($minute <= '30')) or (($minute >= '31') and ($minute <= '37'))) {
		$minute = '30';
	} elseif ((($minute >= '38') and ($minute <= '45')) or (($minute >= '46') and ($minute <= '52'))) {
		$minute = '45';
	} if (($minute >= '53') and ($minute <= '59')) {
		$minute = '00';
		if ($hour == "23") {
			$hour = "00";
			$date = strftime('%d.%m.%Y', strtotime($date)+86400);
		} else {
			$hour++;
		}
	}
	return strtotime($date." ".$hour.":".$minute.":00");
}

function dateDiff($startdate, $enddate) {
	if (($startdate) and ($enddate)) {
		return ($enddate - $startdate);
	} else {
		return false;
	}
}

function formatedInterval($interval_len) {
	if ($interval_len) {
		if ($interval_len < 0) {
			$interval_len = $interval_len * -1;
		}
		if ($interval_len > 31536000) {
			$result .= sprintf("%sГ ", bcdiv($interval_len, 31536000));
			$interval_len = ($interval_len - (31536000*(bcdiv($interval_len, 31536000))));
			$result .= sprintf("%sМ ", bcdiv($interval_len, 2592000));
			$interval_len = ($interval_len - (2592000*(bcdiv($interval_len, 2592000))));
			$result .= sprintf("%sн ", bcdiv($interval_len, 604800));
			$interval_len = ($interval_len - (604800*(bcdiv($interval_len, 604800))));
			$result .= sprintf("%sд ", bcdiv($interval_len, 86400));
			$interval_len = ($interval_len - (86400*(bcdiv($interval_len, 86400))));
			$result .= sprintf("%sч ", bcdiv($interval_len, 3600));
			$interval_len = ($interval_len - (3600*(bcdiv($interval_len, 3600))));
			$result .= sprintf("%sм ", bcdiv($interval_len, 60));
		} else { 
			if ($interval_len > 2592000) {
				$interval_len = ($interval_len - (31536000*(bcdiv($interval_len, 31536000))));
				$result .= sprintf("%sМ ", bcdiv($interval_len, 2592000));
				$interval_len = ($interval_len - (2592000*(bcdiv($interval_len, 2592000))));
				$result .= sprintf("%sн ", bcdiv($interval_len, 604800));
				$interval_len = ($interval_len - (604800*(bcdiv($interval_len, 604800))));
				$result .= sprintf("%sд ", bcdiv($interval_len, 86400));
				$interval_len = ($interval_len - (86400*(bcdiv($interval_len, 86400))));
				$result .= sprintf("%sч ", bcdiv($interval_len, 3600));
				$interval_len = ($interval_len - (3600*(bcdiv($interval_len, 3600))));
				$result .= sprintf("%sм ", bcdiv($interval_len, 60));
			} else{
				if ($interval_len > 604800) {
					$interval_len = ($interval_len - (2592000*(bcdiv($interval_len, 2592000))));
					$result .= sprintf("%sн ", bcdiv($interval_len, 604800));
					$interval_len = ($interval_len - (604800*(bcdiv($interval_len, 604800))));
					$result .= sprintf("%sд ", bcdiv($interval_len, 86400));
					$interval_len = ($interval_len - (86400*(bcdiv($interval_len, 86400))));
					$result .= sprintf("%sч ", bcdiv($interval_len, 3600));
					$interval_len = ($interval_len - (3600*(bcdiv($interval_len, 3600))));
					$result .= sprintf("%sм ", bcdiv($interval_len, 60));
				} else { 
					if ($interval_len > 86400) {
						$interval_len = ($interval_len - (604800*(bcdiv($interval_len, 604800))));
						$result .= sprintf("%sд ", bcdiv($interval_len, 86400));
						$interval_len = ($interval_len - (86400*(bcdiv($interval_len, 86400))));
						$result .= sprintf("%sч ", bcdiv($interval_len, 3600));
						$interval_len = ($interval_len - (3600*(bcdiv($interval_len, 3600))));
						$result .= sprintf("%sм ", bcdiv($interval_len, 60));
					} else {
						if ($interval_len > 3600) { 
							$interval_len = ($interval_len - (86400*(bcdiv($interval_len, 86400))));
							$result .= sprintf("%sч ", bcdiv($interval_len, 3600));
							$interval_len = ($interval_len - (3600*(bcdiv($interval_len, 3600))));
							$result .= sprintf("%sм ", bcdiv($interval_len, 60));
						} else {
							if ($interval_len > 60) {
								$interval_len = ($interval_len - (3600*(bcdiv($interval_len, 3600))));
								$result .= sprintf("%sм ", bcdiv($interval_len, 60));
								$interval_len = ($interval_len - (60*(bcdiv($interval_len, 60))));
								$result .= sprintf("%sс ", $interval_len);
							} else {
								$result .= sprintf("%sс", $interval_len);
							}
						}
					}
				}
			}
		}
		return $result;
	} else {
		return NULL;
	}
}

function str2time($str, $format = '%d.%m.%Y %H:%M:%S') {
	$time = strptime($str, $format);
	return mktime($time['tm_hour'], $time['tm_min'], $time['tm_sec'], ($time['tm_mon']+1), $time['tm_mday'], ($time['tm_year']+1900));
}

function beginOfWorkTime($time = NULL) {
	if (isNotNULL($time)) {
		return strtotime(strftime('%h.%m.%Y', $time).BEGIN_WORK_TIME);
	} else {
		return NULL;
	}
}

function endOfWorkTime($time = NULL) {
	if (isNotNULL($time)) {
		return strtotime(strftime('%h.%m.%Y', $time).END_WORK_TIME);
	} else {
		return NULL;
	}
}

function todayIsNextDay($time = NULL) {
	if (isNotNULL($time)) {
		return ((strftime("%d.%m.%Y", time()) >= strftime("%d.%m.%Y", ($time+86399)))?true:false);
	} else {
		return false;
	}
	
}

function toTranslit($str) {
	return StrTr($str, array("Ґ"=>"G", "Ё"=>"YO", "Є"=>"E", "Ї"=>"YI", "І"=>"I", "і"=>"i", "ґ"=>"g", "ё"=>"yo", "№"=>"#", "є"=>"e",
							 "ї"=>"yi", "А"=>"A", "Б"=>"B", "В"=>"V", "Г"=>"G", "Д"=>"D", "Е"=>"E", "Ж"=>"ZH", "З"=>"Z", "И"=>"I",
							 "Й"=>"Y", "К"=>"K", "Л"=>"L", "М"=>"M", "Н"=>"N", "О"=>"O", "П"=>"P", "Р"=>"R", "С"=>"S", "Т"=>"T",
							 "У"=>"U", "Ф"=>"F", "Х"=>"H", "Ц"=>"TS", "Ч"=>"CH", "Ш"=>"SH", "Щ"=>"SCH", "Ъ"=>"'", "Ы"=>"YI", "Ь"=>"",
							 "Э"=>"E", "Ю"=>"YU", "Я"=>"YA", "а"=>"a", "б"=>"b", "в"=>"v", "г"=>"g", "д"=>"d", "е"=>"e", "ж"=>"zh",
							 "з"=>"z", "и"=>"i", "й"=>"y", "к"=>"k", "л"=>"l", "м"=>"m", "н"=>"n", "о"=>"o", "п"=>"p", "р"=>"r",
							 "с"=>"s", "т"=>"t", "у"=>"u", "ф"=>"f", "х"=>"h", "ц"=>"ts", "ч"=>"ch", "ш"=>"sh", "щ"=>"sch", "ъ"=>"",
							 "ы"=>"yi", "ь"=>"", "э"=>"e", "ю"=>"yu", "я"=>"ya"));
}
?>