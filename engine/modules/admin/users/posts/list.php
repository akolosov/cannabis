<?php if ($user_permissions[getParentModule()][getParentChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
<?php if ($user_permissions[getParentModule()][getParentChildModule()]['can_write']): ?>
	<tr>
		<th colspan="3" width="99%" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить должность пользователя"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add&user_id=<?= USER_ID; ?>"><img
			src="images/create_icon.png" /></a></th>
	</tr>
<?php endif; ?>
<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				foreach ($parameters['X_USER_POSTS'] as $post_id) {
					foreach ($parameters['X_USER_DIVISIONS'] as $division_id) {
						$post = $connection->getTable('CsAccountPost')->create();
						$post['account_id'] = X_ACCOUNT_ID;
						$post['division_id'] = $division_id;
						$post['post_id'] = $post_id;
						$post->save();
					}
				}
				break;
			case "change" :
				$posts = $connection->execute('delete from cs_account_post where id = '.X_ID)->fetch();
				foreach ($parameters['X_USER_POSTS'] as $post_id) {
					foreach ($parameters['X_USER_DIVISIONS'] as $division_id) {
						$post = $connection->getTable('CsAccountPost')->create();
						$post['account_id'] = X_ACCOUNT_ID;
						$post['division_id'] = $division_id;
						$post['post_id'] = $post_id;
						$post->save();
					}
				}
				break;
			case "delete" :
				$posts = $connection->execute('delete from cs_account_post where id = '.POST_ID)->fetch();
				break;
			default:
				break;
		}
	}

	$account = $connection->execute('select * from cs_account where id = '.USER_ID)->fetch();
	$posts = $connection->execute('select * from account_posts_list where account_id = '.USER_ID.' order by id')->fetchAll();
	print "<caption class=\"caption\"><b>Пользователь: </b>".$account['name']." (".$account['description'].")</caption>\n";
	foreach ($posts as $post) {
		print "<tr>";
		print "<td title=\"".$post['divisiondescr']."\">".($post['parentdivisionname']?$post['parentdivisionname'].' - ':'').$post['divisionname']."</td>";
		print "<td title=\"".$post['postdescr']."\">".$post['postname']."</td>";
		if ($user_permissions[getParentModule()][getParentChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Изменить должность\" href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&post_id=".$post['id']."&user_id=".$post['account_id']."&division_id=".$post['division_id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getParentChildModule()]['can_delete']) {
			print "<td width=\"3%\" align=\"center\"><a title=\"Удалить должность\" href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&post_id=".$post['id']."&user_id=".$post['account_id']."&division_id=".$post['division_id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		}
		print "</tr>\n";
	}
?>
<?php if ($user_permissions[getParentModule()][getParentChildModule()]['can_write']): ?>
	<tr>
		<th colspan="3" width="99%" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить должность пользователя"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add&user_id=<?= USER_ID; ?>"><img
			src="images/create_icon.png" /></a></th>
	</tr>
<?php endif; ?>
</table>
<script>
<!--
	var accounts_tree_options = {
			'theme' : { 'name' : 'SimpleTree' },
			closeSiblings : false,
			maxOpenDepth : 1,
			flagClosedClass : 'close',
			toggleMenuOnClick : true,
			incrementalConvert : false,
			openTimeout : 0,
			closeTimeout: 0
	}
	var projects_tree = new CompleteMenuSolution;
	projects_tree.initMenu('accounts_tree', accounts_tree_options);
//-->
</script>
<?php endif; ?>
