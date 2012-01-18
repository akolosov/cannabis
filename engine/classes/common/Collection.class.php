<?php

// $Id$

	class Collection extends Core {

		public $_collection = array();

		function __construct($owner = NULL) {
			if ($owner <> NULL) {
				parent::__construct($owner, 0, $owner->getConnection());
			}
		}

		function getElement($name) {
			if ($this->elementExists($name)) {
				return $this->_collection[strtolower(trim($name))];
			} else {
				return $this->getFirstElement();
			}
		}

		function setElement($name = "", $value = NULL) {
			if ((empty($name)) || (is_null($name))) {
				$this->_collection[] = $value;
			} else {
				$this->_collection[strtolower(trim($name))] = $value;
			}
			if (is_null($value)) {
				$this->purgeNulls();
			}
		}

		function getElements() {
			return $this->_collection;
		}

		function setElements($value) {
			if (is_array($value)) {
				$this->_collection = $value;
			}
		}
		
		function findElementByPropertyValue($name, $value) {
			foreach ($this->_collection as $key => $element) {
				if ((is_subclass_of($element, 'Core')) and ($element->getProperty($name) == $value)) {
					return $element;
				}
			}
			return NULL;
		}

		function getElementsFromPropertyValue($name, $value) {
			$result = array();
			$found = false;

			foreach ($this->_collection as $key => $element) {
				if ((is_subclass_of($element, 'Core')) and (($element->getProperty($name) == $value) or $found)) {
					$result[$key] = $element;
					$found = true;
				}
			}

			return $result;
		}
		
		function findElementByID($value) {
			return $this->findElementByPropertyValue('id', $value);
		}

		function findElementByName($value) {
			return $this->findElementByPropertyValue('name', $value);
		}

		function sortByValue() {
			asort($this->_collection);
			reset($this->_collection);
		}

		function sortByName() {
			ksort($this->_collection);
			reset($this->_collection);
		}

		function getFirstElement() {
			foreach ($this->_collection as $key => $element) {
				return $element;
			}
		}

		function getFirstElementName() {
			foreach ($this->_collection as $key => $element) {
				return $key;
			}
		}

		function getLastElement() {
			$count = 1;
			foreach ($this->_collection as $key => $element) {
				if ($count == count($this->_collection)) {
					return $element;
				}
				$count++;
			}
		}

		function getLastElementName() {
			$count = 0;
			foreach ($this->_collection as $key => $element) {
				if ($count == count($this->_collection)) {
					return $key;
				}
				$count++;
			}
		}

		function elementExists($name) {
			return (is_null($this->_collection[strtolower(trim($name))])?false:true);
		}

		function isEmpty() {
			return (count($this->_collection) > 0?false:true);
		}

		function purgeNulls() {
			$result = array();
			foreach ($this->_collection as $key => $value) {
				if (isNotNULL($value)) {
					$result[$key] = $value;
				}
			}
			$this->setElements(array());
			$this->setElements($result);
		}
	}

?>