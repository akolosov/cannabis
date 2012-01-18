<?php

	class CalendarEventBlob extends Core {

		public	$_blob = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
			if ($owner <> NULL) {

				parent::__construct($owner, $id, $owner->getConnection());

				if (($id > 0) or (isNotNULL($options['data']))) {
					if (is_null($options['data'])) {
						$blob = $this->getConnection()->execute('select * from cs_calendar_event_blob where id = '.$this->_id)->fetch();
					} else {
						$blob = $options['data'];
					}

					foreach ($blob as $key => $data) {
						$this->_blob[$key] = $data;
					}
				}

			}
			$this->_blob['[model]'] = "CsCalendarEventBlob";
		}

		static function create($options = array()) {
			$blob = new MessageBlob($options['owner']);
			$blob->setProperty('name', $options['blobname']);
			$blob->setProperty('event_id', (isNotNULL($options['owner'])?$options['owner']->getProperty('id'):NULL));
			$blob->setProperty('blob', $options['blobdata']);
			return $blob;
		}

		static function getBlob($owner = NULL, $event_id = 0, $full = false) {
			if (($event_id > 0) && ($owner <> NULL)) {
				$blob = $owner->getConnection()->execute('select id from cs_calendar_event_blob where event_id = '.$event_id)->fetch();

				$result = new MessageBlob($owner, $blob['id'], $full);
				$result->_owner = $owner;
				$result->_connection = $owner->getConnection();
				$result->_id = $blob['id'];
				$result->setProperty('event_id', $event_id);

				return $result; 
			}
		}

		function getProperty($name) {
			return $this->_blob[$name];
		}

		function setProperty($name, $value) {
			$this->_blob[$name] = $value;
		}

		function save($event_id = NULL) {
			if (isNotNULL($event_id)) {
				$this->setProperty('event_id', $event_id);
			}
			logRuntime('['.get_class($this).'.save->ID_'.$this->getProperty('id').'] save data to '.$this->_blob['[model]']);
			return $this->saveData($this->_blob['[model]'], $this->_blob);
		}

	}
?>
