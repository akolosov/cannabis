<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<?php
if (ACTION == "change") {
	$role = prepareForView($connection->execute('select * from cs_role where id = '.ROLE_ID)->fetch());
} else {
	$role = array();
}
?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<form width="100%" height="100%" align="right"
	action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("role_ID")?"&role_id=".role_ID:""); ?>"
	method="POST"><input type="hidden" name="x_role_id"
	value="<?= ($role['id']?$role['id']:'0'); ?>" />
<table width="100%">
	<tr>
		<td align="right" valign="top">Наименование:</td>
		<td align="left" valign="top"><input type="text" name="x_role_name"
			value="<?= $role['name']; ?>" size="35" style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Описание:</td>
		<td align="left" valign="top"><input type="text" name="x_role_descr"
			value="<?= $role['description']; ?>" size="255"
			style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align="left"><input title="Принять внесенные изменения"
			type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	</tr>
</table>
</form>
<?php endif; ?>