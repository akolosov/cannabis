<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<?php
if (ACTION == "change") {
	$transport = prepareForView($connection->execute('select * from cs_transport where id = '.TRANSPORT_ID)->fetch());
} else {
	$transport = array();
}
?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<form width="100%" height="100%" align="right"
	action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("transport_ID")?"&transport_id=".transport_ID:""); ?>"
	method="POST"><input type="hidden" name="x_transport_id"
	value="<?= ($transport['id']?$transport['id']:'0'); ?>" />
<table width="100%">
	<tr>
		<td align="right" valign="top">Наименование:</td>
		<td align="left" valign="top" colspan="3"><input type="text"
			name="x_transport_name" value="<?= $transport['name']; ?>" size="35"
			style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Описание:</td>
		<td align="left" valign="top" colspan="3"><input type="text"
			name="x_transport_descr" value="<?= $transport['description']; ?>"
			size="255" style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Сервер:</td>
		<td align="left" valign="top"><input type="text"
			name="x_transport_server" value="<?= $transport['server_address']; ?>"
			size="255" style=" width : 100%; " /></td>
		<td align="right" valign="top">Порт:</td>
		<td align="left" valign="top"><input type="text"
			name="x_transport_port" value="<?= $transport['server_port']; ?>"
			size="255" style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Пользователь:</td>
		<td align="left" valign="top"><input type="password"
			name="x_transport_login" value="<?= $transport['server_login']; ?>"
			size="255" style=" width : 100%; " /></td>
		<td align="right" valign="top">Пароль:</td>
		<td align="left" valign="top"><input type="password"
			name="x_transport_password" value="<?= $transport['server_passwd']; ?>"
			size="255" style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Класс PHP5:</td>
		<td align="left" valign="top" colspan="3"><input type="text"
			name="x_transport_class" value="<?= $transport['class_name']; ?>"
			size="255" style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align="left" colspan="3"><input title="Принять внесенные изменения"
			type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	</tr>
</table>
</form>
<?php endif; ?>