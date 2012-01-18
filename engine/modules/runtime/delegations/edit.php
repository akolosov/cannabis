<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<?php
if (ACTION == "change") {
	$delegate = prepareForView($connection->execute('select * from cs_delegate where id = '.DELEGATE_ID)->fetch());
} else {
	$delegate = array('is_active' => true);
}
?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<form width="100%" height="100%" align="right"
	action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("DELEGATE_ID")?"&delegate_id=".DELEGATE_ID:""); ?>"
	method="POST">
	<input type="hidden" name="x_delegate_id" value="<?= ($delegate['id']?$delegate['id']:DELEGATE_ID); ?>" />
	<input type="hidden" name="x_account_id" value="<?= ($delegate['delegate_id']?$delegate['delegate_id']:NULL); ?>" />
<table width="100%">
<?= $engine->getFormManager()->formCombo(array('title' => 'Кому делигировать',
													   'name' => 'X_DELEGATE_ACCOUNT',
													   'description' => 'Пользователь, которому делигируются полномочия',
													   'value' => $delegate['delegate_id'],
													   'size' => 0,
												  	   'required' => false,
												  	   'error' => false,
													   'colspan' => 3,
												       'nocurrentuser' => true,
													   'titlewidth' => '20%',
													   'datawidth' => '80%',
													   'readonly' => false,
													   'disabled' => false,
													   'emptyline' => false,
													   'reverse' => false,
													   'data' => $engine->getDirectoryList(array_merge(array('directory' => 'get_accounts_by_post_and_division(\'%%USER_POSTS%%\', \'%%USER_DIVISIONCODE%%\')', 'valueasname' => false, 'where' => 'id not in (select account_id from cs_delegate where started_at <= localtimestamp and ended_at >= localtimestamp) and id <> '.USER_CODE)))));
?>
<? print $engine->getFormManager()->useCalendars(); ?>
<tr>
<td class="form" width="20%" align="right" valign="top" title="Делигировать полномочия с ...">Период с:</td><td width="30%"><input class="calendarinput" name="x_period_from" id="x_period_from" type="text" value="<?= (defined('X_PERIOD_FROM')?X_PERIOD_FROM:strftime("%d.%m.%Y %H:%M", (isNotNULL($delegate['started_at'])?strtotime($delegate['started_at']):time()))); ?>" /><img valign="middle" src="images/date.png" title="Открыть календарь" onClick="return showCalendar('x_period_from', '%d.%m.%Y %H:%M', '24');" /></td>
<td class="form" width="20%" align="right" valign="top" title="Делигировать полномочия по ...">Период по:</td><td width="30%"><input class="calendarinput" name="x_period_to" id="x_period_to" type="text" value="<?= (defined('X_PERIOD_TO')?X_PERIOD_TO:strftime("%d.%m.%Y %H:%M", (isNotNULL($delegate['ended_at'])?strtotime($delegate['ended_at']):time()))); ?>" /><img valign="middle" src="images/date.png" title="Открыть календарь" onClick="return showCalendar('x_period_to', '%d.%m.%Y %H:%M', '24');" /></td>
</tr>
	<tr>
		<td>&nbsp;</td>
		<td colspan="3" align="left"><input title="Принять внесенные изменения"
			type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	</tr>
</table>
</form>
<?php endif; ?>