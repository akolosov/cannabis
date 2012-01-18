<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<?php
$posts = $connection->execute('select * from cs_post');
$relposts = $connection->execute('select * from cs_post');
$divisions = $connection->execute('select * from divisions_tree where id > 0');
$relations_list = array();
if (ACTION == "change") {
	$relation = prepareForView($connection->execute('select * from cs_post_relation where post_id = '.POST_ID.' and division_id = '.DIVISION_ID)->fetch());
	$post_relations = $connection->execute('select relation_post_id from cs_post_relation where post_id = '.POST_ID.' and division_id = '.DIVISION_ID)->fetchAll();
	foreach ($post_relations as $post_relation) {
		$relations_list[] = $post_relation['relation_post_id'];
	}
} else {
	$relation = array();
}
?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<form width="100%" height="100%" align="right"
	action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("RELATION_ID")?"&relation_id=".RELATION_ID:""); ?><?= (defined("POST_ID")?"&post_id=".POST_ID:""); ?><?= (defined("DIVISION_ID")?"&division_id=".DIVISION_ID:""); ?>"
	method="POST"><input type="hidden" name="x_relation_id"
	value="<?= ($relation['id']?$relation['id']:NULL); ?>" /> <input
	type="hidden" name="x_post_id"
	value="<?= ($relation['post_id']?$relation['post_id']:NULL); ?>" />
<table width="100%">
	<tr>
		<td align="right" valign="top">Должность:</td>
		<td align="left" valign="top"><select name="x_relation_post_id"
			style="width: 100%;" size="0">
			<?php foreach ($posts as $post): ?>
			<option value="<?= $post['id']; ?>"
			<?= ($post['id'] == $relation['post_id']?"selected":""); ?> /><?= trim($post['name'])." (".trim($post['description']).")"; ?>
			<?php endforeach; ?>
		
		</select></td>
	</tr>
	<tr>
		<td align="right" valign="top">Подразделение:</td>
		<td align="left" valign="top"><select name="x_relation_divisions[]"
			style="width: 100%;" multiple="multiple" size="20">
			<?php foreach ($divisions as $division): ?>
			<option value="<?= $division['id']; ?>"
			<?= ($division['id'] == $relation['division_id']?"selected":""); ?> /><?= str_pad_html("", $division['level']).trim($division['name'])." (".trim($division['description']).")"; ?>
			<?php endforeach; ?>
		
		</select></td>
	</tr>
	<tr>
		<td align="right" valign="top">Отношения с:</td>
		<td align="left" valign="top"><select name="x_relation_relations[]"
			style="width: 100%;" multiple="multiple" size="20">
			<?php foreach ($relposts as $relpost): ?>
			<option value="<?= $relpost['id']; ?>"
			<?= (in_array($relpost['id'], $relations_list)?"selected":""); ?> /><?= trim($relpost['name'])." (".trim($relpost['description']).")"; ?>
			<?php endforeach; ?>
		
		</select></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align="left"><input title="Принять внесенные изменения"
			type="submit" name="submit" value=" Принять " style="width: 100%;" /></td>
	</tr>
</table>
</form>
<?php endif; ?>