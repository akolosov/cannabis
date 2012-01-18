	<?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR.getParentModule().DIRECTORY_SEPARATOR.getChildModule()).DIRECTORY_SEPARATOR."list.php"; ?>
	<br />
<?php
	$signs = $connection->execute('select * from cs_sign');
	$directories = $connection->execute('select * from cs_directory');
	$types = $connection->execute('select * from cs_property_type');
        if (ACTION == "change") {
	    $property = prepareForView($connection->execute('select cs_process_property.*, cs_process.name as processname from cs_process_property, cs_process where cs_process_property.id = '.PROPERTY_ID.' and cs_process_property.process_id = '.PROCESS_ID.' and cs_process_property.process_id = cs_process.id')->fetch());
        } else {
	    $property = prepareForView($connection->execute('select cs_process.name as processname from cs_process where cs_process.id = '.PROCESS_ID)->fetch());
        }
?>
<script language="JavaScript">
<!--
	function checkValueType() {
		if ($('x_property_type_id').value == <?= Constants::PROPERTY_TYPE_OBJECT; ?>) {
			$('x_property_value').value = ""; 
			$('x_property_value').disabled = true; 
			$('x_property_list').disabled = true;
			$('x_property_name_as_value').disabled = true;
			$('x_property_directory_id').disabled = true;
			$('x_property_field').disabled = true;
		} else {
			$('x_property_value').value = "<?= $property['default_value']; ?>"; 
			$('x_property_value').disabled = false; 
			if (($('x_property_type_id').value == <?= Constants::PROPERTY_TYPE_TEXT; ?>) || ($('x_property_type_id').value == <?= Constants::PROPERTY_TYPE_STRING; ?>) || ($('x_property_type_id').value == <?= Constants::PROPERTY_TYPE_NUMBER; ?>)) {
				$('x_property_list').disabled = false;
				checkIsList();
			}
		}
		return true;
	}
	
	function checkIsList() {
		if ($('x_property_list').checked == true) {
			$('x_property_value').disabled = true;
			$('x_property_field').disabled = false;
			$('x_property_name_as_value').disabled = false;
			$('x_property_directory_id').disabled = false;
		} else {
			$('x_property_value').disabled = false;
			$('x_property_field').disabled = true;
			$('x_property_name_as_value').disabled = true;
			$('x_property_directory_id').disabled = true;
		}
		return true;
	}

	function checkNameAsValue() {
		if ($('x_property_name_as_value').checked == true) {
			$('x_property_field').value = "<?= $property['value_field']; ?>"; 
		} else {
			$('x_property_field').value = "id"; 
		}
	}
	
-->
</script>
	<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
	<form width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("PROPERTY_ID")?'&property_id='.PROPERTY_ID:(defined("X_PROPERTY_ID")?'&property_id='.X_PROPERTY_ID:"")); ?><?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>" method="POST">
	 <input type="hidden" name="x_property_id" value="<?= ($property['id']?$property['id']:NULL); ?>" />
	 <input type="hidden" name="x_process_id" value="<?= ($property['process_id']?$property['process_id']:(defined('PROCESS_ID')?PROCESS_ID:NULL)); ?>" />
	 <input type="hidden" name="x_sign_id" value="<?= ($property['sign_id']?$property['sign_id']:NULL); ?>" />
	 <input type="hidden" name="x_type_id" value="<?= ($property['type_id']?$property['type_id']:NULL); ?>" />
	 <input type="hidden" name="x_directory_id" value="<?= ($property['directory_id']?$property['directory_id']:NULL); ?>" />
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
			<td align="right" valign="top">Значение из справочника:</td>
			<td align="left" valign="top"><input type="checkbox" onChange="javascript:checkIsList();" 
			name="x_property_list" id="x_property_list" <?= $property['is_list']?"checked":""; ?>
			style=" width : 100%; " /></td>
		</tr>
          <tr>
           <td align="right" valign="top" width="20%">Справочник:</td>
           <td align="left" valign="top">
             <select id="x_property_directory_id" name="x_property_directory_id" style=" width : 100%; ">
              <?php foreach ($directories as $directory): ?>
              <option value="<?= $directory['id']; ?>" <?= ($directory['id'] == $property['directory_id'])?"selected":""; ?> /><?= trim($directory['name'])." (".trim($directory['description']).")"; ?>
              <?php endforeach; ?>
             </select>
           </td>
          </tr>
		<tr>
			<td align="right" valign="top">Имя как значение:</td>
			<td align="left" valign="top"><input type="checkbox" onChange="javascript:checkNameAsValue();"
			name="x_property_name_as_value" id="x_property_name_as_value" <?= $property['is_name_as_value']?"checked":""; ?>
			style=" width : 100%; " /></td>
		</tr>
          <tr>
           <td align="right" valign="top">Значение из поля:</td>
           <td align="left" valign="top"><input type="text" id="x_property_field" name="x_property_field" value="<?= $property['value_field']; ?>" size="35" style=" width : 100%; " /></td>
          </tr>
	  <tr>
	   <td>&nbsp;</td>
	   <td align="left"><input title="Принять внесенные изменения" type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	  </tr>
	 </table>
	</form>
<script language="JavaScript">
<!--
	checkIsList();
	checkValueType();
	checkNameAsValue();
-->
</script>