<?php

// $Id$

class FormManager extends Core {
	public $_errors = array();
	
	function __construct($owner = NULL) {
		parent::__construct($owner, 0, $owner->getConnection());
		$this->_calendarloaded = false;
		$this->_formerror = false;
		$this->_formpassed = false;
		$this->_formforupload = false;
		$this->_nextpass = false;
	}
	
	function setError($name) {
		$this->_formerror = true;
		$this->_errors[$name] = true;
	}

	function getError($name) {
		return $this->_errors[$name];
	}

	function getNextPass() {
		return $this->_nextpass;
	}
	
	function setNoError() {
		$this->_formerror = false;
		$this->_errors = array();
	}
	
	function formPassed() {
		$this->_formpassed = true;
		$this->setNoError();
		$this->_nextpass = true;
	}

	function formNotPassed() {
		$this->_formpassed = false;
		$this->_formerror = true;
		$this->_nextpass = false;
	}

	function formForUpload() {
		$this->_forforupload = true;
	}

	function formNotForUpload() {
		$this->_forforupload = false;
	}

	function formIsValid() {
		return (($_SERVER['REQUEST_METHOD'] == "POST") and ($this->_formpassed == false) and ($this->_formerror == false));
	}

	function isValid($name, $type, $multiple = false, $extra_parameters = NULL) {
		global $parameters;

		if (($_SERVER['REQUEST_METHOD'] == "POST") and ($this->_nextpass == false)) {
			logRuntime('['.get_class($this).'.isValid] check validation for '.strtoupper($name));
			if ($type == Constants::PROPERTY_TYPE_OBJECT) {
				return ((trim($_FILES[$name]['name']) <> '') and ($this->isMatch($_FILES[$name]['name'], $type)));
			} else {
				if ($multiple) {
					return ((isNotNULL($parameters[strtoupper($name)]) == true) and ((trim(implode('||', $parameters[strtoupper($name)])) <> '') == true) and ($this->isMatch(trim(implode('||', $parameters[strtoupper($name)])), $type)));
				} else {
					if (isNull($extra_parameters)) {
						return ((defined(strtoupper($name)) == true) and ((trim(constant(strtoupper($name))) <> '') == true) and ($this->isMatch(trim(constant(strtoupper($name))), $type)));
					} else {
						if (strpos($extra_parameters, "%") === false) {
							$eval_expression = "(trim(constant(strtoupper(\$name))) ".$extra_parameters.")";
						} else {
							$eval_expression = "(".preg_replace("/\%var\%/i", (($type == Constants::PROPERTY_TYPE_NUMBER)?"str_replace(\",\", \".\", trim(constant(strtoupper(\$name))))":"trim(constant(strtoupper(\$name)))"), $extra_parameters).")";
						}
						return ((defined(strtoupper($name)) == true) and ((trim(constant(strtoupper($name))) <> '') == true) and ($this->isMatch(trim(constant(strtoupper($name))), $type)) and (eval("if ".$eval_expression." { return true; } else { return false; }")));
					}
				}
			}
		} else {
			return true;
		}
	}

	function isNotValid($name, $type, $multiple = false, $extra_parameters = NULL) {
		return ($this->isValid(strtoupper($name), $type, $multiple, $extra_parameters)?false:true);
	}

	function formNotValid() {
		if (($_SERVER['REQUEST_METHOD'] == "POST") and ($this->_formpassed == false) and ($this->_formerror == true) and (count($this->_errors) > 0)) {
			logRuntime('['.get_class($this).'.formNotValid] validation check failed!');
			print "<div class=\"message\">Форма содержит ошибки! Вводите данные внимательней!</div>\n";
		}
	}

	private function arrayToParams($array) {
		$result = "";
		foreach($array as $key => $data) {
			$result .= "&".$key."=".$data;
		}
		return $result; 
	}
	
	function defaultAction($process = NULL, $action = NULL, $additions = NULL) {
		if (is_null($process)) {
			return "?module=".getParentModule().DIRECTORY_SEPARATOR.(isNotNULL($action)?getChildModule():getParentChildModule()).DIRECTORY_SEPARATOR."list&action=".(isNotNULL($action)?trim($action):"execute").(defined('PROJECT_INSTANCE_ID')?'&project_instance_id='.PROJECT_INSTANCE_ID:'').(defined('PROCESS_INSTANCE_ID')?'&process_instance_id='.PROCESS_INSTANCE_ID:'').$this->arrayToParams($additions);
		} else {
			return "?module=".getParentModule().DIRECTORY_SEPARATOR.(isNotNULL($action)?getChildModule():getParentChildModule()).DIRECTORY_SEPARATOR."list&action=".(isNotNULL($action)?trim($action):"execute")."&process_instance_id=".$process->getProperty('id')."&project_instance_id=".$process->getProject()->getProperty('id').$this->arrayToParams($additions);
		}
	}

	function formBegin(array $options = array()) {
		$options = array_merge(array('title' => 'Форма действия', 'process' => NULL, 'name' => 'cs_process_form', 'readonly' => false, 'fileupload' => true, 'error' => false), $options);
		if ($options['fileupload']) {
			$this->formForUpload();
		} else {
			$this->formNotForUpload();
		}

		return ($options['print']?"<h1 align=\"left\">".$options['title']."</h1><br /><br />":($options['readonly']?"":"<form method=\"POST\" id=\"".toTranslit($options['name'])."\" action=\"".$this->defaultAction($options['process'], $options['actionname'], $options['additions'])."\" ".($options['fileupload']?'enctype="multipart/form-data"':'').">").(($options['fileupload'])?'<input type="hidden" name="MAX_FILE_SIZE" value="'.MAX_FILE_SIZE.'" />':'').
				(((!$options['readonly']) and (isNotNULL($options['process'])) and ((is_a($options['process'], 'ProcessInstance')) or (is_a($options['process'], 'ProcessInstanceWrapper'))) and ($options['process']->haveHistory()))?"<h3 class=\"red blink center\">ВНИМАНИЕ! Документ имеет историю движения (возвраты или откаты)! Для просмотра истории нажмите на этот <img class=\"action\" src=\"images/date.png\" onClick=\"hideIt('history_data')\" title=\"Показать история движения документа\" /> значек!</h3>":"<br />").
				(((!$options['readonly']) and (!$options['print']))?"<h4 class=\"red\">ВНИМАНИЕ! Поля, выделенные ЖИРНЫМ шрифтом - ОБЯЗАТЕЛЬНЫ К ЗАПОЛНЕНИЮ!</h4>":"")."<table class=\"form\"><caption class=\"title\">".
				((isNotNULL($options['process']))?"<img class=\"action\" src=\"images/print.png\" style=\" float : right; \" onClick=\"openWindow('".($options['process']->getEngine()->getTemplate()->simpleProcess("%%SERVER_URI%%?module=%%".(((is_a($options['process'], 'ProcessInstance')) or (is_a($options['process'], 'ProcessInstanceWrapper')))?"INBOX":"HISTORY")."_PROCESS_MODULE%%")."&media=print".
				(((is_a($options['process'], 'ProcessInstance')) or (is_a($options['process'], 'ProcessInstanceWrapper')))?"&project_instance_id=".$options['process']->getProject()->getProperty('id'):"").
				(((is_a($options['process'], 'ProcessInstance')) or (is_a($options['process'], 'ProcessInstanceWrapper')))?"&process_instance_id=".$options['process']->getProperty('id'):"&process_instance_id=".$options['process']->getProperty('instance_id')."&chrono_instance_id=".$options['process']->getProperty('id')).
				"&process_id=".$options['process']->getProperty('process_id'))."');\" title=\"Печать документа\" />":"").$options['title']."</caption>");
	}

