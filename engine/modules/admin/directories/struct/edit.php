<?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR.getParentModule().DIRECTORY_SEPARATOR.getChildModule()).DIRECTORY_SEPARATOR."list.php"; ?>
<br />
<?php
	$types = $connection->execute('select * from cs_property_type');
    if (ACTION == "change") {
	    $field = prepareForView($connection->execute('select * from cs_directory_field where id = '.FIELD_ID)->fetch());
    } else {
	    $field = array('autoinc' => false);
    }
?>
<script language="JavaScript">
<!--
	function checkValueType() {
		if ($('x_field_type_id').value == <?= Constants::PROPERTY_TYPE_OBJECT; ?>) {
			$('x_field_value').value = ""; 
			$('x_field_value').disabled = true; 
			$('x_field_autoinc').disabled = true;
		} else {
			$('x_field_value').value = "<?= $field['default_value']; ?>"; 
			$('x_field_value').disabled = false; 
		}

		if ($('x_field_type_id').value == <?= Constants::PROPERTY_TYPE_NUMBER; ?>) {
			$('x_field_autoinc').disabled = false;
		} else {
			$('x_field_autoinc').disabled = true;
		}
		return true;
	}
-->
</script>
	<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
	<form width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("FIELD_ID")?'&field_id='.FIELD_ID:(defined("X_FIELD_ID")?'&field_id='.X_FIELD_ID:"")); ?><?= (defined("DIRECTORY_ID")?'&directory_id='.DIRECTORY_ID:(defined("X_DIRECTORY_ID")?'&directory_id='.X_DIRECTORY_ID:"")); ?>" method="POST">
	 <input type="hidden" name="x_field_id" value="<?= ($field['id']?$field['id']:(defined('FIELD_ID')?FIELD_ID:NULL)); ?>" />
	 <input type="hidden" name="x_directory_id" value="<?= ($field['directory_id']?$field['directory_id']:(defined('DIRECTORY_ID')?DIRECTORY_ID:NULL)); ?>" />
	 <input type="hidden" name="x_type_id" value="<?= ($field['type_id']?$field['type_id']:NULL); ?>" />
         <table width="100%">
          <tr>
           <td align="right" valign="top">Наименование:</td>
           <td align="left" valign="top"><input type="text" name="x_field_name" value="<?= $field['name']; ?>" size="35" style=" width : 100%; " /></td>
          </tr>
          <tr>
           <td align="right" valign="top">Заголовок:</td>
           <td align="left" valign="top"><input type="text" name="x_field_caption" value="<?= $field['caption']; ?>" size="35" style=" width : 100%; " /></td>
          </tr>
          <tr>
           <td align="right" valign="top" width="20%">Тип значения:</td>
           <td align="left" valign="top">
             <select onChange="javascript:checkValueType();" id="x_field_type_id" name="x_field_type_id" style=" width : 100%; ">
              <?php foreach ($types as $type): ?>
              <option value="<?= $type['id']; ?>" <?= ($type['id'] == $field['type_id'])?"selected":""; ?> /><?= trim($type['name'])." (".trim($type['description']).")"; ?>
              <?php endforeach; ?>
             </select>
           </td>
          </tr>
          <tr>
           <td align="right" valign="top">Значение по умолчанию:</td>
           <td align="left" valign="top"><input type="text" id="x_field_value" name="x_field_value" value="<?= $field['default_value']; ?>" size="35" style=" width : 100%; " /></td>
          </tr>
		<tr>
			<td align="right" valign="top">Автоинкремент:</td>
			<td align="left" valign="top"><input type="checkbox"
			name="x_field_autoinc" id="x_field_autoinc" <?= $field['autoinc']?"checked":""; ?>
			style=" width : 100%; " /></td>
		</tr>
	  <tr>
	   <td>&nbsp;</td>
	   <td align="left"><input title="Принять внесенные изменения" type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	  </tr>
	 </table>
	</form>
<script language="JavaScript">
<!--
	checkValueType();
-->
</script>