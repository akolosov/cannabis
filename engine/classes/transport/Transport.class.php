<?php
class Transport extends Core {

	public $_transport = array();

	function __construct($owner = NULL, $id = 0) {
		if (($id > 0) && ($owner <> NULL)) {

			parent::__construct($owner, $id, $owner->getConnection());

			$transport = $this->getConnection()->execute('select * from cs_transport where id = '.$this->_id)->fetch();

			foreach ($transport as $key => $data) {
				$this->_transport[$key] = $data;
			}

			$this->_transport['[model]'] = 'CsTransport';
		}
	}

	function getProperty($name) {
		return $this->_transport[$name];
	}

	function setProperty($name, $value) {
		$this->_transport[$name] = $value;
	}
	
	function send(array $options = array('from' => NULL, 'to' => array(), 'text' => NULL)) {
			
	}

	function save() {
		logRuntime('['.get_class($this).'.save->'.$this->getProperty('name').'] data saved to '.$this->_transport['[model]']);
		return $this->saveData($this->_transport['[model]'], $this->_transport);
	}

	function export() {
		return "<transportdata>\n".$this->exportData($this->_transport['[model]'], $this->_transport)."</transportdata>\n";
	}

	static function import(array $transportdata = array(), $connection = NULL) {
		global $engine;

		$transport = self::importData("Transport", "CsTransport", $transportdata, (is_resource($connection)?$connection:$engine->getConnection()));

		if (($find = $transport->getConnection()->execute('select * from '.$transport->getConnection()->getTable($transport->getProperty('[model]'))->getTableName().' where name = \''.trim($transport->getProperty('name')).'\'')->fetch()) != false) {
			$transport->setProperty('id', $find['id']);
			$transport->_id = $find['id'];
		}
		
		$transport->setProperty('id', $transport->save());
		
		return $transport;
	}
}
?>