	function formEnd(array $options = array()) {
		$options = array_merge(array('title' => 'Данные введены и верны - Отправить документ дальше',
									 'description' => 'Записать введённые данные и продолжить дальнейшее выполнение процесса прохождения документа',
									 'readonly' => false,
									 'error' => false,
									 'savebtn' => false,
									 'colspan' => 4,
									 'switch' => false), $options);

		if (($options['switch']) and (isNotNULL($options['action']))) {
			$options = array_merge(array('truetitle' => 'Перейти к действию \''.$options['action']->getProperty('trueactionname').'\'',
										 'truedescription' => 'Записать введённые данные и продолжить движение документа переходом к действию \''.$options['action']->getProperty('trueactionname').'\'',
										 'falsetitle' => 'Перейти к действию \''.$options['action']->getProperty('falseactionname').'\'',
										 'falsedescription' => 'Записать введённые данные и продолжить движение документа переходом к действию \''.$options['action']->getProperty('falseactionname').'\''), $options);
		}

		return ($options['readonly']?"":($options['savebtn']?"<tr><td class=\"form\" ".(isNULL($options['colspan'])?"":"colspan=\"".$options['colspan']."\"")."><input class=\"button\" type=\"button\" value=\"Данные ещё будут изменятся - Сохранить документ ".(($options['action']->getProperty('npp') == 0)?"как черновик":"")."\" title=\"Сохранить введённые данные для последующего внесения изменений и корректировки\" style=\" width : 100%; \" onClick=\" document.forms.".$options['name'].".action = '".$this->defaultAction($options['process'], 'saveform', $options['additions'])."'; document.forms.".$options['name'].".submit(); \" /></td></tr>\n":"").
		($options['switch']
		 ?"<tr><td class=\"form\" ".(isNULL($options['colspan'])?"":"colspan=\"".$options['colspan']."\"")."><input class=\"button\" type=\"button\" value=\"".$options['falsetitle']."\" title=\"".$options['falsedescription']."\" style=\" width : 100%; \"  onClick=\" document.forms.".$options['name'].".action = '".$this->defaultAction($options['process'], $options['actionname'], $options['additions'])."&choice=false'; document.forms.".$options['name'].".submit(); \" /></td></tr>\n".
		  "<tr><td class=\"form\" ".(isNULL($options['colspan'])?"":"colspan=\"".$options['colspan']."\"")."><input class=\"button\" type=\"button\" value=\"".$options['truetitle']."\" title=\"".$options['truedescription']."\" style=\" width : 100%; \"  onClick=\" document.forms.".$options['name'].".action = '".$this->defaultAction($options['process'], $options['actionname'], $options['additions'])."&choice=true'; document.forms.".$options['name'].".submit(); \" /></td></tr>\n"
		 :"<tr><td class=\"form\" ".(isNULL($options['colspan'])?"":"colspan=\"".$options['colspan']."\"")."><input class=\"button\" type=\"button\" value=\"".$options['title']."\" title=\"".$options['description']."\" style=\" width : 100%; \"  onClick=\" document.forms.".$options['name'].".action = '".$this->defaultAction($options['process'], $options['actionname'], $options['additions'])."'; document.forms.".$options['name'].".submit(); \" /></td></tr>")).($options['print']?"":"</table>").($options['readonly']?"":"</form>\n")."<br /><br />\n";
	}

	function formInput(array $options = array()) {
		$options = array_merge(array('title' => 'Введите строку', 'description' => 'Введите строку', 'name' => 'cs_input', 'value' => NULL, 'size' => 0, 'readonly' => false, 'disabled' => false, 'error' => false, 'colspan' => 3, 'titlewidth' => '20%', 'datawidth' => '80%'), $options); 
		return "<tr><td title=\"".$options['description']."\" class=\"form\" style=\"".($options['error']?" background : red; ":"").($options['required']?"font-weight : bolder;":"")."\" width=\"".$options['titlewidth']."\" align=\"right\" valign=\"top\">".$options['title'].":</td><td class=\"form\" ".(is_null($options['colspan'])?"":"colspan=\"".$options['colspan']."\"").($options['error']?" style=\" background : red; font-weight : bolder; \"":"")." width=\"".$options['datawidth']."\"><input ".($options['disabled']?"style=\" background-color: #E0E0E0; \"":"")." ".($options['readonly']?"readonly":"")." name=\"".$options['name']."\" id=\"".toTranslit($options['name'])."\" type=\"text\" value=\"".(is_null($options['value'])?"":$options['value'])."\" size=\"".($options['size'] > 0?$options['size']:0)."\" style=\" width : 100%; \" /></td></tr>\n";
	}

	function formCheckBox(array $options = array()) {
		$options = array_merge(array('title' => 'Отметить значение', 'description' => 'Отметить значение', 'name' => 'cs_checkbox', 'value' => false, 'readonly' => false, 'disabled' => false, 'error' => false, 'colspan' => 3, 'titlewidth' => '20%', 'datawidth' => '80%'), $options); 
		return "<tr><td title=\"".$options['description']."\" class=\"form\" style=\"".($options['error']?" background : red; ":"").($options['required']?"font-weight : bolder;":"")."\" width=\"".$options['titlewidth']."\" align=\"right\" valign=\"top\">".$options['title'].":</td><td class=\"form\" ".(is_null($options['colspan'])?"":"colspan=\"".$options['colspan']."\"").($options['error']?" style=\" background : red; font-weight : bolder; \"":"")." width=\"".$options['datawidth']."\"><input ".($options['disabled']?"style=\" background-color: #E0E0E0; \"":"")." ".($options['readonly']?"readonly":"")." name=\"".$options['name']."\" id=\"".toTranslit($options['name'])."\" type=\"checkbox\" ".($options['value'] == 'on'?"checked":"")." style=\" width : 100%; \" /></td></tr>\n";
	}
	
	function formInfo(array $options = array()) {
		if (isNotNULL(stripMacros($options['value']))) {
			$options = array_merge(array('title' => 'Просмотр данных', 'name' => 'cs_info', 'value' => NULL, 'size' => 0, 'readonly' => false, 'disabled' => false, 'error' => false, 'colspan' => 3, 'text' => false), $options);
			if ($options['text']) {
				return "<br /><span class=\"bold_big\">".$options['title'].":</span><br />\n".
					   "<div style=\" border-top: 1px solid black; border-bottom: 1px solid black; padding: 10px; \" width=\"100%\"><span align=\"justify\" width=\"100%\" class=\"big_italic\" name=\"".$options['name']."\" id=\"".toTranslit($options['name'])."\">".mb_str_ireplace("\n", "<br />", stripMacros($options['value']))."</span></div><br /><br />\n";
			} else {
				return "<span class=\"bold_big\">".$options['title'].":</span>&nbsp;&nbsp;<span name=\"".$options['name']."\" id=\"".toTranslit($options['name'])."\" class=\"big_italic\">".mb_str_ireplace("\n", "<br />", stripMacros($options['value']))."</span><br /><br />\n";
			}
		} else {
			return '';
		}
	}

	function formHidden(array $options = array()) {
		$options = array_merge(array('name' => 'cs_hidden', 'value' => NULL), $options); 
		return "<input name=\"".$options['name']."\" id=\"".toTranslit($options['name'])."\" type=\"hidden\" value=\"".(is_null($options['value'])?"":$options['value'])."\" />\n";
	}

	function formFile(array $options = array()) {
		$options = array_merge(array('title' => 'Введите имя файла', 'description' => 'Введите или выберите имя файла', 'name' => 'cs_file', 'value' => NULL, 'size' => 50, 'readonly' => false, 'disabled' => false, 'mimetypes' => NULL, 'error' => false, 'colspan' => 3, 'titlewidth' => '20%', 'datawidth' => '80%'), $options);
		if (($options['readonly']) or ($options['disabled'])) {
			return "<tr><td title=\"".$options['description']."\" class=\"form\" style=\"".($options['error']?" background : red; ":"").($options['required']?"font-weight : bolder;":"")."\" width=\"".$options['titlewidth']."\" align=\"right\" valign=\"top\">".$options['title'].":</td><td class=\"form\" ".(is_null($options['colspan'])?"":"colspan=\"".$options['colspan']."\"")." width=\"".$options['datawidth']."\"><a href=\"".FILE_CACHE_PATH."/".$options['value']."\">".$options['value']."</a></td></tr>\n";
		} else { 
			return "<tr><td title=\"".$options['description']."\" class=\"form\" style=\"".($options['error']?" background : red; ":"").($options['required']?"font-weight : bolder;":"")."\" width=\"".$options['titlewidth']."\" align=\"right\" valign=\"top\">".$options['title'].":</td><td class=\"form\" ".(is_null($options['colspan'])?"":"colspan=\"".$options['colspan']."\"").($options['error']?" style=\" background : red; font-weight : bolder; \"":"")." width=\"".$options['datawidth']."\"><input ".($options['disabled']?"style=\" background-color: #E0E0E0; \"":"")." ".($options['readonly']?"readonly":"")." name=\"".$options['name']."\" id=\"".toTranslit($options['name'])."\" type=\"file\" value=\"".(is_null($options['value'])?"":$options['value'])."\" size=\"".($options['size'] > 0?$options['size']:0)."\" ".($options['mimetypes']?"accept=\"".$options['mimetypes']."\"":"")." style=\" width : 100%; \" /></td></tr>\n";
		}
	}

