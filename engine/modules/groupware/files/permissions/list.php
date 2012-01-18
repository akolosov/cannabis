<?php if ((defined('FILE_ID')) and (isNotNULL(FILE_ID))): ?>
<?php
$file = new File($engine, FILE_ID);

if (defined("ACTION")) {
	switch (ACTION) {
		case "add" :
			if ((defined('X_PERMISSION_ID')) and (isNotNULL(X_PERMISSION_ID)) and (defined('X_ACCOUNT_ID')) and (isNotNULL(X_ACCOUNT_ID))) {
				$permission = $file->createPermission(array('permission_id' => X_PERMISSION_ID, 'account_id' => X_ACCOUNT_ID));
				$permission->save();
				$file->initPermissions();
			}
			break;
		case "change" :
			if ((defined('PERMISSION_ID')) and (isNotNULL(PERMISSION_ID)) and ($file->permissionExists(PERMISSION_ID))) {
				$permission = $file->getPermission(PERMISSION_ID);
				$permission->setProperty('permission_id', (((defined('X_PERMISSION_ID')) and (isNotNULL(X_PERMISSION_ID)))?X_PERMISSION_ID:X_OLD_PERMISSION_ID));
				$permission->setProperty('account_id', (((defined('X_ACCOUNT_ID')) and (isNotNULL(X_ACCOUNT_ID)))?X_ACCOUNT_ID:X_OLD_ACCOUNT_ID));
				$permission->save();
				$file->initPermissions();
			}
			break;
		case "erase" :
			if ((defined('PERMISSION_ID')) and (isNotNULL(PERMISSION_ID))) {
				$file->clearPermission(PERMISSION_ID);
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
	<th colspan="3" align="center">Файл: <?= $file->getProperty('name'); ?>, Владелец: <?= $file->getProperty('ownername'); ?></th>
	<th align="center"><a title="Добавить право доступа" href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&file_id=<?= $file->getProperty('id'); ?>&action=add"><img src="images/create_icon.png" /></a></th>
</tr>
<?php // endif; ?>
<?php foreach ($file->getPermissions() as $permission): ?>
<tr>
	<td title="<?= $permission->getProperty('accountdescr'); ?>"><?= $permission->getProperty('accountname'); ?></td>
	<td title="<?= $permission->getProperty('permissiondescr'); ?>"><?= $permission->getProperty('permissionname'); ?></td>
	<td colspan="2" width="32"><img src="images/edit_icon.png" class=" action " title="Изменить/Редактировать право доступа" onClick="document.location.href = '?module=<?= getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR; ?>edit&action=change&file_id=<?= $permission->getProperty('file_id'); ?>&permission_id=<?= $permission->getProperty('account_id'); ?>';" /><img src="images/delete_icon.png" class=" action " title="Удалить право доступа" onClick="javascript:confirmIt('?module=<?= getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR; ?>list&action=erase&file_id=<?= $permission->getProperty('file_id'); ?>&permission_id=<?= $permission->getProperty('account_id'); ?>', '_top', true);" /></td>
</tr>
<?php endforeach; ?>
<?php // if ($user_permissions[getParentModule()][getParentChildModule()]['can_write']): ?>
<tr>
	<th colspan="3" align="center">&nbsp;</th>
	<th align="center"><a title="Добавить право доступа" href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&file_id=<?= $file->getProperty('id'); ?>&action=add"><img src="images/create_icon.png" /></a></th>
</tr>
<?php // endif; ?>
</table>
<?php endif; ?>