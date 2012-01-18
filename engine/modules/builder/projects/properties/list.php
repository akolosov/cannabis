 <?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
 <table width="100%" align="center">
  <tr>
   <th colspan="6">&nbsp;</th>
   <th><a href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("PROJECT_ID")?'&project_id='.PROJECT_ID:(defined("X_PROJECT_ID")?'&project_id='.X_PROJECT_ID:"")); ?>"><img src="images/create_icon.png" /></a></th>
  </tr>
     <tr>
      <th>Наименование свойства</th>
      <th>Описание</th>
      <th>Признак</th>
      <th>Тип значения</th>
      <th>По умолчанию</th>
      <th colspan="2">Действия</th>
     </tr>
<?php
  if (defined("ACTION")) {
        switch (ACTION) {
                case "add" :
			if (defined('X_PROJECT_ID')) {
				$property = $connection->getTable('CsProjectProperty')->create();
				$property['project_id'] = X_PROJECT_ID;
				$property['sign_id'] = X_PROPERTY_SIGN_ID;
				$property['type_id'] = X_PROPERTY_TYPE_ID;
				$property['name'] = prepareForSave(X_PROPERTY_NAME);
				$property['description'] = prepareForSave(X_PROPERTY_DESCR);
				$property['default_value'] = (defined('X_PROPERTY_VALUE')?prepareForSave(X_PROPERTY_VALUE):NULL);
				$property->save();
			}
			break;
                case "change" :
			if (defined('X_PROPERTY_ID')) {
				$property = $connection->getTable('CsProjectProperty')->find(X_PROPERTY_ID);
				$property['sign_id'] = (defined('X_PROPERTY_SIGN_ID')?X_PROPERTY_SIGN_ID:(X_SIGN_ID == ""?NULL:X_SIGN_ID));
				$property['type_id'] = (defined('X_PROPERTY_TYPE_ID')?X_PROPERTY_TYPE_ID:(X_TYPE_ID == ""?NULL:X_TYPE_ID));
				$property['name'] = prepareForSave(X_PROPERTY_NAME);
				$property['description'] = prepareForSave(X_PROPERTY_DESCR);
				$property['default_value'] = (defined('X_PROPERTY_VALUE')?prepareForSave(X_PROPERTY_VALUE):NULL);
				$property->save();
			}
			break;
                case "delete" :
		    $property = $connection->getTable('CsProjectProperty')->find(PROPERTY_ID);
		    $property->delete();
	    	    break;
                default:
	    	    break;
        }
  }

  $properties = $connection->execute('select cs_project_property.*, cs_project.name as projectname, cs_sign.name as signname, cs_property_type.name as typename from cs_project_property, cs_project, cs_sign, cs_property_type where cs_project_property.project_id = cs_project.id and cs_project_property.sign_id = cs_sign.id and cs_project_property.type_id = cs_property_type.id and cs_project_property.project_id = '.PROJECT_ID.' order by cs_project_property.id, cs_project_property.name');

  $transition = $connection->execute('select name from cs_project where id = '.PROJECT_ID)->fetch();
  print "<caption class=\"caption\"><b>Наименование проекта: </b>".$transition['name']."</caption>\n";
    
  foreach ($properties as $property) {
        print "<tr>";
        print "<td>".$property['name']."</td>";
        print "<td>".$property['description']."</td>";
        print "<td align=\"center\">".$property['signname']."</td>";
        print "<td align=\"center\">".$property['typename']."</td>";
        print "<td align=\"center\">".$property['default_value']."</td>";
        print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&property_id=".$property['id']."&project_id=".$property['project_id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
        print "<td width=\"3%\" align=\"center\"><a href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&property_id=".$property['id']."&project_id=".$property['project_id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
        print "</tr>\n";
  }
?>
   </td>
  </tr>
  <tr>
   <th colspan="6">&nbsp;</th>
   <th><a href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add<?= (defined("PROJECT_ID")?'&project_id='.PROJECT_ID:(defined("X_PROJECT_ID")?'&project_id='.X_PROJECT_ID:"")); ?>"><img src="images/create_icon.png" /></a></th>
  </tr>
 </table>
