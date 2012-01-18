<?php // if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<?php
$accounts = $connection->execute('select * from accounts_without_groups_list')->fetchAll();
$permissions = $connection->execute('select * from cs_object_permission')->fetchAll();
if (ACTION == "change") {
	$permission = prepareForView($connection->execute('select * from file_permissions_list where file_id = '.FILE_ID.' and account_id = '.PERMISSION_ID)->fetch());
} else {
	$permission = array('permission_id' => Constants::PERMISSION_READ_ONLY);
}
?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<form width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("FILE_ID")?"&file_id=".FILE_ID:"").(defined("PERMISSION_ID")?"&PERMISSION_id=".PERMISSION_ID:""); ?>" method="POST">
	<input type="hidden" name="x_old_id" value="<?= ($permission['id']?$permission['id']:NULL); ?>" />
	<input type="hidden" name="x_old_permission_id" value="<?= ($permission['permission_id']?$permission['permission_id']:Constants::PERMISSION_READ_ONLY); ?>" />
	<input type="hidden" name="x_old_account_id" value="<?= ($permission['account_id']?$permission['account_id']:NULL); ?>" />
	<table width="100%">
		<tr>
			<td align="right" valign="top">Пользователь:</td>
			<td align="left" valign="top">
				<select name="x_account_id" style=" width : 100%; " size="15">
					<?php foreach ($accounts as $account): ?>
					<option value="<?= $account['id']; ?>"
					<?= ($account['id'] == $permission['account_id'])?"selected":""; ?> /><?= trim($account['name'])." (".trim($account['description']).")"; ?>
					<?php endforeach; ?>
				</select></td>
		</tr>
		<tr>
			<td align="right" valign="top">Право доступа:</td>
			<td align="left" valign="top">
				<select name="x_permission_id" style=" width : 100%; " size="5">
					<?php foreach ($permissions as $perm): ?>
					<option value="<?= $perm['id']; ?>"
					<?= ($perm['id'] == $permission['permission_id'])?"selected":""; ?> /><?= trim($perm['name'])." (".trim($perm['description']).")"; ?>
					<?php endforeach; ?>
				</select></td>
		</tr>
		<tr>
			<td align="left" colspan="2"><input title="Принять внесенные изменения" type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
		</tr>
	</table>
</form>
<?php // endif; ?>