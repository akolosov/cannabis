<?php // if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<?php
$accounts = $connection->execute('select * from accounts_without_groups_list')->fetchAll();
if (ACTION == "change") {
	$account = prepareForView($connection->execute('select * from contact_accounts_list where contact_id = '.CONTACTLIST_ID.' and account_id = '.ACCOUNT_ID)->fetch());
} else {
	$account = array();
}
?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<form width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("CONTACTLIST_ID")?"&contactlist_id=".CONTACTLIST_ID:"").(defined("account_ID")?"&account_id=".account_ID:""); ?>" method="POST">
	<input type="hidden" name="x_old_id" value="<?= ($account['id']?$account['id']:NULL); ?>" />
	<input type="hidden" name="x_old_account_id" value="<?= ($account['account_id']?$account['account_id']:NULL); ?>" />
	<table width="100%">
		<tr>
			<td align="right" valign="top">Пользователи:</td>
			<td align="left" valign="top">
				<select name="x_account_id" style=" width : 100%; " size="25">
					<?php foreach ($accounts as $accountdata): ?>
					<option value="<?= $accountdata['id']; ?>"
					<?= ($accountdata['id'] == $account['account_id'])?"selected":""; ?> /><?= trim($accountdata['name'])." (".trim($accountdata['description']).")"; ?>
					<?php endforeach; ?>
				</select></td>
		</tr>
		<tr>
			<td align="left" colspan="2"><input title="Принять внесенные изменения" type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
		</tr>
	</table>
</form>
<?php // endif; ?>