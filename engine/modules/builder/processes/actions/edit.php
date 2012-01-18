<?php
$properties = $connection->execute('select * from process_properties_list where process_id = '.PROCESS_ID.' order by id, name')->fetchAll();
$types = $connection->execute('select * from cs_action_type where in_use = true')->fetchAll();
$switchactions = $connection->execute('select * from cs_process_action where process_id = '.PROCESS_ID)->fetchAll();
$roles = $connection->execute('select cs_process_role.*, cs_role.name, cs_role.description from cs_process_role, cs_role where cs_process_role.process_id = '.PROCESS_ID.' and cs_process_role.role_id = cs_role.id order by cs_role.id, cs_role.name')->fetchAll();
$properties_list = array();

if (ACTION == "change") {
	$action = prepareForView($connection->execute('select cs_process_action.*, cs_process.name as processname from cs_process_action, cs_process where cs_process_action.id = '.ACTION_ID.' and cs_process_action.process_id = '.PROCESS_ID.' and cs_process_action.process_id = cs_process.id')->fetch());

	$action_properties = $connection->execute('select property_id from process_action_properties_list where action_id = '.ACTION_ID)->fetchAll();
	foreach ($action_properties as $action_property) {
		$properties_list[] = $action_property['property_id'];
	}
} else {
	$action = $connection->execute('select cs_process.name as processname from cs_process where cs_process.id = '.PROCESS_ID)->fetch();
}
?>
<script language="JavaScript">
<!--
	function isInteractive() {
		if ($('x_action_interactive').checked) {
			$('x_action_form').disabled = false; 
		} else {
			$('x_action_form').disabled = true; 
		}
		return true;
	}
	
	var old_value = "";

    function checkTimeFormat(input) {
		var countFormat = /^(\d{1,2} mounth(s)? )?(\d{1,2} day(s)? )?(\d{1,2}\:\d{1,2}(\:\d{1,2})?)$/i;
		return countFormat.test(input);
    }

	function isSwitch() {
		if ($('x_action_type_id').value == <?= Constants::ACTION_TYPE_SWITCH; ?>) {
			$('x_action_true_id').disabled = false; 
			$('x_action_false_id').disabled = false; 
		} else {
			$('x_action_true_id').disabled = true; 
			$('x_action_false_id').disabled = true; 
		}
		return true;
	}

-->
</script>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<div class="caption"><b>Наименование процесса: </b><?= $action['processname'];?></div>
<form width="100%" height="100%" align="right"
	action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("ACTION_ID")?'&action_id='.ACTION_ID:(defined("X_ACTION_ID")?'&action_id='.X_ACTION_ID:"")); ?><?= (defined("PROCESS_ID")?'&process_id='.PROCESS_ID:(defined("X_PROCESS_ID")?'&process_id='.X_PROCESS_ID:"")); ?><?= (defined('PROJECT_ID')?"&project_id=".PROJECT_ID:""); ?>"
	method="POST"><input type="hidden" name="x_action_id"
	value="<?= ($action['id']?$action['id']:NULL); ?>" /> <input
	type="hidden" name="x_process_id"
	value="<?= ($action['process_id']?$action['process_id']:(defined('PROCESS_ID')?PROCESS_ID:NULL)); ?>" />
<input type="hidden" name="x_type_id"
	value="<?= ($action['type_id']?$action['type_id']:NULL); ?>" /> <input
	type="hidden" name="x_old_role_action_id"
	value="<?= $role_action_id; ?>" /> <input type="hidden"
	name="x_old_role_id" value="<?= $role_id; ?>" />
