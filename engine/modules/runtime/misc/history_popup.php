<?php
print "<img class=\"action\" src=\"images/date.png\" style=\" float : right; \" onClick=\"hideIt('history_data')\" title=\"Показать история движения документа\" />";
print "<div class=\"process_info\" id=\"history_data\"><img src=\"images/close.png\" style=\" float: right; \" onClick=\"hideIt('history_data')\" title=\"Закрыть история движения документа\" />";

function printChronedItem($chronos = array()) {
	$npp = 1;
	foreach ($chronos as $chrono) {
		print "<tr>";
		print "<td class=\"small\" width=\"5%\" align=\"right\"><a href=\"#\"></a>".$npp."</td>";
		print "<td align=\"center\" class=\"small\" width=\"15%\" title=\"Пользователь, сделавший откат документа\">".$chrono->getProperty('accountname')."</td>";
		print "<td align=\"center\" class=\"small\" width=\"15%\" title=\"Дата и время отката\">".strftime("%d.%m.%Y в %H:%M", strtotime($chrono->getProperty('chrono_at')))."</td>";
		print "<td align=\"center\" class=\"small\" width=\"30%\" title=\"Откат от действия\">".$chrono->getProperty('fromactionname')."</td>";
		print "<td align=\"center\" class=\"small\" width=\"30%\" title=\"Откат к действию\">".$chrono->getProperty('toactionname')."</td>";
		print "<td width=\"35\" align=\"right\">";
		print "<span class=\"small action\" title=\"Просмотр состояния документа '".$chrono->getProperty('processname')."' на момент до отката\" onClick=\"openWindow('/?module=".getParentModule().DIRECTORY_SEPARATOR.getParentChildModule().DIRECTORY_SEPARATOR."history".DIRECTORY_SEPARATOR."processes".DIRECTORY_SEPARATOR."list&chrono_instance_id=".$chrono->getProperty('id')."&process_instance_id=".$chrono->getProperty('instance_id')."&process_id=".$chrono->getProperty('process_id')."');\"><img src=\"images/template.png\" /></span>";
		print "&nbsp";
		print "<span class=\"small action\" title=\"Печать состояния документа '".$chrono->getProperty('processname')."' на момент до отката\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getParentChildModule().DIRECTORY_SEPARATOR."history".DIRECTORY_SEPARATOR."processes".DIRECTORY_SEPARATOR."list&media=print&chrono_instance_id=".$chrono->getProperty('id')."&process_instance_id=".$chrono->getProperty('instance_id')."&process_id=".$chrono->getProperty('process_id')."');\"><img src=\"images/print.png\" /></span>";
		print "</td></tr>";
		$npp++;
	}
}

print "<div class=\"caption\">История движения документа ".$this->getProperty('name')." №".$this->getProperty('id')."</div>\n";
print "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"0\" border=\"0\" class=\"treeitem\">";
printChronedItem($this->getHistory());
print "</table><br />";
print "</div>";
?>
