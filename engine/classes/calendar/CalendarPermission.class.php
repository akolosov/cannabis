<?php
	class CalendarPermission extends Core {

		public $_calendarpermission = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {

			if (isNotNULL($owner)) {
				parent::__construct($owner, $id, $owner->getConnection());

				if (($id > 0) or (isNotNULL($options['data']))) {
					if (is_null($options['data'])) {
						$calendarpermission = $this->getConnection()->execute('select * from calendar_permissions_list where id = '.$this->_id)->fetch();
					} else {
						$calendarpermission = $options['data'];
					}
					foreach ($calendarpermission as $key => $data) {
						$this->_calendarpermission[$key] = $data;
					}
				}
			}

			$this->_calendarpermission['[model]'] = 'CsCalendarPermission';
		}

		static function create($options = array()) {
			$permission = new CalendarPermission($options['owner']);
			$permission->setProperty('calendar_id', (isNotNULL($options['owner'])?$options['owner']->getProperty('id'):$options['calendar_id']));
			$permission->setProperty('account_id', $options['account_id']);
			$permission->setProperty('permission_id', $options['permission_id']);
			return $permission;
		}

		function getProperty($name) {
			return $this->_calendarpermission[$name];
		}

		function setProperty($name, $value) {
			$this->_calendarpermission[$name] = $value;
		}

		function getPermission() {
			return $this->getProperty('permission_id');
		}

		function save($calendar_id = 0) {
			if ($calendar_id > 0) {
				$this->setProperty('calendar_id', $calendar_id);
			}
			return $this->saveData($this->_calendarpermission['[model]'], $this->_calendarpermission);
		}
	}
?>