	function formText(array $options = array()) {
		$options = array_merge(array('title' => 'Введите текст', 'description' => 'Введите текст', 'name' => 'cs_text', 'value' => NULL, 'rows' => 10, 'readonly' => false, 'disabled' => false, 'error' => false, 'colspan' => 3, 'titlewidth' => '20%', 'datawidth' => '80%'), $options);
		return "<tr><td title=\"".$options['description']."\" class=\"form\" style=\"".($options['error']?" background : red; ":"").($options['required']?"font-weight : bolder;":"")."\" width=\"".$options['titlewidth']."\" align=\"right\" valign=\"top\">".$options['title'].":</td><td class=\"form\" ".(is_null($options['colspan'])?"":"colspan=\"".$options['colspan']."\"").($options['error']?" style=\" background : red; font-weight : bolder; \"":"")." width=\"".$options['datawidth']."\"><textarea ".($options['disabled']?"style=\" background-color: #E0E0E0; \"":"")." ".($options['readonly']?"readonly":"")." name=\"".$options['name']."\" id=\"".toTranslit($options['name'])."\" rows=\"".($options['rows'] > 0?$options['rows']:10)."\" style=\" width : 100%; \" />".(is_null($options['value'])?"":$options['value'])."</textarea></td></tr>\n";
	}

	function formCombo(array $options = array()) {
		$options = array_merge(array('title' => 'Выберите строку', 'description' => 'Выберите строку', 'name' => 'cs_combo', 'value' => NULL, 'size' => 0, 'readonly' => false, 'disabled' => false, 'emptyline' => false, 'reverse' => false, 'data' => array(), 'error' => false, 'colspan' => 3, 'multiple' => false, 'titlewidth' => '20%', 'datawidth' => '80%', 'nocurrentuser' => false,), $options);

		$result = "";
		$result .= "<tr><td title=\"".$options['description']."\" class=\"form\" style=\"".($options['error']?" background : red; ":"").($options['required']?"font-weight : bolder; ":"")."\" width=\"".$options['titlewidth']."\" align=\"right\" valign=\"top\">".$options['title'].":</td><td class=\"form\" ".(is_null($options['colspan'])?"":"colspan=\"".$options['colspan']."\"").($options['error']?" style=\" background : red; font-weight : bolder; \"":"")." width=\"".$options['datawidth']."\">\n<select ".($options['disabled']?"style=\" background-color: #E0E0E0; \"":"")." ".($options['readonly']?"readonly":"")." name=\"".$options['name'].($options['multiple']?"[]":"")."\" id=\"".toTranslit($options['name'])."\" ".($options['multiple']?"multiple=\"multiple\"":"")." size=\"".($options['size'] > 0?$options['size']:0)."\" style=\" width : 100%; \" />\n";
		if ($options['emptyline']) {
			$result .= "<option value=\"\" />Все\n";
		}
		if ($options['multiple']) {
			$options['value'] = explode('||', $options['value']);
		}
		if ($options['reverse']) {
			arsort($options['data']);
		}
		foreach ($options['data'] as $value => $data) {
			$result .= "<option value=\"".htmlentities(str_replace("\"", "", $value), ENT_COMPAT, DEFAULT_CHARSET)."\" ".($options['multiple']?((in_array($value, $options['value']))?"selected":""):(((isNotNULL($options['value'])) and ($options['value'] == $value))?"selected":(((($options['nocurrentuser']) == false) and (isNULL($options['value']) and (($value == USER_CODE) or ($value == USER_NAME))))?"selected":"")))." />".$data."\n";
		}
		return $result."</select></td></tr>\n";
	}

	function useCalendars() {
		global $calendar_options;

		$result = "";
		$result .= "\n<link rel=\"stylesheet\" type=\"text/css\" media=\"all\" href=\"css/calendar/calendar-".$calendar_options['theme'].".css\" />\n";
		$result .= "<script type=\"text/javascript\" src=\"".JAVASCRIPT_PATH."/calendar/calendar".($calendar_options['stripped']?'_stripped':'').".js\"></script>\n";
		$result .= "<script type=\"text/javascript\" src=\"".JAVASCRIPT_PATH."/calendar/lang/calendar-".$calendar_options['lang'].".js\"></script>\n";
		$result .= "<script type=\"text/javascript\" src=\"".JAVASCRIPT_PATH."/calendar/calendar-setup".($calendar_options['stripped']?'_stripped':'').".js\"></script>\n";

		$this->_calendarloaded = true;
		return $result;
	}

	function formCalendar(array $options = array()) {
		$options = array_merge(array('title' => 'Выберите дату', 'description' => 'Выберите дату', 'name' => 'cs_calendar', 'value' => NULL, 'disabled' => true, 'error' => false, 'showtime' => false, 'format' => '%d.%m.%Y'.($options['showtime']?" %H:%M":""), 'colspan' => 3, 'titlewidth' => '20%', 'datawidth' => '80%'), $options);

		$result = "";
		if ($this->_calendarloaded == false) {
			$result .= $this->useCalendars();
		}

		$result .= "<script type=\"text/javascript\">\n<!--\n  var valueOf".$this->prepareForForm($options['name'])." = '".(isNotNull($options['value'])?$options['value']:"")."';\n//-->\n</script>";

		$validation = " onBlur=\" if (!isValidDate".($options['showtime']?"Time":"")."(this.value)) { this.value = valueOf".$this->prepareForForm($options['name'])."; } \" onFocus=\" valueOf".$options['name']." = \$F('".$this->prepareForForm($options['name'])."'); \" ";

// TODO: Попытка сделать через Calendar.setup()
//		$result .= "\n\n<script type=\"text/javascript\">\n<!--\n";
//		$result .= "  Calendar.setup ( {\n";
//		$result .= "    inputField :	\"".toTranslit($options['name'])."\",\n";
//		$result .= "    ifFormat :		\"".$options['format']."\",\n";
//		if (!$options['disabled']) {
//			$result .= "    button :		\"".toTranslit($options['name'])."_button\",\n";
//			$result .= "    singleClick :	true,\n";
//		}
//		if ($options['showtime']) {
//			$result .= "    showsTime :	true,\n";
//		}
//		$result .= "    step :	1\n";
//		$result .= "  });\n";
//		$result .= "\n//-->\n</script>\n";
//		return $result."<tr><td title=\"".$options['description']."\" class=\"form\" style=\"".($options['error']?" background : red; ":"").($options['required']?"font-weight : bolder;":"")."\" width=\"".$options['titlewidth']."\" align=\"right\" valign=\"top\">".$options['title'].":</td><td class=\"form\" ".(is_null($options['colspan'])?"":"colspan=\"".$options['colspan']."\"").($options['error']?" style=\" background : red; font-weight : bolder; \"":"")." width=\"".$options['titlewidth']."\"><nobr><input class=\"calendarinput\" ".($options['disabled']?"style=\" background-color: #E0E0E0; \"":"")." name=\"".$options['name']."\" id=\"".toTranslit($options['name'])."\" type=\"text\" value=\"".(is_null($options['value'])?strftime($options['format'], time()):$options['value'])."\" ".$validation." /><img id=\"".toTranslit($options['name'])."_button\" valign=\"middle\" src=\"images/date.png\" title=\"Открыть календарь\" /></nobr></td></tr>\n";
// TODO: Не работает почему... Позже разберусь

		return $result."<tr><td title=\"".$options['description']."\" class=\"form\" style=\"".($options['error']?" background : red; ":"").($options['required']?"font-weight : bolder;":"")."\" width=\"".$options['titlewidth']."\" align=\"right\" valign=\"top\">".$options['title'].":</td><td class=\"form\" ".(is_null($options['colspan'])?"":"colspan=\"".$options['colspan']."\"").($options['error']?" style=\" background : red; font-weight : bolder; \"":"")." width=\"".$options['titlewidth']."\"><nobr><input class=\"calendarinput\" ".($options['disabled']?"style=\" background-color: #E0E0E0; \"":"")." name=\"".$options['name']."\" id=\"".toTranslit($options['name'])."\" type=\"text\" value=\"".(is_null($options['value'])?strftime($options['format'], time()):$options['value'])."\" ".$validation." /><img valign=\"middle\" src=\"images/date.png\" title=\"Открыть календарь\" ".($options['disabled']?"":"onClick=\"showCalendar('".toTranslit($options['name'])."', '".$options['format']."'".($options['showtime']?", '24'":"")."); \"")." /></nobr></td></tr>\n";
	}

