<?php // if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php

require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."functions.php");

$manager = new FileManager($engine, USER_CODE);

if (defined("ACTION")) {
	switch (ACTION) {
		case "add" :
			if ((is_array($_FILES['x_file_name'])) and (in_array($_FILES['x_file_name']['type'], $mime_names)) and ($_FILES['x_file_name']['size'] <= MAX_FILE_SIZE) and (is_uploaded_file($_FILES['x_file_name']['tmp_name']))) {
				$filecontent = base64_encode(file_get_contents($_FILES['x_file_name']['tmp_name']));
				$file = $manager->createFile(array('name' => basename($_FILES['x_file_name']['name']),
													'description' => X_DESCRIPTION,
													'parent_id' => X_PARENT_ID,
													'blob' => $filecontent,
													'mime' => $_FILES['x_file_name']['type']));
				if (isNotNULL(X_PARENT_ID)) {
					$file->copyPermissionsFrom($manager->getFile(X_PARENT_ID));
				}
				$file->save();
				if (!is_dir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha(USER_NAME))) {
					mkdir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha(USER_NAME));
				}
				if (!is_dir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha(USER_NAME).DIRECTORY_SEPARATOR."files")) {
					mkdir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha(USER_NAME).DIRECTORY_SEPARATOR."files");
				}
				move_uploaded_file($_FILES['x_file_name']['tmp_name'], FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha(USER_NAME).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR.basename($_FILES['x_file_name']['name']));
				$manager->initOwnedFiles();
			} elseif ((defined('X_IS_FOLDER')) and (X_IS_FOLDER == 'on') and (defined('X_FOLDER_NAME')) and (X_FOLDER_NAME <> '')) {
				$file = $manager->createFile(array('name' => X_FOLDER_NAME,
													'description' => X_DESCRIPTION,
													'parent_id' => X_PARENT_ID,
													'is_folder' => true));
				if (isNotNULL(PARENT_ID)) {
					$file->copyPermissionsFrom($manager->getFile(PARENT_ID));
				}
				$file->save();
				$manager->initOwnedFiles();
			}
			break;
		case "change" :
			if ((defined('FILE_ID')) and ($manager->fileExists(FILE_ID))) {
				if ((is_array($_FILES['x_file_name'])) and (in_array($_FILES['x_file_name']['type'], $mime_names)) and ($_FILES['x_file_name']['size'] <= MAX_FILE_SIZE) and (is_uploaded_file($_FILES['x_file_name']['tmp_name']))) {
					$file = $manager->getFile(FILE_ID);
					$file->setProperty('blob', base64_encode(file_get_contents($_FILES['x_file_name']['tmp_name'])));
					$file->setProperty('name', basename($_FILES['x_file_name']['name']));
					$file->setProperty('description', X_DESCRIPTION);
					$file->setProperty('parent_id', ((defined('X_PARENT_ID'))?X_PARENT_ID:X_OLD_PARENT_ID));
					if ((isNotNULL(X_PARENT_ID)) and (X_PARENT_ID <> X_OLD_PARENT_ID)) {
						$file->copyPermissionsFrom($manager->getFile(X_PARENT_ID));
					}
					$file->save();
					if (!is_dir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha(USER_NAME))) {
						mkdir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha(USER_NAME));
					}
					if (!is_dir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha(USER_NAME).DIRECTORY_SEPARATOR."files")) {
						mkdir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha(USER_NAME).DIRECTORY_SEPARATOR."files");
					}
					move_uploaded_file($_FILES['x_file_name']['tmp_name'], FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha(USER_NAME).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR.basename($_FILES['x_file_name']['name']));
					$manager->initOwnedFiles();
				} elseif ((defined('X_OLD_IS_FOLDER')) and (X_OLD_IS_FOLDER == 'off')) {
					$file = $manager->getFile(FILE_ID);
					$file->setProperty('description', X_DESCRIPTION);
					$file->setProperty('parent_id', ((defined('X_PARENT_ID'))?X_PARENT_ID:X_OLD_PARENT_ID));
					if ((isNotNULL(X_PARENT_ID)) and (X_PARENT_ID <> X_OLD_PARENT_ID)) {
						$file->copyPermissionsFrom($manager->getFile(X_PARENT_ID));
					}
					$file->save();
					$manager->initOwnedFiles();
				} elseif ((defined('X_OLD_IS_FOLDER')) and (X_OLD_IS_FOLDER == 'on') and (defined('X_FOLDER_NAME')) and (X_FOLDER_NAME <> '')) {
					$file = $manager->getFile(FILE_ID);
					$file->setProperty('name', X_FOLDER_NAME);
					$file->setProperty('description', X_DESCRIPTION);
					$file->setProperty('parent_id', ((defined('X_PARENT_ID'))?X_PARENT_ID:X_OLD_PARENT_ID));
					if ((isNotNULL(X_PARENT_ID)) and (X_PARENT_ID <> X_OLD_PARENT_ID)) {
						$file->copyPermissionsFrom($manager->getFile(X_PARENT_ID));
					}
					$file->save();
					$manager->initOwnedFiles();
				}
			}
			break;
		case "delete" :
			if ((defined('FILE_ID')) and ($manager->fileExists(FILE_ID))) {
				if ($manager->isDeleted(FILE_ID)) {
					$manager->undeleteFile(FILE_ID);
				} else {
					$manager->deleteFile(FILE_ID);
				}
			}
			break;
		case "erase" :
			if ((defined('FILE_ID')) and ($manager->fileExists(FILE_ID))) {
				$manager->eraseFile(FILE_ID);
			}
			break;
		default:
			break;
	}
}

