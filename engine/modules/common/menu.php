<?php 
	print "<ul class=\"menu\">Общие:<nobr>";
	if ($user_permissions['admin']['users']['can_admin']) {
		print "<a href=\"/?module=common/authorize&backto=".urlencode($_SERVER['REQUEST_URI'])."\" class=\"warnbutton\"><li title=\"Авторизоваться в системе\" class=\"menuitem\"><img src=\"images/password.png\" valign=\"top\" />&nbsp;Авторизация</li></a>";
	}
	print "<a href=\"/?module=common/authorized\" class=\"button\"><li title=\"О системе документооборота\" class=\"menuitem\"><img src=\"images/about.png\" valign=\"top\" />&nbsp;О системе</li></a>";
	print "<a href=\"/?module=common/forum\" class=\"button\"><li title=\"Зайти на форум\" class=\"menuitem\"><img src=\"images/comment.png\" valign=\"top\" />&nbsp;Форум</li></a>";
	$topics = $connection->execute('select * from cs_public_topic where is_active=true')->fetchAll();
	foreach ($topics as $topic) {
		print "<nobr><a href=\"/?module=public/documents/list&topic_id=".$topic['id']."\" class=\"button\"><li title=\"".str_replace("\n", "<br />", $topic['description'])."\" class=\"menuitem\"><img src=\"images/static_icon.png\" valign=\"top\" />&nbsp;".$topic['name']."</li></a></nobr>";
	}
	print "</nobr></ul>";
	foreach ($user_permissions as $parent_module => $child_modules) {
		if (!$user_permissions[$parent_module]['is_hidden']) {
			print "<ul class=\"menu\">".($child_modules['display']?$child_modules['display']:$parent_module).":";
			foreach ($child_modules as $module_name => $module_rights) {
				if (!$module_rights['is_hidden']) {
					if (($module_rights['can_read']) && ($module_name <> 'display') && ($module_name <> 'name') && ($module_name <> 'description')) {
						print "<nobr><a href=\"/?module=".$parent_module.DIRECTORY_SEPARATOR.$module_name.DIRECTORY_SEPARATOR."list\" class=\"button\"><li title=\"".$module_rights['description']."\" class=\"menuitem\"><img src=\"images/".$module_name.".png\" valign=\"top\" />&nbsp;".$module_rights['display']."</li></a></nobr>";
					}
				}
			}
			print "</ul>";
		}
	}
	print "<ul class=\"menu\">Инструкции:<nobr>";
	print "<a href=\"/files/gw/СЗ.doc\" class=\"button\"><li title=\"Инструкция по Служебной Записке\" class=\"menuitem\"><img src=\"images/help.png\" valign=\"top\" />&nbsp;Служебная Записка</li></a>";
	print "</nobr></ul>";
?>