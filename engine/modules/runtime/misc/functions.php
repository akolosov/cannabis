<?php

function getByProject(array $processes = array(), $project_id = 0) {
	$result = array();
	$count = 0;
	foreach ($processes as $process) {
		if ($process['project_instance_id'] == $project_id) {
			$result[] = $process;
		}
	}
	return $result;
}

function getProjectsList(array $processes = array()) {
	$result = array();
	foreach($processes as $process) {
		$result[$process['project_instance_id']] = $process['projectname'];
	}
	return $result;
}

function getNotResponded(array $processes = array()) {
	$result = array();
	$count = 0;
	foreach ($processes as $process) {
		if (($process['status_id'] <> Constants::PROCESS_STATUS_COMPLETED) and (isNotNULL($process['ended_at']))) {
			$result[] = $process;
		}
	}
	return $result;
}

function getByProcess(array $processes = array(), $process_id = 0) {
	$result = array();
	$count = 0;
	foreach ($processes as $process) {
		if ($process['process_id'] == $process_id) {
			$result[] = $process;
		}
	}
	return $result;
}

function getProcessesList(array $processes = array()) {
	$result = array();
	foreach($processes as $process) {
		$result[$process['process_id']] = $process['name'];
	}
	return $result;
}

function getByInitiator(array $processes = array(), $initiator_id = 0) {
	$result = array();
	$count = 0;
	foreach ($processes as $process) {
		if ($process['initiator_id'] == $initiator_id) {
			$result[] = $process;
		}
	}
	return $result;
}

function getInitiatorsList(array $processes = array()) {
	$result = array();
	foreach($processes as $process) {
		$result[$process['initiator_id']] = $process['initiatorname'];
	}
	return $result;
}

function getByStatus(array $processes = array(), $status_id = 0) {
	$result = array();
	$count = 0;
	foreach ($processes as $process) {
		if ($process['status_id'] == $status_id) {
			$result[] = $process;
		}
	}
	return $result;
}

function getStatusesList(array $processes = array()) {
	$result = array();
	foreach($processes as $process) {
		$result[$process['status_id']] = $process['statusname'].($process['statusdescr']?" (".$process['statusdescr'].")":"");
	}
	return $result;
}

function getByParent($processes = array(), $parent_id = 0) {
	$result = array();
	foreach ($processes as $process) {
		if (($process['parent_id'] == $parent_id) or ($process['parent_id'] == $pid)) {
			$pid = $process['id'];
			$result[] = $process;
		}
	}
	return $result;
}

function getPropertyValueByName($properties = array(), $name = NULL) {
	foreach ($properties as $property) {
		if (trim($property['name']) == trim($name)) {
			return stripMacros($property['value']);
		}
	}
	return NULL;
}

?>