<?php // if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php

require_once(MODULES_PATH.DIRECTORY_SEPARATOR."groupware".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."functions.php");

$manager = new MessageManager($engine, USER_CODE);

if (defined("ACTION")) {
	switch (ACTION) {
		case "add" :
			$message = $manager->createMessage(array('owner' => $manager));
			setMessageParams($message);
			if (SUBACTION == 'send') {
				$message->send();
			} else {
				$message->save();
			}
			break;
		case "change" :
			if ((defined('MESSAGE_ID')) and ($manager->messageExists(MESSAGE_ID))) {
				$message = $manager->getMessage(MESSAGE_ID);
				setMessageParams($message);
				if (SUBACTION == 'send') {
					$message->send();
				} else {
					$message->save();
				} 
			}
			break;
		case "delete" :
			if ((defined('MESSAGE_ID')) and ($manager->messageExists(MESSAGE_ID))) {
				$manager->deleteMessage(MESSAGE_ID);
			}
			break;
		case "undelete" :
			if ((defined('MESSAGE_ID')) and ($manager->messageExists(MESSAGE_ID))) {
				$manager->undeleteMessage(MESSAGE_ID);
			}
			break;
		case "erase" :
			if ((defined('MESSAGE_ID')) and ($manager->messageExists(MESSAGE_ID))) {
				$manager->eraseMessage(MESSAGE_ID);
			}
			break;
		case "erasedeleted" :
			foreach ($manager->getDeleted() as $message) {
				$message->erase();
			}
			break;
		case "uneraseerased" :
			foreach ($manager->getErased() as $message) {
				$message->unerase();
			}
			break;
		case "unerase" :
			if ((defined('MESSAGE_ID')) and ($manager->messageExists(MESSAGE_ID))) {
				$manager->uneraseMessage(MESSAGE_ID);
			}
			break;
		case "send" :
			if ((defined('MESSAGE_ID')) and ($manager->messageExists(MESSAGE_ID))) {
				$manager->sendMessage(MESSAGE_ID);
				$manager->initOwnedMessages();
			}
			break;
		case "resend" :
			if ((defined('MESSAGE_ID')) and ($manager->messageExists(MESSAGE_ID))) {
				$manager->resendMessage(MESSAGE_ID);
				$manager->initOwnedMessages();
			}
			break;
		default:
			break;
	}
}
?>
<div class="title">
<img src="images/trashcan.png" class=" action " style=" float: right; " onClick="javascript:confirmIt('?module=<?= getParentModule()."/".getChildModule(); ?>/list&action=erasedeleted', '_top', true);" title="Очистить корзину от удалённых сообщений" />
<img src="images/transports.png" class=" action " style=" float: right; " onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=add';" title="Создать новое сообщение" />
Менеджер сообщений пользователя</div><br />
<?php
$messages = $manager->getRecievedAndReaded();

rsort($messages);
reset($messages);

if (isNotNULL($messages)): ?>
<div class="caption action" onClick="javascript:hideIt('recieved');">Входящие сообщения пользователя</div>
<div id="recieved" style=" display : block; visibility : visible; ">
	<table width="100%" align="center" cellspacing="1" cellpadding="1" style=" border : 1px dotted #ccc; border-collapse : collapse; margin-bottom : 1px; ">
		<tr>
			<th>&nbsp;</th>
			<th>Тема сообщения</th>
			<th>Отправитель</th>
			<th>Дата создания</th>
			<th>Дата получения</th>
			<th width="50">Действия</th>
		</tr>
	<?php foreach ($messages as $message): ?>
		<tr class="selectable">
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&content_only=true&action=view&message_id=<?= $message->getProperty('id'); ?>';" align="center"><img src="images/<?= ($message->isReaded()?"green_dot.gif":"red_dot.gif"); ?>" /></td>
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" class="<?= ($message->isReaded()?"":"bold"); ?>" title="<?= str_replace("\n", "<br />", $message->getProperty('message')); ?>"><?= $message->getProperty('subject'); ?></td>
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" class="<?= ($message->isReaded()?"":"bold"); ?>" title="<?= $message->getProperty('authordescr'); ?>"><?= $message->getProperty('authorname'); ?></td>
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" class="<?= ($message->isReaded()?"":"bold"); ?>" align="center"><?= $message->getProperty('created_at'); ?></td>
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" class="<?= ($message->isReaded()?"":"bold"); ?>" align="center"><?= $message->getReciever(USER_CODE)->getProperty('recieved_at'); ?></td>
			<td width="50" class="nonselectable" align="center">
				<img class=" action " src="images/default_icon.png" title="Прочитать сообщение" onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" />
				<img class=" action " src="images/delete_icon.png" title="Удалить сообщение" onClick="javascript:confirmIt('?module=<?= getParentModule()."/".getChildModule(); ?>/list&action=delete&message_id=<?= $message->getProperty('id'); ?>', '_top', true);" />
				<img class=" action " src="images/erase_icon.png" title="Удалить СОВСЕМ сообщение" onClick="javascript:confirmIt('?module=<?= getParentModule()."/".getChildModule(); ?>/list&action=erase&message_id=<?= $message->getProperty('id'); ?>', '_top', true);" />
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
</div><br />
<?php endif; ?>
<?php
$messages = $manager->getCreated();

