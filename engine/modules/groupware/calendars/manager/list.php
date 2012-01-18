<?php // if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php

require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."functions.php");

$manager = new CalendarManager($engine, USER_CODE);

if (defined("ACTION")) {
	switch (ACTION) {
		case "add" :
			break;
		case "change" :
			break;
		case "delete" :
			break;
		case "erase" :
			break;
		default:
			break;
	}
}

function printOwnedCalendars() {
	global $manager, $user_permissions;

	// print "<div class=\"caption\">".(($user_permissions[getParentModule()][getChildModule()]['can_write'])?"<img src=\"images/create_icon.png\" class=\" action \" style=\" float: right; z-index: 1000; \" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/edit&action=add';\" title=\"Добавить файл или папку\" />":"")."Личные файлы пользователя</div>\n";
	print "<div class=\"caption\"><img src=\"images/create_icon.png\" class=\" action \" style=\" float: right; z-index: 1000; \" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/edit&action=add';\" title=\"Добавить календарь пользователя\" />Личные календари пользователей</div>\n";
	print "<table id=\"owned\" width=\"100%\" align=\"center\" cellspacing=\"1\" cellpadding=\"1\" style=\" border : 1px dotted #ccc; border-collapse : collapse; margin-bottom : 1px; \">";
	print "<tr>";
	print "<th>Наименование</th>";
	print "<th>Действия</th>";
	print "</tr>";
	foreach ($manager->getOwnedCalendars() as $calendar) {
		print "<tr>";
		print "<td width=\"auto\" class=\" ".(($calendar->isDeleted())?"strike":"")." \" title=\"".$calendar->getProperty('description')."\">".$calendar->getProperty('name')."</td>";
		print "<td width=\"160\" align=\"center\">";
		print "<img src=\"images/day.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getParentChildModule()."/events/list&calendar_mode=day&calendar_id=".$calendar->getProperty('id')."';\" title=\"Просмотр/Изменение событий в календаре [Режим: ДЕНЬ]\" />";
		print "<img src=\"images/week.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getParentChildModule()."/events/list&calendar_mode=week&calendar_id=".$calendar->getProperty('id')."';\" title=\"Просмотр/Изменение событий в календаре [Режим: НЕДЕЛЯ]\" />";
		print "<img src=\"images/month.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getParentChildModule()."/events/list&calendar_mode=mounth&calendar_id=".$calendar->getProperty('id')."';\" title=\"Просмотр/Изменение событий в календаре [Режим: МЕСЯЦ]\" />";
		print "<img src=\"images/year.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getParentChildModule()."/events/list&calendar_mode=year&calendar_id=".$calendar->getProperty('id')."';\" title=\"Просмотр/Изменение событий в календаре [Режим: ГОД]\" />";
		print "<img src=\"images/edit_icon.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/edit&action=change&calendar_id=".$calendar->getProperty('id')."';\" title=\"Изменить настройки календаря\" />";
		print "<img src=\"images/delete_icon.png\" class=\"action\" onClick=\"javascript:confirmIt('?module=".getParentModule()."/".getChildModule()."/list&action=delete&calendar_id=".$calendar->getProperty('id')."', '_top', true);\" title=\"Удалить календарь\" />";
		print "<img src=\"images/permissions.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/permissions/list&calendar_id=".$calendar->getProperty('id')."';\" title=\"Изменить/Редактировать права доступа к календарю\" />";
		print "</td>";
		print "</tr>";
	}
	print "</table>";
}

