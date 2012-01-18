<?php

// $Id$

	class Template extends Core {
		public $_template = array();
		
		function __construct($owner = NULL) {
			parent::__construct($owner, 0, $owner->getConnection());
			$this->initDefaults();
		}

		function getTemplate() {
			return $this->_template;
		}

		function setTemplate(array $template = array()) {
			$this->initDefaults();
			$this->_template = array_merge($this->_template, $template);
		}

		function setValueTo($name, $value) {
			$this->_template[$name] = $value;
		}

		function process($text = NULL) {
			if (isNotNull($text) and isNotNull($this->_template)) {
				$text = $this->simpleProcess($text);
				
				if (preg_match_all('/%%(emailof|icqof|jabberof|cellof)\[[а-яa-z0-9\.\s\-]*\]%%/ui', $text, $tagmatches, PREG_SET_ORDER)) {
					foreach ($tagmatches as $tagmatch) {
						if (preg_match_all('/%%'.$tagmatch[1].'\[([а-яa-z0-9\.\s\-]*)\]%%/ui', $text, $matches, PREG_SET_ORDER)) {
							foreach ($matches as $match) {
								if (preg_match('/^\d*$/', $match)) {
									$account = $this->getConnection()->execute('select * from accounts_tree where id = '.trim($match[1]))->fetch();
								} else {
									$account = $this->getConnection()->execute('select * from accounts_tree where name = \''.trim($match[1]).'\'')->fetch();
								}
								$field = strtolower(substr($tagmatch[1], 0, strlen($tagmatch[1])-2));
								$text = mb_str_ireplace('%%'.$tagmatch[1].'['.$match[1].']%%', (($field == 'cell')?(isNotNULL($account['cellop'])?preg_replace('/[^\d]/', '', preg_replace('/^(8)(.*)/', '7$2', $account[$field])).'@'.$account['cellop']:''):(isNotNULL($account[$field])?$account[$field]:'')), $text);
							}
						}
					}
				}
				
				if (preg_match_all('/%%(emailsof|icqsof|jabbersof|cellsof)\[[а-яa-z0-9\.\s\-\,\;]*\]%%/ui', $text, $tagmatches, PREG_SET_ORDER)) {
					foreach ($tagmatches as $tagmatch) {
						if (preg_match_all('/%%'.$tagmatch[1].'\[([а-яa-z0-9\.\s\-\,\;]*)\]%%/ui', $text, $matches, PREG_SET_ORDER)) {
							foreach ($matches as $match) {
								foreach(preg_split('/[\,\;]/', $match[1]) as $splited) {
									if (preg_match('/^\d*$/', $match)) {
										$account = $this->getConnection()->execute('select * from accounts_tree where id = '.trim($splited))->fetch();
									} else {
										$account = $this->getConnection()->execute('select * from accounts_tree where name = \''.trim($splited).'\'')->fetch();
									}
									$field = strtolower(substr($tagmatch[1], 0, strlen($tagmatch[1])-3));
									$replace .= (($field == 'cell')?(isNotNULL($account['cellop'])?preg_replace('/[^\d]/', '', preg_replace('/^(8)(.*)/', '7$2', $account[$field])).'@'.$account['cellop']:''):(isNotNULL($account[$field])?$account[$field]:''))."   "; 
								}
								$text = mb_str_ireplace('%%'.$tagmatch[1].'['.$match[1].']%%', str_replace('   ', ', ', trim($replace)), $text);
							}
						}
					}
				}
				
			}
			return $text;
		}

		function simpleProcess($text = NULL) {
			if (isNotNull($text) and isNotNull($this->_template)) {
				foreach ($this->_template as $key => $data) {
					if ((isNotNULL($key)) and (isNotNULL($data))) {
						$text = mb_str_ireplace('%%'.$key.'%%', stripslashes($data), $text);
					} elseif ((isNotNULL($key)) and (isNULL($data))) {
						$text = mb_str_ireplace('%%'.$key.'%%', "", $text);
					}
				}
			}
			return $text;
		}

		function initDefaults() {
			global $parameters;

			$this->_template['ENGINE_NAME']				= constant('ENGINE_NAME');
			$this->_template['ENGINE_VERSION']			= constant('ENGINE_VERSION');
			$this->_template['ENGINE_BUILD']			= constant('ENGINE_BUILD');
			$this->_template['ENGINE_DESCR']			= constant('ENGINE_DESCR');
			$this->_template['USER_CODE']				= constant('USER_CODE');
			$this->_template['USER_NAME']				= constant('USER_NAME');
			$this->_template['USER_DESCR']				= constant('USER_DESCR');
			$this->_template['USER_MAIL']				= constant('USER_MAIL');
			$this->_template['USER_GROUPCODE']			= constant('USER_GROUPCODE');
			$this->_template['USER_GROUPNAME']			= constant('USER_GROUPNAME');
			$this->_template['USER_ABOVEGROUPCODE']		= constant('USER_ABOVEGROUPCODE');
			$this->_template['USER_ABOVEGROUPNAME']		= constant('USER_ABOVEGROUPNAME');
			$this->_template['USER_POSTS']				= constant('USER_POSTS');
			$this->_template['USER_DIVISIONS']			= constant('USER_DIVISIONS');
			$this->_template['USER_DIVISIONCODE']		= constant('USER_DIVISIONCODE');
			$this->_template['USER_DIVISIONNAME']		= constant('USER_DIVISIONNAME');
			$this->_template['USER_BOSSCODE']			= constant('USER_BOSSCODE');
			$this->_template['USER_BOSSNAME']			= constant('USER_BOSSNAME');
			$this->_template['USER_ABOVEDIVISIONCODE']	= constant('USER_ABOVEDIVISIONCODE');
			$this->_template['USER_ABOVEDIVISIONNAME']	= constant('USER_ABOVEDIVISIONNAME');
			$this->_template['SERVER_URI']				= (isNotNULL($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER['SERVER_NAME']."/";
			$this->_template['CURRENT_SERVER_URI']		= (isNotNULL($_SERVER['HTTPS'])?"https":"http")."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			$this->_template['INBOX_MODULE']			= 'runtime/inboxes/list';
			$this->_template['OUTBOX_MODULE']			= 'runtime/outboxes/list';
			$this->_template['INBOX_PROCESS_MODULE']	= 'runtime/inboxes/processes/list';
			$this->_template['HISTORY_MODULE']			= 'runtime/inboxes/history/list';
			$this->_template['HISTORY_PROCESS_MODULE']	= 'runtime/inboxes/history/processes/list';
			$this->_template['INBOX_ACTION_MODULE']		= 'runtime/inboxes/actions/list';
			$this->_template['ENGINE_EMAIL']			= $this->getConstantValue('server_email');
			$this->_template['CURRENT_DATE']			= strftime("%d.%m.%Y", time());
			$this->_template['CURRENT_TIME']			= strftime("%H:%M:%S", time());
			$this->_template['CURRENT_DATETIME']		= strftime("%d.%m.%Y %H:%M:%S", time());

			foreach ($parameters as $key => $data) {
				if (!is_array($data)) {
					$this->_template['PARAMS_'.strtoupper($key)] = $data;
				} else {
					$this->_template['PARAMS_'.strtoupper($key)] = implode('||', $data);
				}
			}
		}
	}
?>