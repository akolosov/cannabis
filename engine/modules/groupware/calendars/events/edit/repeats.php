<table width="100%" class="noborders intabspanel" >
	<tr>
		<td align="right" valign="top" width="20%">Событие повторяется:</td>
		<td align="left" valign="top" width="auto"><input <?= ($readonly?"disabled":"") ?> type="checkbox" id="x_is_periodic" name="x_is_periodic" <?= $event['is_periodic']?"checked":""; ?> style=" width : 100%; " onChange=" $('period_types').toggle(); " /></td>
	</tr>
	<tr>
		<td align="left" valign="top" width="100%" colspan="2">
			<div <?= $event['is_periodic']?"":"style=\" display : none; \""; ?> id="period_types">
				<div align="center">
					<input style=" width: auto !important; " <?= ($readonly?"disabled":"") ?> <?= (($period['period_id'] == Constants::EVENT_PERIOD_DAILY)?"checked":"") ?> type="radio" id="period_type_daily" name="x_period_id" value="1" onChange="if (this.checked) { $('period_daily').show(); $('period_weekly').hide(); $('period_mounthly').hide(); $('period_yearly').hide(); }" /><label for="period_type_daily">Ежедневно&nbsp;&nbsp;</label>
					<input style=" width: auto !important; " <?= ($readonly?"disabled":"") ?> <?= (($period['period_id'] == Constants::EVENT_PERIOD_WEEKLY)?"checked":"") ?> type="radio" id="period_type_weekly" name="x_period_id" value="2" onChange="if (this.checked) { $('period_daily').hide(); $('period_weekly').show(); $('period_mounthly').hide(); $('period_yearly').hide(); }" /><label for="period_type_weekly">Еженедельно&nbsp;&nbsp;</label>
					<input style=" width: auto !important; " <?= ($readonly?"disabled":"") ?> <?= (($period['period_id'] == Constants::EVENT_PERIOD_MOUNTHLY)?"checked":"") ?> type="radio" id="period_type_mounthly" name="x_period_id" value="3" onChange="if (this.checked) { $('period_daily').hide(); $('period_weekly').hide(); $('period_mounthly').show(); $('period_yearly').hide(); }" /><label for="period_type_mounthly">Ежемесячно&nbsp;&nbsp;</label>
					<input style=" width: auto !important; " <?= ($readonly?"disabled":"") ?> <?= (($period['period_id'] == Constants::EVENT_PERIOD_YEARLY)?"checked":"") ?> type="radio" id="period_type_yearly" name="x_period_id" value="4" onChange="if (this.checked) { $('period_daily').hide(); $('period_weekly').hide(); $('period_mounthly').hide(); $('period_yearly').show(); }" /><label for="period_type_yearly">Ежегодно&nbsp;&nbsp;</label>
				</div>
				<div <?= ($period['period_id'] == Constants::EVENT_PERIOD_DAILY)?"":"style=\" display : none; \""; ?> id="period_daily"><?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."calendars".DIRECTORY_SEPARATOR."events".DIRECTORY_SEPARATOR."edit".DIRECTORY_SEPARATOR."repeats".DIRECTORY_SEPARATOR."daily.php"); ?></div>
				<div <?= ($period['period_id'] == Constants::EVENT_PERIOD_WEEKLY)?"":"style=\" display : none; \""; ?> id="period_weekly"><?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."calendars".DIRECTORY_SEPARATOR."events".DIRECTORY_SEPARATOR."edit".DIRECTORY_SEPARATOR."repeats".DIRECTORY_SEPARATOR."weekly.php"); ?></div>
				<div <?= ($period['period_id'] == Constants::EVENT_PERIOD_MOUNTHLY)?"":"style=\" display : none; \""; ?> id="period_mounthly"><?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."calendars".DIRECTORY_SEPARATOR."events".DIRECTORY_SEPARATOR."edit".DIRECTORY_SEPARATOR."repeats".DIRECTORY_SEPARATOR."mounthly.php"); ?></div>
				<div <?= ($period['period_id'] == Constants::EVENT_PERIOD_YEARLY)?"":"style=\" display : none; \""; ?> id="period_yearly"><?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."calendars".DIRECTORY_SEPARATOR."events".DIRECTORY_SEPARATOR."edit".DIRECTORY_SEPARATOR."repeats".DIRECTORY_SEPARATOR."yearly.php"); ?></div>
			</div>
		</td>
	</tr>
</table>
