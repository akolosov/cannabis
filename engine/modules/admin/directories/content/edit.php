<?php
if (($user_permissions[getParentModule()][getParentChildModule()]['can_write']) and (defined('DIRECTORY_ID'))) {
	$directory = new DirectoryInfo($engine, DIRECTORY_ID);
	require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php");
	print $engine->getFormManager()->generateDirectoryEditForm(array('directory' => $directory, 'record' => (defined('RECORD_ID')?$directory->getRecord(RECORD_ID):NULL)));
}
?>
