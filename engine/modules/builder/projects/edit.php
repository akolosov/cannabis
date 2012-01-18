<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<?php
        if (ACTION == "change") {
            $project = prepareForView($connection->execute('select * from cs_project where id = '.PROJECT_ID)->fetch());
        } else {
            $project = array();
        }
?>
	<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
        <form width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("project_ID")?"&project_id=".project_ID:""); ?>" method="POST">
	 <input type="hidden" name="x_project_id" value="<?= ($project['id']?$project['id']:NULL); ?>" />
         <table width="100%">
          <tr>
           <td align="right" valign="top">Наименование:</td>
           <td align="left" valign="top"><input type="text" name="x_project_name" value="<?= $project['name']; ?>" size="35" style=" width : 100%; " /></td>
	  </tr>
          <tr>
           <td align="right" valign="top">Описание:</td>
           <td align="left" valign="top"><input type="text" name="x_project_descr" value="<?= $project['description']; ?>" size="35" style=" width : 100%; " /></td>
	  </tr>
	  <tr>
           <td align="right" valign="top">Активно:</td>
	   <td align="left" valign="top"><input type="checkbox" name="x_project_active" <?= $project['is_active']?"checked":""; ?> style=" width : 100%; " /></td>
	  </tr>
	  <tr>
           <td align="right" valign="top">Постоянный:</td>
	   <td align="left" valign="top"><input type="checkbox" name="x_project_permanent" <?= $project['is_permanent']?"checked":""; ?> style=" width : 100%; " /></td>
	  </tr>
	  <tr>
           <td align="right" valign="top">Ситемный:</td>
	   <td align="left" valign="top"><input type="checkbox" name="x_project_system" <?= $project['is_system']?"checked":""; ?> style=" width : 100%; " /></td>
	  </tr>
	  <tr>
	   <td>&nbsp;</td>
	   <td align="left"><input title="Принять внесенные изменения" type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	  </tr>
	 </table>
	</form>
     </td>
    </tr>
   </table>
<?php endif; ?>