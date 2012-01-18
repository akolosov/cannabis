<?php

function getDocumentByParentID($documents = array(), $parent_id = 0) {
	$result = array();
	
	foreach($documents as $document) {
		if ($document['parent_id'] == $parent_id) {
			$result[$document['id']] = $document;
		}
	}
	return $result;
}

?>