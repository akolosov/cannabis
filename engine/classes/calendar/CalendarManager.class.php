<?php
	class CalendarManager extends Core {

		public $_calendarmanager = array();

		function __construct($owner = NULL, $id = 0, $options = array('startdate' => NULL, 'enddate' => NULL, 'onlydate' => NULL, 'ids' => NULL)) {
			if (isNULL($id)) {
				$id = USER_CODE;
			}

			if (isNotNULL($owner)) {
				parent::__construct($owner, $id, $owner->getConnection());

				$this->_calendarmanager['[options]'] = $options;

				$this->reinitCalendarManager();
			}
		}

		function getProperty($name) {
			return $this->_calendarmanager[$name];
		}

		function setProperty($name, $value) {
			$this->_calendarmanager[$name] = $value;
		}

		function reinitCalendarManager() {
			$this->initOwnedCalendars();
			$this->initDelegatedCalendars();
			$this->initPublicCalendars();
		}

		function initOwnedCalendars() {
			$this->_calendarmanager['[owned]'] = new Collection($this);

			$ownedcalendars = $this->getConnection()->execute('select * from calendars_list where (owner_id = '.$this->_id.')'.((isNotNULL($this->_calendarmanager['[options]']['ids']))?' and id in ('.$this->_calendarmanager['[options]']['ids'].')':''))->fetchAll();
			foreach ($ownedcalendars as $ownedcalendar) {
				$this->_calendarmanager['[owned]']->setElement($ownedcalendar['id'], new Calendar($this, $ownedcalendar['id'], array_merge(array('data' => $ownedcalendar), $this->getProperty('[options]'))));
			}
		}

		function initDelegatedCalendars() {
			$this->_calendarmanager['[delegated]'] = new Collection($this);

			$delegatedcalendars = $this->getConnection()->execute('select * from calendars_list where is_public = false and is_deleted = false and id in (select calendar_id from cs_calendar_permission where account_id = '.$this->_id.')'.((isNotNULL($this->_calendarmanager['[options]']['ids']))?' and id in ('.$this->_calendarmanager['[options]']['ids'].')':''))->fetchAll();
			foreach ($delegatedcalendars as $delegatedcalendar) {
				$delegatedcalendar['is_delegated'] = true;
				$this->_calendarmanager['[delegated]']->setElement($delegatedcalendar['id'], new Calendar($this, $delegatedcalendar['id'],  array_merge(array('data' => $delegatedcalendar), $this->getProperty('[options]'))));
			}
		}

		function initPublicCalendars() {
			$this->_calendarmanager['[public]'] = new Collection($this);

			$publiccalendars = $this->getConnection()->execute('select * from calendars_list where is_public = true and is_deleted = false and owner_id <> '.$this->_id.((isNotNULL($this->_calendarmanager['[options]']['ids']))?' and id in ('.$this->_calendarmanager['[options]']['ids'].')':''))->fetchAll();
			foreach ($publiccalendars as $publiccalendar) {
				$delegatedcalendar['is_delegated'] = true;
				$this->_calendarmanager['[public]']->setElement($publiccalendar['id'], new Calendar($this, $publiccalendar['id'], array_merge(array('data' => $publiccalendar), $this->getProperty('[options]'))));
			}
		}

		function getOwnedCalendar($calendar_id = 0) {
			return $this->_calendarmanager['[owned]']->getElement($calendar_id);
		}

		function getOwnedCalendars() {
//			return $this->_calendarmanager['[owned]']->getElements();
		}

		function getPublicCalendar($calendar_id = 0) {
			return $this->_calendarmanager['[public]']->getElement($calendar_id);
		}

		function getPublicCalendars() {
//			return $this->_calendarmanager['[public]']->getElements();
		}

		function getDelegatedCalendar($calendar_id = 0) {
			return $this->_calendarmanager['[delegated]']->getElement($calendar_id);
		}

		function getDelegatedCalendars() {
//			return $this->_calendarmanager['[delegated]']->getElements();
		}

		function getCalendar($calendar_id = 0) {
			if ($this->_calendarmanager['[owned]']->elementExists($calendar_id)) {
				return $this->_calendarmanager['[owned]']->getElement($calendar_id);
			} elseif ($this->_calendarmanager['[delegated]']->elementExists($calendar_id)) {
				return $this->_calendarmanager['[delegated]']->getElement($calendar_id);
			} elseif ($this->_calendarmanager['[public]']->elementExists($calendar_id)) {
				return $this->_calendarmanager['[public]']->getElement($calendar_id);
			} else {
				return NULL;
			}
		}

		function getAllCalendars() {
			return array_merge($this->getOwnedCalendars(), $this->getDelegatedCalendars(), $this->getPublicCalendars());
		}

		function getAllEvents() {
			$result = array();
			foreach ($this->getAllCalendars() as $calendar) {
				$result = array_merge($result, $calendar->getEvents());
			}
			usort($result, 'compareEventStartedAt');
			return $result;
		}

		function createCalendar($options = array()) {
			$options['owner'] = $this;
			$calendar = Calendar::create($options);
			$this->_calendarmanager['[owned]']->setElement((($calendar->getProperty('id') > 0)?$calendar->getProperty('id'):mt_rand()), $calendar);
			return $calendar;
		}

		function deleteCalendar($calendar_id = 0) {
			if ($calendar_id > 0) {
				if ($this->_calendarmanager['[owned]']->elementExists($calendar_id)) {
					$this->_calendarmanager['[owned]']->getElement($calendar_id)->delete();
					$this->initOwnedFiles();
				} elseif (($this->_calendarmanager['[delegated]']->elementExists($calendar_id)) and
					($this->_calendarmanager['[delegated]']->getElement($calendar_id)->getPermissions()->elementExists(USER_CODE)) and 
					(($this->_calendarmanager['[delegated]']->getElement($calendar_id)->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
					($this->_calendarmanager['[delegated]']->getElement($calendar_id)->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS))
				) {
					$this->_calendarmanager['[delegated]']->getElement($calendar_id)->delete();
					$this->initDelegatedFiles();
				}
			}
		}

		function undeleteCalendar($calendar_id = 0) {
			if ($calendar_id > 0) {
				if ($this->_calendarmanager['[owned]']->elementExists($calendar_id)) {
					$this->_calendarmanager['[owned]']->getElement($calendar_id)->undelete();
					$this->initOwnedCalendars();
				} elseif (($this->_calendarmanager['[delegated]']->elementExists($calendar_id)) and
					($this->_calendarmanager['[delegated]']->getElement($calendar_id)->getPermissions()->elementExists(USER_CODE)) and 
					(($this->_calendarmanager['[delegated]']->getElement($calendar_id)->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
					($this->_calendarmanager['[delegated]']->getElement($calendar_id)->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS))
				) {
					$this->_calendarmanager['[delegated]']->getElement($calendar_id)->undelete();
					$this->initDelegatedCalendars();
				}
			}
		}

		function eraseCalendar($calendar_id = 0) {
			if ($calendar_id > 0) {
				if ($this->_calendarmanager['[owned]']->elementExists($calendar_id)) {
					$this->_calendarmanager['[owned]']->getElement($calendar_id)->erase();
					$this->initOwnedCalendars();
				}
			}
		}

	}
?>