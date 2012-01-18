<?php
	class Calendar extends Core {

		public $_calendar = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL, 'startdate' => NULL, 'enddate' => NULL, 'onlydate' => NULL)) {

			if (isNotNULL($owner)) {
				parent::__construct($owner, $id, $owner->getConnection());

				if (($id > 0) or (isNotNULL($options['data']))) {
					if (is_null($options['data'])) {
						$calendar = $this->getConnection()->execute('select * from calendars_list where id = '.$this->_id)->fetch();
					} else {
						$calendar = $options['data'];
					}
					foreach ($calendar as $key => $data) {
						$this->_calendar[$key] = $data;
					}

					$this->_calendar['[startdate]'] = $options['startdate'];
					$this->_calendar['[enddate]'] = $options['enddate'];
					$this->_calendar['[onlydate]'] = $options['onlydate'];

					$this->_calendar['[permissions]'] = new Collection($this);
					$this->_calendar['[events]'] = new Collection($this);
					$this->_calendar['[periodicevents]'] = new Collection($this);
					
					$this->initPermissions();
					$this->initEvents();
					$this->initPeriodicEvents();
				}
			}

			$this->_calendar['[model]'] = 'CsCalendar';
		}

		static function create($options = array()) {
			$calendar = new Calendar($options['owner']);
			$calendar->setProperty('name', $options['name']);
			$calendar->setProperty('description', $options['description']);
			$calendar->setProperty('owner_id', USER_CODE);
			$calendar->setProperty('is_public', $options['is_public']);
			$calendar->setPermissions($options['permissions']);
			$calendar->setProperty('[permissions]', new Collection($calendar));
			$calendar->setProperty('[events]', new Collection($calendar));
			$calendar->setProperty('[periodicevents]', new Collection($calendar));
			return $calendar;
		}

		function createEvent($options = array()) {
			$options['owner'] = $this;
			$event = CalendarEvent::create($options);
			$this->_calendar['[events]']->setElement((($event->getProperty('id') > 0)?$event->getProperty('id'):mt_rand()), $event);
			return $event;
		}

		function createPermission($options = array()) {
			$options['owner'] = $this;
			$permission = CalendarPermission::create($options);
			$this->_calendar['[permissions]']->setElement($permission->getProperty('account_id'), $permission);
			return $permission;
		}

		function getProperty($name) {
			return $this->_calendar[$name];
		}

		function setProperty($name, $value) {
			$this->_calendar[$name] = $value;
		}

		function initPermissions() {
			$permissions = $this->getConnection()->execute('select * from calendar_permissions_list where calendar_id = '.$this->getProperty('id'))->fetchAll();
			foreach ($permissions as $permission) {
				$this->_calendar['[permissions]']->setElement($permission['account_id'], new CalendarPermission($this, $permission['id'], array('data' => $permission)));
			}
		}

		function initEvents() {
			$events = $this->getConnection()->execute('select * from calendar_events_list where parent_id is null and calendar_id = '.$this->getProperty('id').(isNotNULL($this->getProperty('[onlydate]'))?" and (date(started_at) = '".$this->getProperty('[onlydate]')."' or date(ended_at) = '".$this->getProperty('[onlydate]')."')":(isNotNULL($this->getProperty('[startdate]'))?" and date(started_at) >= '".$this->getProperty('[startdate]')."'":"").(isNotNULL($this->getProperty('[enddate]'))?" and date(ended_at) <= '".$this->getProperty('[enddate]')."'":"")).' order by started_at')->fetchAll();
			foreach ($events as $event) {
				$this->_calendar['[events]']->setElement($event['id'], new CalendarEvent($this, $event['id'], array('data' => $event)));
			}
		}

		function initPeriodicEvents() {
			$events = $this->getConnection()->execute('select * from calendar_events_list where parent_id is not null and calendar_id = '.$this->getProperty('id').(isNotNULL($this->getProperty('[onlydate]'))?" and (date(started_at) = '".$this->getProperty('[onlydate]')."' or date(ended_at) = '".$this->getProperty('[onlydate]')."')":(isNotNULL($this->getProperty('[startdate]'))?" and date(started_at) >= '".$this->getProperty('[startdate]')."'":"").(isNotNULL($this->getProperty('[enddate]'))?" and date(ended_at) <= '".$this->getProperty('[enddate]')."'":"")).' order by started_at')->fetchAll();
			foreach ($events as $event) {
				$event['is_dynamic'] = true;
				$this->_calendar['[periodicevents]']->setElement($event['id'], new CalendarEvent($this, $event['id'], array('data' => $event)));
			}
		}

		function getAllEvents() {
			$result = array_merge($this->_calendar['[events]']->getElements(), $this->_calendar['[periodicevents]']->getElements());

			usort($result, 'compareEventStartedAt');
			return $result;
		}

		function getEvents() {
			return $this->_calendar['[events]']->getElements();
		}

		function getEvent($event_id = NULL) {
			if ($this->_calendar['[events]']->elementExists($event_id)) {
				return $this->_calendar['[events]']->getElement($event_id);
			} elseif ($this->_calendar['[periodicevents]']->elementExists($event_id)) {
				return $this->_calendar['[periodicevents]']->getElement($event_id);
			} else {
				return NULL;
			}
		}

		function saveEvents() {
			foreach ($this->getAllEvents() as $event) {
				$event->save($this->getProperty('id'));
			}
		}

		function savePermissions() {
			foreach ($this->getPermissions() as $permission) {
				$permission->save($this->getProperty('id'));
			}
		}

		function copyPermissionsFrom($parent = NULL) {
			if ((isNotNULL($parent)) and (is_a($parent, 'Calendar'))) {
				$this->setPermissions($parent->getPermissions());
			}
		}

		function setPermission($account = NULL, $permission = NULL) {
			if (isNotNULL($accountname)) {
				if (isNULL($permission)) {
					$permission = new CalendarPermission($this);
					$permission->setProperty('calendar_id', $this->getProperty('id'));
					$permission->setProperty('permission_id', Constants::PERMISSION_READ_ONLY);
					$permission->setProperty('account_id', $account);
				}
				$this->getProperty('[permissions]')->setElement($account, $permission);
			}
		}

		function setPermissions($permissions = array()) {
			if (isNotNULL($permissions)) {
				foreach ($permissions as $key => $data) {
					$this->setPermission($key, $data);
				}
			}
		}

		function getPermission($account = NULL) {
			return $this->getProperty('[permissions]')->getElement($account);
		}

		function getPermissionValue($account = NULL) {
			if ($this->getProperty('[permissions]')->elementExists($account)) {
				return $this->getProperty('[permissions]')->getElement($account)->getPermission();
			} else {
				return Constants::PERMISSION_NO_ACCESS;
			}
		}

		function getPermissions() {
			return $this->_calendar['[permissions]']->getElements();
		}

		function save() {
			logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_contactlist['[model]']);

			if (isNULL($this->getProperty('owner_id'))) {
				$this->setProperty('owner_id', USER_CODE);
			}
			$result = $this->saveData($this->_calendar['[model]'], $this->_calendar);
			$this->setProperty('id', $result);
			$this->saveEvents();
			$this->savePermissions();
			return $result;
		}

	}
?>