
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
	<tr>
		<th colspan="3">&nbsp;</th>
		<th><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>#form"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<tr>
		<th>Наименование транспорта</th>
		<th>Событие</th>
		<th colspan="2">Действия</th>
	</tr>
	<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				if (defined('X_PROCESS_ID')) {
					$transport = $connection->getTable('CsProcessTransport')->create();
					$transport['process_id'] = X_PROCESS_ID;
					$transport['transport_id'] = X_TRANSPORT_TRANSPORT_ID;
					$transport['event_id'] = X_TRANSPORT_EVENT_ID;
					$transport['subject_template'] = prepareForSave(X_TRANSPORT_SUBJECT);
					$transport['recipients_template'] = prepareForSave(X_TRANSPORT_RECIPIENTS);
					$transport['text_template'] = prepareForSave(X_TRANSPORT_TEXT);
					$transport->save();
				}
				break;
			case "change" :
				if (defined('X_TRANSPORT_ID')) {
					$transport = $connection->getTable('CsProcessTransport')->find(X_TRANSPORT_ID);
					$transport['transport_id'] = (defined('X_TRANSPORT_TRANSPORT_ID')?X_TRANSPORT_TRANSPORT_ID:(X_OLD_TRANSPORT_ID == ""?NULL:X_OLD_TRANSPORT_ID));
					$transport['event_id'] = (defined('X_TRANSPORT_EVENT_ID')?X_TRANSPORT_EVENT_ID:(X_OLD_EVENT_ID == ""?NULL:X_OLD_EVENT_ID));
					$transport['subject_template'] = prepareForSave(X_TRANSPORT_SUBJECT);
					$transport['recipients_template'] = prepareForSave(X_TRANSPORT_RECIPIENTS);
					$transport['text_template'] = prepareForSave(X_TRANSPORT_TEXT);
					$transport->save();
				}
				break;
			case "delete" :
				$transport = $connection->getTable('CsProcessTransport')->find(TRANSPORT_ID);
				$transport->delete();
				break;
			default:
				break;
		}
	}

	$transports = $connection->execute('select * from process_transports_list where process_id = '.PROCESS_ID.' order by id');

	$transition = $connection->execute('select name from cs_process where id = '.PROCESS_ID)->fetch();
	print "<caption class=\"caption\"><b>Наименование процесса: </b>".$transition['name']."</caption>\n";
	foreach ($transports as $transport) {
		print "<tr>";
		print "<td title=\"".$transport['description']."\">".$transport['name']."</td>";
		print "<td>".$transport['eventname']."</td>";
		print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&transport_id=".$transport['id']."&process_id=".$transport['process_id'].(defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"")."#form\"><img src=\"images/edit_icon.png\" /></a></td>";
		print "<td width=\"3%\" align=\"center\"><a href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&transport_id=".$transport['id']."&process_id=".$transport['process_id'].(defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"")."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		print "</tr>\n";
	}
	?>
	<tr>
		<th colspan="3">&nbsp;</th>
		<th><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>#form"><img
			src="images/create_icon.png" /></a></th>
	</tr>
</table>
