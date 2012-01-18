<?php
if (MODULE <> "common/authorize") {
	print "<img title=\"Скрыть/Показать главное меню\" id=\"menuhider\" class=\"action\" style=\" float: left; \" src=\"images/".((MENU_VISIBILITY == "true")?"previous":"next").".png\" onClick=\" hideItAndSetCookie('menu'); if ($('menu').style.display == 'none') { $('menuhider').src = 'images/next.png'; $('content').style.width = '99%'; } else { $('menuhider').src = 'images/previous.png'; $('content').style.width = '84%'; }; \" />&nbsp;";
	if (getModuleChildCount() > 2) {
		print "<span class=\"navigation\">Навигация:";
		print "<span class=\"navigationitem\"><a title=\"".$user_permissions[getParentModule()][getParentChildModule()]['description']."\" href=\"?module=";
		print getParentModule().DIRECTORY_SEPARATOR.getParentChildModule().DIRECTORY_SEPARATOR."list";
		print getURIParams();
		print "\" class=\"navbutton\">";
		print "<img src=\"images/".$user_permissions[getParentModule()][getParentChildModule()]['name'].".png\" /> ";
		print $user_permissions[getParentModule()][getParentChildModule()]['display'];
		print "</a></span>";
		if (getModuleByLevel(4) == 'edit')  {
			print "<span class=\"navigationitem\"><a title=\"Назад к списку\" href=\"?module=";
			print getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list";
			print getURIParams();
			print "\" class=\"navbutton\">";
			print "<img src=\"images/list.png\" /> Список</a></span>";
		}
		if ((getModuleByLevel(5) == 'list') || (getModuleChildCount() >= 4))  {
			print "<span class=\"navigationitem\"><a title=\"На уровень вверх\" href=\"?module=";
			print getParentModule().DIRECTORY_SEPARATOR.getModuleByLevel(2).DIRECTORY_SEPARATOR.getModuleByLevel(3).DIRECTORY_SEPARATOR."list";
			print getURIParams();
			print "\" class=\"navbutton\">";
			print "<img src=\"images/tree.png\" /> Вверх</a></span>";
		}
		if (getModuleByLevel(5) == 'edit')  {
			print "<span class=\"navigationitem\"><a title=\"Назад к списку\" href=\"?module=";
			print getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list";
			print getURIParams();
			print "\" class=\"navbutton\">";
			print "<img src=\"images/list.png\" /> Список</a></span>";
		}
		print "</span>";
	} else {
		print "&nbsp;";
	}
}
?>
