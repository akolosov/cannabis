<?php

if (($user_permissions[getParentModule()][getParentChildModule()]['can_read']) && (defined('PROJECT_INSTANCE_ID')) && (defined('PROCESS_INSTANCE_ID')) && (defined('ACTION'))) {
	require_once(MODULES_PATH.DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."process_action.php");
}

?>
