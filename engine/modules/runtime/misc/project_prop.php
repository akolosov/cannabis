<?php if (defined('PROJECT_INSTANCE_ID')): ?>
 <br />
 <table width="100%" align="center">
  <tr>
   <th>Наименование свойства</th>
   <th>Тип</th>
   <th>Значение</th>
  </tr>
<?php
  $project = new ProjectInstanceWrapper($engine, PROJECT_INSTANCE_ID, array('onlyprocess' => -1, 'workingonly' => true, 'ownedby' => -1));
  print "<div class=\"caption\"><b>Свойства предприятия: </b>".$project->getProperty('name')."</div>\n";
  $properties = $project->getProperty('[properties]')->getElements();
  foreach ($properties as $property) {
        print "<tr>";
        print "<td title=\"".$property->getProperty('description')."\">".$property->getProperty('name')."</td>";
        print "<td align=\"center\">".$property->getProperty('typename')."</td>";
        print "<td align=\"center\">".(isNULL($property->getProperty('mime_type'))?$property->getProperty('value'):"")."</td>";
        print "</tr>\n";
  }
?>
 </table>
<?php endif; ?>
