<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<?php
	$modules = $connection->execute('select * from modules_tree');
        if (ACTION == "change") {
	    $module = prepareForView($connection->execute('select * from cs_module where id = '.MODULE_ID)->fetch());
        } else {
            $module = array();
        }
?>
	<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
        <form width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("module_ID")?"&module_id=".module_ID:""); ?>" method="POST">
	 <input type="hidden" name="x_module_id" value="<?= ($module['id']?$module['id']:'0'); ?>" />
	 <input type="hidden" name="x_parent_id" value="<?= ($module['parent_id']?$module['parent_id']:NULL); ?>" />
         <table width="100%">
          <tr>
           <td align="right" valign="top">Наименование:</td>
           <td align="left" valign="top"><input type="text" name="x_module_name" value="<?= $module['name']; ?>" size="35" style=" width : 100%; " /></td>
	  </tr>
          <tr>
           <td align="right" valign="top">Описание:</td>
           <td align="left" valign="top"><input type="text" name="x_module_descr" value="<?= $module['description']; ?>" size="255" style=" width : 100%; " /></td>
	  </tr>
          <tr>
           <td align="right" valign="top">Заголовок:</td>
           <td align="left" valign="top"><input type="text" name="x_module_caption" value="<?= $module['caption']; ?>" size="35" style=" width : 100%; " /></td>
	  </tr>
	  <tr>
           <td align="right" valign="top" width="20%">Parent module:<br/><br/><a href="/?module=admin/modules/list" class="button"><img src="images/list.png" valign="middle"> посмотреть все</a></td>
           <td align="left" valign="top">
             <select name="x_module_parent_id" style=" width : 100%; " size="7" <?= defined("PARENT_ID")?"":"";?>>
              <option value="" />
              <?php foreach ($modules as $parentmodule): ?>
              <option value="<?= $parentmodule['id']; ?>" <?= ($module['parent_id'] == $parentmodule['id'])?"selected":""; ?> /><?= str_pad_html("", $parentmodule['level']).trim($parentmodule['name'])." (".trim($parentmodule['description']).")"; ?>
              <?php endforeach; ?>
             </select>
           </td>
          </tr>
	<tr>
		<td align="right" valign="top">Скрытый:</td>
		<td align="left" valign="top"><input type="checkbox" name="x_module_is_hidden" <?= $module['is_hidden']?"checked":""; ?> style=" width : 100%; " /></td>
	</tr>
	  <tr>
	   <td>&nbsp;</td>
	   <td align="left"><input title="Принять внесенные изменения" type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	  </tr>
	 </table>
	</form>
<?php endif; ?>