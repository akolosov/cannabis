<?php // if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php

require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."functions.php");

$manager = new ContactListManager($engine, USER_CODE);

if (defined("ACTION")) {
	switch (ACTION) {
		case "add" :
			$contactlist = $manager->createContactList(array('name' => X_NAME,
															 'description' => X_DESCRIPTION,
															 'is_public' => ((X_IS_PUBLIC == 'on')?true:false)));
			$contactlist->save();
			$manager->initOwnedContactLists();
			break;
		case "change" :
			if ((defined('CONTACTLIST_ID')) and ($manager->contactlistExists(CONTACTLIST_ID))) {
				$manager->getContactList(CONTACTLIST_ID)->setProperty('name', X_NAME);
				$manager->getContactList(CONTACTLIST_ID)->setProperty('description', X_DESCRIPTION);
				$manager->getContactList(CONTACTLIST_ID)->setProperty('is_public', ((X_IS_PUBLIC == 'on')?true:false));
				$manager->getContactList(CONTACTLIST_ID)->save();
			}
			break;
		case "delete" :
			if ((defined('CONTACTLIST_ID')) and ($manager->contactlistExists(CONTACTLIST_ID))) {
				if ($manager->isDeleted(CONTACTLIST_ID)) {
					$manager->undeleteContactList(CONTACTLIST_ID);
				} else {
					$manager->deleteContactList(CONTACTLIST_ID);
				}
			}
			break;
		case "erase" :
			if ((defined('CONTACTLIST_ID')) and ($manager->contactlistExists(CONTACTLIST_ID))) {
				$manager->eraseContactList(CONTACTLIST_ID);
			}
			break;
		default:
			break;
	}
}

function printOwnedContactLists() {
	global $manager, $user_permissions;

	// print "<div class=\"caption\">".(($user_permissions[getParentModule()][getChildModule()]['can_write'])?"<img src=\"images/create_icon.png\" class=\" action \" style=\" float: right; z-index: 1000; \" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/edit&action=add';\" title=\"Добавить файл или папку\" />":"")."Личные файлы пользователя</div>\n";
	print "<div class=\"caption\"><img src=\"images/create_icon.png\" class=\" action \" style=\" float: right; z-index: 1000; \" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/edit&action=add';\" title=\"Добавить список пользователей\" />Личные списки пользователей</div>\n";
	print "<table id=\"owned\" width=\"100%\" align=\"center\" cellspacing=\"1\" cellpadding=\"1\" style=\" border : 1px dotted #ccc; border-collapse : collapse; margin-bottom : 1px; \">";
	print "<tr>";
	print "<th>Наименование</th>";
	print "<th>Кол-во</th>";
	print "<th>Действия</th>";
	print "</tr>";
	foreach ($manager->getOwnedContactLists() as $contactlist) {
		print "<tr>";
		print "<td width=\"auto\" class=\" ".(($contactlist->isDeleted())?"strike":"")." \" title=\"".$contactlist->getProperty('description')."\">".$contactlist->getProperty('name')."</td>";
		print "<td width=\"10%\" align=\"center\" title=\"Реальное/Актуальное количество пользователей\">".$contactlist->getProperty('realcontactcount')." (".$contactlist->getProperty('actualcontactcount').")</td>";
		print "<td width=\"80\" align=\"center\">";
		print "<img src=\"images/list.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/contacts/list&contactlist_id=".$contactlist->getProperty('id')."';\" title=\"Просмотр/Изменение списка пользователей\" />";
		print "<img src=\"images/edit_icon.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/edit&action=change&contactlist_id=".$contactlist->getProperty('id')."';\" title=\"Изменить описания списка пользователей\" />";
		print "<img src=\"images/delete_icon.png\" class=\"action\" onClick=\"javascript:confirmIt('?module=".getParentModule()."/".getChildModule()."/list&action=delete&contactlist_id=".$contactlist->getProperty('id')."', '_top', true);\" title=\"Удалить список пользователей\" />";
		print "<img src=\"images/erase_icon.png\" class=\"action\" onClick=\"javascript:confirmIt('?module=".getParentModule()."/".getChildModule()."/list&action=erase&contactlist_id=".$contactlist->getProperty('id')."', '_top', true);\" title=\"Удалить СОВСЕМ список пользователей\" />";
		print "<img src=\"images/permissions.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/permissions/list&contactlist_id=".$contactlist->getProperty('id')."';\" title=\"Изменить/Редактировать права доступа к списку пользователей\" />";
		print "</td>";
		print "</tr>";
	}
	print "</table>";
}

