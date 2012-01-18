 <?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
 <table width="100%" align="center">
  <tr>
   <th colspan="3">&nbsp;</th>
   <th><a href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>#form"><img src="images/create_icon.png" /></a></th>
  </tr>
  <tr>
   <th>Наименование роли</th>
   <th>Пользователь или Группа</th>
   <th colspan="2">Действия</th>
  </tr>
<?php
  if (defined("ACTION")) {
        switch (ACTION) {
                case "add" :
			if (defined('X_PROCESS_ID')) {
				$role = $connection->getTable('CsProcessRole')->create();
				$role['process_id'] = X_PROCESS_ID;
				$role['role_id'] = X_ROLE_ROLE_ID;
				$role['account_id'] = X_ROLE_ACCOUNT_ID;
				$role->save();
			}
			break;
                case "change" :
			if (defined('X_ROLE_ID')) {
				$role = $connection->getTable('CsProcessRole')->find(X_ROLE_ID);
				$role['role_id'] = (defined('X_ROLE_ROLE_ID')?X_ROLE_ROLE_ID:(X_OLD_ROLE_ID == ""?NULL:X_OLD_ROLE_ID));
				$role['account_id'] = (defined('X_ROLE_ACCOUNT_ID')?X_ROLE_ACCOUNT_ID:(X_ACCOUNT_ID == ""?NULL:X_ACCOUNT_ID));
				$role->save();
			}
			break;
                case "delete" :
		    $role = $connection->getTable('CsProcessRole')->find(ROLE_ID);
		    $role->delete();
	    	    break;
                default:
	    	    break;
        }
  }

  $roles = $connection->execute('select cs_process_role.*, cs_process.name as processname, cs_role.name as rolename, cs_role.description as roledescr,
  cs_account.name as accountname, cs_account.description as accountdescr from cs_process_role, cs_process, cs_role, cs_account where cs_process_role.process_id = cs_process.id and cs_process_role.role_id = cs_role.id and cs_process_role.account_id = cs_account.id and cs_process_role.process_id = '.PROCESS_ID.' order by cs_process_role.id');

  $transition = $connection->execute('select name from cs_process where id = '.PROCESS_ID)->fetch();
  print "<caption class=\"caption\"><b>Наименование процесса: </b>".$transition['name']."</caption>\n";
  foreach ($roles as $role) {
        print "<tr>";
        print "<td>".$role['rolename']." (".$role['roledescr'].")</td>";
        print "<td>".$role['accountname']." (".$role['accountdescr'].")</td>";
        print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&role_id=".$role['id']."&process_id=".$role['process_id'].(defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"")."#form\"><img src=\"images/edit_icon.png\" /></a></td>";
        print "<td width=\"3%\" align=\"center\"><a href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&role_id=".$role['id']."&process_id=".$role['process_id'].(defined('PROJECT_ID')?"&project_id=".PROJECT_ID:"")."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
        print "</tr>\n";
  }
?>
  <tr>
   <th colspan="3">&nbsp;</th>
   <th><a href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>#form"><img src="images/create_icon.png" /></a></th>
  </tr>
 </table>