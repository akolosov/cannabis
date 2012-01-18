<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<?php
if (ACTION == "change") {
	$mime = prepareForView($connection->execute('select * from cs_mime where id = '.MIME_ID)->fetch());
} else {
	$mime = array();
}
?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<form width="100%" height="100%" align="right"
	action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("MIME_ID")?"&mime_id=".MIME_ID:""); ?>"
	method="POST"><input type="hidden" name="x_mime_id"
	value="<?= ($mime['id']?$mime['id']:'0'); ?>" />
<table width="100%">
	<tr>
		<td align="right" valign="top">Наименование:</td>
		<td align="left" valign="top"><input type="text" name="x_mime_name"
			value="<?= $mime['name']; ?>" size="35" style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Расширение:</td>
		<td align="left" valign="top"><input type="text" name="x_mime_ext"
			value="<?= $mime['ext']; ?>" size="255"
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