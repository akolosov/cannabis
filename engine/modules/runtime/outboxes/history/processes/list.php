<?php

if (($user_permissions[getParentModule()][getParentChildModule()]['can_read']) && (defined('PROCESS_INSTANCE_ID'))) {
	require_once(MODULES_PATH.DIRECTORY_SEPARATOR."runtime".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."history_prop.php");
}

?>
