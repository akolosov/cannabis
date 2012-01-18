<?php

require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."functions.php");

if (defined("ACTION")) {
	switch (ACTION) {
		case "save" :
			if (defined('MESSAGE_ID')) {
				$message = new Message($engine, MESSAGE_ID);
			} else {
				$message = Message::create(array('owner' => $engine));
			}
			setMessageParams($message);
			$message->save();
			if (!defined('MESSAGE_ID')) {
				define('MESSAGE_ID', $message->getProperty('id'));
			}
			break;
		case "addfile" :
			if (defined('MESSAGE_ID')) {
				if ((is_array($_FILES['x_file_name'])) and (in_array($_FILES['x_file_name']['type'], $mime_names)) and ($_FILES['x_file_name']['size'] <= MAX_FILE_SIZE) and (is_uploaded_file($_FILES['x_file_name']['tmp_name']))) {
					$message = new Message($engine, MESSAGE_ID);
					$filecontent = base64_encode(file_get_contents($_FILES['x_file_name']['tmp_name']));
					$message->addBlob(MessageBlob::create(array('owner' => $message,
																'message_id' => MESSAGE_ID,
																'name' => $_FILES['x_file_name']['name'],
																'blob' => $filecontent)));
					setMessageParams($message);
					$message->save();
				}
			}
			break;
		case "erasefile" :
			if ((defined('MESSAGE_ID')) and (defined('BLOB_ID'))) {
				$message = new Message($engine, MESSAGE_ID);
				$message->eraseBlob(BLOB_ID);
			}
			break;
		default:
			break;
	}
}
?>
<?php if ((ACTION == 'view') and ((defined('MESSAGE_ID')) and (isNotNULL(MESSAGE_ID)))): ?>
	<?php
		$message = new Message($engine, MESSAGE_ID);
		if ($message->getProperty('[recievers]')->elementExists(USER_CODE)) {
			$message->getReciever(USER_CODE)->setProperty('status_id', Constants::MESSAGE_READED);
			$message->getReciever(USER_CODE)->save();
		}
	?>
	<div class="caption">
	<img class=" action " style=" float: right; " src="images/erase_icon.png" title="Удалить СОВСЕМ сообщение" onClick="javascript:confirmIt('?module=<?= getParentModule()."/".getChildModule(); ?>/list&action=erase&message_id=<?= $message->getProperty('id'); ?>', '_top', true);" />
	<?php if ($message->isSended()): ?>
		<img class=" action " style=" float: right; " src="images/resend_icon.png" title="Отправить сообщение снова" onClick="javascript:confirmIt('?module=<?= getParentModule()."/".getChildModule(); ?>/list&action=resend&message_id=<?= $message->getProperty('id'); ?>', '_top', true);" />
	<?php endif; ?>
	<?php if ($message->isDeleted()): ?>
		<img class=" action " style=" float: right; " src="images/create_icon.png" title="Восстановить сообщение" onClick="javascript:confirmIt('?module=<?= getParentModule()."/".getChildModule(); ?>/list&action=undelete&message_id=<?= $message->getProperty('id'); ?>', '_top', true);" />
	<?php else: ?>
		<img class=" action " style=" float: right; " src="images/delete_icon.png" title="Удалить сообщение" onClick="javascript:confirmIt('?module=<?= getParentModule()."/".getChildModule(); ?>/list&action=delete&message_id=<?= $message->getProperty('id'); ?>', '_top', true);" />
	<?php endif; ?>
	<?php if ($message->isCreated()): ?>
		<img class=" action " style=" float: right; " src="images/edit_icon.png" title="Редактировать сообщение" onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=change&message_id=<?= $message->getProperty('id'); ?>';" />
	<?php endif; ?>
	<img class=" action " style=" float: right; " src="images/list.png" title="Назад к списку сообщений" onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/list';" />
	Просмотр сообщения (Статус: <?= $message->getProperty('statusname'); ?>)</div>
	<table width="100%" align="center" cellspacing="1" cellpadding="1" style=" border : 1px dotted #ccc; border-collapse : collapse; margin-bottom : 1px; ">
		<tr>
			<td valign="top" align="right" class=" bold " width="20%">Отправитель:</td>
			<td valign="top" width="auto" style=" background-color: #fff; "><?= $message->getProperty('authorname'); ?></td>
		</tr>
		<tr>
			<td valign="top" align="right" class=" bold " width="20%">Получатели:</td>
			<td valign="top" width="auto" style=" background-color: #fff; "><?= join('; ', $message->getRecieversProperty('recievername')); ?></td>
		</tr>
		<tr>
			<td valign="top" align="right" class=" bold " width="20%">Тема сообщения:</td>
			<td valign="top" width="auto" style=" background-color: #fff; "><?= $message->getProperty('subject'); ?></td>
		</tr>
		<tr>
			<td valign="top" align="right" class=" bold " width="20%">Текст сообщения:</td>
			<td valign="top" width="auto" style=" background-color: #fff; "><?= str_replace("\n", "<br />", $message->getProperty('message')); ?></td>
		</tr>
	<?php
		$blobs = $message->getBlobs();

		if (isNotNULL($blobs)): ?>
		<tr>
			<td valign="top" align="right" class=" bold " width="20%">Вложенные файлы:</td>
			<td valign="top" width="auto" style=" background-color: #fff; ">
			<?php
				foreach ($blobs as $blob) {
					storeMessageBlobToCache($blob->getProperty('id'), $blob->getBlobFileName($message->getProperty('authorname')));
					print "<a href=\"".$blob->getBlobFileName($message->getProperty('authorname'))."\">".$blob->getProperty('name')."</a><br />";
				}
			?>
			</td>
		</tr>
	<?php endif; ?>
	</table>
