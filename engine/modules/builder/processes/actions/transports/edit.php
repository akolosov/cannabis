<?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR.getParentModule().DIRECTORY_SEPARATOR.getChildModule()).DIRECTORY_SEPARATOR."list.php"; ?>
<br />
<?php
$transports = $connection->execute('select * from cs_transport')->fetchAll();
$events = $connection->execute('select * from cs_event where name like \'%Action%\'')->fetchAll();
if (ACTION == "change") {
	$transport = prepareForView($connection->execute('select * from process_action_transports_list where id = '.TRANSPORT_ID)->fetch());
} else {
	$transport = array();
}
?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<a name="form"></a>
<form width="100%" height="100%" align="right"
	action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("TRANSPORT_ID")?'&transport_id='.TRANSPORT_ID:(defined("X_TRANSPORT_ID")?'&transport_id='.X_TRANSPORT_ID:"")); ?><?= (defined("ACTION_ID")?'&action_id='.ACTION_ID:(defined("X_ACTION_ID")?'&action_id='.X_ACTION_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"")."&process_id=".PROCESS_ID; ?>"
	method="POST"><input type="hidden" name="x_transport_id"
	value="<?= ($transport['id']?$transport['id']:NULL); ?>" /> <input
	type="hidden" name="x_action_id"
	value="<?= ($transport['action_id']?$transport['action_id']:(defined('ACTION_ID')?ACTION_ID:NULL)); ?>" />
<input type="hidden" name="x_old_transport_id"
	value="<?= ($transport['transport_id']?$transport['transport_id']:NULL); ?>" />
<input type="hidden" name="x_old_event_id"
	value="<?= ($transport['event_id']?$transport['event_id']:NULL); ?>" />
<table width="100%">
	<td align="right" valign="top" width="20%">Транспорт:</td>
	<td align="left" valign="top"><select name="x_transport_transport_id"
		style=" width : 100%; ">
		<?php foreach ($transports as $atransport): ?>
		<option value="<?= $atransport['id']; ?>"
		<?= ($atransport['id'] == $transport['transport_id'])?"selected":""; ?> /><?= trim($atransport['name'])." (".trim($atransport['description']).")"; ?>
		<?php endforeach; ?>
	</select></td>
	</tr>
	<tr>
	<td align="right" valign="top" width="20%">Событие:</td>
	<td align="left" valign="top"><select name="x_transport_event_id"
		style=" width : 100%; ">
		<?php foreach ($events as $event): ?>
		<option value="<?= $event['id']; ?>"
		<?= ($event['id'] == $transport['event_id'])?"selected":""; ?> /><?= trim($event['name'])." (".trim($event['description']).")"; ?>
		<?php endforeach; ?>
	</select></td>
	</tr>
	<tr>
		<td align="right" valign="top">Шаблон получателей:</td>
		<td align="left" valign="top"><input type="text" name="x_transport_recipients"
			value="<?= $transport['recipients_template']; ?>" size="255" style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Шаблон темы:</td>
		<td align="left" valign="top"><input type="text" name="x_transport_subject"
			value="<?= $transport['subject_template']; ?>" size="255" style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Шаблон текста:</td>
		<td align="left" valign="top"><textarea name="x_transport_text"
			rows="20" style=" width : 100%; "><?= $transport['text_template']; ?></textarea></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align="left"><input title="Принять внесенные изменения"
			type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	</tr>
</table>
</form>
