<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<?php
	$parentdivisions = $connection->execute('select * from divisions_tree where id >= 0')->fetchAll();
	$accounts = $connection->execute('select * from accounts_tree where id >= 0 and is_active = true')->fetchAll();
	if (ACTION == "change") {
		$division = prepareForView($connection->execute('select * from cs_division where id = '.DIVISION_ID)->fetch());
	} else {
		$division = array();
	}
?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<form width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("division_ID")?"&division_id=".division_ID:""); ?>" method="POST">
	<input type="hidden" name="x_division_id" value="<?= ($division['id']?$division['id']:'0'); ?>" />
	<input type="hidden" name="x_parent_id" value="<?= (defined('PARENT_ID')?PARENT_ID:($division['parent_id']?$division['parent_id']:NULL)); ?>" />
	<input type="hidden" name="x_boss_id" value="<?= ($division['boss_id']?$division['boss_id']:NULL); ?>" />
	<table width="100%">
		<tr>
			<td align="right" valign="top">Наименование:</td>
			<td align="left" valign="top"><input type="text" name="x_division_name" value="<?= $division['name']; ?>" size="35" style=" width : 100%; " /></td>
		</tr>
		<tr>
			<td align="right" valign="top">Описание:</td>
			<td align="left" valign="top"><input type="text" name="x_division_descr" value="<?= $division['description']; ?>" size="35" style=" width : 100%; " /></td>
		</tr>
		<tr>
			<td align="right" valign="top" width="20%">Родитель:</td>
			<td align="left" valign="top">
				<select name="x_division_parent_id" style=" width : 100%; " size="20" <?= defined("DIVISION_ID")?"":"";?>>
					<option value="" />
					<?php foreach ($parentdivisions as $parentdivision): ?>
					<option value="<?= $parentdivision['id']; ?>" <?= (($parentdivision['id'] == $division['parent_id']) || ($parentdivision['id'] == PARENT_ID)?"selected":""); ?> /><?= str_pad_html("", $parentdivision['level']).trim($parentdivision['name'])." (".trim($parentdivision['description']).")"; ?>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<td align="right" valign="top" width="20%">Руководитель:</td>
			<td align="left" valign="top">
				<select name="x_division_boss_id" style=" width : 100%; " size="0">
					<option value="" />
					<?php foreach ($accounts as $account): ?>
					<option value="<?= $account['id']; ?>" <?= (($account['id'] == $division['boss_id'])?"selected":""); ?> /><?= str_pad_html("", $account['level']).trim($account['name'])." (".trim($account['description']).")"; ?>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align="left"><input title="Принять внесенные изменения" type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
		</tr>
		</table>
</form>
<?php endif; ?>