<?php else: ?>
	<?php
		if (ACTION == 'add') {
			$message = Message::create(array('owner' => $engine));
		} elseif ((defined('MESSAGE_ID')) and (isNotNULL(MESSAGE_ID))) {
			$message = new Message($engine, MESSAGE_ID);
		}
		$accounts = $connection->execute('select * from accounts_without_groups_list')->fetchAll();
		$contactlists = $connection->execute('select * from contacts_list where ((is_public = true) or (owner_id = '.USER_CODE.') or (id in (select contact_id from cs_contact_permission where account_id = '.USER_CODE.'))) and is_deleted = false')->fetchAll();
		$recievers = $message->getRecieversProperty('reciever_id');
	?>
	<script type="text/javascript">
	<!--
		function makeRecieversList() {
			$('x_recievers').value = '';

			for (i = 0; i < $('x_recievers_list').length; i++) {
				if ($('x_recievers_list').options[i].selected) {
					$('x_recievers').value = $F('x_recievers') + $('x_recievers_list').options[i].text + '; ';
				}
			}
		}
	//-->
	</script>
	<form width="100%" height="100%" align="center" id="message_form" name="message_form" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= (((ACTION == 'addfile') or (ACTION == 'erasefile') or (ACTION == 'save'))?"change":ACTION); ?><?= (defined("MESSAGE_ID")?"&message_id=".MESSAGE_ID:""); ?>" method="POST" enctype="multipart/form-data">
		<table width="100%" align="center" cellspacing="1" cellpadding="1" style=" border : 1px dotted #ccc; border-collapse : collapse; margin-bottom : 1px; ">
			<tr>
				<td valign="top" align="right" class=" bold " width="20%">Получатели:
				</td>
				<td valign="top" align="center" width="auto" style=" background-color: #fff; "><img  src="images/users.png" class="action" style=" float: right; " onClick="hideIt('recievers_list');" /><input type="text" id="x_recievers" name="x_recievers" style=" width : 97%; " value="<?= join('; ', $message->getRecieversProperty('recievername')); ?>" readonly />
					<div id="recievers_list" class="popup_form" style=" width: 40% !important; right: 1% !important; " onDblClick="makeRecieversList(); hideIt('recievers_list');"><img src="images/close.png" style=" float: right; " onClick="hideIt('recievers_list');" title="Закрыть форму выбора получателей" />
						<select id="x_recievers_list" name="x_recievers_list[]" style=" width : 100%; " size="35" multiple="multiple" onChange="makeRecieversList();" onDblClick="hideIt('recievers_list');">
							<?php foreach ($contactlists as $contactlist): ?>
							<option value="L<?= $contactlist['id']; ?>" /><?= trim($contactlist['name']); ?> [Список пользователей]
							<?php endforeach; ?>
							<?php foreach ($accounts as $account): ?>
							<option value="<?= $account['id']; ?>"
							<?= (in_array($account['id'], $recievers))?"selected":""; ?> /><?= trim($account['name']); ?>
							<?php endforeach; ?>
						</select>
					</div>
				</td>
			</tr>
			<tr>
				<td valign="top" align="right" class=" bold " width="20%">Тема сообщения:</td>
				<td valign="top" align="center" width="auto" style=" background-color: #fff; "><input type="text" id="x_subject" name="x_subject" style=" width : 100%; " value="<?= $message->getProperty('subject'); ?>" /></td>
			</tr>
			<tr>
				<td valign="top" align="right" class=" bold " width="20%">Текст сообщения:</td>
				<td valign="top" align="center" width="auto" style=" background-color: #fff; "><textarea id="x_message" name="x_message" style=" width : 100%; " rows="20"><?= $message->getProperty('message'); ?></textarea></td>
			</tr>
		<?php $blobs = $message->getBlobs(); ?>
			<tr>
				<td valign="top" align="right" class=" bold " width="20%">Вложенные файлы:<br />
					<input class="button" title="Добавить файл к сообщению" type="button" name="x_add_file" value=" Добавить файл " style=" width : 100%; " onClick="hideIt('add_file');" />
					<div id="add_file" class="popup_form"><img src="images/close.png" style=" float: right; " onClick="hideIt('add_file');" title="Закрыть форму добавления файла" />
					<?php if ($message->getProperty('id') > 0): ?>
						<table width="100%" align="center">
							<tr>
								<td valign="top" align="right" class=" bold " width="20%">Имя файла:</td>
								<td valign="top" align="center" width="auto" style=" background-color: #fff; "><input size="80" type="file" id="x_file_name" name="x_file_name" style=" width : 100%; " /></td>
							</tr>
							<tr>
								<td align="center" colspan="2"><input class="button" title="Добавить файл к сообщению" type="submit" name="submit" value=" Добавить файл" style=" width : 100%; " onClick="document.forms.message_form.action = '/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=addfile<?= (defined("MESSAGE_ID")?"&message_id=".MESSAGE_ID:""); ?>'; document.forms.message_form.submit();" /></td>
							</tr>
						</table>
					<?php else: ?>
						<h1 align="center">Сначала сохраните сообщение, нажав на кнопку<br />[ Сохранить, но не отправлять ]!</h1>
					<?php endif; ?>
					</div>
				</td>
				<td valign="top" align="center" width="auto" style=" background-color: #fff; ">
					<table width="100%">
				<?php
					foreach ($blobs as $blob) {
						storeMessageBlobToCache($blob->getProperty('id'), $blob->getBlobFileName($message->getProperty('authorname')));
						print "<tr><td width='auto'>";
						print "<a href=\"".$blob->getBlobFileName($message->getProperty('authorname'))."\">".$blob->getProperty('name')."</a><br />";
						print "</td><td width='16'>";
						print "<img class=\" action \" src=\"images/erase.png\" onClick=\"javascript:confirmIt('/?module=".getParentModule().'/'.getChildModule().'/edit&action=erasefile'.(defined("MESSAGE_ID")?"&message_id=".MESSAGE_ID:"").'&blob_id='.$blob->getProperty('id')."', '_top', true);\" />";
						print "</td></tr>";
					}
				?>
					</table>
				</td>
			</tr>
			<tr>
				<td align="center" colspan="2"><input class="button" title="Сохранить внесенные изменения и НЕ отправлять сообщение получателям" type="submit" name="submit" value=" Сохранить, но не отправлять" style=" width : 100%; " onClick="document.forms.message_form.action = '/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=save<?= (defined("MESSAGE_ID")?"&message_id=".MESSAGE_ID:""); ?>'; document.forms.message_form.submit();" /></td>
			</tr>
			<tr>
				<td align="center" colspan="2"><input class="button" title="Сохранить внесенные изменения и отправить сообщение получателям" type="submit" name="submit" value=" Сохранить и отправить" style=" width : 100%; " onClick="document.forms.message_form.action = '/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= (defined("MESSAGE_ID")?"change":"add"); ?>&subaction=send<?= (defined("MESSAGE_ID")?"&message_id=".MESSAGE_ID:""); ?>'; document.forms.message_form.submit();" /></td>
			</tr>
		</table>
	</form>
<?php endif; ?>
