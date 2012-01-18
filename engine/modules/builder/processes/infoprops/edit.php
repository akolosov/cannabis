<?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR.getParentModule().DIRECTORY_SEPARATOR.getChildModule()).DIRECTORY_SEPARATOR."list.php"; ?>
<br />
<?php
    $properties = $connection->execute('select * from process_properties_list where process_id = '.PROCESS_ID)->fetchAll();
    if (ACTION == "change") {
	    $property = prepareForView($connection->execute('select * from cs_process_info_property where id = '.PROCESS_PROPERTY_ID)->fetch());
    } else {
    	$property = array();
    }
?>
	<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
	<form width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("PROPERTY_ID")?'&property_id='.PROPERTY_ID:(defined("X_PROPERTY_ID")?'&property_id='.X_PROPERTY_ID:"")); ?><?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>" method="POST">
	 <input type="hidden" name="x_process_id" value="<?= ($property['process_id']?$property['process_id']:(defined('PROCESS_ID')?PROCESS_ID:(defined('X_PROCESS_ID')?X_PROCESS_ID:NULL))); ?>" />
	 <input type="hidden" name="x_id" value="<?= ($property['id']?$property['id']:(defined('PROCESS_PROPERTY_ID')?PROCESS_PROPERTY_ID:NULL)); ?>" />
	 <input type="hidden" name="x_process_property_id" value="<?= ($property['property_id']?$property['property_id']:(defined('PROPERTY_ID')?PROPERTY_ID:NULL)); ?>" />
         <table width="100%">
           <td align="right" valign="top" width="20%">Cвойства:</td>
           <td align="left" valign="top">
             <select name="x_property_id" style=" width : 100%; " size="20">
              <?php foreach ($properties as $propertydata): ?>
              <option value="<?= $propertydata['id']; ?>" <?= ($propertydata['id'] == $property['property_id'])?"selected":""; ?> /><?= trim($propertydata['name'])." (".trim($propertydata['description']).")"; ?>
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