	function prepareForForm($str) {
		return (preg_replace('/[^А-Яа-яA-Za-z0-9]+/u', '_', $str));
	}

	private function prepareParameters($property) {
		$result = array('where' => NULL, 'parameters' => NULL);
		
		if (isNotNULL($property->getProperty('directoryparameters')) and (trim($property->getProperty('directoryparameters')) <> "")) {
			if (preg_match("/([\=\<\>]+)|(\sis\s)|(\snot\s)/i", $property->getProperty('directoryparameters'))) {
				// это WHERE 
				$result['where'] = $this->getEngine()->getTemplate()->simpleProcess($property->getProperty('directoryparameters'));
			} else {
				$result['parameters'] = $this->getEngine()->getTemplate()->simpleProcess($property->getProperty('directoryparameters'));
			}
		}
		
		if (isNotNULL($property->getProperty('parameters')) and (trim($property->getProperty('parameters')) <> "")) {
			if (preg_match("/([\=\<\>]+)|(\sis\s)|(\snot\s)/i", $property->getProperty('parameters'))) {
				// это WHERE 
				$result['where'] = (isNotNULL($result['where'])?$result['where']." and ":"").$this->getEngine()->getTemplate()->simpleProcess($property->getProperty('parameters'));
			} else {
				$result['parameters'] = (isNotNULL($result['parameters'])?$result['parameters'].", ":"").$this->getEngine()->getTemplate()->simpleProcess($property->getProperty('parameters'));
			}
		}
		return $result;
	}

	function generateActionEditForm(array $options = array()) {
		if (isNotNULL($options['action'])) {

			if (is_null($options['process'])) {
				$options['process'] = $options['action']->getOwnerByClass('ProcessInstanceWrapper');
			}

			$options['action']->initTemplate();
			$options['additions'] = array('action_id' => $options['action']->getProperty('id'));
			$options = array_merge(array('title' => "Форма редактирования свойств действия \"".$options['action']->getProperty('name')."\"", 'name' => 'cs_action_edit_form_'.$options['action']->getProperty('id'), 'readonly' => false, 'fileupload' => false, 'actionname' => 'changeaction'), $options);

			$result .= $this->formBegin($options);

			foreach ($options['action']->getAction() as $name => $value) {
				if ($this->getConnection()->getTable($options['action']->getProperty('[model]'))->hasColumn($name)) {
					switch (strtoupper($name)) {
						case "STATUS_ID" :
							$result .= $this->formCombo(array('title' => 'Статус',
												   'name' => 'x_action_status_'.$options['action']->getProperty('id'),
												   'description' => 'Текущий статус действия',
												   'value' => $value,
												   'size' => 0,
												   'required' => true,
												   'error' => false, 
												   'readonly' => false,
												   'disabled' => false,
												   'emptyline' => true,
												   'reverse' => false,
												   'data' => $this->getDirectoryList(array_merge(array('directory' => 'cs_status', 'valueasname' => false)))));
							break;

						case "INITIATOR_ID" :
							$result .= $this->formCombo(array('title' => 'Инициатор',
												   'name' => 'x_action_initiator_'.$options['action']->getProperty('id'),
												   'description' => 'Инициатор действия',
												   'value' => $value,
												   'size' => 0,
												   'required' => true,
												   'error' => false, 
												   'readonly' => false,
												   'disabled' => false,
												   'emptyline' => true,
												   'reverse' => false,
												   'data' => $this->getDirectoryList(array_merge(array('directory' => 'accounts_without_groups_list', 'valueasname' => false, 'where' => 'is_active = true')))));
							break;

						case "PERFORMER_ID" :
							$result .= $this->formCombo(array('title' => 'Исполнитель',
												   'name' => 'x_action_performer_'.$options['action']->getProperty('id'),
												   'description' => 'Исполнитель действия',
												   'value' => $value,
												   'size' => 0,
												   'required' => true,
												   'error' => false, 
												   'readonly' => false,
												   'disabled' => false,
												   'emptyline' => true,
												   'reverse' => false,
												   'data' => $this->getDirectoryList(array_merge(array('directory' => 'accounts_without_groups_list', 'valueasname' => false, 'where' => 'is_active = true')))));
							break;

						case "STARTED_AT" :
							$result .= $this->formCalendar(array('title' => 'Начало',
												   'name' => 'x_action_started_'.$options['action']->getProperty('id'),
												   'description' => 'Начало действия',
												   'value' => $value,
												   'format' => '%Y-%m-%d %H:%M',
												   'showtime' => true,
												   'required' => true,
												   'error' => false, 
												   'readonly' => false,
												   'disabled' => false));
							break;

						case "ENDED_AT" :
							$result .= $this->formCalendar(array('title' => 'Конец',
												   'name' => 'x_action_ended_'.$options['action']->getProperty('id'),
												   'description' => 'Конец действия',
												   'value' => $value,
												   'format' => '%Y-%m-%d %H:%M',
												   'showtime' => true,
												   'required' => true,
												   'error' => false, 
												   'readonly' => false,
												   'disabled' => false));
							break;

						default:
							break;
					}
				}
			}

			$options = array_merge($options, array('title' => 'Принять', 'descr' => 'Принять и записать данные')); 
			$result .= $this->formEnd($options);
		}

		return $result;
	}

