<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<?php
$permissions = $connection->execute('select * from cs_permission')->fetchAll();
$parentaccounts = $connection->execute('select * from accounts_tree where permission_id IS NULL AND passwd IS NULL')->fetchAll();
$cellops = $connection->execute('select * from cs_cellop')->fetchAll();
$divisions = $connection->execute('select * from divisions_tree where id >= 0')->fetchAll();
$maindivisions = $connection->execute('select * from divisions_tree where id >= 0')->fetchAll();
$divisions_list = array();

if (ACTION == "change") {
	$user = prepareForView($connection->execute('select * from cs_account where id = '.USER_ID)->fetch());
	$user_divisions = $connection->execute('select division_id from cs_account_division where account_id = '.USER_ID)->fetchAll();
	foreach ($user_divisions as $user_division) {
		$divisions_list[] = $user_division['division_id'];
	}
} else {
	$user = array('is_active' => true);
}
?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<form width="100%" height="100%" align="right"
	action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("USER_ID")?"&user_id=".USER_ID:""); ?>"
	method="POST"><input type="hidden" name="x_user_id"
	value="<?= ($user['id']?$user['id']:NULL); ?>" /> <input type="hidden"
	name="x_parent_id"
	value="<?= ($user['parent_id']?$user['parent_id']:NULL); ?>" /> <input
	type="hidden" name="x_user_old_passwd"
	value="<?= ($user['passwd']?$user['passwd']:NULL); ?>" /> <input type="hidden"
	name="x_cellop_id"
	value="<?= ($user['cellop_id']?$user['cellop_id']:NULL); ?>" /> <input type="hidden"
	name="x_division_id"
	value="<?= ($user['division_id']?$user['division_id']:NULL); ?>" /> 
<table width="100%">
	<tr>
		<td align="right" valign="top" width="20%">Наименование:</td>
		<td align="left" valign="top" colspan="3"><input type="text" name="x_user_name"
			value="<?= $user['name']; ?>" size="35" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right" valign="top" width="20%">Описание:</td>
		<td align="left" valign="top" colspan="3"><input type="text" name="x_user_descr"
			value="<?= $user['description']; ?>" size="255" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right" valign="top" width="20%">Пароль:</td>
		<td align="left" valign="top"><input type="password"
			name="x_user_passwd" value="<?= $user['passwd']; ?>" size="35"
			style="width: 100%;"
			onChange=" setCookie('passwd_changed', true); return true; " /></td>
		<td align="right" valign="top" width="20%">E-Mail:</td>
		<td align="left" valign="top"><input type="text" name="x_user_mail"
			value="<?= $user['email']; ?>" size="35" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right" valign="top" width="20%">ICQ UID:</td>
		<td align="left" valign="top"><input type="text" name="x_user_icq"
			value="<?= $user['icq']; ?>" size="35" style="width: 100%;" /></td>
		<td align="right" valign="top" width="20%">Jabber ID:</td>
		<td align="left" valign="top"><input type="text" name="x_user_jabber"
			value="<?= $user['jabber']; ?>" size="35" style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="right" valign="top" width="20%">Мобильный телефон:</td>
		<td align="left" valign="top"><input type="text" name="x_user_cell" value="<?= $user['cell']; ?>" size="35" style="width: 100%;" /></td>
		<td align="right" valign="top" width="20%">Мобильный оператор:</td>
		<td align="left" valign="top"><select name="x_user_cellop"
			style="width: 100%;" size="0">
			<option value="" /><?php foreach ($cellops as $cellop): ?>
			<option value="<?= $cellop['id']; ?>"
			<?= ($cellop['id'] == $user['cellop_id'])?"selected":""; ?> /><?= trim($cellop['name']).' ('.trim($cellop['description']).')'; ?>
			<?php endforeach; ?>
		</select></td>
	</tr>
	<tr>
		<td align="right" valign="top">Группа:</td>
		<td align="left" valign="top" colspan="3"><select name="x_user_parent_id"
			style="width: 100%;" size="0">
			<option value="" /><?php foreach ($parentaccounts as $parentaccount): ?>
			<option value="<?= $parentaccount['id']; ?>"
			<?= ($parentaccount['id'] == $user['parent_id'])?"selected":""; ?> /><?= str_pad_html("", $parentaccount['level']).trim($parentaccount['name'])." (".trim($parentaccount['description']).")"; ?>
			<?php endforeach; ?>
		</select></td>
	</tr>
	<tr>
		<td align="right" valign="top">Подразделения:</td>
		<td align="left" valign="top" colspan="3"><select name="x_user_divisions[]"
			style="width: 100%;" multiple="multiple" size="10">
			<?php foreach ($divisions as $division): ?>
			<option value="<?= $division['id']; ?>"
			<?= (in_array($division['id'], $divisions_list)?"selected":""); ?> /><?= str_pad_html("", $division['level']).trim($division['name'])." (".trim($division['description']).")"; ?>
			<?php endforeach; ?>
		</select></td>
	</tr>
	<tr>
		<td align="right" valign="top">Основное подразделение:</td>
		<td align="left" valign="top" colspan="3"><select name="x_user_division"
			style="width: 100%;">
			<?php foreach ($maindivisions as $division): ?>
			<option value="<?= $division['id']; ?>"
			<?= ($division['id'] == $user['division_id']?"selected":""); ?> /><?= str_pad_html("", $division['level']).trim($division['name'])." (".trim($division['description']).")"; ?>
			<?php endforeach; ?>
		</select></td>
	</tr>
	<tr>
		<td align="right" valign="top">Права:</td>
		<td align="left" valign="top" colspan="3"><select name="x_user_permission"
			style="width: 100%;" size="7">
			<option value="" /><?php foreach ($permissions as $permission): ?>
			<option value="<?= $permission['id']; ?>"
			<?= ($permission['id'] == $user['permission_id'])?"selected":""; ?> /><?= trim($permission['name']); ?>
			<?php endforeach; ?>
		</select></td>
	</tr>
	<tr>
		<td align="right" valign="top">Активный:</td>
		<td align="left" valign="top" colspan="3"><input type="checkbox"
			name="x_user_active" id="x_user_active"
			<?= $user['is_active']?"checked":""; ?> style="width: 100%;" /></td>
	</tr>
	<tr>
		<td align="left" colspan="4"><input title="Принять внесенные изменения"
			type="submit" name="submit" value=" Принять " style="width: 100%;" /></td>
	</tr>
</table>
</form>
<?php endif; ?>