	<?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR.getParentModule().DIRECTORY_SEPARATOR.getChildModule()).DIRECTORY_SEPARATOR."list.php"; ?>
	<br />
<?php
	$signs = $connection->execute('select * from cs_sign');
	$types = $connection->execute('select * from cs_property_type');
        if (ACTION == "change") {
	    $property = prepareForView($connection->execute('select cs_project_property.*, cs_project.name as projectname from cs_project_property, cs_project where cs_project_property.id = '.PROPERTY_ID.' and cs_project_property.project_id = '.PROJECT_ID.' and cs_project_property.project_id = cs_project.id')->fetch());
        } else {
	    $property = prepareForView($connection->execute('select cs_project.name as projectname from cs_project where cs_project.id = '.PROJECT_ID)->fetch());
        }
?>
<script language="JavaScript">
<!--
	function checkValueType() {
		if ($('x_property_type_id').value == <?= Constants::PROPERTY_TYPE_OBJECT; ?>) {
			$('x_property_value').value = ""; 
			$('x_property_value').disabled = true; 
		} else {
			$('x_property_value').value = "<?= $property['default_value']; ?>"; 
			$('x_property_value').disabled = false; 
		}
		return true;
	}
-->
</script>
	<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
        <form width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("PROPERTY_ID")?'&property_id='.PROPERTY_ID:(defined("X_PROPERTY_ID")?'&property_id='.X_PROPERTY_ID:"")); ?><?= (defined("PROJECT_ID")?'&project_id='.PROJECT_ID:(defined("X_PROJECT_ID")?'&project_id='.X_PROJECT_ID:"")); ?>" method="POST">
	 <input type="hidden" name="x_property_id" value="<?= ($property['id']?$property['id']:NULL); ?>" />
	 <input type="hidden" name="x_project_id" value="<?= ($property['project_id']?$property['project_id']:(defined('PROJECT_ID')?PROJECT_ID:NULL)); ?>" />
	 <input type="hidden" name="x_sign_id" value="<?= ($property['sign_id']?$property['sign_id']:NULL); ?>" />
	 <input type="hidden" name="x_type_id" value="<?= ($property['type_id']?$property['type_id']:NULL); ?>" />
         <table width="100%">
          <tr>
           <td align="right" valign="top">Наименование:</td>
           <td align="left" valign="top"><input type="text" name="x_property_name" value="<?= $property['name']; ?>" size="35" style=" width : 100%; " /></td>
          </tr>
          <tr>
           <td align="right" valign="top">Описание:</td>
           <td align="left" valign="top"><input type="text" name="x_property_descr" value="<?= $property['description']; ?>" size="35" style=" width : 100%; " /></td>
          </tr>
          <tr>
           <td align="right" valign="top" width="20%">Признак свойства:</td>
           <td align="left" valign="top">
             <select name="x_property_sign_id" style=" width : 100%; ">
              <?php foreach ($signs as $sign): ?>
              <option value="<?= $sign['id']; ?>" <?= ($sign['id'] == $property['sign_id'])?"selected":""; ?> /><?= trim($sign['name'])." (".trim($sign['description']).")"; ?>
              <?php endforeach; ?>
             </select>
           </td>
          </tr>
          <tr>
           <td align="right" valign="top" width="20%">Тип значения:</td>
           <td align="left" valign="top">
             <select onChange="javascript:checkValueType();" id="x_property_type_id" name="x_property_type_id" style=" width : 100%; ">
              <?php foreach ($types as $type): ?>
              <option value="<?= $type['id']; ?>" <?= ($type['id'] == $property['type_id'])?"selected":""; ?> /><?= trim($type['name'])." (".trim($type['description']).")"; ?>
              <?php endforeach; ?>
             </select>
           </td>
          </tr>
          <tr>
           <td align="right" valign="top">Значение по умолчанию:</td>
           <td align="left" valign="top"><input type="text" id="x_property_value" name="x_property_value" value="<?= $property['default_value']; ?>" size="35" style=" width : 100%; " /></td>
          </tr>
	  <tr>
	   <td>&nbsp;</td>
	   <td align="left"><input title="Принять внесенные изменения" type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	  </tr>
	 </table>
	</form>
<script language="JavaScript">
<!--
	window.onLoad = checkValueType();
-->
</script>