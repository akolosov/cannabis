 <?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
 <table width="100%" align="center">
  <tr>
   <th colspan="3">&nbsp;</th>
   <th><a href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>"><img src="images/create_icon.png"></a></th>
  </tr>
     <tr>
      <th>От действия</th>
      <th>К действию</th>
      <th colspan="2">Действия</th>
     </tr>
<?php
  if (defined("ACTION")) {
        switch (ACTION) {
		case "add" :
			if (defined('X_PROCESS_ID')) {
				$test = $connection->execute('select * from cs_process_transition where (from_action_id='.X_TRANSITION_FROM_ACTION_ID.' and to_action_id='.X_TRANSITION_TO_ACTION_ID.') or (from_action_id='.X_TRANSITION_TO_ACTION_ID.' and to_action_id='.X_TRANSITION_FROM_ACTION_ID.') and process_id='.X_PROCESS_ID)->fetch();
				if (isNULL($test)) {
					$transition = $connection->getTable('CsProcessTransition')->create();
					$transition['process_id'] = X_PROCESS_ID;
					$transition['from_action_id'] = X_TRANSITION_FROM_ACTION_ID;
					$transition['to_action_id'] = X_TRANSITION_TO_ACTION_ID;
					$transition->save();
					$connection->execute('select sort_process_actions('.$transition['process_id'].');');
				}
			}
			break;
		case "change" :
			if (defined('X_TRANSITION_ID')) {
				$test = $connection->execute('select * from cs_process_transition where (from_action_id='.(defined('X_TRANSITION_FROM_ACTION_ID')?X_TRANSITION_FROM_ACTION_ID:(X_FROM_ACTION_ID == ""?NULL:X_FROM_ACTION_ID)).' and to_action_id='.(defined('X_TRANSITION_TO_ACTION_ID')?X_TRANSITION_TO_ACTION_ID:(X_TO_ACTION_ID == ""?NULL:X_TO_ACTION_ID)).') or (from_action_id='.(defined('X_TRANSITION_TO_ACTION_ID')?X_TRANSITION_TO_ACTION_ID:(X_TO_ACTION_ID == ""?NULL:X_TO_ACTION_ID)).' and to_action_id='.(defined('X_TRANSITION_FROM_ACTION_ID')?X_TRANSITION_FROM_ACTION_ID:(X_FROM_ACTION_ID == ""?NULL:X_FROM_ACTION_ID)).') and process_id='.X_PROCESS_ID)->fetch();
				if (isNULL($test)) {
					$transition = $connection->getTable('CsProcessTransition')->find(X_TRANSITION_ID);
					$transition['from_action_id'] = (defined('X_TRANSITION_FROM_ACTION_ID')?X_TRANSITION_FROM_ACTION_ID:(X_FROM_ACTION_ID == ""?NULL:X_FROM_ACTION_ID));
					$transition['to_action_id'] = (defined('X_TRANSITION_TO_ACTION_ID')?X_TRANSITION_TO_ACTION_ID:(X_TO_ACTION_ID == ""?NULL:X_TO_ACTION_ID));
					$transition->save();
					$connection->execute('select sort_process_actions('.$transition['process_id'].');');
				}
			}
			break;
		case "delete" :
			$transition = $connection->getTable('CsProcessTransition')->find(TRANSITION_ID);
			$transition->delete();
			$connection->execute('select sort_process_actions('.$transition['process_id'].');');
			break;
		default:
		    	    break;
        }
  }

  $transitions = $connection->execute('select * from process_transitions where process_id = '.PROCESS_ID);

  $transition = $connection->execute('select name from cs_process where id = '.PROCESS_ID)->fetch();
  print "<caption class=\"caption\"><b>Наименование процесса: </b>".$transition['name']."</caption>\n";

  foreach ($transitions as $transition) {
        print "<tr>";
        print "<td>".$transition['fromactionname']." (".$transition['fromactiondescr'].")</td>";
        print "<td>".$transition['toactionname']." (".$transition['toactiondescr'].")</td>";
        print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&transition_id=".$transition['id']."&process_id=".$transition['process_id'].(defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"")."\"><img src=\"images/edit_icon.png\" /></a></td>";
        print "<td width=\"3%\" align=\"center\"><a href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&transition_id=".$transition['id']."&process_id=".$transition['process_id'].(defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"")."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
        print "</tr>\n";
  }
?>
   </td>
  </tr>
  <tr>
   <th colspan="3">&nbsp;</th>
   <th><a href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>"><img src="images/create_icon.png"></a></th>
  </tr>
 </table>
