<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<?php
        if (ACTION == "change") {
	    $permission = prepareForView($connection->execute('select * from cs_permission where id = '.PERMISSION_ID)->fetch());
        } else {
            $permission = array();
        }
?>
	<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
        <form width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("permission_ID")?"&permission_id=".permission_ID:""); ?>" method="POST">
	 <input type="hidden" name="x_permission_id" value="<?= ($permission['id']?$permission['id']:'0'); ?>" />
         <table width="100%">
          <tr>
           <td align="right" valign="top">Наименование:</td>
           <td align="left" valign="top"><input type="text" name="x_permission_name" value="<?= $permission['name']; ?>" size="35" style=" width : 100%; " /></td>
	  </tr>
          <tr>
           <td align="right" valign="top">Описание:</td>
           <td align="left" valign="top"><input type="text" name="x_permission_descr" value="<?= $permission['description']; ?>" size="255" style=" width : 100%; " /></td>
	  </tr>
	  <tr>
	   <td>&nbsp;</td>
	   <td align="left"><input title="Принять внесенные изменения" type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	  </tr>
	 </table>
	</form>
<?php endif; ?>