<table width="100%">
	<tr>
		<td align="right" valign="top">Наименование:</td>
		<td align="left" valign="top"><input type="text" name="x_action_name"
			value="<?= $action['name']; ?>" size="35" style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Описание:</td>
		<td align="left" valign="top"><input type="text" name="x_action_descr"
			value="<?= $action['description']; ?>" size="35"
			style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top" width="20%">Тип действия:</td>
		<td align="left" valign="top"><select name="x_action_type_id" id="x_action_type_id" onChange="javascript:isSwitch();"
			style=" width : 100%; ">
			<?php foreach ($types as $type): ?>
			<option value="<?= $type['id']; ?>"
			<?= ($type['id'] == $action['type_id'])?"selected":""; ?> /><?= trim($type['name'])." (".trim($type['description']).")"; ?>
			<?php endforeach; ?>
		
		</select></td>
	</tr>
	<tr>
		<td align="right" valign="top" width="20%">Действие по "ДА":</td>
		<td align="left" valign="top"><select name="x_action_true_id" id="x_action_true_id" style=" width : 100%; ">
		<?php foreach ($switchactions as $switchaction): ?>
			<option value="<?= $switchaction['id']; ?>"
			<?= ($switchaction['id'] == $action['true_action_id'])?"selected":""; ?> /><?= trim($switchaction['name'])." (".trim($switchaction['description']).")"; ?>
		<?php endforeach; ?>
		</select></td>
	</tr>
	<tr>
		<td align="right" valign="top" width="20%">Действие по "НЕТ":</td>
		<td align="left" valign="top"><select name="x_action_false_id" id="x_action_false_id" style=" width : 100%; ">
		<?php foreach ($switchactions as $switchaction): ?>
			<option value="<?= $switchaction['id']; ?>"
			<?= ($switchaction['id'] == $action['false_action_id'])?"selected":""; ?> /><?= trim($switchaction['name'])." (".trim($switchaction['description']).")"; ?>
		<?php endforeach; ?>
		</select></td>
	</tr>
	<tr>
		<td align="right" valign="top" width="20%">Роль:</td>
		<td align="left" valign="top"><select name="x_action_role_id"
			style=" width : 100%; ">
			<?php foreach ($roles as $role): ?>
			<option value="<?= $role['id']; ?>"
			<?= ($role['id'] == $action['role_id']?"selected":""); ?> /><?= trim($role['name'])." (".trim($role['description']).")"; ?>
			<?php endforeach; ?>
		
		</select></td>
	</tr>
	<tr>
		<td align="right" valign="top">Вес:</td>
		<td align="left" valign="top"><input type="text"
			name="x_action_weight"
			value="<?= ($action['weight']?$action['weight']:"0"); ?>" size="35"
			style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top">Планируемое время:</td>
		<td align="left" valign="top"><input
			onFocus="old_value = this.value; "
			onBlur="if (!checkTimeFormat(this.value)) { this.value = old_value; }"
			type="text" name="x_action_planed"
			value="<?= ($action['planed']?$action['planed']:"01:00:00"); ?>"
			size="35" style=" width : 100%; " /></td>
	</tr>
      <tr>
           <td align="right" valign="top" width="20%">Необходимые свойства:</td>
           <td align="left" valign="top">
             <select name="x_properties[]" multiple="multiple" style=" width : 100%; " size="10">
              <?php foreach ($properties as $propertydata): ?>
              <option value="<?= $propertydata['id']; ?>" <?= (in_array($propertydata['id'], $properties_list)?"selected":""); ?> /><?= trim($propertydata['name'])." (".trim($propertydata['description']).")"; ?>
              <?php endforeach; ?>
             </select>
           </td>
          </tr>
	<tr>
		<td align="right" valign="top" width="20%">Обработка:</td>
		<td align="left" valign="top" width="80%"
			style=" margin: 0; padding : 0; "><textarea id="x_action_code"
			name="x_action_code" rows="10" cols="100" style=" width : 100%; " /><?= $action['code']; ?></textarea></td>
	</tr>
	<tr>
		<td align="right" valign="top">Интерактивно:</td>
		<td align="left" valign="top"><input
			onChange="javascript:isInteractive();" type="checkbox"
			name="x_action_interactive" id="x_action_interactive"
			<?= $action['is_interactive']?"checked":""; ?>
			style=" width : 100%; " /></td>
	</tr>
	<tr>
		<td align="right" valign="top" width="20%">Форма:</td>
		<td align="left" valign="top" width="80%"
			style=" margin: 0; padding : 0; "><textarea id="x_action_form"
			name="x_action_form" rows="10" cols="100" style=" width : 100%; " /><?= $action['form']; ?></textarea></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align="left"><input title="Принять внесенные изменения"
			type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	</tr>
</table>
</form>
<script language="JavaScript">
<!--
	isInteractive();
	isSwitch();
-->
</script>
