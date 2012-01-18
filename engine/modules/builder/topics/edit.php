<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<?php
if (ACTION == "change") {
	$topic = prepareForView($connection->execute('select * from cs_public_topic where id = '.TOPIC_ID)->fetch());
} else {
	$topic = array();
}
?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<form width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("TOPIC_ID")?"&topic_id=".TOPIC_ID:""); ?>" method="POST">
	<input type="hidden" name="x_topic_id" value="<?= ($topic['id']?$topic['id']:'0'); ?>" />
	<table width="100%">
		<tr>
			<td align="right" valign="top">Наименование:</td>
			<td align="left" valign="top"><input type="text"
				name="x_topic_name" value="<?= $topic['name']; ?>" size="35"
				style=" width : 100%; " /></td>
		</tr>
		<tr>
			<td align="right" valign="top">Описание:</td>
			<td align="left" valign="top"><textarea 
				name="x_topic_descr" style=" width : 100%; " rows="5"><?= $topic['description']; ?></textarea></td>
		</tr>
		<tr>
			<td align="right" valign="top">Активная:</td>
			<td align="left" valign="top"><input type="checkbox"
				name="x_topic_is_active"
				<?= $topic['is_active']?"checked":""; ?> style=" width : 100%; " /></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td align="left"><input title="Принять внесенные изменения"
				type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
		</tr>
	</table>
</form>
<?php endif; ?>