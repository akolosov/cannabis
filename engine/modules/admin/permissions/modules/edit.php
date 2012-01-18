
<?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR.getParentModule().DIRECTORY_SEPARATOR.getChildModule()).DIRECTORY_SEPARATOR."list.php"; ?>
<br />
<?php
$modules = $connection->execute('select * from modules_tree');
if (ACTION == "change") {
	$permission = prepareForView($connection->execute('select cs_permission_list.*, cs_permission.name as permissionname from cs_permission_list, cs_permission where cs_permission_list.permission_id = '.PERMISSION_ID.' and cs_permission_list.module_id = '.MODULE_ID.' and cs_permission_list.permission_id = cs_permission.id')->fetch());
} else {
	$permission = array();
}
?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<form width="100%" height="100%" align="right"
	action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("PERMISSION_ID")?'&permission_id='.PERMISSION_ID:(defined("X_PERMISSION_ID")?'&permission_id='.X_PERMISSION_ID:"")); ?><?= (defined("MODULE_ID")?'&module_id='.MODULE_ID:(defined("X_MODULE_ID")?'&module_id='.X_MODULE_ID:"")); ?>"
	method="POST"><input type="hidden" name="x_permission_list_id"
	value="<?= ($permission['id']?$permission['id']:"0"); ?>" /> <input
	type="hidden" name="x_permission_id"
	value="<?= ($permission['permission_id']?$permission['permission_id']:(defined("PERMISSION_ID")?PERMISSION_ID:"0")); ?>" />
<input type="hidden" name="x_module_id"
	value="<?= ($permission['module_id']?$permission['module_id']:(defined("MODULE_ID")?MODULE_ID:"0")); ?>" />
<table width="100%">
	<tr>
		<td align="right" valign="top" width="20%">Модуль:<br />
		<br />
		<a href="/?module=<?= getParentModule(); ?>/modules/list"
			class="button"><img src="images/list.png" valign="middle" />
		посмотреть все</a></td>
		<td align="left" valign="top"><select name="x_permission_module_id"
			style=" width : 100%; " size="7"
			<?= defined("MODULE_ID")?"disabled":"";?>>
			<option value="0" /><?php foreach ($modules as $module): ?>
			
			
			<option value="<?= $module['id']; ?>"
			<?= ($module['id'] == $permission['module_id'])?"selected":""; ?> /><?= str_pad_html("", $module['level']).trim($module['name'])." (".trim($module['description']).")"; ?>
			<?php endforeach; ?>
		
		</select></td>
	</tr>
	<tr>
		<td align="right" valign="top">Чтение:</td>
		<td align="left" valign="top"><input type="checkbox"
			name="x_permission_can_read"
			<?= $permission['can_read']?"checked":""; ?> style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Запись:</td>
		<td align="left" valign="top"><input type="checkbox"
			name="x_permission_can_write"
			<?= $permission['can_write']?"checked":""; ?> style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Удаление:</td>
		<td align="left" valign="top"><input type="checkbox"
			name="x_permission_can_delete"
			<?= $permission['can_delete']?"checked":""; ?>
			style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Администрирование:</td>
		<td align="left" valign="top"><input type="checkbox"
			name="x_permission_can_admin"
			<?= $permission['can_admin']?"checked":""; ?> style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Обозревание:</td>
		<td align="left" valign="top"><input type="checkbox"
			name="x_permission_can_review"
			<?= $permission['can_review']?"checked":""; ?> style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Наблюдение:</td>
		<td align="left" valign="top"><input type="checkbox"
			name="x_permission_can_observe"
			<?= $permission['can_observe']?"checked":""; ?> style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align="left"><input title="Принять внесенные изменения"
			type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	</tr>
</table>
</form>