rsort($messages); reset($messages);

if (isNotNULL($messages)): ?>
<div class="caption action" onClick="javascript:hideIt('created');">Исходящие сообщения пользователя</div>
<div id="created" style=" display : block; visibility : visible; ">
	<table width="100%" align="center" cellspacing="1" cellpadding="1" style=" border : 1px dotted #ccc; border-collapse : collapse; margin-bottom : 1px; ">
		<tr>
			<th>Тема сообщения</th>
			<th>Дата создания</th>
			<th width="80">Действия</th>
		</tr>
	<?php foreach ($messages as $message): ?>
		<tr class="selectable">
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" title="<?= str_replace("\n", "<br />", $message->getProperty('message')); ?>"><?= $message->getProperty('subject'); ?></td>
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" align="center"><?= $message->getProperty('created_at'); ?></td>
			<td width="100" class="nonselectable" align="center">
				<img class=" action " src="images/default_icon.png" title="Прочитать сообщение" onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" />
				<img class=" action " src="images/edit_icon.png" title="Редактировать сообщение" onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=change&message_id=<?= $message->getProperty('id'); ?>';" />
				<img class=" action " src="images/send_icon.png" title="Отправить сообщение" onClick="javascript:confirmIt('?module=<?= getParentModule()."/".getChildModule(); ?>/list&action=send&message_id=<?= $message->getProperty('id'); ?>', '_top', true);" />
				<img class=" action " src="images/delete_icon.png" title="Удалить сообщение" onClick="javascript:confirmIt('?module=<?= getParentModule()."/".getChildModule(); ?>/list&action=delete&message_id=<?= $message->getProperty('id'); ?>', '_top', true);" />
				<img class=" action " src="images/erase_icon.png" title="Удалить СОВСЕМ сообщение" onClick="javascript:confirmIt('?module=<?= getParentModule()."/".getChildModule(); ?>/list&action=erase&message_id=<?= $message->getProperty('id'); ?>', '_top', true);" />
			</td>
		</tr>

	<?php endforeach; ?>
	</table>
</div><br />
<?php endif; ?>
<?php
$messages = $manager->getSended();

rsort($messages); reset($messages);

if (isNotNULL($messages)): ?>
<div class="caption action" onClick="javascript:hideIt('sended');">Отправленные сообщения пользователя</div>
<div id="sended" style=" display : block; visibility : visible; ">
	<table width="100%" align="center" cellspacing="1" cellpadding="1" style=" border : 1px dotted #ccc; border-collapse : collapse; margin-bottom : 1px; ">
		<tr>
			<th>Тема сообщения</th>
			<th>Дата создания</th>
			<th>Дата отправки</th>
			<th width="80">Действия</th>
		</tr>
	<?php foreach ($messages as $message): ?>
		<tr class="selectable">
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&content_only=true&action=view&message_id=<?= $message->getProperty('id'); ?>';" title="<?= str_replace("\n", "<br />", $message->getProperty('message')); ?>"><?= $message->getProperty('subject'); ?></td>
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" align="center"><?= $message->getProperty('created_at'); ?></td>
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" align="center"><?= $message->getProperty('sended_at'); ?></td>
			<td width="80" class="nonselectable" align="center">
				<img class=" action " src="images/default_icon.png" title="Прочитать сообщение" onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" />
				<img class=" action " src="images/resend_icon.png" title="Отправить сообщение снова" onClick="javascript:confirmIt('?module=<?= getParentModule()."/".getChildModule(); ?>/list&action=resend&message_id=<?= $message->getProperty('id'); ?>', '_top', true);" />
				<img class=" action " src="images/delete_icon.png" title="Удалить сообщение" onClick="javascript:confirmIt('?module=<?= getParentModule()."/".getChildModule(); ?>/list&action=delete&message_id=<?= $message->getProperty('id'); ?>', '_top', true);" />
				<img class=" action " src="images/erase_icon.png" title="Удалить СОВСЕМ сообщение" onClick="javascript:confirmIt('?module=<?= getParentModule()."/".getChildModule(); ?>/list&action=erase&message_id=<?= $message->getProperty('id'); ?>', '_top', true);" />
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
</div><br />
<?php endif; ?>
<?php
$messages = $manager->getDeleted();

