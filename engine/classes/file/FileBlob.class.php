<?php

	class FileBlob extends Core {

		public	$_blob = array();

		function __construct($owner = NULL, $id = 0, $full = false) {
			if ($owner <> NULL) {

				parent::__construct($owner, $id, $owner->getConnection());

				if ($id > 0) {
					$blob = $this->getConnection()->execute('select id, file_id'.($full?', blob':'').' from cs_file_blob where id = '.$this->_id)->fetch();
	
					foreach ($blob as $key => $data) {
						$this->_blob[$key] = $data;
					}
				}

			}
			$this->_blob['[model]'] = "CsFileBlob";
		}

		static function create($options = array()) {
			$blob = new FileBlob($options['owner']);
			$blob->setProperty('file_id', (isNotNULL($options['owner'])?$options['owner']->getProperty('id'):NULL));
			$blob->setProperty('blob', $options['blob']);
			return $blob;
		}

		static function getBlob($owner = NULL, $file_id = 0, $full = false) {
			if (($file_id > 0) && ($owner <> NULL)) {
				$blob = $owner->getConnection()->execute('select id from cs_file_blob where file_id = '.$file_id)->fetch();

				$result = new FileBlob($owner, $blob['id'], $full);
				$result->_owner = $owner;
				$result->_connection = $owner->getConnection();
				$result->_id = $blob['id'];
				$result->setProperty('file_id', $file_id);

				return $result; 
			}
		}

		function getProperty($name) {
			return $this->_blob[$name];
		}

		function setProperty($name, $value) {
			$this->_blob[$name] = $value;
		}

		function save() {
			logRuntime('['.get_class($this).'.save->ID_'.$this->getProperty('id').'] save data to '.$this->_blob['[model]']);
			return $this->saveData($this->_blob['[model]'], $this->_blob);
		}

	}
?>
