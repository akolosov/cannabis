<?php // if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<?php
$folders = $connection->execute('select * from files_tree where is_folder = true and owner_id = '.USER_CODE)->fetchAll();
if (ACTION == "change") {
	$file = prepareForView($connection->execute('select * from files_list where id = '.FILE_ID)->fetch());
} else {
	$file = array('is_folder' => false);
}
?>
<script type="text/javascript">
<!--
	function checkFolder() {
		if ($('x_is_folder').checked == true) {
			$('x_folder_name').disabled = false;
			$('x_file_name').disabled = true;
		} else {
			$('x_folder_name').disabled = true;
			$('x_file_name').disabled = false;
		}
	}
//-->
</script>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<form width="100%" height="100%" align="center" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("FILE_ID")?"&file_id=".FILE_ID:""); ?>" method="POST" <?= ($file['is_folder']?"":"enctype=\"multipart/form-data\""); ?>>
	<input type="hidden" name="x_old_id" value="<?= ($file['id']?$file['id']:NULL); ?>" />
	<input type="hidden" name="x_old_is_folder" value="<?= ($file['is_folder']?"on":"off"); ?>" />
	<input type="hidden" name="x_old_parent_id" value="<?= ($file['parent_id']?$file['parent_id']:NULL); ?>" />
	<table width="100%">
		<?php if (ACTION == "change"): ?>
		<tr>
			<th colspan="2">Файл: <?= $file['name']; ?>, Владелец: <?= $file['ownername']; ?></th>
		</tr>
		<? endif; ?>
		<?php if (ACTION == "add"): ?>
		<tr>
			<td align="right" valign="top" width="20%">Создать папку:</td>
			<td align="left" valign="top" width="auto"><input type="checkbox" id="x_is_folder" name="x_is_folder" onChange="javascript:checkFolder();" <?= $file['is_folder']?"checked":""; ?> style=" width : 100%; " /></td>
		</tr>
		<? else: ?>
		<tr>
			<td align="right" valign="top" width="20%">Это папка:</td>
			<td align="left" valign="top" width="auto"><input type="checkbox" disabled id="x_is_folder" name="x_is_folder" <?= $file['is_folder']?"checked":""; ?> style=" width : 100%; " /></td>
		</tr>
		<? endif; ?>
		<tr>
			<td align="right" valign="top" width="20%">Наименование:</td>
			<td align="left" valign="top" width="auto"><input type="text" id="x_folder_name" name="x_folder_name" value="<?= $file['name']; ?>" size="35" style=" width : 100%; " /></td>
		</tr>
		<tr>
			<td align="right" valign="top" width="20%">Имя файла:</td>
			<td align="left" valign="top" width="auto"><input type="file" id="x_file_name" name="x_file_name" value="<?= $file['name']; ?>" size="35" style=" width : 100%; " /></td>
		</tr>
		<tr>
			<td align="right" valign="top" width="20%">Описание:</td>
			<td align="left" valign="top" width="auto"><textarea name="x_description" size="5" style=" width : 100%; " /><?= $file['description']; ?></textarea></td>
		</tr>
		<tr>
			<td align="right" valign="top" width="20%">Родительская папка:</td>
			<td align="left" valign="top" width="auto">
				<select name="x_parent_id" style=" width : 100%; " size="15">
					<option value=""/>
					<?php foreach ($folders as $folder): ?>
					<?php if ($folder['id'] <> $file['id']): ?>
					<option value="<?= $folder['id']; ?>"<?= ($folder['id'] == $file['parent_id'])?"selected":""; ?> /><?= str_pad_html("", $folder['level']).trim($folder['name'])." (".trim($folder['description']).")"; ?>
					<?php endif; ?>
					<?php endforeach; ?>
				</select></td>
		</tr>
		<tr>
			<td align="left" colspan="2"><input title="Принять внесенные изменения" type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
		</tr>
	</table>
</form>

<script type="text/javascript">
<!--
	checkFolder();
//-->
</script>
<?php // endif; ?>