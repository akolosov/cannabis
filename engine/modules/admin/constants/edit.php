<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<?php
if (ACTION == "change") {
	$constant = prepareForView($connection->execute('select * from cs_constants where id = '.CONSTANT_ID)->fetch());
} else {
	$constant = array();
}
?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<form width="100%" height="100%" align="right"
	action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("CONSTANT_ID")?"&constant_id=".CONSTANT_ID:""); ?>"
	method="POST"><input type="hidden" name="x_constant_id"
	value="<?= ($constant['id']?$constant['id']:'0'); ?>" />
<table width="100%">
	<tr>
		<td align="right" valign="top">Наименование:</td>
		<td align="left" valign="top"><input type="text"
			name="x_constant_name" <?= ($constant['fixed_name']?'readonly':''); ?> value="<?= $constant['name']; ?>" size="35"
			style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Описание:</td>
		<td align="left" valign="top"><input type="text"
			name="x_constant_descr" value="<?= $constant['description']; ?>"
			size="255" style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Значение:</td>
		<td align="left" valign="top"><input type="text"
			name="x_constant_value" value="<?= $constant['value']; ?>" size="255"
			style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Зарезервированое:</td>
		<td align="left" valign="top"><input type="checkbox"
			name="x_constant_fixed_name"
			<?= $constant['fixed_name']?"checked":""; ?> style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align="left"><input title="Принять внесенные изменения"
			type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	</tr>
</table>
</form>
			<?php endif; ?>