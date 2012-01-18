<?php require_once(MODULES_PATH.DIRECTORY_SEPARATOR.getParentModule().DIRECTORY_SEPARATOR.getChildModule()).DIRECTORY_SEPARATOR."list.php"; ?>
<br />
<?php
    $properties = $connection->execute('select * from process_properties_list where process_id = '.PROCESS_ID)->fetchAll();
    if (ACTION == "change") {
	    $property = prepareForView($connection->execute('select * from process_action_properties_list where id = '.ACTION_PROPERTY_ID)->fetch());
    } else {
    	$property = array('is_active' => true);
    }
?>
	<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
	<form width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("ACTION_ID")?'&action_id='.ACTION_ID:(defined("X_ACTION_ID")?'&action_id='.X_ACTION_ID:"")); ?><?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>" method="POST">
	 <input type="hidden" name="x_action_id" value="<?= ($property['action_id']?$property['action_id']:(defined('ACTION_ID')?ACTION_ID:(defined('X_ACTION_ID')?X_ACTION_ID:NULL))); ?>" />
	 <input type="hidden" name="x_action_property_id" value="<?= ($property['id']?$property['id']:(defined('ACTION_PROPERTY_ID')?ACTION_PROPERTY_ID:(defined('X_ACTION_PROPERTY_ID')?X_ACTION_PROPERTY_ID:NULL))); ?>" />
	 <input type="hidden" name="x_property_property_id" value="<?= ($property['property_id']?$property['property_id']:(defined('PROPERTY_ID')?PROPERTY_ID:NULL)); ?>" />
         <table width="100%">
         <tr>
           <td align="right" valign="top" width="20%">Cвойства:</td>
           <td align="left" valign="top">
             <select name="x_property_id" style=" width : 100%; ">
              <?php foreach ($properties as $propertydata): ?>
              <option value="<?= $propertydata['id']; ?>" <?= ($propertydata['id'] == $property['property_id'])?"selected":""; ?> /><?= trim($propertydata['name'])." (".trim($propertydata['description']).")"; ?>
              <?php endforeach; ?>
             </select>
           </td>
          </tr>
          <tr>
           <td align="right" valign="top">Номер по порядку:</td>
           <td align="left" valign="top"><input type="text" id="x_property_npp" name="x_property_npp" value="<?= $property['npp']; ?>" size="35" style=" width : 100%; " /></td>
          </tr>
		<tr>
			<td align="right" valign="top">Значение обязательно:</td>
			<td align="left" valign="top"><input type="checkbox" 
			name="x_property_required" id="x_property_required" <?= $property['is_required']?"checked":""; ?>
			style=" width : 100%; " /></td>
		</tr>
	<tr>
		<td align="right" valign="top">Параметры отбора:</td>
		<td align="left" valign="top"><input type="text" name="x_property_parameters"
			value="<?= $property['parameters']; ?>" size="35" style=" width : 100%; " /></td>
	</tr>
		<tr>
			<td align="right" valign="top">Активный:</td>
			<td align="left" valign="top"><input type="checkbox" 
			name="x_property_active" id="x_property_active" <?= $property['is_active']?"checked":""; ?>
			style=" width : 100%; " /></td>
		</tr>
		<tr>
			<td align="right" valign="top">Скрытый:</td>
			<td align="left" valign="top"><input type="checkbox" 
			name="x_property_hidden" id="x_property_hidden" <?= $property['is_hidden']?"checked":""; ?>
			style=" width : 100%; " /></td>
		</tr>
		<tr>
			<td align="right" valign="top">Только чтение:</td>
			<td align="left" valign="top"><input type="checkbox" 
			name="x_property_readonly" id="x_property_readonly" <?= $property['is_readonly']?"checked":""; ?>
			style=" width : 100%; " /></td>
		</tr>
	<? if ($property['is_list']): ?>
		<tr>
			<td align="right" valign="top">Отображать ComboBox:</td>
			<td align="left" valign="top"><input type="checkbox" 
			name="x_property_combo" id="x_property_combo" <?= $property['is_combo']?"checked":""; ?>
			style=" width : 100%; " /></td>
		</tr>
		<tr>
			<td align="right" valign="top">Множественный выбор:</td>
			<td align="left" valign="top"><input type="checkbox" 
			name="x_property_multiple" id="x_property_multiple" <?= $property['is_multiple']?"checked":""; ?>
			style=" width : 100%; " /></td>
		</tr>
	<? endif; ?>
		<tr>
			<td align="right" valign="top">Следующий Исполнитель:</td>
			<td align="left" valign="top"><input type="checkbox" 
			name="x_property_nextuser" id="x_property_nextuser" <?= $property['is_nextuser']?"checked":""; ?>
			style=" width : 100%; " /></td>
		</tr>
		<tr>
			<td align="right" valign="top">Дочерний Процесс:</td>
			<td align="left" valign="top"><input type="checkbox" 
			name="x_property_childprocess" id="x_property_childprocess" <?= $property['is_childprocess']?"checked":""; ?>
			style=" width : 100%; " /></td>
		</tr>
	  <tr>
	   <td>&nbsp;</td>
	   <td align="left"><input title="Принять внесенные изменения" type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	  </tr>
	 </table>
	</form>
