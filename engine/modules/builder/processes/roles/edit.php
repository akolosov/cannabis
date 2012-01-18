	<?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR.getParentModule().DIRECTORY_SEPARATOR.getChildModule()).DIRECTORY_SEPARATOR."list.php"; ?>
	<br />
<?php
	$roles = $connection->execute('select * from cs_role');
	$accounts = $connection->execute('select * from accounts_tree');
        if (ACTION == "change") {
	    $role = prepareForView($connection->execute('select cs_process_role.*, cs_process.name as processname from cs_process_role, cs_process where cs_process_role.id = '.ROLE_ID.' and cs_process_role.process_id = '.PROCESS_ID.' and cs_process_role.process_id = cs_process.id')->fetch());
        } else {
            $role = prepareForView($connection->execute('select cs_process.name as processname from cs_process where cs_process.id = '.PROCESS_ID)->fetch());
        }
?>
	<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
	<a name="form"></a>
        <form width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("PROPERTY_ID")?'&role_id='.PROPERTY_ID:(defined("X_PROPERTY_ID")?'&role_id='.X_PROPERTY_ID:"")); ?><?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>" method="POST">
	 <input type="hidden" name="x_role_id" value="<?= ($role['id']?$role['id']:NULL); ?>" />
	 <input type="hidden" name="x_process_id" value="<?= ($role['process_id']?$role['process_id']:(defined('PROCESS_ID')?PROCESS_ID:NULL)); ?>" />
	 <input type="hidden" name="x_old_role_id" value="<?= ($role['role_id']?$role['role_id']:NULL); ?>" />
	 <input type="hidden" name="x_account_id" value="<?= ($role['account_id']?$role['account_id']:NULL); ?>" />
         <table width="100%">
           <td align="right" valign="top" width="20%">Роль в процессе:</td>
           <td align="left" valign="top">
             <select name="x_role_role_id" style=" width : 100%; " size="7">
              <?php foreach ($roles as $arole): ?>
              <option value="<?= $arole['id']; ?>" <?= ($arole['id'] == $role['role_id'])?"selected":""; ?> /><?= trim($arole['name'])." (".trim($arole['description']).")"; ?>
              <?php endforeach; ?>
             </select>
           </td>
          </tr>
          <tr>
           <td align="right" valign="top" width="20%">Пользователь или Группа:</td>
           <td align="left" valign="top">
             <select name="x_role_account_id" style=" width : 100%; " size="7">
	      <option value="" />
              <?php foreach ($accounts as $account): ?>
              <option value="<?= $account['id']; ?>" <?= ($account['id'] == $role['account_id'])?"selected":""; ?> /><?= str_pad_html("", $account['level']).trim($account['name'])." (".trim($account['description']).")"; ?>
              <?php endforeach; ?>
	   </select>
	   </td>
	  </tr>
	  <tr>
	   <td>&nbsp;</td>
	   <td align="left"><input title="Принять внесенные изменения" type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	  </tr>
	 </table>
	</form>
