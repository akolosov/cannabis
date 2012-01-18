	<?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR.getParentModule().DIRECTORY_SEPARATOR.getChildModule()).DIRECTORY_SEPARATOR."list.php"; ?>
	<br />
<?php
	$roles = $connection->execute('select * from cs_role');
	$accounts = $connection->execute('select * from divisions_tree');
        if (ACTION == "change") {
	    $role = prepareForView($connection->execute('select cs_project_role.*, cs_project.name as projectname from cs_project_role, cs_project where cs_project_role.id = '.ROLE_ID.' and cs_project_role.project_id = '.PROJECT_ID.' and cs_project_role.project_id = cs_project.id')->fetch());
        } else {
            $role = prepareForView($connection->execute('select cs_project.name as projectname from cs_project where cs_project.id = '.PROJECT_ID)->fetch());
        }
?>
	<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
        <form width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("PROPERTY_ID")?'&role_id='.PROPERTY_ID:(defined("X_PROPERTY_ID")?'&role_id='.X_PROPERTY_ID:"")); ?><?= (defined("PROJECT_ID")?'&project_id='.PROJECT_ID:(defined("X_PROJECT_ID")?'&project_id='.X_PROJECT_ID:"")); ?>" method="POST">
	 <input type="hidden" name="x_role_id" value="<?= ($role['id']?$role['id']:NULL); ?>" />
	 <input type="hidden" name="x_project_id" value="<?= ($role['project_id']?$role['project_id']:(defined('PROJECT_ID')?PROJECT_ID:NULL)); ?>" />
	 <input type="hidden" name="x_old_role_id" value="<?= ($role['role_id']?$role['role_id']:NULL); ?>" />
	 <input type="hidden" name="x_account_id" value="<?= ($role['account_id']?$role['account_id']:NULL); ?>" />
         <table width="100%">
          <tr>
           <td align="right" valign="top" width="20%">Роль в проекте:</td>
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
