<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<?php
$parentprocesses = $connection->execute('select * from processes_tree');
if (ACTION == "change") {
	$process = prepareForView($connection->execute('select * from cs_process where id = '.PROCESS_ID)->fetch());
} else {
	$process = array();
}
?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<form width="100%" height="100%" align="right"
	action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("process_ID")?"&process_id=".process_ID:""); ?>"
	method="POST"><input type="hidden" name="x_process_id"
	value="<?= ($process['id']?$process['id']:NULL); ?>" /> <input
	type="hidden" name="x_parent_id"
	value="<?= ($process['parent_id']?$process['parent_id']:NULL); ?>" />
<table width="100%">
	<tr>
		<td align="right" valign="top">Наименование:</td>
		<td align="left" valign="top"><input type="text" name="x_process_name"
			value="<?= $process['name']; ?>" size="35" style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Описание:</td>
		<td align="left" valign="top"><input type="text"
			name="x_process_descr" value="<?= $process['description']; ?>"
			size="35" style=" width : 100%; " /></td>
	</tr>
	<!--
          <tr>
           <td align="right" valign="top">Родитель:</td>
           <td align="left" valign="top">
	   <select name="x_process_parent_id" style=" width : 100%; " size="7">
	    <option value="" />
              <?php foreach ($parentprocesses as $parentprocess): ?>
              <option value="<?= $parentprocess['id']; ?>" <?= ($parentprocess['id'] == $process['parent_id'])?"selected":""; ?> /><?= str_pad_html("", $parentprocess['level']).trim($parentprocess['name'])." (".trim($parentprocess['description']).")"; ?>
              <?php endforeach; ?>
	   </select>
	   </td>
	  </tr>
-->
	<tr>
		<td align="right" valign="top">Активно:</td>
		<td align="left" valign="top"><input type="checkbox"
			name="x_process_active" <?= $process['is_active']?"checked":""; ?>
			style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Самостоятельный:</td>
		<td align="left" valign="top"><input type="checkbox"
			name="x_process_standalone" <?= $process['is_standalone']?"checked":""; ?>
			style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Общий:</td>
		<td align="left" valign="top"><input type="checkbox"
			name="x_process_public" <?= $process['is_public']?"checked":""; ?>
			style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Системный:</td>
		<td align="left" valign="top"><input type="checkbox"
			name="x_process_system" <?= $process['is_system']?"checked":""; ?>
			style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Скрытый:</td>
		<td align="left" valign="top"><input type="checkbox"
			name="x_process_hidden" <?= $process['is_hidden']?"checked":""; ?>
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