function printDelegatedContactLists() {
	global $manager;

	print "<br /><div class=\"caption\" onClick=\"javascript:hideIt('delegated');\">Доверенные списки пользователей</div>\n";
	print "<div id=\"delegated\" style=\" display : block; visibility : visible; \">";
	print "<table width=\"100%\" align=\"center\" cellspacing=\"1\" cellpadding=\"1\" style=\" border : 1px dotted #ccc; border-collapse : collapse; margin-bottom : 1px; \">";
	print "<tr>";
	print "<th>Наименование</th>";
	print "<th>Кол-во</th>";
	print "<th>Действия</th>";
	print "</tr>";
	foreach ($manager->getDelegatedContactLists() as $contactlist) {
		print "<tr>";
		print "<td width=\"auto\" class=\"".(($contactlist->isDeleted())?"strike":"")."\" title=\"".$contactlist->getProperty('description')."\">".$contactlist->getProperty('name')." <span class=\"small\">(Владелец: ".$contactlist->getProperty('ownername').")</span></td>";
		print "<td width=\"10%\" align=\"center\" title=\"Реальное/Актуальное количество пользователей\">".$contactlist->getProperty('realcontactcount')." (".$contactlist->getProperty('actualcontactcount').")</td>";
		print "<td width=\"80\" align=\"center\">";
		if (($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE) or
			($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
			($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_ONLY) or
			($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS)) {
			print "<img src=\"images/list.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/contacts/list&contactlist_id=".$contactlist->getProperty('id')."';\" title=\"Просмотр/Изменение списка пользователей\" />";
		}
		if (($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE) or
			($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
			($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS)) {
			print "<img src=\"images/edit_icon.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/edit&action=change&contactlist_id=".$contactlist->getProperty('id')."';\" title=\"Изменить описания списка пользователей\" />";
		}
		if (($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
			($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS)) {
			print "<img src=\"images/delete_icon.png\" class=\"action\" onClick=\"javascript:confirmIt('?module=".getParentModule()."/".getChildModule()."/list&action=delete&contactlist_id=".$contactlist->getProperty('id')."', '_top', true);\" title=\"Удалить список пользователей\" />";
		}
		if ($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS) {
			print "<img src=\"images/permissions.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/permissions/list&contactlist_id=".$contactlist->getProperty('id')."';\" title=\"Изменить/Редактировать права доступа к списку пользователей\" />";
		}
		print "</td>";
		print "</tr>";
	}
	print "</table>";
	print "</div>";
}

function printPublicContactLists() {
	global $manager;

	print "<br /><div class=\"caption\" onClick=\"javascript:hideIt('public');\">Общие списки пользователей</div>\n";
	print "<div id=\"public\" style=\" display : block; visibility : visible; \">";
	print "<table width=\"100%\" align=\"center\" cellspacing=\"1\" cellpadding=\"1\" style=\" border : 1px dotted #ccc; border-collapse : collapse; margin-bottom : 1px; \">";
	print "<tr>";
	print "<th>Наименование</th>";
	print "<th>Кол-во</th>";
	print "<th>Действия</th>";
	print "</tr>";
	foreach ($manager->getPublicContactLists() as $contactlist) {
		print "<tr>";
		print "<td width=\"auto\" class=\"".(($contactlist->isDeleted())?"strike":"")."\" title=\"".$contactlist->getProperty('description')."\">".$contactlist->getProperty('name')." <span class=\"small\">(Владелец: ".$contactlist->getProperty('ownername').")</span></td>";
		print "<td width=\"10%\" align=\"center\" title=\"Реальное/Актуальное количество пользователей\">".$contactlist->getProperty('realcontactcount')." (".$contactlist->getProperty('actualcontactcount').")</td>";
		print "<td width=\"80\" align=\"center\">";
		if (($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE) or
			($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
			($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_ONLY) or
			($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS)) {
			print "<img src=\"images/list.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/contacts/list&contactlist_id=".$contactlist->getProperty('id')."';\" title=\"Просмотр/Изменение списка пользователей\" />";
		}
		if (($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE) or
			($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
			($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS)) {
			print "<img src=\"images/edit_icon.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/edit&action=change&contactlist_id=".$contactlist->getProperty('id')."';\" title=\"Изменить описания списка пользователей\" />";
		}
		if (($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
			($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS)) {
			print "<img src=\"images/delete_icon.png\" class=\"action\" onClick=\"javascript:confirmIt('?module=".getParentModule()."/".getChildModule()."/list&action=delete&contactlist_id=".$contactlist->getProperty('id')."', '_top', true);\" title=\"Удалить список пользователей\" />";
		}
		if ($contactlist->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS) {
			print "<img src=\"images/permissions.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/permissions/list&contactlist_id=".$contactlist->getProperty('id')."';\" title=\"Изменить/Редактировать права доступа к списку пользователей\" />";
		}
		print "</td>";
		print "</tr>";
	}
	print "</table>";
	print "</div>";
}

printOwnedContactLists();
printPublicContactLists();
printDelegatedContactLists();
?>
<?php // endif; ?>