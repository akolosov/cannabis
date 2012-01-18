<?php // if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>

<?php
if (ACTION == "change") {
	$contactlist = prepareForView($connection->execute('select * from contacts_list where id = '.CONTACTLIST_ID)->fetch());
} else {
	$contactlist = array('is_public' => false);
}

require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php");
?>

<form width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("CONTACTLIST_ID")?"&contactlist_id=".CONTACTLIST_ID:""); ?>" method="POST">
<table width="100%">
	<tr>
		<td align="right" valign="top">Наименование:</td>
		<td align="left" valign="top">
			<input type="text" name="x_name" value="<?= $contactlist['name']; ?>" size="35" style=" width : 100%; " />
		</td>
	</tr>
	<tr>
		<td align="right" valign="top">Описание:</td>
		<td align="left" valign="top">
			<textarea name="x_description" size="15" style=" width : 100%; " ><?= $contactlist['description']; ?></textarea>
		</td>
	</tr>
	<tr>
		<td align="right" valign="top">Общий список:</td>
		<td align="left" valign="top">
			<input type="checkbox" name="x_is_public" <?= $contactlist['is_public']?"checked":""; ?> style=" width : 100%; " />
		</td>
	</tr>
	<tr>
		<td align="left" colspan="2">
			<input title="Принять внесенные изменения" type="submit" name="submit" value=" Принять " style=" width : 100%; " />
		</td>
	</tr>
</table>
</form>

<?php // endif; ?>