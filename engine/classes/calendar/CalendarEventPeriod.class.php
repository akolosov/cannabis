<?php
	class CalendarEventPeriod extends Core {

		public $_calendareventperiod = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {

			if (isNotNULL($owner)) {
				parent::__construct($owner, $id, $owner->getConnection());

				if (($id > 0) or (isNotNULL($options['data']))) {
					if (is_null($options['data'])) {
						$calendareventperiod = $this->getConnection()->execute('select * from calendar_event_periods_list where id = '.$this->_id)->fetch();
					} else {
						$calendareventperiod = $options['data'];
					}
					foreach ($calendareventperiod as $key => $data) {
						$this->_calendareventperiod[$key] = $data;
					}
				}
			}

			$this->_calendareventperiod['[model]'] = 'CsCalendarEventPeriod';
		}

		static function create($options = array()) {
			$period = new CalendarEventPeriod($options['owner']);
			$period->setProperty('event_id', (isNotNULL($options['owner'])?$options['owner']->getProperty('id'):$options['event_id']));
			$period->setProperty('period_id', $options['period_id']);
			$period->setProperty('condition_id', $options['condition_id']);
			$period->setProperty('value', $options['value']);
			return $period;
		}

		function getProperty($name) {
			return $this->_calendareventperiod[$name];
		}

		function setProperty($name, $value) {
			$this->_calendareventperiod[$name] = $value;
		}

		function save($event_id = NULL) {
			if (isNotNULL($event_id)) {
				$this->setProperty('event_id', $event_id);
			}
			return $this->saveData($this->_calendareventperiod['[model]'], $this->_calendareventperiod);
		}

	}
?>