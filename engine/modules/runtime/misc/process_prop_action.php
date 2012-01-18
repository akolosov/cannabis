<?php if ((defined('PROJECT_INSTANCE_ID')) && (defined('PROCESS_INSTANCE_ID'))): ?>
<?php
	require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php");
	require_once(MODULES_PATH.DIRECTORY_SEPARATOR.'runtime'.DIRECTORY_SEPARATOR.'misc'.DIRECTORY_SEPARATOR.'change.php');			
	
	if ((is_null($project)) or ($project->getProperty('id') <> PROJECT_INSTANCE_ID)) {
		$project = new ProjectInstanceWrapper($engine, PROJECT_INSTANCE_ID, array('onlyprocess' => PROCESS_INSTANCE_ID));
		$process = $project->getProperty('[processes]')->findElementByID(PROCESS_INSTANCE_ID);
	} else {
		logRuntime('[Controllers.Processes->List] reinitialize process instance by id '.PROCESS_INSTANCE_ID);
		$process = $project->reinitProcess(PROCESS_INSTANCE_ID);
	}
	if ((ACTION <> 'execute') and (ACTION <> 'saveform')) {
		$process->view(((MEDIA <> 'print')?false:true));
	}
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
	print "<div class=\"project\"><b>Предприятие: </b>".$project->getProperty('name').")</div>";
	print "<div class=\"process\"><b>Документ: </b>".$process->getProperty('name').' №'.$process->getProperty('id').' (статус: '.$process->getProperty('statusname').(($process->haveIncomletedChilds())?' [ожидает завершения работы дочерних документов]':'').")</div>\n";
	$actions = $process->getProperty('[actions]')->getElements();

	foreach ($actions as $action) {
		print "<tr>";
		print "<td align=\"center\" width=\"16\">".$action->getProperty('npp')."</td>";
		print "<td class=\"".((($action->getProperty('name') == $process->getCurrentAction()->getProperty('name') and ($process->getProperty('status_id') <> Constants::PROCESS_STATUS_COMPLETED)))?" bold ":"").(($action->getProperty('status_id') == Constants::ACTION_STATUS_SKIPED)?" strike ":"")."\" title=\"".$action->getProperty('description')." <b>(".$action->getProperty('statusname').")</b>\">".$action->getProperty('name')."</td>";
		print "<td width=\"16\" align=\"center\" title=\"Тип: ".$action->getProperty('typename').($action->getProperty('is_interactive') == Constants::TRUE?" (интерактивное)":"")."\"><img src=\"images/actions/".($action->getProperty('is_interactive') == Constants::TRUE?"i_":"a_").$actions_icons[$action->getProperty('type_id')-1].".gif\" /></td>";
		print "<td align=\"center\" class=\"small\">".($action->getProperty('started_at')?strftime("%d.%m.%Y в %H:%M", strtotime($action->getProperty('started_at'))):"")."</td>";
		print "<td align=\"center\" class=\"small\">".($action->getProperty('ended_at')?strftime("%d.%m.%Y в %H:%M", strtotime($action->getProperty('ended_at'))):"")."</td>";
		print "<td ".(($action->getProperty('initiator_id') <> $action->getProperty('performer_id'))?"title=\"Действие делигировано пользователем ".$action->getProperty('initiatorname')."\" class=\"small red\"":" class=\"small\"")." align=\"center\">".$action->getProperty('performername')."</td>";
		print "<td align=\"center\">";

		if ($action->getProperty('status_id') == Constants::ACTION_STATUS_IN_PROGRESS) {
			if (($user_permissions[getParentModule()][getParentChildModule()]['can_write']) && ($action->canPerform()) && ($action->getProperty('status_id') <> Constants::ACTION_STATUS_WAITING)) {
				print "<span class=\"small action\" title=\"Запустить действие документа '".$action->getProperty('name')."'\" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getParentChildModule().DIRECTORY_SEPARATOR."list&action=execute&project_instance_id=".PROJECT_INSTANCE_ID."&project_id=".$action->getProperty('project_id')."&process_instance_id=".$action->getProperty('instance_id')."&process_id=".$action->getProperty('process_id')."';\"><img src=\"images/play.png\" /></span>";
			}
		}

		if (($user_permissions[getParentModule()][getParentChildModule()]['can_admin']) or (($process->getProperty('initiator_id') == USER_CODE) and ($process->getProperty('performer_id') == USER_CODE))) {
			
		}

		if (($user_permissions[getParentModule()][getParentChildModule()]['can_admin']) and ($action->getProperty('type_id') <> Constants::ACTION_TYPE_INFO)) {
			print "&nbsp;<img src=\"images/edit.png\" onClick=\"hideIt('action_edit_".$action->getProperty('id')."')\" title=\"Изменение значения действия\" /><div class=\"prop_info\" id=\"action_edit_".$action->getProperty('id')."\"><img src=\"images/close.png\" style=\" float: right; \"  onClick=\"hideIt('action_edit_".$action->getProperty('id')."')\" title=\"Закрыть форму\" />".($action->getFormManager()->generateActionEditForm(array('action' => $action)))."</div>";
		}

		print "</td>";
		print "</tr>\n";
	}
	?>
</table>
<br />
<table width="100%" align="center">
	<tr>
		<th width="50%">Наименование</th>
		<th width="49%">Значение</th>
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
		if (($user_permissions[getParentModule()][getParentChildModule()]['can_admin']) and ((isNotNULL($propval)) or (isNotNULL($property->getProperty('mime_type'))))) {
			print "<img src=\"images/edit.png\" onClick=\"hideIt('prop_edit_".$property->getProperty('id')."')\" title=\"Изменение значения\" /><div class=\"prop_info\" id=\"prop_edit_".$property->getProperty('id')."\"><img src=\"images/close.png\" style=\" float: right; \"  onClick=\"hideIt('prop_edit_".$property->getProperty('id')."')\" title=\"Закрыть форму\" />".($process->getFormManager()->generatePropertyEditForm(array('property' => $property, 'fileupload' => (isNotNULL($property->getProperty('mime_type'))))))."</div>";
		}
		if ((isNotNULL($propval)) or (isNotNULL($property->getProperty('mime_type')))) {
			if (isNotNULL($property->getProperty('mime_type'))) {
				storeToCache($property->getProperty('value_id'), FILE_CACHE_PATH."/".$property->getPropertyFileName());
			}
			print "&nbsp;".(isNULL($property->getProperty('mime_type'))?"<img src=\"images/list.png\" onClick=\"hideIt('prop_view_".$property->getProperty('id')."')\" title=\"Просмотр значения\" /><div title=\"Кликните чтобы закрыть\" class=\"prop_info\" id=\"prop_view_".$property->getProperty('id')."\" onClick=\"hideIt('prop_view_".$property->getProperty('id')."')\">".str_replace("\n", "<br />", $property->getProperty('value')):"<a href=\"".FILE_CACHE_PATH."/".$property->getPropertyFileName()."\"><img src=\"images/export.png\" /></a>")."</div>";
		}
		print "</td>";
		print "</tr>\n";
	}
	?>
</table>
	<?php endif; ?>
<? endif; ?>