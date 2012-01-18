<?php

// $Id$

	class Core {

		public $_owner		= NULL;
		public $_connection	= NULL;
		public $_id			= NULL;
		public $_debug		= false;
		public $_properties	= array();

		function __construct($owner = NULL, $id = 0, $connection = NULL) {
			$this->_owner		= $owner;
			$this->_connection	= $connection;
			$this->_id			= $id;
			$this->_debug		= DEBUG_MODE;
		}

		function getProperty($name) {
			return $this->_properties[strtolower($name)];
		}

		private function __get($name) {
			if ($this->_debug) {
				logDebug('get value from "'.get_class($this).'->'.strtolower($name).'"');
			}
			
			if (isset($this->_properties[strtolower($name)])) {
				return $this->_properties[strtolower($name)];
			} else {
				return NULL;
			}
		}

		private function __set($name, $value) {
			if ($this->_debug) {
				logDebug('set to "'.get_class($this).'->'.strtolower($name).'" value "'.$value.'"');
			}

			$this->_properties[strtolower($name)] = $value;
		}

		private function __isset($name) {
			if ($this->_debug) {
				logDebug('test value of "'.get_class($this).'->'.strtolower($name).'"');
			}

			return isset($this->_properties[strtolower($name)]);
		}

		private function __unset($name) {
			if ($this->_debug) {
				logDebug('unset value of "'.get_class($this).'->'.strtolower($name).'"');
			}

			unset($this->_properties[strtolower($name)]);
		}

		function erase() {
			if ((isNotNULL($this->getProperty('[model]'))) and (isNotNULL($this->getProperty('id')))) {
				$this->getConnection()->getTable($this->getProperty('[model]'))->find($this->getProperty('id'))->delete();
			}
		}

		function save() {
			return 0;
		}

		function delete() {
			$this->setProperty('is_deleted', true);
			$this->save();
		}

		function undelete() {
			$this->setProperty('is_deleted', false);
			$this->save();
		}

		function isDeleted() {
			if (($this->getProperty('is_deleted')) or
				(((is_a($this, 'Message')) or (is_a($this, 'MessageReciever'))) and ($this->getProperty('status_id') == Constants::MESSAGE_DELETED)) or
				(((is_a($this, 'CalendarEvent')) or (is_a($this, 'CalendarEventReciever'))) and ($this->getProperty('status_id') == Constants::EVENT_STATUS_DELETED))) { 
				return true;
			} else {
				return false;
			}
		}

		protected function saveData($model = NULL, array $data = array()) {
			logRuntime('['.get_class($this).'.saveData] try to save data to '.$model);
			if ((!is_null($model)) && (count($data) > 0)) {
				$value = $this->getConnection()->getTable($model)->find($this->_id);
				if (!$value) {
					$value = $this->getConnection()->getTable($model)->create();
				}
	
				foreach ($data as $key => $datavalue) {
					if ((!preg_match('/^\d*$/', $key)) and (!preg_match('/^\[.*\]$/', $key)) and ($key <> 'id') and ($this->getConnection()->getTable($model)->hasColumn($key))) {
						if (isNotNULL($datavalue)) {
							$value[$key] = prepareForSave($datavalue);
						} else {
							$value[$key] = $this->getConnection()->getTable($model)->getDefaultValueOf($key);
						}
					}
				}
				$value->save();

				logRuntime('['.get_class($this).'.saveData] data to '.$model.' saved');

				if (($data['id'] <> $value['id']) or (is_null($data['id']))) {
					$data['id']	= $value['id'];
				}
				if (($this->_id <= 0) or (is_null($this->_id))) {
					$this->_id	= $value['id'];
				}
				
				return $value['id'];
			} else {
				logRuntime('['.get_class($this).'.saveData] data for '.$model.' is not valid or '.$model.'is not valid!');
				return 0;
			}
		}

		protected function exportData($model = NULL, array $data = array()) {
			$result = "";
			logRuntime('['.get_class($this).'.exportData] try to export data model '.$model);
			if ((!is_null($model)) && (count($data) > 0)) {
				foreach ($data as $key => $value) {
 					if ((!preg_match('/^\d*$/', $key)) and (!preg_match('/^\[.*\]$/', $key)) and (isNotNULL($value)) and ($this->getConnection()->getTable($model)->hasColumn($key))) {
						$result .= "<".$key.">".addslashes(htmlentities($value, ENT_COMPAT, DEFAULT_CHARSET))."</".$key.">\n";
					}
				}
				logRuntime('['.get_class($this).'.exportData] data model '.$model.' exported');
			} else {
				logRuntime('['.get_class($this).'.exportData] data model '.$model.' is not valid or '.$model.'is not valid!');
			}
			return $result;
		}

		static function importData($class, $model, array $data = array(), $connection = NULL) {
			global $engine;

			if (class_exists($class)) {
				$result = new $class(NULL, 0);
				$result->setProperty('[model]', $model);

				if (is_resource($connection)) {
					$result->_connection = $connection;
				} else {
					$result->_connection = $engine->getConnection();
				}
				
				foreach ($data as $key => $value) {
					if (($result->getConnection()->getTable($model)->hasColumn($key)) and (isNotNULL($value))) {
						if ($key <> 'id') {
							$result->setProperty($key, $value);
						} else {
							$result->setProperty('__id__', $value);
						}
					}
				}
			}
			return $result; 
		}

		function getConnection() {
			return $this->_connection;
		}

		function setConnection($connection = NULL) {
			if (isNotNULL($connection)) {
				$this->_connection = $connection;
			}
		}

		function getEngine() {
			if (is_a($this, 'Engine')) {
				return $this;
			} else {
				if ($this->_owner) {
					return $this->_owner->getEngine();
				} else {
					return NULL;
				}
			}
		}

		function getFormManager() {
			if (is_a($this, 'FormManager')) {
				return $this;
			} else {
				if ($this->_owner) {
					return $this->_owner->getFormManager();
				} else {
					return NULL;
				}
			}
		}

		function getFileManager() {
			if (is_a($this, 'FileManager')) {
				return $this;
			} else {
				if ($this->_owner) {
					return $this->_owner->getFileManager();
				} else {
					return NULL;
				}
			}
		}

		function getEngineSettings() {
			if (is_a($this, 'EngineSettings')) {
				return $this;
			} else {
				if ($this->_owner) {
					return $this->_owner->getEngineSettings();
				} else {
					return NULL;
				}
			}
		}

		function getProject() {
			if ((is_a($this, 'ProjectInstance')) or (is_a($this, 'ProjectInstanceWrapper'))) {
				return $this;
			} else {
				if ($this->_owner) {
					return $this->_owner->getProject();
				} else {
					return NULL;
				}
			}
		}

		function getOwnerByClass($name) {
			if (is_a($this, $name)) {
				return $this;
			} else {
				if ($this->_owner) {
					return $this->_owner->getOwnerByClass($name);
				} else {
					return NULL;
				}
			}
		}

		function getNameByID($id, $tableORmodel) {
			$result = $this->getRecordByParams('id = '.$id, $tableORmodel);
			
			return $result['name'];
		}

		function getFieldByID($id, $fieldname, $tableORmodel) {
			$result = $this->getRecordByParams('id = '.$id, $tableORmodel);
			
			return $result[$fieldname];
		}

		function getFieldByParams($params, $fieldname, $tableORmodel) {
			$result = $this->getRecordByParams($params, $tableORmodel);
			
			return $result[$fieldname];
		}

		function getFieldByName($name, $fieldname, $tableORmodel) {
			$result = $this->getRecordByParams('name = \''.$name.'\'', $tableORmodel);
			
			return $result[$fieldname];
		}

		function getIDByName($name, $tableORmodel) {
			$result = $this->getRecordByParams('name = \''.$name.'\'', $tableORmodel);
			
			return $result['id'];
		}

		function getRecordByID($id, $tableORmodel) {
			return $this->getRecordByParams('id = '.$id, $tableORmodel);
		}

		function getRecordByParams($where, $tableORmodel) {
			$tableORmodel = $this->getTableName($tableORmodel);
			$result = $this->getConnection()->execute('select * from '.$tableORmodel.' where '.$where)->fetch();
			
			return $result;
		}

		function getRecordsByParams($exp, $tableORmodel) {
			$tableORmodel = $this->getTableName($tableORmodel);
			$result = $this->getConnection()->execute('select * from '.$tableORmodel.' where '.$exp)->fetchAll();
			
			return $result;
		}

		function getConstantValue($name) {
			$result = $this->getConnection()->execute('select value from cs_constants where name = \''.trim($name).'\'')->fetch();

			return $result['value'];
		}

		function getTableName($template) {
			$result = $template;
			if (preg_match("/^Cs[A-Z].*/", $template)) {
				$result = $this->getConnection()->getTable($template)->getTableName();
			} elseif (preg_match("/^[А-ЯA-Z].*$/u", $template)) {
				$result = $this->getFieldByName($template, 'tablename', 'CsDirectory');
			}
			return $this->getEngine()->getTemplate()->process($result);
		}

		function editDirectoryRecord(array $options = array()) {
			global $mime_names;

			if ((isNotNULL($options['directory'])) and (isNotNULL($options['data']))) {
				if (is_null($options['record'])) {
					$options['record'] = new DirectoryRecord($options['directory'], 0);
					$options['record']->setProperty('id', $options['record']->save());
					$options['newrecord'] = true;
				} else {
					$options['newrecord'] = false;
				}

				foreach ($options['data'] as $key => $data) {
					if (($options['newrecord'] == false) and ($options['record']->getProperty('[values]')->elementExists($key))) {
						$value = $options['record']->getValue($key);
					} else {
						$value = new DirectoryValue($options['record'], 0);
					}
					$value->setProperty('field_id', $options['directory']->getField($key)->getProperty('id'));
					$value->setProperty('record_id', $options['record']->getProperty('id'));

					if ($options['directory']->getField($key)->getProperty('type_id') == Constants::PROPERTY_TYPE_OBJECT) {
						if (is_array($_FILES['dir_'.$this->getFormManager()->prepareForForm($options['directory']->getField($key)->getProperty('name'))]) and
						   (in_array($_FILES['dir_'.$this->getFormManager()->prepareForForm($options['directory']->getField($key)->getProperty('name'))]['type'], $mime_names)) and
						   ($_FILES['dir_'.$this->getFormManager()->prepareForForm($options['directory']->getField($key)->getProperty('name'))]['size'] <= MAX_FILE_SIZE) and
						   (is_uploaded_file($_FILES['dir_'.$this->getFormManager()->prepareForForm($options['directory']->getField($key)->getProperty('name'))]['tmp_name']))) {

						   	if (file_exists(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.($value->getFileName()))) {
								unlink(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.($value->getFileName()));
							}

							$value->setProperty('mime_type', $_FILES['dir_'.$this->getFormManager()->prepareForForm($options['directory']->getField($key)->getProperty('name'))]['type'].'|'.stripNonAlpha(USER_NAME).'_('.strftime("%d_%m_%Y_%H_%M", time()).')_'.basename($_FILES['dir_'.$this->getFormManager()->prepareForForm($options['directory']->getField($key)->getProperty('name'))]['name']));
							$value->setProperty('value', NULL);
							$value->setProperty('id', $value->save());
							
							$blob = DirectoryBlob::getBlob($value, $value->getProperty('id'));
							$blob->setProperty('blob', base64_encode(file_get_contents($_FILES['dir_'.$this->getFormManager()->prepareForForm($options['directory']->getField($key)->getProperty('name'))]['tmp_name'])));
							$blob->save();

							move_uploaded_file($_FILES['dir_'.$this->getFormManager()->prepareForForm($options['directory']->getField($key)->getProperty('name'))]['tmp_name'], FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha(USER_NAME).'_('.strftime("%d_%m_%Y_%H_%M", time()).')_'.basename($_FILES['dir_'.$this->getFormManager()->prepareForForm($options['directory']->getField($key)->getProperty('name'))]['name']));
							
							$blob = NULL;
						}
					} else {
						$value->setProperty('value', $data);
						$value->setProperty('id', $value->save());

						if ($options['directory']->getField($key)->getProperty('autoinc') == Constants::TRUE) {
							$options['directory']->getField($key)->setProperty('default_value', $data);
							$options['directory']->getField($key)->save();
						}
					}
				}
			}
		}

		function getDirectoryList(array $options = array()) {
			$options = array_merge(array('directory' => NULL, 'valueasname' => false, 'value_field' => 'id', 'custom' => false, 'order' => NULL, 'full' => false, 'parameters' => NULL, 'where' => NULL), $options);

			if (($options['valueasname']) and (($options['value_field'] == 'id') or (isNULL($options['value_field'])))) {
				$options['value_field'] = 'name';
			}

			$result = array();
			if ($options['custom']) {
				$dirinfo = $this->getConnection()->execute('select * from cs_directory where '.(isNotNULL($options['directory'])?'name =\''.$options['directory'].'\'':'id = '.$options['directory_id']))->fetch();
				if (isNotNULL($dirinfo)) {
					$directory = new DirectoryInfo($this, $dirinfo['id'], array('data' => $dirinfo));
					foreach ($directory->getRecords() as $record) {
						if ($options['full']) {
							$values = array();
							$values['[directory_id]'] = $directory->getProperty('id');
							$values['[record_id]'] = $record->getProperty('id');
							foreach($directory->getFields() as $field) {
								if ($record->getProperty('[values]')->elementExists($field->getProperty('name'))) {
									$values[$field->getProperty('name')] = $record->getValue($field->getProperty('name'))->getProperty('value');
								} else {
									$values[$field->getProperty('name')] = '';
								}
							}
							$result[] = $values;
						} else {
							$result["\"".$record->getValue($options['value_field'])->getProperty('value')."\""] = $record->getValue('name')->getProperty('value').' ('.$record->getValue('description')->getProperty('value').')';
						}
					}
				}
			} else {
				$options['directory'] = $this->getTableName($options['directory']);
				if (isNotNULL($options['directory'])) {
					$where = ($options['where']?" where ".stripslashes($options['where']):"");
					$parameters = ($options['parameters']?"(".stripslashes($options['parameters']).")":"");
					$list = $this->getConnection()->execute('select * from '.$options['directory'].$parameters.' '.$where.(isNotNULL($options['order'])?' order by '.$options['order']:''))->fetchAll();
					foreach ($list as $item) {
						if ($options['full']) {
							$result[] = $item;
						} else {
							$result[$item[$options['value_field']]] = str_pad_html("", $item['level']).$item['name'].' ('.$item['description'].')';
						}
					}
				}
			}
			return $result;
		}

		function prepareDirectoryParameters($directory) {
			$result = array('directory' => NULL, 'where' => NULL, 'parameters' => NULL);
			
			if (isNotNULL($directory['parameters']) and (trim($directory['parameters']) <> "")) {
				if (preg_match("/([\=\<\>]+)|(\sis\s)|(\snot\s)/i", $directory['parameters'])) {
					// это WHERE 
					$result['where'] = $this->getEngine()->getTemplate()->simpleProcess($directory['parameters']);
				} else {
					$result['parameters'] = $this->getEngine()->getTemplate()->simpleProcess($directory['parameters']);
				}
			}
			
			$result['directory'] = $this->getEngine()->getTemplate()->simpleProcess($directory['tablename']);

			$result['where'] = preg_replace('/%%[а-яa-z0-9\_]*%%/ui', '1', $result['where']);
			$result['parameters'] = preg_replace('/%%[а-яa-z0-9\_]*%%/ui', '1', $result['parameters']);
			$result['directory'] = preg_replace('/%%[а-яa-z0-9\_]*%%/ui', '1', $result['directory']);

			return $result;
		}

		function getAccountsWithoutGroupsList(array $options = array()) {
			$options = array_merge(array('valueasname' => false, 'where' => NULL), $options);
			
			$result = $this->getDerectoryList(array('directory' => 'cs_account', 'valueasname' => $options['valueasname'], 'where' => 'permission_id is not null and passwd is not null'.(isNotNULL($options['where'])?' and ('.$options['where'].')':'')));
			return $result;
		}

		function sendMessage($event = 0) {
		}

		private function sendTextMessage($transport = NULL, $recipients = array(), $subject = 'Текстовой сообщение', $message = NULL, $options = array()) {
			if ((isNotNULL($transport)) and (isNotNULL($recipients)) and (isNotNULL($message))) {
				return $transport->send(array('to' => $recipients, 'subject' => $subject, 'text' => $message));
			} else {
				return false;
			}
		}

		function sendMessageByMail($recipients = array(), $subject = 'Текстовой сообщение', $message = NULL, $options = array()) {
			if ((isNotNULL($recipients)) and (isNotNULL($message))) {
				$transport = new TransportMail($this);
				return $this->sendTextMessage($transport, $recipients, $subject, $message, $options = array());
			} else {
				return false;
			}
		}

		function sendMessageBySMTPMail($recipients = array(), $subject = 'Текстовой сообщение', $message = NULL, $options = array()) {
			if ((isNotNULL($recipients)) and (isNotNULL($message))) {
				$transport = new TransportSMTPMail($this);
				return $this->sendTextMessage($transport, $recipients, $subject, $message, $options = array());
			} else {
				return false;
			}
		}

		function sendMessageByJabber($recipients = array(), $subject = 'Текстовой сообщение', $message = NULL, $options = array()) {
			if ((isNotNULL($recipients)) and (isNotNULL($message))) {
				$transport = new TransportJabber($this);
				return $this->sendTextMessage($transport, $recipients, $subject, $message, $options = array());
			} else {
				return false;
			}
		}

		function sendMessageByICQ($recipients = array(), $subject = 'Текстовой сообщение', $message = NULL, $options = array()) {
			if ((isNotNULL($recipients)) and (isNotNULL($message))) {
				$transport = new TransportICQ($this);
				return $this->sendTextMessage($transport, $recipients, $subject, $message, $options = array());
			} else {
				return false;
			}
		}

		function sendMessageBySMS($recipients = array(), $subject = 'Текстовой сообщение', $message = NULL, $options = array()) {
			if ((isNotNULL($recipients)) and (isNotNULL($message))) {
				$transport = new TransportSMS($this);
				return $this->sendTextMessage($transport, $recipients, $subject, $message, $options = array());
			} else {
				return false;
			}
		}

		function getAccountProperty($name = NULL, $property = NULL) {
			if ((isNotNULL($name)) and (isNotNULL($property))) {
				$account = $this->getConnection()->execute('select * from account_tree where name = \''.$name.'\'')->fetch();
				return $account[$property];
			} else {
				return NULL;
			}
		}
	}
?>