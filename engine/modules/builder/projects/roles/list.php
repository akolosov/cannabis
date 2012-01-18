 <?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
 <table width="100%" align="center">
  <tr>
   <th colspan="3">&nbsp;</th>
   <th><a href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("PROJECT_ID")?'&project_id='.PROJECT_ID:(defined("X_PROJECT_ID")?'&project_id='.X_PROJECT_ID:"")); ?>"><img src="images/create_icon.png" /></a></th>
  </tr>
     <tr>
      <th>Наименование роли</th>
      <th>Подразделение</th>
      <th colspan="2">Действия</th>
     </tr>
<?php
  if (defined("ACTION")) {
        switch (ACTION) {
                case "add" :
			if (defined('X_PROJECT_ID')) {
				$role = $connection->getTable('CsProjectRole')->create();
				$role['project_id'] = X_PROJECT_ID;
				$role['role_id'] = X_ROLE_ROLE_ID;
				$role['division_id'] = X_ROLE_ACCOUNT_ID;
				$role->save();
			}
			break;
                case "change" :
			if (defined('X_ROLE_ID')) {
				$role = $connection->getTable('CsProjectRole')->find(X_ROLE_ID);
				$role['role_id'] = (defined('X_ROLE_ROLE_ID')?X_ROLE_ROLE_ID:(X_OLD_ROLE_ID == ""?NULL:X_OLD_ROLE_ID));
				$role['division_id'] = (defined('X_ROLE_ACCOUNT_ID')?X_ROLE_ACCOUNT_ID:(X_ACCOUNT_ID == ""?NULL:X_ACCOUNT_ID));
				$role->save();
			}
			break;
                case "delete" :
		    $role = $connection->getTable('CsProjectRole')->find(ROLE_ID);
		    $role->delete();
	    	    break;
                default:
	    	    break;
        }
  }

  $roles = $connection->execute('select cs_project_role.*, cs_project.name as projectname, cs_role.name as rolename, cs_role.description as roledescr,
  cs_division.name as divisionname, cs_division.description as divisiondescr from cs_project_role, cs_project, cs_role, cs_division where cs_project_role.project_id = cs_project.id and cs_project_role.role_id = cs_role.id and cs_project_role.division_id = cs_division.id and cs_project_role.project_id = '.PROJECT_ID.' order by cs_project_role.id');

  $transition = $connection->execute('select name from cs_project where id = '.PROJECT_ID)->fetch();
  print "<caption class=\"caption\"><b>Наименование проекта: </b>".$transition['name']."</caption>\n";
    
  foreach ($roles as $role) {
        print "<tr>";
        print "<td>".$role['rolename']." (".$role['roledescr'].")</td>";
        print "<td>".$role['divisionname']." (".$role['divisiondescr'].")</td>";
        print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&role_id=".$role['id']."&project_id=".$role['project_id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
        print "<td width=\"3%\" align=\"center\"><a href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&role_id=".$role['id']."&project_id=".$role['project_id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
        print "</tr>\n";
  }
?>
   </td>
  </tr>
  <tr>
   <th colspan="3">&nbsp;</th>
   <th><a href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("PROJECT_ID")?'&project_id='.PROJECT_ID:(defined("X_PROJECT_ID")?'&project_id='.X_PROJECT_ID:"")); ?>"><img src="images/create_icon.png" /></a></th>
  </tr>
 </table>