	function generateDirectoryEditForm(array $options = array()) {
		if (isNotNULL($options['directory'])) {
			$options = array_merge(array('title' => "Форма редактирования справочника \"".$options['directory']->getProperty('name')."\"", 'name' => 'cs_directory_edit_form', 'readonly' => false, 'fileupload' => $options['directory']->haveObjectFields(), 'actionname' => 'change', 'record' => NULL), $options);
			$options['additions'] = array('directory_id' => $options['directory']->getProperty('id'));
			
			if (is_null($options['record'])) {
				$options['actionname'] = 'add';
				$recorddata = array();
				$valuedata = array();
				
				$recorddata['directory_id'] = $options['directory']->getProperty('id');
				
				foreach ($options['directory']->getFields() as $field) {
					if ($field->getProperty('autoinc') == Constants::TRUE) {
						$valuedata[$field->getProperty('name')] = ($field->getProperty('default_value') + 1); 
					} else {
						$valuedata[$field->getProperty('name')] = $field->getProperty('default_value'); 
					}
				}
				$options['record'] = new DirectoryRecord($options['directory'], 0, array('data' => $recorddata, 'valuedata' => $valuedata));
			} else {
				$options['additions']['record_id'] = $options['record']->getProperty('id');
			}

			$result .= "\n".$this->formBegin($options)."\n";
			
			foreach ($options['directory']->getFields() as $field) {
				switch ($field->getProperty('type_id')) {
					case Constants::PROPERTY_TYPE_BOOL:
						$result .= $this->formCheckBox(array('title' => $field->getProperty('caption'),
										  'name' => 'dir_'.$this->prepareForForm($field->getProperty('name')),
										  'value' => $options['record']->getValue($field->getProperty('name'))->getProperty('value'),
										  'required' => true,
										  'error' => false))."\n";
						break;

					case Constants::PROPERTY_TYPE_STRING:
						$result .= $this->formInput(array('title' => $field->getProperty('caption'),
										  'name' => 'dir_'.$this->prepareForForm($field->getProperty('name')),
										  'value' => $options['record']->getValue($field->getProperty('name'))->getProperty('value'),
										  'size' => 0,
										  'required' => true,
										  'error' => false))."\n";
						break;

					case Constants::PROPERTY_TYPE_TEXT:
						$result .= $this->formText(array('title' => $field->getProperty('caption'),
										  'name' => 'dir_'.$this->prepareForForm($field->getProperty('name')),
										  'value' => $options['record']->getValue($field->getProperty('name'))->getProperty('value'),
										  'rows' => 10,
										  'required' => true,
										  'error' => false))."\n";
						break;

					case Constants::PROPERTY_TYPE_NUMBER:
						$result .= $this->formInput(array('title' => $field->getProperty('caption'),
										  'name' => 'dir_'.$this->prepareForForm($field->getProperty('name')),
										  'value' => $options['record']->getValue($field->getProperty('name'))->getProperty('value'),
										  'size' => 0,
										  'required' => true,
										  'error' => false,
										  'readonly' => ($field->getProperty('autoinc') == Constants::TRUE),
										  'disabled' => ($field->getProperty('autoinc') == Constants::TRUE)))."\n";
						break;

					case Constants::PROPERTY_TYPE_TIME:
						$result .= $this->formInput(array('title' => $field->getProperty('caption'),
										  'name' => 'dir_'.$this->prepareForForm($field->getProperty('name')),
										  'value' => ((($options['record']->getProperty('[values]')->elementExists($field->getProperty('name'))) and isNotNULL($options['record']->getValue($field->getProperty('name'))->getProperty('value')))?$options['record']->getValue($field->getProperty('name'))->getProperty('value'):strftime("%H:%M:%S", time())),
										  'size' => 0,
										  'required' => true,
										  'error' => false))."\n";
						break;

					case Constants::PROPERTY_TYPE_DATE:
					case Constants::PROPERTY_TYPE_DATETIME:
						$format = (($field->getProperty('type_id') == Constants::PROPERTY_TYPE_DATE)?"%d.%m.%Y":(($field->getProperty('type_id') == Constants::PROPERTY_TYPE_DATETIME)?"%d.%m.%Y %H:%M":"%d.%m.%Y"));
						$result .= $this->formCalendar(array('title' => $field->getProperty('caption'),
										  'name' => 'dir_'.$this->prepareForForm($field->getProperty('name')),
										  'required' => true,
										  'format' => $format,
										  'showtime' => ($field->getProperty('type_id') == Constants::PROPERTY_TYPE_DATETIME),
										  'value' => (((($options['record']->getProperty('[values]')->elementExists($field->getProperty('name'))) and (isNotNULL($options['record']->getValue($field->getProperty('name'))->getProperty('value'))))?$options['record']->getValue($field->getProperty('name'))->getProperty('value'):strftime($format, time()))),
									  	  'error' => false, 
									  	  'disabled' => false));
						break;
	
						
						
					case Constants::PROPERTY_TYPE_OBJECT:
						$result .= $this->formFile(array('title' => $field->getProperty('caption'),
										  'name' => 'dir_'.$this->prepareForForm($field->getProperty('name')),
										  'value' => $options['record']->getValue($field->getProperty('name'))->getProperty('value'),
										  'required' => true,
										  'error' => false))."\n";
						break;

					default:
						break; 
				}
			}
			
			$options = array_merge($options, array('title' => 'Принять', 'descr' => 'Принять и записать данные')); 
			$result .= $this->formEnd($options)."\n";
		}
		
		return $result;
	}
	
	function generatePropertyEditForm(array $options = array()) {
		if (isNotNULL($options['property'])) {

			if (is_null($options['process'])) {
				$options['process'] = $options['property']->getOwnerByClass('ProcessInstanceWrapper');
			}
			
			$options['process']->initTemplate();
			$options['additions'] = array('value_id' => $options['property']->getProperty('value_id'), 'type_id' => $options['property']->getProperty('type_id'), 'multiple' => ((mb_strpos($options['process']->getPropertyValue($options['property']->getProperty('name')), '||'))?"true":"false"));
			$options = array_merge(array('title' => "Форма редактирования свойства \"".$options['property']->getProperty('name')."\"", 'name' => 'cs_property_edit_form_'.$options['property']->getProperty('value_id'), 'readonly' => false, 'fileupload' => ($options['property']->getProperty('type_id') ==  Constants::PROPERTY_TYPE_OBJECT), 'actionname' => 'changeproperty'), $options);
			
			$result .= $this->formBegin($options);
			$property = $options['property'];
			switch ($property->getProperty('type_id')) {
				case Constants::PROPERTY_TYPE_TEXT:
					$result .= $this->formText(array('title' => $property->getProperty('name'),
										  'name' => 'x_property_value',
										  'description' => $property->getProperty('description'),
										  'value' => $options['process']->getPropertyValue($property->getProperty('name')),
										  'rows' => 10,
										  'required' => true,
										  'error' => false, 
										  'readonly' => false,
										  'disabled' => false));
					break;

				case Constants::PROPERTY_TYPE_BOOL:
					$result .= $this->formCheckBox(array('title' => $property->getProperty('name'),
										  'name' => 'x_property_value',
										  'description' => $property->getProperty('description'),
										  'value' => ($options['process']->getPropertyValue($property->getProperty('name'))?true:false),
										  'required' => true,
										  'error' => false, 
										  'readonly' => false,
										  'disabled' => false));
					break;
					
				case Constants::PROPERTY_TYPE_STRING:
				case Constants::PROPERTY_TYPE_NUMBER:
				case Constants::PROPERTY_TYPE_TIME:
				case Constants::PROPERTY_TYPE_DATE:
				case Constants::PROPERTY_TYPE_DATETIME:
					if (($property->getProperty('is_list') == Constants::TRUE) and (isNotNULL($property->getProperty('directory_id')))) {
						$result .= $this->formCombo(array('title' => $property->getProperty('name'),
											   'name' => 'x_property_value',
											   'description' => $property->getProperty('description'),
											   'value' => $options['process']->getPropertyValue($property->getProperty('name')),
											   'multiple' => (mb_strpos($options['process']->getPropertyValue($property->getProperty('name')), '||')),
											   'size' => ((mb_strpos($options['process']->getPropertyValue($property->getProperty('name')), '||'))?15:5),
										  	   'required' => true,
										  	   'error' => false, 
										  	   'readonly' => false,
											   'disabled' => false,
											   'emptyline' => true,
											   'reverse' => false,
											   'data' => $this->getDirectoryList(array_merge(array('directory' => $property->getProperty('directoryname'), 'directory_id' => $property->getProperty('directory_id'), 'custom' => $property->getProperty('directorycustom'), 'valueasname' => $property->getProperty('is_name_as_value'), 'value_field' => $property->getProperty('value_field')), $this->prepareParameters($property)))));
					} else {
						$result .= $this->formInput(array('title' => $property->getProperty('name'),
											   'name' => 'x_property_value',
											   'description' => $property->getProperty('description'),
											   'value' => $options['process']->getPropertyValue($property->getProperty('name')),
											   'size' => 0,
											   'required' => true,
										  	   'error' => false, 
										  	   'readonly' => false,
										  	   'disabled' => false));
					}
					break;

				case Constants::PROPERTY_TYPE_OBJECT:
					$result .= $this->formFile(array('title' => $property->getProperty('name'),
										  'name' => 'x_property_value',
										  'description' => $property->getProperty('description'),
										  'value' => $options['process']->getPropertyFileName($property->getProperty('name')),
										  'mimetypes' => NULL,
										  'required' => true,
										  'error' => false, 
										  'readonly' => false,
										  'disabled' => false));
					break;

				default:
					break;
			}

			$options = array_merge($options, array('title' => 'Принять', 'descr' => 'Принять и записать данные')); 
			$result .= $this->formEnd($options);
		}
		return $result;
	}