rsort($messages); reset($messages);

if (isNotNULL($messages)): ?>
<div class="caption action" onClick="javascript:hideIt('deleted');">Удалённые сообщения пользователя</div>
<div id="deleted" style=" display : block; visibility : visible; ">
	<table width="100%" align="center" cellspacing="1" cellpadding="1" style=" border : 1px dotted #ccc; border-collapse : collapse; margin-bottom : 1px; ">
		<tr>
			<th>&nbsp;</th>
			<th>Тема сообщения</th>
			<th>Отправитель</th>
			<th>Дата создания</th>
			<th>Дата отправки/получения</th>
			<th width="50">Действия</th>
		</tr>
	<?php foreach ($messages as $message): ?>
		<tr class="selectable">
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" align="center" title="<?= ($message->getProperty('is_recieved')?"Принятое":"Отправленное"); ?> сообщение"><img src="images/<?= ($message->getProperty('is_recieved')?"inboxes.png":"outboxes.png"); ?>" /></td>
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" title="<?= str_replace("\n", "<br />", $message->getProperty('message')); ?>"><?= $message->getProperty('subject'); ?></td>
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" title="<?= $message->getProperty('authordescr'); ?>"><?= $message->getProperty('authorname'); ?></td>
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" align="center"><?= $message->getProperty('created_at'); ?></td>
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" align="center"><?= ($message->getProperty('is_recieved')?$message->getReciever(USER_CODE)->getProperty('recieved_at'):$message->getProperty('sended_at')); ?></td>
			<td width="50" class="nonselectable" align="center">
				<img class=" action " src="images/default_icon.png" title="Прочитать сообщение" onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" />
				<img class=" action " src="images/create_icon.png" title="Восстановить сообщение" onClick="javascript:confirmIt('?module=<?= getParentModule()."/".getChildModule(); ?>/list&action=undelete&message_id=<?= $message->getProperty('id'); ?>', '_top', true);" />
				<img class=" action " src="images/erase_icon.png" title="Удалить СОВСЕМ сообщение" onClick="javascript:confirmIt('?module=<?= getParentModule()."/".getChildModule(); ?>/list&action=erase&message_id=<?= $message->getProperty('id'); ?>', '_top', true);" />
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
</div>
<?php endif; ?>
<?php $messages = $manager->getErased();

rsort($messages); reset($messages);

if (($user_permissions[getParentModule()][getChildModule()]['can_admin']) and (isNotNULL($messages))): ?>
<div class="caption action" onClick="javascript:hideIt('deleted');">Совсем удалённые сообщения пользователя</div>
<div id="deleted" style=" display : block; visibility : visible; ">
	<table width="100%" align="center" cellspacing="1" cellpadding="1" style=" border : 1px dotted #ccc; border-collapse : collapse; margin-bottom : 1px; ">
		<tr>
			<th>&nbsp;</th>
			<th>Тема сообщения</th>
			<th>Отправитель</th>
			<th>Дата создания</th>
			<th>Дата отправки/получения</th>
			<th width="40">Действия</th>
		</tr>
	<?php foreach ($messages as $message): ?>
		<tr class="selectable">
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" align="center" title="<?= ($message->getProperty('is_recieved')?"Принятое":"Отправленное"); ?> сообщение"><img src="images/<?= ($message->getProperty('is_recieved')?"inboxes.png":"outboxes.png"); ?>" /></td>
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" title="<?= str_replace("\n", "<br />", $message->getProperty('message')); ?>"><?= $message->getProperty('subject'); ?></td>
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" title="<?= $message->getProperty('authordescr'); ?>"><?= $message->getProperty('authorname'); ?></td>
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" align="center"><?= $message->getProperty('created_at'); ?></td>
			<td onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" align="center"><?= ($message->getProperty('is_recieved')?$message->getReciever(USER_CODE)->getProperty('recieved_at'):$message->getProperty('sended_at')); ?></td>
			<td width="40" class="nonselectable" align="center">
				<img class=" action " src="images/default_icon.png" title="Прочитать сообщение" onClick="document.location.href = '?module=<?= getParentModule()."/".getChildModule(); ?>/edit&action=view&message_id=<?= $message->getProperty('id'); ?>';" />
				<img class=" action " src="images/create_icon.png" title="Восстановить сообщение" onClick="javascript:confirmIt('?module=<?= getParentModule()."/".getChildModule(); ?>/list&action=unerase&message_id=<?= $message->getProperty('id'); ?>', '_top', true);" />
			</td>
		</tr>
	<?php endforeach; ?>
	</table>
</div>
<?php endif; ?>
<?php // endif; ?>