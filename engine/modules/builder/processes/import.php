<?php if ($user_permissions[getParentModule()][getChildModule()]['can_admin']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<form width="100%" height="100%" align="right"
	enctype="multipart/form-data"
	action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=import" method="POST">
<table width="100%">
	<tr>
		<td align="right" valign="top">Имя файл:</td>
		<td align="left" valign="top"><input type="file" size="100"
			name="x_filename" style=" width : 100%; " accept="text/xml" /></td>
	</tr>
	<td>&nbsp;</td>
	<td align="left"><input title="Загрузать выбранный файл" type="submit"
		name="submit" value=" Загрузить " style=" width : 100%; " /></td>
	</tr>
</table>
</form>
<?php endif; ?>