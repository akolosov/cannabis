<?php if ((defined('CONTACTLIST_ID')) and (isNotNULL(CONTACTLIST_ID))): ?>
<?php
$contactlist = new ContactList($engine, CONTACTLIST_ID);

if (defined("ACTION")) {
	switch (ACTION) {
		case "add" :
			if ((defined('X_ACCOUNT_ID')) and (isNotNULL(X_ACCOUNT_ID))) {
				$account = $contactlist->createaccount(array('account_id' => X_ACCOUNT_ID));
				$account->save();
				$contactlist->initContacts();
			}
			break;
		case "change" :
			if ((defined('ACCOUNT_ID')) and (isNotNULL(ACCOUNT_ID)) and ($contactlist->accountExists(ACCOUNT_ID))) {
				$account = $contactlist->getAccount(ACCOUNT_ID);
				$account->setProperty('account_id', (((defined('X_ACCOUNT_ID')) and (isNotNULL(X_ACCOUNT_ID)))?X_ACCOUNT_ID:X_OLD_ACCOUNT_ID));
				$account->save();
				$contactlist->initContacts();
			}
			break;
		case "delete" :
			if ((defined('ACCOUNT_ID')) and (isNotNULL(ACCOUNT_ID))) {
				if ($contactlist->isDeleted(ACCOUNT_ID)) {
					$contactlist->undeleteAccount(ACCOUNT_ID);
				} else {
					$contactlist->deleteAccount(ACCOUNT_ID);
				}
				$contactlist->initContacts();
			}
			break;
		case "erase" :
			if ((defined('ACCOUNT_ID')) and (isNotNULL(ACCOUNT_ID))) {
				$contactlist->eraseAccount(ACCOUNT_ID);
				$contactlist->initContacts();
			}
			break;
		default:
			break;
	}
}
?>
<table width="100%" align="center">
<?php // if ($user_permissions[getParentModule()][getParentChildModule()]['can_write']): ?>
<tr>
	<th colspan="2" align="center">Контакт-лист: <?= $contactlist->getProperty('name'); ?>, Владелец: <?= $contactlist->getProperty('ownername'); ?></th>
	<th align="center"><a title="Добавить пользователя" href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&contactlist_id=<?= $contactlist->getProperty('id'); ?>&action=add"><img src="images/create_icon.png" /></a></th>
</tr>
<?php // endif; ?>
<?php foreach ($contactlist->getAccounts() as $account): ?>
<tr>
	<td title="<?= $account->getProperty('accountdescr'); ?>" <?= (($account->isDeleted())?"class =\" strike \"":""); ?>><?= $account->getProperty('accountname'); ?></td>
	<td colspan="2" align="center" width="50"><img src="images/edit_icon.png" class=" action " title="Изменить/Редактировать пользователя  в списке" onClick="document.location.href = '?module=<?= getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR; ?>edit&action=change&contactlist_id=<?= $account->getProperty('contact_id'); ?>&account_id=<?= $account->getProperty('account_id'); ?>';" /><img src="images/delete_icon.png" class=" action " title="Удалить пользователя из списка" onClick="javascript:confirmIt('?module=<?= getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR; ?>list&action=delete&contactlist_id=<?= $account->getProperty('contact_id'); ?>&account_id=<?= $account->getProperty('account_id'); ?>', '_top', true);" /><img src="images/erase_icon.png" class=" action " title="Удалить СОВСЕМ пользователя из списка" onClick="javascript:confirmIt('?module=<?= getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR; ?>list&action=erase&contactlist_id=<?= $account->getProperty('contact_id'); ?>&account_id=<?= $account->getProperty('account_id'); ?>', '_top', true);" /></td>
</tr>
<?php endforeach; ?>
<?php // if ($user_permissions[getParentModule()][getParentChildModule()]['can_write']): ?>
<tr>
	<th colspan="2" align="center">&nbsp;</th>
	<th align="center"><a title="Добавить пользователя" href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&contactlist_id=<?= $contactlist->getProperty('id'); ?>&action=add"><img src="images/create_icon.png" /></a></th>
</tr>
<?php // endif; ?>
</table>
<?php endif; ?>