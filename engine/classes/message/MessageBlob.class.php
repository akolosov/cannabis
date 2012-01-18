<?php

	class MessageBlob extends Core {

		public	$_blob = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
			if ($owner <> NULL) {

				parent::__construct($owner, $id, $owner->getConnection());

				if (($id > 0) or (isNotNULL($options['data']))) {
					if (is_null($options['data'])) {
						$blob = $this->getConnection()->execute('select * from cs_message_blob where id = '.$this->_id)->fetch();
					} else {
						$blob = $options['data'];
					}
	
					foreach ($blob as $key => $data) {
						$this->_blob[$key] = $data;
					}
				}

			}
			$this->_blob['[model]'] = "CsMessageBlob";
		}

		static function create($options = array()) {
			$blob = new MessageBlob($options['owner']);
			$blob->setProperty('name', $options['name']);
			$blob->setProperty('message_id', (isNotNULL($options['owner'])?$options['owner']->getProperty('id'):NULL));
			$blob->setProperty('blob', $options['blob']);
			return $blob;
		}

		static function getBlob($owner = NULL, $message_id = 0, $full = false) {
			if (($message_id > 0) && ($owner <> NULL)) {
				$blob = $owner->getConnection()->execute('select id from cs_message_blob where message_id = '.$message_id)->fetch();

				$result = new MessageBlob($owner, $blob['id'], $full);
				$result->_owner = $owner;
				$result->_connection = $owner->getConnection();
				$result->_id = $blob['id'];
				$result->setProperty('message_id', $message_id);

				return $result;
			}
		}

		function getProperty($name) {
			return $this->_blob[$name];
		}

		function setProperty($name, $value) {
			$this->_blob[$name] = $value;
		}

		function getBlobFileName($authorname = NULL) {
			if (!is_dir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($authorname))) {
				mkdir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($authorname));
			}
			if (!is_dir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($authorname).DIRECTORY_SEPARATOR."messages")) {
				mkdir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($authorname).DIRECTORY_SEPARATOR."messages");
			}
			if (!is_dir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($authorname).DIRECTORY_SEPARATOR."messages".DIRECTORY_SEPARATOR.$this->getProperty('message_id'))) {
				mkdir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($authorname).DIRECTORY_SEPARATOR."messages".DIRECTORY_SEPARATOR.$this->getProperty('message_id'));
			}

			return FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($authorname).DIRECTORY_SEPARATOR."messages".DIRECTORY_SEPARATOR.$this->getProperty('message_id').DIRECTORY_SEPARATOR.$this->getProperty('name');
		}

		function erase() {
			if (unlink($this->getBlobFileName(USER_NAME))) {
				parent::erase();
			}
		}

		function save($message_id = NULL) {
			if (isNotNULL($message_id)) {
				$this->setProperty('message_id', $message_id);
			}
			logRuntime('['.get_class($this).'.save->ID_'.$this->getProperty('id').'] save data to '.$this->_blob['[model]']);
			return $this->saveData($this->_blob['[model]'], $this->_blob);
		}

	}
?>
