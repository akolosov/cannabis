<?php if ($user_permissions[getParentModule()][getParentChildModule()]['can_write']): ?>
<?php
$divisions = $connection->execute('select * from divisions_tree where id in (select division_id from cs_account_division where account_id = '.USER_ID.')')->fetchAll();
$posts = $connection->execute('select * from cs_post')->fetchAll();
$account = $connection->execute('select * from cs_account where id = '.USER_ID)->fetch();
$divisions_list = array();
$posts_list = array();

if (ACTION == "change") {
	$user = prepareForView($connection->execute('select * from account_posts_list where account_id = '.USER_ID.' and division_id = '.DIVISION_ID)->fetch());
	$user_posts = $connection->execute('select post_id from cs_account_post where account_id = '.USER_ID.' and division_id = '.DIVISION_ID)->fetchAll();
	$user_divisions = $connection->execute('select division_id from cs_account_division where account_id = '.USER_ID)->fetchAll();
	foreach ($user_divisions as $user_division) {
		$divisions_list[] = $user_division['division_id'];
	}
	foreach ($user_posts as $user_post) {
		$posts_list[] = $user_post['post_id'];
	}
} else {
	$user = array();
}
?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<?php print "<div class=\"caption\"><b>Пользователь: </b>".$account['name']." (".$account['description'].")</div>\n"; ?>
<form width="100%" height="100%" align="right"
	action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?><?= (defined("USER_ID")?"&user_id=".USER_ID:""); ?><?= (defined("DIVISION_ID")?"&division_id=".DIVISION_ID:""); ?>"
	method="POST">
	<input type="hidden" name="x_id" value="<?= ($user['id']?$user['id']:NULL); ?>" />
	<input type="hidden" name="x_account_id" value="<?= ($user['account_id']?$user['account_id']:USER_ID); ?>" />
<table width="100%">
	<tr>
		<td align="right" valign="top">Подразделения:</td>
		<td align="left" valign="top"><select name="x_user_divisions[]"
			style="width: 100%;" multiple="multiple" size="10">
			<?php foreach ($divisions as $division): ?>
			<option value="<?= $division['id']; ?>"
			<?= ($division['id'] == $user['division_id']?"selected":""); ?> /><?= str_pad_html("", $division['level']).trim($division['name'])." (".trim($division['description']).")"; ?>
			<?php endforeach; ?>
		
		</select></td>
	</tr>
	<tr>
		<td align="right" valign="top">Должности:</td>
		<td align="left" valign="top"><select name="x_user_posts[]" style=" width : 100%; " multiple="multiple" size="10">
		<?php foreach ($posts as $post): ?>
			<option value="<?= $post['id']; ?>"
			<?= (in_array($post['id'], $posts_list)?"selected":""); ?> /><?= trim($post['name'])." (".trim($post['description']).")"; ?>
		<?php endforeach; ?>
		</select></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td align="left"><input title="Принять внесенные изменения"
			type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
	</tr>
</table>
</form>
			<?php endif; ?>