function printOwnedCurrentRow($file = NULL) {
	global $user_permissions;

	if (isNotNULL($file)) {
		print "<table width=\"auto\" cellspacing=\"1\" cellpadding=\"1\" style=\" border : 1px dotted #ccc; border-collapse : collapse; margin-bottom : 1px; \">";
		print "<tr>";
		print "<td width=\"16\" align=\"center\" valign=\"middle\"><img src=\"images/".(($file->isFolder())?"folder":"file").".png\" /></td>";
		print "<td ".((!$file->isFolder())?" onClick=\"document.location.href = '".FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($file->getProperty('ownername')).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR.basename($file->getProperty('name'))."';\"":"")." width=\"auto\" class =\" action ".(($file->isDeleted())?" strike ":"")."\" title=\"".$file->getProperty('description')."\">".$file->getProperty('name')."</td>";
		print "<td width=\"15%\" align=\"center\" class=\" small \" title=\"Дата создания\">".$file->getProperty('created_at')."</td>";
		print "<td width=\"15%\" align=\"center\" class=\" small \" title=\"Дата последнего обновления\">".$file->getProperty('updated_at')."</td>";
//		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			if (!$file->isFolder()) {
				print "<td width=\"16\"><img src=\"images/export.png\" class=\" action \" onClick=\"document.location.href = '".FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($file->getProperty('ownername')).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR.basename($file->getProperty('name'))."';\" title=\"Сохранить файл на диск\" /></td>";
			}
//		}
//		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"16\"><img src=\"images/edit_icon.png\" class=\" action \" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/edit&action=change&file_id=".$file->getProperty('id')."';\" title=\"Изменить/Редактировать файл или папку\" /></td>";
//		}
//		if ($user_permissions[getParentModule()][getChildModule()]['can_delete']) {
			print "<td width=\"16\"><img src=\"images/delete_icon.png\" class=\" action \" onClick=\"javascript:confirmIt('?module=".getParentModule()."/".getChildModule()."/list&action=delete&file_id=".$file->getProperty('id')."', '_top', true);\" title=\"".(($file->isDeleted())?"Восстановить":"Удалить")." файл или папку\" /></td>";
//		}
//		if ($user_permissions[getParentModule()][getChildModule()]['can_delete']) {
			print "<td width=\"16\"><img src=\"images/erase_icon.png\" class=\" action \" onClick=\"javascript:confirmIt('?module=".getParentModule()."/".getChildModule()."/list&action=erase&file_id=".$file->getProperty('id')."', '_top', true);\" title=\"Удалить СОВСЕМ (!) файл или папку\" /></td>";
//		}
//		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"16\"><img src=\"images/permissions.png\" class=\" action \" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/permissions/list&file_id=".$file->getProperty('id')."';\" title=\"Изменить/Редактировать права доступа к файлу или папке\" /></td>";
//		}
		print "</tr>";
		print "</table>";

		if (!is_dir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($file->getProperty('ownername')))) {
			mkdir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($file->getProperty('ownername')));
		}
		if (!is_dir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($file->getProperty('ownername')).DIRECTORY_SEPARATOR."files")) {
			mkdir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($file->getProperty('ownername')).DIRECTORY_SEPARATOR."files");
		}
		if ((!file_exists(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($file->getProperty('ownername')).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR.basename($file->getProperty('name')))) and (!$file->isFolder())) {
			file_put_contents(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($file->getProperty('ownername')).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR.basename($file->getProperty('name')), base64_decode($file->getProperty('blob')));
		}
	}
}

