	<?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR.getParentModule().DIRECTORY_SEPARATOR.getChildModule()).DIRECTORY_SEPARATOR."list.php"; ?>
	<br />
<?php
	$fromactions = $connection->execute('select * from cs_process_action where process_id = '.PROCESS_ID.' order by npp, id, name');
	$toactions = $connection->execute('select * from cs_process_action where process_id = '.PROCESS_ID.' order by npp, id, name');
        if (ACTION == "change") {
	    $transition = prepareForView($connection->execute('select cs_process_transition.*, cs_process.name as processname from cs_process_transition, cs_process where cs_process_transition.id = '.TRANSITION_ID.' and cs_process_transition.process_id = '.PROCESS_ID.' and cs_process_transition.process_id = cs_process.id')->fetch());
        } else {
            $transition = prepareForView($connection->execute('select cs_process.name as processname from cs_process where cs_process.id = '.PROCESS_ID)->fetch());
        }
?>
	<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
        <form width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("TRANSITION_ID")?'&transition_id='.TRANSITION_ID:(defined("X_TRANSITION_ID")?'&transition_id='.X_TRANSITION_ID:"")); ?><?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>" method="POST">
	 <input type="hidden" name="x_transition_id" value="<?= ($transition['id']?$transition['id']:NULL); ?>" />
	 <input type="hidden" name="x_process_id" value="<?= ($transition['process_id']?$transition['process_id']:(defined('PROCESS_ID')?PROCESS_ID:NULL)); ?>" />
	 <input type="hidden" name="x_from_action_id" value="<?= ($transition['from_action_id']?$transition['from_action_id']:NULL); ?>" />
	 <input type="hidden" name="x_to_action_id" value="<?= ($transition['to_action_id']?$transition['to_action_id']:NULL); ?>" />
         <table width="100%">
          <tr>
           <td align="left" valign="top" width="20%">От действия:</td>
           <td align="left" valign="top">
             <select name="x_transition_from_action_id" style=" width : 100%; " size="12">
              <?php foreach ($fromactions as $fromaction): ?>
              <option value="<?= $fromaction['id']; ?>" <?= ($fromaction['id'] == $transition['from_action_id'])?"selected":""; ?> /><?= trim($fromaction['name'])." (".trim($fromaction['description']).")"; ?>
              <?php endforeach; ?>
             </select>
           </td>
          </tr>
          <tr>
           <td align="left" valign="top" width="20%">К действию:</td>
           <td align="left" valign="top">
             <select name="x_transition_to_action_id" style=" width : 100%; " size="12">
              <?php foreach ($toactions as $toaction): ?>
              <option value="<?= $toaction['id']; ?>" <?= ($toaction['id'] == $transition['to_action_id'])?"selected":""; ?> /><?= trim($toaction['name'])." (".trim($toaction['description']).")"; ?>
              <?php endforeach; ?>
             </select>
           </td>
	  </tr>
	  <tr>
	   <td align="left" colspan="2"><input title="Принять внесенные изменения" type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	  </tr>
	 </table>
	</form>
