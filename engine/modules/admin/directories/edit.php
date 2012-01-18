<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<?php
if (ACTION == "change") {
	$directory = prepareForView($connection->execute('select * from cs_directory where id = '.DIRECTORY_ID)->fetch());
} else {
	$directory = array('custom' => false);
}
?>
<script language="JavaScript">
<!--
	function isCustom() {
		if ($('x_directory_custom').checked) {
			$('x_directory_table').disabled = true; 
			$('x_directory_parameters').disabled = true; 
		} else {
			$('x_directory_table').disabled = false; 
			$('x_directory_parameters').disabled = false; 
		}
		return true;
	}
-->
</script>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<form width="100%" height="100%" align="right"
	action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("directory_ID")?"&directory_id=".directory_ID:""); ?>"
	method="POST"><input type="hidden" name="x_directory_id"
	value="<?= ($directory['id']?$directory['id']:'0'); ?>" />
<table width="100%">
	<tr>
		<td align="right" valign="top">Наименование:</td>
		<td align="left" valign="top"><input type="text" name="x_directory_name"
			value="<?= $directory['name']; ?>" size="35" style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Описание:</td>
		<td align="left" valign="top"><input type="text" name="x_directory_descr"
			value="<?= $directory['description']; ?>" size="255"
			style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Пользовательский:</td>
		<td align="left" valign="top"><input type="checkbox"
			id="x_directory_custom" name="x_directory_custom" onChange="javascripte:isCustom();"
			<?= $directory['custom']?"checked":""; ?> style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Таблица или Представление:</td>
		<td align="left" valign="top"><input type="text" id="x_directory_table" name="x_directory_table"
			value="<?= $directory['tablename']; ?>" size="35" style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Параметры отбора:</td>
		<td align="left" valign="top"><input type="text" id="x_directory_parameters" name="x_directory_parameters"
			value="<?= $directory['parameters']; ?>" size="35" style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align="left"><input title="Принять внесенные изменения"
			type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	</tr>
</table>
</form>
<script language="JavaScript">
<!--
	isCustom();
-->
</script>
<?php endif; ?>