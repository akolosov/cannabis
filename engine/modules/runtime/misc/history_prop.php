<?php if (defined('CHRONO_INSTANCE_ID')): ?>
<?php
	require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php");
	$process = new Chrono($engine, CHRONO_INSTANCE_ID);
	$process->view(((MEDIA <> 'print')?false:true));
?>
<? if (MEDIA <> 'print'): ?>
<table width="100%" align="center">
	<tr>
		<th width="3%">#</th>
		<th width="50%">Наименование</th>
		<th width="16"></th>
		<th width="15%">Начало</th>
		<th width="15%">Конец</th>
		<th width="15%">Исполнитель</th>
		<th width="auto"></th>
	</tr>
	<?php
	print "<div class=\"process\"><b>Документ: </b>".$process->getProperty('processname').' №'.$process->getProperty('id').' (статус: '.$process->getProperty('statusname').")</div>\n";
	$actions = $process->getProperty('[actions]')->getElements();

	foreach ($actions as $action) {
		print "<tr>";
		print "<td align=\"center\" width=\"16\">".$action->getProperty('npp')."</td>";
		print "<td class=\"".((($action->getProperty('status_id') == Constants::ACTION_STATUS_IN_PROGRESS))?" bold ":"").(($action->getProperty('status_id') == Constants::ACTION_STATUS_SKIPED)?" strike ":"")."\" title=\"".$action->getProperty('description')." <b>(".$action->getProperty('statusname').")</b>\">".$action->getProperty('name')."</td>";
		print "<td width=\"16\" align=\"center\" title=\"Тип: ".$action->getProperty('typename').($action->getProperty('is_interactive') == Constants::TRUE?" (интерактивное)":"")."\"><img src=\"images/actions/".($action->getProperty('is_interactive') == Constants::TRUE?"i_":"a_").$actions_icons[$action->getProperty('type_id')-1].".gif\" /></td>";
		print "<td align=\"center\" class=\"small\">".($action->getProperty('started_at')?strftime("%d.%m.%Y в %H:%M", strtotime($action->getProperty('started_at'))):"")."</td>";
		print "<td align=\"center\" class=\"small\">".($action->getProperty('ended_at')?strftime("%d.%m.%Y в %H:%M", strtotime($action->getProperty('ended_at'))):"")."</td>";
		print "<td ".(($action->getProperty('initiator_id') <> $action->getProperty('performer_id'))?"title=\"Действие делигировано пользователем ".$action->getProperty('initiatorname')."\" class=\"small red\"":" class=\"small\"")." align=\"center\">".$action->getProperty('performername')."</td>";
		print "</tr>\n";
	}
	?>
</table>
<br />
<table width="100%" align="center">
	<tr>
		<th width="70%">Наименование</th>
		<th width="29%">Значение</th>
		<th width="auto"></th>
	</tr>
	<?php
	print "<div class=\"actions\"><b>Свойства документа: </b>".$process->getProperty('name')."</div>\n";
	$properties = $process->getProperty('[properties]')->getElements();
	foreach ($properties as $property) {
		print "<tr>";
		print "<td title=\"".$property->getProperty('description')." (Тип: ".$property->getProperty('typename').")\">".$property->getProperty('name')."</td>";
		$propval = stripMacros($property->getProperty('value'));
		print "<td align=\"center\">".(is_null($property->getProperty('mime_type'))?((mb_strlen($propval) > 20)?mb_substr($propval, 0, 20)."...":$propval):"<a href=\"".FILE_CACHE_PATH."/".$property->getPropertyFileName()."\">".$property->getPropertyFileName()."</a>")."</td>";
		print "<td>";
		if ((isNotNULL($propval)) or (isNotNULL($property->getProperty('mime_type')))) {
			if (isNotNULL($property->getProperty('mime_type'))) {
				storeChronoToCache($property->getProperty('value_id'), FILE_CACHE_PATH."/".$property->getPropertyFileName());
			}
			print "&nbsp;".(isNULL($property->getProperty('mime_type'))?"<img class=\"action\" src=\"images/list.png\" onClick=\"hideIt('prop_view_".$property->getProperty('id')."')\" title=\"Просмотр значения\" /><div title=\"Кликните чтобы закрыть\" class=\"prop_info\" id=\"prop_view_".$property->getProperty('id')."\" onClick=\"hideIt('prop_view_".$property->getProperty('id')."')\">".str_replace("\n", "<br />", $property->getProperty('value')):"<a href=\"".FILE_CACHE_PATH."/".$property->getPropertyFileName()."\"><img src=\"images/export.png\" /></a>")."</div>";
		}
		print "</td>";
		print "</tr>\n";
	}
	?>
</table>
<?php endif; ?>
<?php endif; ?>