function printOwnedRow($parent_id = 0) {
	global $manager;

	foreach (getByParent($manager->getOwnedFiles(), $parent_id) as $file) {
		print "<ul>\n";
		print "<li class=\"treeitem\">";
		printOwnedCurrentRow($file);
		printOwnedRow($file->getProperty('id'));
		print "</ul>\n";
	}
}

print "<ul class=\"tree\" id=\"owned_tree\" style=\" display : none; \">\n";
// print "<div class=\"caption\">".(($user_permissions[getParentModule()][getChildModule()]['can_write'])?"<img src=\"images/create_icon.png\" class=\" action \" style=\" float: right; z-index: 1000; \" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/edit&action=add';\" title=\"Добавить файл или папку\" />":"")."Личные файлы пользователя</div>\n";
print "<div class=\"caption\"><img src=\"images/create_icon.png\" class=\" action \" style=\" float: right; z-index: 1000; \" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/edit&action=add';\" title=\"Добавить файл или папку\" />Личные файлы пользователя</div>\n";
foreach (getByParent($manager->getOwnedFiles(), 0) as $file) {
	print "<li class=\"roottreeitem\">\n";
	printOwnedCurrentRow($file);
	printOwnedRow($file->getProperty('id'));
	print "</li>\n";
}
print "</ul>\n";
?>
<br /><br />
<?php
function printDelegatedCurrentRow($file = NULL) {
	global $user_permissions;

	if (isNotNULL($file)) {
		print "<table width=\"auto\" cellspacing=\"1\" cellpadding=\"1\" style=\" border : 1px dotted #ccc; border-collapse : collapse; margin-bottom : 1px; \">";
		print "<tr>";
		print "<td width=\"16\" align=\"center\" valign=\"middle\"><img src=\"images/".(($file->isFolder())?"folder":"file").".png\" /></td>";
		print "<td ".((!$file->isFolder())?" onClick=\"document.location.href = '".FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($file->getProperty('ownername')).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR.basename($file->getProperty('name'))."';\"":"")." width=\"auto\" class =\" action ".(($file->isDeleted())?" strike ":"")."\" title=\"".$file->getProperty('description')."\">".$file->getProperty('name')."</td>";
		print "<td width=\"15%\" align=\"center\" class=\" small \" title=\"Дата создания\">".$file->getProperty('created_at')."</td>";
		print "<td width=\"15%\" align=\"center\" class=\" small \" title=\"Дата последнего обновления\">".$file->getProperty('updated_at')."</td>";
		if (($file->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE) or
			($file->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
			($file->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_ONLY) or
			($file->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS)) {
			if (!$file->isFolder()) {
				print "<td width=\"16\"><img src=\"images/export.png\" class=\" action \" onClick=\"document.location.href = '".FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($file->getProperty('ownername')).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR.basename($file->getProperty('name'))."';\" title=\"Сохранить файл на диск\" /></td>";
			}
		}
		if (($file->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE) or
			($file->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
			($file->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS)) {
			print "<td width=\"16\"><img src=\"images/edit_icon.png\" class=\" action \" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/edit&action=change&file_id=".$file->getProperty('id')."';\" title=\"Изменить/Редактировать файл или папку\" /></td>";
		}
		if (($file->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
			($file->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS)) {
			print "<td width=\"16\"><img src=\"images/delete_icon.png\" class=\" action \" onClick=\"javascript:confirmIt('?module=".getParentModule()."/".getChildModule()."/list&action=delete&file_id=".$file->getProperty('id')."', '_top', true);\" title=\"".(($file->isDeleted())?"Восстановить":"Удалить")." файл или папку\" /></td>";
		}
		if ($file->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS) {
			print "<td width=\"16\"><img src=\"images/permissions.png\" class=\" action \" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/permissions/list&file_id=".$file->getProperty('id')."';\" title=\"Изменить/Редактировать права доступа к файлу или папке\" /></td>";
		}
		print "</tr>";
		print "</table>";

		if (!is_dir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($file->getProperty('ownername')))) {
			mkdir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($file->getProperty('ownername')));
		}
		if (!is_dir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($file->getProperty('ownername')).DIRECTORY_SEPARATOR."files")) {
			mkdir(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($file->getProperty('ownername')).DIRECTORY_SEPARATOR."files");
		}
		if ((!file_exists(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($file->getProperty('ownername')).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR.basename($file->getProperty('name')))) and (!$file->isFolder())) {
			file_put_contents(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha($file->getProperty('ownername')).DIRECTORY_SEPARATOR."files".DIRECTORY_SEPARATOR.basename($file->getProperty('name')), base64_decode($file->getProperty('blob')));
		}
	}
}

