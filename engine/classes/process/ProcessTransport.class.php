<?php

// $Id$

class ProcessTransport extends Core {

	public $_transport = array();

	function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
		if (($id > 0) && ($owner <> NULL)) {

			parent::__construct($owner, $id, $owner->getConnection());

			if (is_null($options['data'])) {
				$transport = $this->getConnection()->execute('select * from process_transports_list where id = '.$this->_id)->fetch();
			} else {
				$transport = $options['data'];
			}
			foreach ($transport as $key => $data) {
				$this->_transport[$key] = $data;
			}

			$class = $this->getProperty('class_name');
			if (class_exists($class)) {
				$this->_transport['[transport]']= new $class($this, $this->getProperty('transport_id'));
			}
			$this->_transport['[model]']		= "CsProcessTransport";
		}
	}

	function getProperty($name) {
		return $this->_transport[$name];
	}

	function setProperty($name, $value) {
		$this->_transport[$name] = $value;
	}

	function send(array $options = array('to' => array())) {
		$options = array_merge(array('from' => NULL, 'subject' => $this->getEngine()->getTemplate()->process($this->getProperty('subject_template')), 'text' => stripslashes(html_entity_decode($this->getEngine()->getTemplate()->process($this->getProperty('text_template')."\n\nВНИМАНИЕ! НЕ ОТВЕЧАЙТЕ НА ЭТО ПИСЬМО! ОТВЕТ НИКТО НЕ ПРОЧИТАЕТ!\n\n---\n%%ENGINE_NAME%% v%%ENGINE_VERSION%%/%%ENGINE_BUILD%%"), ENT_COMPAT, DEFAULT_CHARSET))), $options);
		$options['to'] = array_merge($options['to'], explode('\,', $this->getEngine()->getTemplate()->process($this->getProperty('recipients_template'))));
		$this->_transport['[transport]']->send($options);
	}

	function save() {
		if (isNotNULL($this->_transport['[transport]'])) {
			$this->_transport['transport_id'] = $this->_transport['[transport]']->save();
		}
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_transport['[model]']);
		return $this->saveData($this->_transport['[model]'], $this->_transport);
	}

	function export() {
		return "<transport>\n".$this->exportData($this->_transport['[model]'], $this->_transport).(isNotNULL($this->_transport['[transport]'])?$this->_transport['[transport]']->export():'')."</transport>\n";
	}
	
	static function import(array $transportdata = array(), $connection = NULL) {
		global $engine;

		$transport = self::importData("ProcessTransport", "CsProcessTransport", $transportdata, (is_resource($connection)?$connection:$engine->getConnection()));

		$transport->setProperty('[transport]', Transport::import(get_object_vars($transportdata['transportdata']), (is_resource($connection)?$connection:$engine->getConnection())));

		$transport->setProperty('transport_id', $transport->getProperty('[transport]')->getProperty('id'));
		
		if (($find = $transport->getConnection()->execute('select * from '.$transport->getConnection()->getTable($transport->getProperty('[model]'))->getTableName().' where process_id = '.$transport->getProperty('process_id').' and transport_id = '.$transport->getProperty('transport_id'))->fetch()) != false) {
			$transport->setProperty('id', $find['id']);
			$transport->_id = $find['id'];
		}

		$transport->setProperty('id', $transport->save());
		
		return $transport;
	}
}
?>