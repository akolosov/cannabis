<?php
        $parentprocesses = $connection->execute('select * from processes_tree where (is_public = false or is_public is null) and is_active = true');
        if (ACTION == "change") {
            $process = prepareForView($connection->execute('select * from cs_project_process where id = '.PROCESS_ID)->fetch());
        } else {
            $process = array();
        }
?>
	<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
        <form width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("PROJECT_ID")?"&project_id=".PROJECT_ID:""); ?><?= (defined("PROCESS_ID")?"&process_id=".PROCESS_ID:""); ?>" method="POST">
	 <input type="hidden" name="x_project_id" value="<?= ($process['project_id']?$process['project_id']:PROJECT_ID); ?>" />
	 <input type="hidden" name="x_old_project_process_id" value="<?= ($process['process_id']?$process['process_id']:NULL); ?>" />
         <table width="100%">
          <tr>
           <td align="right" valign="top">Процессы:</td>
           <td align="left" valign="top" width="80%">
	   <select name="x_project_process_id" style=" width : 100%; " size="7">
	    <option value="" />
              <?php foreach ($parentprocesses as $parentprocess): ?>
              <option value="<?= $parentprocess['id']; ?>" <?= ($parentprocess['id'] == $process['process_id'])?"selected":""; ?> /><?= str_pad_html("", $parentprocess['level']).trim($parentprocess['name'])." (".trim($parentprocess['description']).")"; ?>
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