function printDelegatedRow($parent_id = 0) {
	global $manager;

	foreach (getByParent($manager->getDelegatedFiles(), $parent_id) as $file) {
		print "<ul>\n";
		print "<li class=\"treeitem\">";
		printDelegatedCurrentRow($file);
		printDelegatedRow($file->getProperty('id'));
		print "</ul>\n";
	}
}

print "<ul class=\"tree\" id=\"delegated_tree\" style=\" display : none; \">\n";
print "<div class=\"caption\">Доверенные файлы пользователя</div>";
foreach (getByParent($manager->getDelegatedFiles(), 0) as $file) {
	print "<li class=\"roottreeitem\">\n";
	printDelegatedCurrentRow($file);
	printDelegatedRow($file->getProperty('id'));
	print "</li>\n";
}
print "</ul>\n";
?>
<script>
<!--
	var owned_tree_options = {
			'theme' : { 'name' : 'SimpleTree' },
			closeSiblings : false,
			maxOpenDepth : 10,
			flagClosedClass : 'close',
			toggleMenuOnClick : true,
			incrementalConvert : false,
			openTimeout : 0,
			closeTimeout: 0
	}

	var delegated_tree_options = {
			'theme' : { 'name' : 'SimpleTree' },
			closeSiblings : false,
			maxOpenDepth : 10,
			flagClosedClass : 'close',
			toggleMenuOnClick : true,
			incrementalConvert : false,
			openTimeout : 0,
			closeTimeout: 0
	}

	var owned_tree = new CompleteMenuSolution;
	var delegated_tree = new CompleteMenuSolution;
	owned_tree.initMenu('owned_tree', owned_tree_options);
	delegated_tree.initMenu('delegated_tree', delegated_tree_options);
//-->
</script>
<?php // endif; ?>