function printDelegatedCalendars() {
	global $manager;

	print "<br /><div class=\"caption\" onClick=\"javascript:hideIt('delegated');\">Доверенные календари пользователей</div>\n";
	print "<div id=\"delegated\" style=\" display : block; visibility : visible; \">";
	print "<table width=\"100%\" align=\"center\" cellspacing=\"1\" cellpadding=\"1\" style=\" border : 1px dotted #ccc; border-collapse : collapse; margin-bottom : 1px; \">";
	print "<tr>";
	print "<th>Наименование</th>";
	print "<th>Действия</th>";
	print "</tr>";
	foreach ($manager->getDelegatedCalendars() as $calendar) {
		print "<tr>";
		print "<td width=\"auto\" class=\"".(($calendar->isDeleted())?"strike":"")."\" title=\"".$calendar->getProperty('description')."\">".$calendar->getProperty('name')." <span class=\"small\">(Владелец: ".$calendar->getProperty('ownername').")</span></td>";
		print "<td width=\"160\" align=\"center\">";
		if (($calendar->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE) or
			($calendar->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
			($calendar->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_ONLY) or
			($calendar->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS)) {
			print "<img src=\"images/day.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getParentChildModule()."/events/list&calendar_mode=day&calendar_id=".$calendar->getProperty('id')."';\" title=\"Просмотр/Изменение событий в календаре [Режим: ДЕНЬ]\" />";
			print "<img src=\"images/week.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getParentChildModule()."/events/list&calendar_mode=week&calendar_id=".$calendar->getProperty('id')."';\" title=\"Просмотр/Изменение событий в календаре [Режим: НЕДЕЛЯ]\" />";
			print "<img src=\"images/month.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getParentChildModule()."/events/list&calendar_mode=mounth&calendar_id=".$calendar->getProperty('id')."';\" title=\"Просмотр/Изменение событий в календаре [Режим: МЕСЯЦ]\" />";
			print "<img src=\"images/year.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getParentChildModule()."/events/list&calendar_mode=year&calendar_id=".$calendar->getProperty('id')."';\" title=\"Просмотр/Изменение событий в календаре [Режим: ГОД]\" />";
		}
		if (($calendar->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE) or
			($calendar->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
			($calendar->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS)) {
			print "<img src=\"images/edit_icon.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/edit&action=change&calendar_id=".$calendar->getProperty('id')."';\" title=\"Изменить настройки календаря\" />";
		}
		if (($calendar->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
			($calendar->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS)) {
			print "<img src=\"images/delete_icon.png\" class=\"action\" onClick=\"javascript:confirmIt('?module=".getParentModule()."/".getChildModule()."/list&action=delete&calendar_id=".$calendar->getProperty('id')."', '_top', true);\" title=\"Удалить календарь\" />";
		}
		if ($calendar->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS) {
			print "<img src=\"images/permissions.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/permissions/list&calendar_id=".$calendar->getProperty('id')."';\" title=\"Изменить/Редактировать права доступа к календарю\" />";
		}
		print "</td>";
		print "</tr>";
	}
	print "</table>";
	print "</div>";
}

function printPublicCalendars() {
	global $manager;

	print "<br /><div class=\"caption\" onClick=\"javascript:hideIt('public');\">Общие календари пользователей</div>\n";
	print "<div id=\"public\" style=\" display : block; visibility : visible; \">";
	print "<table width=\"100%\" align=\"center\" cellspacing=\"1\" cellpadding=\"1\" style=\" border : 1px dotted #ccc; border-collapse : collapse; margin-bottom : 1px; \">";
	print "<tr>";
	print "<th>Наименование</th>";
	print "<th>Действия</th>";
	print "</tr>";
	foreach ($manager->getPublicCalendars() as $calendar) {
		print "<tr>";
		print "<td width=\"auto\" class=\"".(($calendar->isDeleted())?"strike":"")."\" title=\"".$calendar->getProperty('description')."\">".$calendar->getProperty('name')." <span class=\"small\">(Владелец: ".$calendar->getProperty('ownername').")</span></td>";
		print "<td width=\"80\" align=\"center\">";
		print "<img src=\"images/events.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getParentChildModule()."/events/list&calendar_id=".$calendar->getProperty('id')."';\" title=\"Просмотр/Изменение событий в календаре\" />";
		if (($calendar->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE) or
			($calendar->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
			($calendar->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS)) {
			print "<img src=\"images/edit_icon.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/edit&action=change&calendar_id=".$calendar->getProperty('id')."';\" title=\"Изменить настройки календаря\" />";
		}
		if (($calendar->getPermissionValue(USER_CODE) == Constants::PERMISSION_READ_WRITE_DELETE) or
			($calendar->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS)) {
			print "<img src=\"images/delete_icon.png\" class=\"action\" onClick=\"javascript:confirmIt('?module=".getParentModule()."/".getChildModule()."/list&action=delete&calendar_id=".$calendar->getProperty('id')."', '_top', true);\" title=\"Удалить календарь\" />";
		}
		if ($calendar->getPermissionValue(USER_CODE) == Constants::PERMISSION_FULL_ACCESS) {
			print "<img src=\"images/permissions.png\" class=\"action\" onClick=\"document.location.href = '?module=".getParentModule()."/".getChildModule()."/permissions/list&calendar_id=".$calendar->getProperty('id')."';\" title=\"Изменить/Редактировать права доступа к календарю\" />";
		}
		print "</td>";
		print "</tr>";
	}
	print "</table>";
	print "</div>";
}

printOwnedCalendars();
printPublicCalendars();
printDelegatedCalendars();
?>
<?php // endif; ?>