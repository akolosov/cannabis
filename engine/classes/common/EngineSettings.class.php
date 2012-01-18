<?php
	class EngineSettings extends Core {

		public $_settings = array();

		function __construct($owner = NULL, $user_id = 0) {
			if (isNotNULL($owner)) {
				parent::__construct($owner, $user_id, $owner->getConnection());
	
				if ($user_id >= 0) {
					$settings = $this->getConnection()->execute('select * from custom_settings where account_id = '.$user_id)->fetchAll();
					foreach ($settings as $setting) {
						$this->_settings[$setting['modulename']] = $setting;
					}
				}
			}

			$this->_settings['[model]'] = "CsCustomSetting";
		}

		function getModuleSettingsProperty($modulename = NULL, $property = NULL) {
			if ((isNotNULL($modulename)) and (isNotNULL($property))) {
				return $this->_settings[$modulename][$property];
			} else {
				return NULL;
			}
		}

		function getModuleSetup($modulename = NULL) {
			if (isNotNULL($modulename)) {
				return $this->getModuleSettingsProperty($modulename, 'setup');
			} else {
				return NULL;
			}
		}

		function save() {
			if (isNotNULL($this->_settings)) {
				foreach ($this->_settings as $settins) {
					$this->saveData($this->_settings['[model]'], $settings);
				}
				return true;
			} else {
				return false;
			}
		}
	}
?>