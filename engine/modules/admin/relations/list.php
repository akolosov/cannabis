<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<table width="100%" align="center">
	<tr>
		<th width="99%" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить отношение должностей"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	</table>
<?php endif; ?>
<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				if (defined('X_RELATION_POST_ID') and isNotNULL($parameters['X_RELATION_RELATIONS']) and isNotNULL($parameters['X_RELATION_DIVISIONS'])) {
					$relation = $connection->execute('delete from cs_post_relation where post_id = '.X_RELATION_POST_ID.' and division_id in ('.implode(', ', $parameters['X_RELATION_DIVISIONS']).')')->fetch();
					foreach ($parameters['X_RELATION_RELATIONS'] as $post_id) {
						foreach ($parameters['X_RELATION_DIVISIONS'] as $division_id) {
							$post = $connection->getTable('CsPostRelation')->create();
							$post['division_id'] = $division_id;
							$post['post_id'] = X_RELATION_POST_ID;
							$post['relation_post_id'] = $post_id;
							$post->save();
						}
					}
				}
				break;
			case "change" :
				if (defined('X_POST_ID') and isNotNULL($parameters['X_RELATION_RELATIONS']) and isNotNULL($parameters['X_RELATION_DIVISIONS'])) {
					if (!defined('X_RELATION_POST_ID')){
						define('X_RELATION_POST_ID', X_POST_ID);
					}
					$relation = $connection->execute('delete from cs_post_relation where post_id = '.X_POST_ID.' and division_id in ('.implode(', ', $parameters['X_RELATION_DIVISIONS']).')')->fetch();
					foreach ($parameters['X_RELATION_RELATIONS'] as $post_id) {
						foreach ($parameters['X_RELATION_DIVISIONS'] as $division_id) {
							$post = $connection->getTable('CsPostRelation')->create();
							$post['division_id'] = $division_id;
							$post['post_id'] = X_RELATION_POST_ID;
							$post['relation_post_id'] = $post_id;
							$post->save();
						}
					}
				}
				break;
			case "delete" :
				$relation = $connection->execute('delete from cs_post_relation where post_id = '.POST_ID.' and division_id = '.DIVISION_ID)->fetch();
				break;
			default:
				break;
		}
	}

	function printCurrentRow($division) {
		global $user_permissions;

		print "<li class=\"treeitem\"><table width=\"auto\" cellspacing=\"1\" cellpadding=\"1\" style=\" border : 1px dotted #ccc; border-collapse : collapse; \">";
		print "<tr><td width=\"2%\"><a href=\"#\"></a></td>";
		print "<td width=\"94%\" title=\"".($division['description']?$division['description']:"&nbsp;")."\">Подразделение: ".$division['name']."</td>";
		print "</tr></table>";
		printRelationRow($division['id']);
		printRow($division['id']);
		print "</li>\n";
	}

	function printCurrentRelationRow($relation) {
		global $user_permissions;

		print "<li class=\"treeitem\"><table width=\"auto\" cellspacing=\"1\" cellpadding=\"1\" style=\" border : 1px dotted #ccc; border-collapse : collapse; \">";
		print "<tr><td width=\"2%\"><a href=\"#\"></a></td>";
		print "<td width=\"94%\" title=\"".($relation['postdescr']?$relation['postdescr']:"&nbsp;")."\">Должность: ".$relation['postname']."</td>";
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><span title=\"Изменить отношение\" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&post_id=".$relation['post_id']."&division_id=".$relation['division_id']."'\"><img src=\"images/edit_icon.png\" /></span></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_delete']) {
			print "<td width=\"3%\" align=\"center\"><span title=\"Удалить отношение\" onClick=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&post_id=".$relation['post_id']."&division_id=".$relation['division_id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></span></td>";
		}
		print "</tr></table>";
		print "</li>\n";
	}
	
	function printRow($id = 0, $level = 0) {
		global $connection;

		$divisions = $connection->execute('select * from divisions_tree where parent_id = '.$id)->fetchAll();

		foreach ($divisions as $division) {
			print "<ul>";
			printCurrentRow($division);
			print "</ul>";
		}
	}

	function printRelationRow($id = 0, $level = 0) {
		global $connection;

		$relations = $connection->execute('select distinct post_id, division_id, postname, postdescr from post_relations_list where division_id = '.$id)->fetchAll();

		foreach ($relations as $relation) {
			print "<ul>";
			printCurrentRelationRow($relation);
			print "</ul>";
		}
	}

	
	$divisions = $connection->execute('select * from divisions_tree where level = 0 order by id, name');
	print "<ul class=\"tree\" id=\"relations_tree\" style=\" display : none; \">\n";
	foreach ($divisions as $division) {
		print "<li class=\"roottreeitem\"><table width=\"auto\" cellspacing=\"1\" cellpadding=\"1\" style=\" border : 1px dotted #ccc; border-collapse : collapse; \">";
		print "<tr><td width=\"2%\"><a href=\"#\"></a></td>";
		print "<td width=\"94%\" title=\"".($division['description']?$division['description']:"&nbsp;")."\">".$division['name']."</td>";
		print "</tr></table>";
		printRow($division['id']);
		print "</li>\n";
	}
	print "</ul>\n";
	?>
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
<table width="100%" align="center">
	<tr>
		<th width="99%" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить отношение должностей"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
</table>
<?php endif; ?>
<script>
<!--
	var relations_tree_options = {
			'theme' : { 'name' : 'SimpleTree' },
			closeSiblings : false,
			maxOpenDepth : 1,
			flagClosedClass : 'close',
			toggleMenuOnClick : true,
			incrementalConvert : false,
			openTimeout : 0,
			closeTimeout: 0
	}
	var relations_tree = new CompleteMenuSolution;
	relations_tree.initMenu('relations_tree', relations_tree_options);
//-->
</script>
<?php endif; ?>