	function generateForm(array $options = array()) {
		if (isNotNULL($options['process']) or isNotNULL($options['action'])) {

			if (is_null($options['process'])) {
				if ($options['chrono']) {
					$options['process'] = $options['action']->getOwnerByClass('Chrono');
				} else {
					$options['process'] = $options['action']->getOwnerByClass('ProcessInstanceWrapper');
				}
			}
			if ((is_null($options['action'])) and (!$options['chrono'])) {
				$options['action'] = $options['process']->getCurrentAction();
			}
			if (is_null($options['properties'])) {
				$options['properties'] = $options['action']->getProperty('[action]')->getProperty('[properties]')->getElements();
			}
			
			if (!$options['chrono']) {
				$options['action']->initTemplate();
			}

			$options = array_merge(array('title' => ($options['chrono']?($options['print']?"":"Документ: ").$options['process']->getProperty('processname')." №".$options['process']->getProperty('instance_id')." (снимок №".$options['process']->getProperty('id')." от ".strftime("%d.%m.%Y в %H:%M", strtotime($options['process']->getProperty('chrono_at'))).")":(($options['action']->getProperty('type_id') == Constants::ACTION_TYPE_INFO)?($options['print']?"":"Документ: ").$options['process']->getProperty('name').((($options['print']) or ($options['action']->getProperty('type_id') == Constants::ACTION_TYPE_INFO))?" №".$options['process']->getProperty('id'):""):$options['action']->getProperty('name'))), 'name' => 'cs_process_form', 'readonly' => (($options['action']->getProperty('type_id') == Constants::ACTION_TYPE_INFO)?true:false), 'fileupload' => $options['action']->haveObjectProperty()), $options);
			print "\n<script><!--\n top.document.title = top.document.title+'-[".$options['process']->getProperty('name').' №'.$options['process']->getProperty('id').']-['.$options['action']->getProperty('name')."]';\n--></script>\n";
			$result .= $this->formBegin($options);
			foreach($options['properties'] as $property) {
				if ($property->getProperty('is_active') == Constants::TRUE) {
					if ($options['print']) {
						if (($property->getProperty('type_id') <> Constants::PROPERTY_TYPE_OBJECT) and ($options['process']->getProperty('[infoproperties]')->elementExists($property->getProperty('name')))) {
							$result .= $this->formInfo(array('title' => $property->getProperty('name'),
															 'name' => $this->prepareForForm($property->getProperty('name')),
															 'text' => (($property->getProperty('type_id') == Constants::PROPERTY_TYPE_TEXT)?true:false),
															 'value' => (stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)) <> ''?stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)):$options['process']->getPropertyValue($property->getProperty('name')))));
						}
					} else { 
						switch ($property->getProperty('type_id')) {
							case Constants::PROPERTY_TYPE_TEXT:
								if (($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_HIDDEN) or ($property->getProperty('is_hidden') == Constants::TRUE)) {
									$result .= $this->formHidden(array('name' => $this->prepareForForm($property->getProperty('name')),
														  'value' => (stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)) <> ''?stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)):$options['process']->getPropertyValue($property->getProperty('name')))));
								} else {
									$result .= $this->formText(array('title' => $property->getProperty('name'),
														  'name' => $this->prepareForForm($property->getProperty('name')),
														  'description' => $property->getProperty('description'),
														  'required' => $property->getProperty('is_required'),
														  'value' => (stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)) <> ''?stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)):$options['process']->getPropertyValue($property->getProperty('name'))),
														  'rows' => ((($options['readonly']) or ($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_READONLY) or ($property->getProperty('is_readonly') == Constants::TRUE))?5:15),
														  'error' => $this->getError($this->prepareForForm($property->getProperty('name'))), 
														  'readonly' => (($options['readonly']) or ($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_READONLY) or ($property->getProperty('is_readonly') == Constants::TRUE)),
														  'disabled' => (($options['readonly']) or ($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_READONLY) or ($property->getProperty('is_readonly') == Constants::TRUE))));
								}
								break;

							case Constants::PROPERTY_TYPE_BOOL:
								$result .= $this->formCheckBox(array('title' => $property->getProperty('name'),
													  'name' => $this->prepareForForm($property->getProperty('name')),
													  'description' => $property->getProperty('description'),
													  'value' => ($options['process']->getPropertyValue($property->getProperty('name'))?true:false),
													  'required' => $property->getProperty('is_required'),
													  'error' => $this->getError($this->prepareForForm($property->getProperty('name'))), 
													  'readonly' => (($options['readonly']) or ($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_READONLY) or ($property->getProperty('is_readonly') == Constants::TRUE)),
													  'disabled' => (($options['readonly']) or ($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_READONLY) or ($property->getProperty('is_readonly') == Constants::TRUE))));
								break;
								
							case Constants::PROPERTY_TYPE_STRING:
							case Constants::PROPERTY_TYPE_NUMBER:
							case Constants::PROPERTY_TYPE_TIME:
								if (($property->getProperty('is_list') == Constants::TRUE) and (isNotNULL($property->getProperty('directory_id')))) {
									if (($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_HIDDEN) or ($property->getProperty('is_hidden') == Constants::TRUE)) {
										$result .= $this->formHidden(array('name' => $this->prepareForForm($property->getProperty('name')),
															  'value' => (stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)) <> ''?stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)):$options['process']->getPropertyValue($property->getProperty('name')))));
									} else {
										if (($property->getProperty('is_readonly') == Constants::TRUE) or ($options['readonly'])) {
											$result .= $this->formInput(array('title' => $property->getProperty('name'),
																   'name' => $this->prepareForForm($property->getProperty('name')),
																   'description' => $property->getProperty('description'),
																   'required' => $property->getProperty('is_required'),
																   'value' => (stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)) <> ''?stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)):$options['process']->getPropertyValue($property->getProperty('name'))),
																   'size' => 0,
																   'error' => $this->getError($this->prepareForForm($property->getProperty('name'))), 
																   'readonly' => (($options['readonly']) or ($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_READONLY) or ($property->getProperty('is_readonly') == Constants::TRUE)),
																   'disabled' => (($options['readonly']) or ($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_READONLY) or ($property->getProperty('is_readonly') == Constants::TRUE))));
										} else {
											$result .= $this->formCombo(array('title' => $property->getProperty('name'),
																   'name' => $this->prepareForForm($property->getProperty('name')),
																   'description' => $property->getProperty('description'),
																   'required' => $property->getProperty('is_required'),
																   'value' => (stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)) <> ''?stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)):$options['process']->getPropertyValue($property->getProperty('name'))),
																   'size' => ($property->getProperty('is_multiple')?10:0),
																   'error' => $this->getError($this->prepareForForm($property->getProperty('name'))), 
																   'readonly' => (($options['readonly']) or ($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_READONLY) or ($property->getProperty('is_readonly') == Constants::TRUE)),
																   'disabled' => (($options['readonly']) or ($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_READONLY) or ($property->getProperty('is_readonly') == Constants::TRUE)),
																   'emptyline' => (!$property->getProperty('is_required')),
																   'multiple' => $property->getProperty('is_multiple'),
																   'reverse' => false,
																   'data' => $this->getDirectoryList(array_merge(array('directory' => $property->getProperty('directoryname'), 'directory_id' => $property->getProperty('directory_id'), 'custom' => $property->getProperty('directorycustom'), 'valueasname' => $property->getProperty('is_name_as_value'), 'value_field' => $property->getProperty('value_field')), $this->prepareParameters($property)))));
										}
									}
								} else {
									if (($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_HIDDEN) or ($property->getProperty('is_hidden') == Constants::TRUE)) {
										$result .= $this->formHidden(array('name' => $this->prepareForForm($property->getProperty('name')),
															  'value' => (stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)) <> ''?stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)):$options['process']->getPropertyValue($property->getProperty('name')))));
									} else {
										$result .= $this->formInput(array('title' => $property->getProperty('name'),
															   'name' => $this->prepareForForm($property->getProperty('name')),
															   'description' => $property->getProperty('description'),
															   'required' => $property->getProperty('is_required'),
															   'value' => (stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)) <> ''?stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)):$options['process']->getPropertyValue($property->getProperty('name'))),
															   'size' => 0,
															   'error' => $this->getError($this->prepareForForm($property->getProperty('name'))), 
															   'readonly' => (($options['readonly']) or ($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_READONLY) or ($property->getProperty('is_readonly') == Constants::TRUE)),
															   'disabled' => (($options['readonly']) or ($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_READONLY) or ($property->getProperty('is_readonly') == Constants::TRUE))));
									}
								}
								break;
		
							case Constants::PROPERTY_TYPE_DATE:
							case Constants::PROPERTY_TYPE_DATETIME:
								if (($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_HIDDEN) or ($property->getProperty('is_hidden') == Constants::TRUE)) {
									$result .= $this->formHidden(array('name' => $this->prepareForForm($property->getProperty('name')),
														  'value' => (stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)) <> ''?stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)):$options['process']->getPropertyValue($property->getProperty('name')))));
								} else {
									if (($property->getProperty('is_readonly') == Constants::TRUE) or ($options['readonly'])) {
										$result .= $this->formInput(array('title' => $property->getProperty('name'),
															   'name' => $this->prepareForForm($property->getProperty('name')),
															   'description' => $property->getProperty('description'),
															   'required' => $property->getProperty('is_required'),
															   'value' => (stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)) <> ''?stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)):$options['process']->getPropertyValue($property->getProperty('name'))),
															   'size' => 0,
															   'error' => $this->getError($this->prepareForForm($property->getProperty('name'))), 
															   'readonly' => (($options['readonly']) or ($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_READONLY) or ($property->getProperty('is_readonly') == Constants::TRUE)),
															   'disabled' => (($options['readonly']) or ($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_READONLY) or ($property->getProperty('is_readonly') == Constants::TRUE))));
									} else {
										$result .= $this->formCalendar(array('title' => $property->getProperty('name'),
														  'name' => $this->prepareForForm($property->getProperty('name')),
														  'description' => $property->getProperty('description'),
														  'required' => $property->getProperty('is_required'),
														  'showtime' => ($property->getProperty('type_id') == Constants::PROPERTY_TYPE_DATETIME?true:false),
														  'value' => (stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)) <> ''?stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)):($options['process']->getPropertyValue($property->getProperty('name')) <> ''?$options['process']->getPropertyValue($property->getProperty('name')):strftime("%d.%m.%Y", time()))),
													  	  'error' => $this->getError($this->prepareForForm($property->getProperty('name'))), 
													  	  'disabled' => (($options['readonly']) or ($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_READONLY) or ($property->getProperty('is_readonly') == Constants::TRUE))));
									}
								}
								break;
	
							case Constants::PROPERTY_TYPE_OBJECT:
								if ($options['chrono']) {
									storeChronoToCache($options['process']->getProperty('[properties]')->getElement($property->getProperty('name'))->getProperty('value_id'), FILE_CACHE_PATH."/".$options['process']->getPropertyFileName($property->getProperty('name')));
								} else {
									storeToCache($options['process']->getProperty('[properties]')->getElement($property->getProperty('name'))->getProperty('value_id'), FILE_CACHE_PATH."/".$options['process']->getPropertyFileName($property->getProperty('name')));
								}
	
								$result .= $this->formFile(array('title' => $property->getProperty('name'),
													  'name' => $this->prepareForForm($property->getProperty('name')),
													  'description' => $property->getProperty('description'),
													  'required' => $property->getProperty('is_required'),
													  'value' => (stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)) <> ''?stripslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)):$options['process']->getPropertyFileName($property->getProperty('name'))),
													  'error' => $this->getError($this->prepareForForm($property->getProperty('name'))), 
													  'mimetypes' => NULL,
													  'readonly' => (($options['readonly']) or ($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_READONLY) or ($property->getProperty('is_readonly') == Constants::TRUE)),
													  'disabled' => (($options['readonly']) or ($property->getProperty('sign_id') == Constants::PROPERTY_SIGN_READONLY) or ($property->getProperty('is_readonly') == Constants::TRUE))));
								break;
		
							default:
								break;
						}
					}
				}
			}

			$options = array_merge($options, array('title' => 'Данные введены и верны - Отправить документ дальше', 'descr' => 'Записать введённые данные и продолжить дальнейшее выполнение процесса прохождения документа', 'savebtn' => $options['action']->haveNonReadOnlyProperty(), 'colspan' => 4, 'switch' => ($options['action']->getProperty('type_id') == Constants::ACTION_TYPE_SWITCH))); 
			$result .= $this->formEnd($options);
		}
		return $result;
	}
	
	function printGeneratedForm(array $options = array()) {
		print $this->generateForm($options);
	}
	
	function printValidationCode(array $options = array()) {
		print $this->generateValidationCode($options);
	}
	
	function printFinalCode(array $options = array()) {
		print $this->generateFinalCode($options);
	}
	
	function printFullCode(array $options = array()) {
		print $this->generateFullCode($options);
	}
	
	function isMatch($str, $type) {
		global $mime_exts;

		if ((trim($str) == '') and ($this->_nextpass == true)) {
			return true;
		}

		switch ($type) {
			case Constants::PROPERTY_TYPE_NUMBER:
				$exp = '/^\d+([\.\,]\d+)?$/';
				break;

			case Constants::PROPERTY_TYPE_TIME:
				$exp = '/^(\d{1,2})\:(\d{1,2})(\:\d{1,2})?$/';
				break;

			case Constants::PROPERTY_TYPE_DATE:
				$exp = '/^\d{1,4}[\.\-\/]\d{1,2}[\.\-\/]\d{1,4}$/';
				break;

			case Constants::PROPERTY_TYPE_DATETIME:
				$exp = '/^\d{1,4}[\.\-\/]\d{1,2}[\.\-\/]\d{1,4}\s\d{1,2}\:\d{1,2}(\:\d{1,2})?$/';
				break;
				
			case Constants::PROPERTY_TYPE_OBJECT:
				$exp = '/^.*\.('.implode('|', $mime_exts).')$/';
				break;
				
			default:
				$exp = NULL;
				break;
		}
		if (isNotNULL($exp)) {
			return preg_match($exp, $str);
		} else {
			return true;
		}
	}

	function isNotMatch($str, $type) {
		return ($this->isMatch($str, $type)?false:true);
	}
	
	function generateValidationCode(array $options = array()) {
		$result = "";
		
		if (isNotNULL($options['process']) or isNotNULL($options['action'])) {
			if (isNULL($options['process'])) {
				$options['process'] = $options['action']->getOwnerByClass('ProcessInstanceWrapper');
			}
			if (isNULL($options['action'])) {
				$options['action'] = $options['process']->getCurrentAction();
			}
			if (isNULL($options['properties'])) {
				$options['properties'] = $options['action']->getProperty('[action]')->getProperty('[properties]')->getElements();
			}
			
			foreach($options['properties'] as $property) {
				if ($property->getProperty('is_active') == Constants::TRUE) { 
					if ($property->getProperty('is_required') == Constants::TRUE) { 
						switch ($property->getProperty('type_id')) {
							case Constants::PROPERTY_TYPE_TEXT:
							case Constants::PROPERTY_TYPE_STRING:
							case Constants::PROPERTY_TYPE_NUMBER:
							case Constants::PROPERTY_TYPE_TIME:
							case Constants::PROPERTY_TYPE_DATE:
							case Constants::PROPERTY_TYPE_DATETIME:
							case Constants::PROPERTY_TYPE_OBJECT:
//								if ($property->getProperty('is_hidden')) {
// TODO: Пропускает обработку формы без валидации - проверить и исправить
//								} else {
									$result .= "if (\$this->getFormManager()->isNotValid('".$this->prepareForForm($property->getProperty('name'))."', ".($property->getProperty('type_id')).($property->getProperty('is_multiple')?", true":", false").", ".(isNotNULL($property->getProperty('parameters'))?"\"".$property->getProperty('parameters')."\"":"NULL").")) {\n";
									$result .= "  \$this->getFormManager()->setError('".$this->prepareForForm($property->getProperty('name'))."');\n";
									$result .= "}\n\n";
//								}
								break;

							default:
								break;
						}
					} elseif ($property->getProperty('is_readonly') == Constants::FALSE) {
						$result .= "if (!defined('".$this->prepareForForm($property->getProperty('name'))."')) {\n";
						$result .= "  define('".$this->prepareForForm($property->getProperty('name'))."', '".(addslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)) <> ''?addslashes(htmlentities(constant(strtoupper($this->prepareForForm($property->getProperty('name')))), ENT_COMPAT, DEFAULT_CHARSET)):addslashes($options['process']->getPropertyValue($property->getProperty('name'))))."', true);\n";
						$result .= "}\n\n";
					}
				}
			}
		}
		return $result;
	}

	function generateFinalCode(array $options = array()) {
		global $parameters;

		$result = "";
		if (isNotNULL($options['process']) or isNotNULL($options['action'])) {

			if (isNULL($options['process'])) {
				$options['process'] = $options['action']->getOwnerByClass('ProcessInstanceWrapper');
			}
			if (isNULL($options['action'])) {
				$options['action'] = $options['process']->getCurrentAction();
			}
			if (isNULL($options['properties'])) {
				$options['properties'] = $options['action']->getProperty('[action]')->getProperty('[properties]')->getElements();
			}
		
			if ($options['action']->isInteractive() == Constants::TRUE) {
				$result = "if (\$this->getFormManager()->formIsValid()) {\n";
			}

			$options['nextuser'] = NULL;
			$options['nextstep'] = NULL;
			$options['childprocess'] = NULL;

			foreach($options['properties'] as $property) {
				if ($property->getProperty('is_active') == Constants::TRUE) { 
					if ($property->getProperty('is_readonly') == Constants::FALSE) {
						if ($property->getProperty('type_id') <> Constants::PROPERTY_TYPE_OBJECT) {
							if (($property->getProperty('is_multiple')) and (is_array($parameters[strtoupper($this->prepareForForm($property->getProperty('name')))]))) {
								$result .= "  \$this->setPropertyValue('".$property->getProperty('name')."', implode('||', \$parameters[strtoupper('".$this->prepareForForm($property->getProperty('name'))."')])".($property->getProperty('is_required') == Constants::TRUE?", true":"").");\n";
							} else {
								$result .= "  \$this->setPropertyValue('".$property->getProperty('name')."', constant(strtoupper('".$this->prepareForForm($property->getProperty('name'))."'))".($property->getProperty('is_required') == Constants::TRUE?", true":"").");\n";
							}
						} else {
							$result .= "  if (is_array(\$_FILES['".$this->prepareForForm($property->getProperty('name'))."']) and (in_array(\$_FILES['".$this->prepareForForm($property->getProperty('name'))."']['type'], \$mime_names)) and (\$_FILES['".$this->prepareForForm($property->getProperty('name'))."']['size'] <= ".MAX_FILE_SIZE.") and (is_uploaded_file(\$_FILES['".$this->prepareForForm($property->getProperty('name'))."']['tmp_name']))) {\n";
							$result .= "	\$this->setPropertyMimeType('".$property->getProperty('name')."', \$_FILES['".$this->prepareForForm($property->getProperty('name'))."']['type'].'|'.stripNonAlpha(USER_NAME).'_('.strftime(\"%d_%m_%Y_%H_%M\", time()).')_'.basename(\$_FILES['".$this->prepareForForm($property->getProperty('name'))."']['name']));\n";
							$result .= "	\$this->setPropertyValue('".$property->getProperty('name')."', NULL);\n";
							$result .= "	\$blob = Blob::getBlob(\$this, ".$options['process']->getProperty('[properties]')->getElement($property->getProperty('name'))->getProperty('value_id').");\n";
							$result .= "	\$blob->setProperty('blob', base64_encode(file_get_contents(\$_FILES['".$this->prepareForForm($property->getProperty('name'))."']['tmp_name'])));\n";
							$result .= "	\$blob->save();\n";
							$result .= "	move_uploaded_file(\$_FILES['".$this->prepareForForm($property->getProperty('name'))."']['tmp_name'], FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha(USER_NAME).'_('.strftime(\"%d_%m_%Y_%H_%M\", time()).')_'.basename(\$_FILES['".$this->prepareForForm($property->getProperty('name'))."']['name']));\n";
							$result .= "  }\n";
						}
					}
					if (ACTION == 'execute') {
						if ($property->getProperty('is_nextuser') == Constants::TRUE) {
							if (($property->getProperty('is_multiple')) and (is_array($parameters[strtoupper($this->prepareForForm($property->getProperty('name')))]))) {
								$options['nextuser'] = stripslashes(htmlentities(implode('||', $parameters[strtoupper($this->prepareForForm($property->getProperty('name')))]), ENT_COMPAT, DEFAULT_CHARSET));
								$result .= "  \$this->setNextUser('".$options['nextuser']."');\n";
							} else {
								$options['nextuser'] = stripslashes(htmlentities((constant(strtoupper($this->prepareForForm($property->getProperty('name')))) <> ''?constant(strtoupper($this->prepareForForm($property->getProperty('name')))):$options['process']->getPropertyValue($property->getProperty('name'))), ENT_COMPAT, DEFAULT_CHARSET));
								$result .= "  \$this->setNextUser('".$options['nextuser']."');\n";
							}
						}
						if ($property->getProperty('is_childprocess') == Constants::TRUE) {
							if (($property->getProperty('is_multiple')) and (is_array($parameters[strtoupper($this->prepareForForm($property->getProperty('name')))]))) {
								$options['childprocess'] = stripslashes(htmlentities(implode('||', $parameters[strtoupper($this->prepareForForm($property->getProperty('name')))]), ENT_COMPAT, DEFAULT_CHARSET));
								$result .= "  \$this->createChildProcess('".trim($options['childprocess'])."'".(isNotNULL($options['nextuser'])?", '".trim($options['nextuser'])."'":"").");\n";
							} else {
								$options['childprocess'] = stripslashes(htmlentities((constant(strtoupper($this->prepareForForm($property->getProperty('name')))) <> ''?constant(strtoupper($this->prepareForForm($property->getProperty('name')))):$options['process']->getPropertyValue($property->getProperty('name'))), ENT_COMPAT, DEFAULT_CHARSET));
								$result .= "  \$this->createChildProcess('".trim($options['childprocess'])."'".(isNotNULL($options['nextuser'])?", '".trim($options['nextuser'])."'":"").");\n";
							}
						}
					}
				}
			}

			if (ACTION == 'execute') {
				if (($options['action']->getProperty('type_id') == Constants::ACTION_TYPE_SWITCH) and (defined('CHOICE'))) {
					if (CHOICE == 'true') {
						if (isNotNULL($options['action']->getProperty('trueactionname'))) {
							$options['nextstep'] = $options['action']->getProperty('trueactionname');
						}
					} else {
						if (isNotNULL($options['action']->getProperty('falseactionname'))) {
							$options['nextstep'] = $options['action']->getProperty('falseactionname');
						}
					}
					if (isNotNULL($options['nextstep'])) {
						$result .= "  \$this->setNextStep('".trim($options['nextstep'])."'".(isNotNULL($options['nextuser'])?", '".trim($options['nextuser'])."'":"").");\n";
					}
				}
			}
		}
		return $result.((ACTION == 'execute')?"  \$this->complete();\n":((ACTION == 'saveform')?"  \$this->saveForm();\n":"")).($options['action']->isInteractive() == Constants::TRUE?"} else {\n  \$this->getFormManager()->formNotValid();\n}\n\n":"");
	}
	
	function generateFullCode(array $options = array()) {
		return $this->generateValidationCode($options).$this->generateFinalCode($options);
	}
}
?>