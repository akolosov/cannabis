<?php

// $Id$

	class Security extends Core {

		function __construct($owner = NULL) {
			if ($owner <> NULL) {
				parent::__construct($owner, 0, $owner->getConnection());
			}
		}
	}
?>