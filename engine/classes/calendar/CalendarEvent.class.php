<?php
	class CalendarEvent extends Core {

		public $_calendarevent = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {

			if (isNotNULL($owner)) {
				parent::__construct($owner, $id, $owner->getConnection());

				if (($id > 0) or (isNotNULL($options['data']))) {
					if (is_null($options['data'])) {
						$calendarevent = $this->getConnection()->execute('select * from calendar_events_list where id = '.$this->_id)->fetch();
					} else {
						$calendarevent = $options['data'];
					}
					foreach ($calendarevent as $key => $data) {
						$this->_calendarevent[$key] = $data;
					}
	
					$this->_calendarevent['[recievers]'] = new Collection($this);
					$this->_calendarevent['[blobs]'] = new Collection($this);
	
					$this->initRecievers();
					$this->initBlobs();
					$this->initPeriod();

					if ($this->isDynamic()) {
						// TODO: дописать инициализацию динамических событий
					}
				}
			}

			$this->_calendarevent['[model]'] = 'CsCalendarEvent';
		}

		static function create($options = array()) {
			$event = new CalendarEvent($options['owner']);
			$event->setProperty('subject', $options['subject']);
			$event->setProperty('event', $options['event']);
			$event->setProperty('calendar_id', (isNotNULL($options['owner'])?$options['owner']->getProperty('id'):$options['calendar_id']));
			$event->setProperty('status_id', Constants::EVENT_STATUS_WAITING);
			$event->setProperty('author_id', USER_CODE);
			$event->setProperty('started_at', strftime("%Y-%m-%d %H:%M:%S", adjustDateTime(strtotime($options['started_at']))));
			$event->setProperty('ended_at', strftime("%Y-%m-%d %H:%M:%S", adjustDateTime(strtotime($options['ended_at']))));
			$event->setProperty('is_periodic', $options['is_periodic']);
			$event->setProperty('priority_id', $options['priority_id']);
			$event->setProperty('created_at', strftime("%Y-%m-%d %H:%M:%S", time()));
			$event->setProperty('[recievers]', new Collection($event));
			$event->setProperty('[blobs]', new Collection($event));
			return $event;
		}

		function createReciever($options = array()) {
			$options['owner'] = $this;
			$options['event_id'] = $this->getProperty('id');
			$reciever = CalendarEventReciever::create($options);
			$this->_calendarevent['[recievers]']->setElement($reciever->getProperty('account_id'), $reciever);
		}

		function createBlob($options = array()) {
			$options['owner'] = $this;
			$options['event_id'] = $this->getProperty('id');
			$blob = CalendarEventBlob::create($options);
			$this->_calendarevent['[blobs]']->setElement($blob->getProperty('name'), $blob);
		}

		function createPeriod($options = array()) {
			$options['owner'] = $this;
			$options['event_id'] = $this->getProperty('id');
			$this->_calendarevent['[period]'] = CalendarEventPeriod::create($options);
			$this->setProperty('is_periodic', true);
		}

		function getProperty($name) {
			return $this->_calendarevent[$name];
		}

		function setProperty($name, $value) {
			$this->_calendarevent[$name] = $value;
		}

		function isPeriodic() {
			return $this->getProperty('is_periodic');
		}

		function isDynamic() {
			return $this->getProperty('is_dynamic');
		}

		function isGroupEvent() {
			return (isNotNULL($this->getProperty('[recievers]')->getElements()));
		}

		function isOvertimed() {
			return (((time() > strtotime($this->getProperty('started_at'))) and (time() > strtotime($this->getProperty('ended_at')))) and
					($this->getProperty('status_id') < Constants::EVENT_STATUS_IN_PROGRESS));
		}

		function isCompleted() {
			// TODO: дописать проверку групповых событий
			return ($this->getProperty('status_id') == Constants::EVENT_STATUS_COMPLETED);
		}

		function isCanceled() {
			return ($this->getProperty('status_id') == Constants::EVENT_STATUS_CANCELED);
		}

		function isInProgress() {
			return ((((time() > strtotime($this->getProperty('started_at'))) and (time() < strtotime($this->getProperty('ended_at')))) and
					($this->getProperty('status_id') < Constants::EVENT_STATUS_IN_PROGRESS)) or
					($this->getProperty('status_id') == Constants::EVENT_STATUS_IN_PROGRESS));
		}

		function haveNotifiers() {
			// TODO: написать
			return true;
		}

		function userInRecievers($user = NULL) {
			if (is_null($user)) {
				$user	= USER_CODE;
			}
			foreach ($this->getRecievers() as $reciever) {
				if ($reciever->getProperty('account_id') == $user) {
					return $reciever;
				}
			}
			return false;
		}

		function userCanSeeEvent($user = NULL) {
			if (is_null($user)) {
				$user	= USER_CODE;
			}
			return (($this->getProperty('author_id') == $user) or ($this->userInRecievers($user)));
		}

		function initPeriod() {
			if ($this->isPeriodic()) {
				$period = $this->getConnection()->execute('select * from calendar_event_periods_list where event_id = '.$this->getProperty('id'))->fetch();
				$this->_calendarevent['[period]'] = new CalendarEventPeriod($this, $period['id'], array('data' => $period));
			}
		}

		function initRecievers() {
			$recievers = $this->getConnection()->execute('select * from calendar_event_recievers_list where event_id = '.$this->getProperty('id'))->fetchAll();
			foreach ($recievers as $reciever) {
				$this->_calendarevent['[recievers]']->setElement($reciever['account_id'], new CalendarEventReciever($this, $reciever['id'], array('data' => $reciever)));
			}
		}

		function initBlobs() {
			$blobs = $this->getConnection()->execute('select * from cs_calendar_event_blob where event_id = '.$this->getProperty('id'))->fetchAll();
			foreach ($blobs as $blob) {
				$this->_calendarevent['[blobs]']->setElement($blob['name'], new CalendarEventBlob($this, $blob['id'], array('data' => $blob)));
			}
		}

		function getBlobs() {
			return $this->_calendarevent['[blobs]']->getElements();
		}

		function getRecievers() {
			return $this->_calendarevent['[recievers]']->getElements();
		}

		function getBlob($blobname = NULL) {
			return $this->_calendarevent['[blobs]']->getElement($blobname);
		}

		function getReciever($reciever_id = NULL) {
			return $this->_calendarevent['[recievers]']->getElement($reciever_id);
		}

		function saveBlobs() {
			foreach ($this->getBlobs() as $blob) {
				$blob->save($this->getProperty('id'));
			}
		}

		function saveRecievers() {
			foreach ($this->getRecievers() as $reciever) {
				$reciever->save($this->getProperty('id'));
			}
		}

		function save($calendar_id = 0) {
			if ($this->isDynamic()) {
				// TODO: дописать сохранение динамических событий
			} else {
				if ($calendar_id > 0) {
					$this->setProperty('calendar_id', $calendar_id);
				}
				if (isNULL($this->getProperty('created_at'))) {
					$this->setProperty('author_id', USER_CODE);
					$this->setProperty('created_at', strftime("%Y-%m-%d %H:%M:%S", time()));
				}
				$result = $this->saveData($this->_calendarevent['[model]'], $this->_calendarevent);
				$this->setProperty('id', $result);
				$this->saveBlobs();
				$this->saveRecievers();
				if ($this->isPeriodic()) {
					$this->_calendarevent['[period]']->save($this->getProperty('id'));
				}
			}
			return $result;
		}
	}
?>