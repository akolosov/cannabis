<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<?php
$accounts = $connection->execute('select * from accounts_without_groups_list')->fetchAll();
if (ACTION == "change") {
	$responser = prepareForView($connection->execute('select * from cs_responser where id = '.RESPONSER_ID)->fetch());
} else {
	$responser = array();
}
?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<form width="100%" height="100%" align="right"
	action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("RESPONSER_ID")?"&responser_id=".RESPONSER_ID:""); ?>"
	method="POST">
	<input type="hidden" name="x_responser_id" value="<?= ($responser['id']?$responser['id']:RESPONSER_ID); ?>" />
	<input type="hidden" name="x_account_id" value="<?= ($responser['account_id']?$responser['account_id']:NULL); ?>" />
<table width="100%">
	<tr>
		<td align="right" valign="top">Наименование:</td>
		<td align="left" valign="top"><input type="text" name="x_responser_name"
			value="<?= $responser['name']; ?>" size="35" style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Описание:</td>
		<td align="left" valign="top"><input type="text" name="x_responser_descr"
			value="<?= $responser['description']; ?>" size="255"
			style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Ответственный:</td>
		<td align="left" valign="top">
		 <select name="x_responser_account" style=" width : 100%; " size="0">
			<option value="" />
			<?php foreach ($accounts as $account): ?>
			<option value="<?= $account['id']; ?>"
			<?= ($account['id'] == $responser['account_id'])?"selected":""; ?> /><?= trim($account['name'])." (".trim($account['description']).")"; ?>
			<?php endforeach; ?>
		 </select></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align="left"><input title="Принять внесенные изменения"
			type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	</tr>
</table>
</form>
<?php endif; ?>