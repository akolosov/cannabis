<?php if (($user_permissions[getParentModule()][getChildModule()]['can_read']) and (defined('TOPIC_ID'))): ?>
<?php
	require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php");
	require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."search.php");
	require_once(MODULES_PATH.DIRECTORY_SEPARATOR."public".DIRECTORY_SEPARATOR."misc".DIRECTORY_SEPARATOR."functions.php");
	if ((defined('X_SEARCH_BY')) and (defined('X_SEARCH_VALUE')) and (X_SEARCH_VALUE <> '')) {
		if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
			$colspan = 2;
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			if (!((defined('X_SEARCH_BY')) and (defined('X_SEARCH_VALUE')) and (X_SEARCH_VALUE <> ''))) {
				$colspan += 3;
			} else {
				$colspan += 1;
			}
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_delete']) {
			$colspan += 1;
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_admin']) {
			$colspan += 1;
		}
	}
	?>
<table width="100%" align="center">
<?php
	$topic = $connection->execute('select name from cs_public_topic where id = '.TOPIC_ID)->fetch();
?>
	<caption class="caption action" onClick="document.location.href = '/?module=<?= getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR.'list'.getURIParams(); ?>';"><?= $topic['name']; ?></caption>
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th width="auto" align="center" colspan="<?= $colspan; ?>">&nbsp;</th>
		<th width="16" align="center"><a title="Добавить документ" href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add&topic_id=<?= TOPIC_ID; ?>"><img src="images/create_icon.png" /></a></th>
	</tr>
<?php endif; ?>
<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				$document = $connection->getTable('CsPublicDocument')->create();
				$document['parent_id'] = (defined('X_DOCUMENT_PARENT_ID') && X_DOCUMENT_PARENT_ID != ""?X_DOCUMENT_PARENT_ID:NULL);
				$document['topic_id'] = TOPIC_ID;
				$document['name'] = prepareForSave(X_DOCUMENT_NAME);
				if (get_magic_quotes_gpc()) {
					$document['description'] = stripslashes(X_DOCUMENT_DESCR);
				}else {
					$document['description'] = X_DOCUMENT_DESCR;
				}
				$document['created_at'] = strftime("%Y-%m-%d %H:%M:%S", time());
				$document['created_by'] = USER_CODE;
				$document['is_active'] = (X_DOCUMENT_IS_ACTIVE == 'on'?true:false);
				$npp = $connection->execute('select get_max_document_npp('.(isNotNULL($document['parent_id'])?$document['parent_id']:'NULL').'::int8)')->fetch();
				if (is_null($npp['get_max_document_npp'])) {
					$npp['get_max_document_npp'] = 0;
				} else {
					++$npp['get_max_document_npp'];
				}
				$document['npp'] = $npp['get_max_document_npp'];
				$document->save();
				define('DOCUMENT_ID', $document['id']);
				break;
			case "change" :
				$document = $connection->getTable('CsPublicDocument')->find(X_DOCUMENT_ID);
				if ($document['parent_id'] <> (defined('X_DOCUMENT_PARENT_ID')?(X_DOCUMENT_PARENT_ID == ""?NULL:X_DOCUMENT_PARENT_ID):(X_PARENT_ID != ""?X_PARENT_ID:NULL))) {
					$npp = $connection->execute('select get_max_document_npp('.(defined('X_DOCUMENT_PARENT_ID')?(X_DOCUMENT_PARENT_ID == ""?'NULL':X_DOCUMENT_PARENT_ID):(X_PARENT_ID != ""?X_PARENT_ID:'NULL')).'::int8)')->fetch();
					if (is_null($npp['get_max_document_npp'])) {
						$npp['get_max_document_npp'] = 0;
					} else {
						++$npp['get_max_document_npp'];
					}
					$document['npp'] = $npp['get_max_document_npp'];
				}
				$document['parent_id'] = (defined('X_DOCUMENT_PARENT_ID')?(X_DOCUMENT_PARENT_ID == ""?NULL:X_DOCUMENT_PARENT_ID):(X_PARENT_ID != ""?X_PARENT_ID:NULL));
				$document['name'] = prepareForSave(X_DOCUMENT_NAME);
				if (get_magic_quotes_gpc()) {
					$document['description'] = stripslashes(X_DOCUMENT_DESCR);
				}else {
					$document['description'] = X_DOCUMENT_DESCR;
				}
				$document['updated_at'] = strftime("%Y-%m-%d %H:%M:%S", time());
				$document['updated_by'] = USER_CODE;
				$document['is_active'] = (X_DOCUMENT_IS_ACTIVE == 'on'?true:false);
				$document->save();
				define('DOCUMENT_ID', $document['id']);
				break;
			case "moveup" :
				if ((defined('FIRST_DOC_ID')) and (defined('FIRST_NPP')) and (FIRST_NPP > 0)) {
					$first_doc = $connection->execute('select * from cs_public_document where id = '.FIRST_DOC_ID)->fetch();
					if (isNotNULL($first_doc)) {
						$second_doc = $connection->execute('select * from cs_public_document where npp = '.(FIRST_NPP-1).' and parent_id'.(is_null($first_doc['parent_id'])?' is null':' = '.$first_doc['parent_id']))->fetch();
						if (isNotNULL($second_doc)) {
							$res = $connection->execute('select swap_documents_npp('.$first_doc['id'].', '.$second_doc['id'].')')->fetch();
							define('DOCUMENT_ID', FIRST_DOC_ID);
						}
					}
				}
				break;
			case "movedown" :
				if ((defined('FIRST_DOC_ID')) and (defined('FIRST_NPP'))) {
					$first_doc = $connection->execute('select * from cs_public_document where id = '.FIRST_DOC_ID)->fetch();
					if (isNotNULL($first_doc)) {
						$max_npp = $connection->execute('select get_max_document_npp('.(is_null($first_doc['parent_id'])?'NULL':$first_doc['parent_id']).'::int8)')->fetch();
						if ($max_npp['get_max_document_npp'] > FIRST_NPP) { 
							$second_doc = $connection->execute('select * from cs_public_document where npp = '.(FIRST_NPP+1).' and parent_id'.(is_null($first_doc['parent_id'])?' is null':' = '.$first_doc['parent_id']))->fetch();
							if (isNotNULL($second_doc)) {
								$res = $connection->execute('select swap_documents_npp('.$first_doc['id'].', '.$second_doc['id'].')')->fetch();
								define('DOCUMENT_ID', FIRST_DOC_ID);
							}
						}
					}
				}
				break;
			case "delete" :
				$document = $connection->getTable('CsPublicDocument')->find(DOCUMENT_ID);
				$document['updated_at'] = strftime("%Y-%m-%d %H:%M:%S", time());
				$document['updated_by'] = USER_CODE;
				$document['is_active'] = (!$document['is_active']);
				$document->save();
				define('DOCUMENT_ID', $document['id']);
				break;
			case "erase" :
				$document = $connection->getTable('CsPublicDocument')->find(DOCUMENT_ID);
				$parent_id = $document['parent_id'];
				$document->delete();
				$result = $connection->execute('select rebuild_documents_tree('.(isNotNULL($parent_id)?$parent_id:'NULL').'::int8)')->fetch();
				break;
			default:
				break;
		}
	}

	function printTableRow($document) {
		global $user_permissions;

		print "<tr>";
		if (!((defined('X_SEARCH_BY')) and (defined('X_SEARCH_VALUE')) and (X_SEARCH_VALUE <> ''))) {
			if (($user_permissions[getParentModule()][getChildModule()]['can_write']) and (($document['id'] > 0) or ($user_permissions[getParentModule()][getChildModule()]['can_admin']))) {
				print "<td width=\"16\" align=\"center\"><span title=\"Поднять документ выше\" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=moveup&first_doc_id=".$document['id']."&first_npp=".$document['npp']."&topic_id=".$document['topic_id']."'\"><img class=\"action\" src=\"images/outboxes.png\" /></span></td>";
			}
			if (($user_permissions[getParentModule()][getChildModule()]['can_write']) and (($document['id'] > 0) or ($user_permissions[getParentModule()][getChildModule()]['can_admin']))) {
				print "<td width=\"16\" align=\"center\"><span title=\"Опустить документ ниже\" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=movedown&first_doc_id=".$document['id']."&first_npp=".$document['npp']."&topic_id=".$document['topic_id']."'\"><img class=\"action\" src=\"images/inboxes.png\" /></span></td>";
			}
		}
		print "<td width=\"auto\" class=\"action ".(!$document['is_active']?" strike ":"")."\" title=\"<p style=' text-align : left !important; '>";
		print "<b>Создал:</b>".$document['creatorname']."<br />";
		print "<b>Дата создания:</b>".strftime("%d.%m.%Y в %H:%M", strtotime($document['created_at']))."<br />";
		if ($document['updated_at']) {
			print "<b>Изменял:</b>".$document['updatorname']."<br />";
			print "<b>Дата изменения:</b>".strftime("%d.%m.%Y в %H:%M", strtotime($document['updated_at']))."<br />";
		}
		print "</p>\"><a href=\"#\">&nbsp;</a><span valign=\"middle\" ".((DOCUMENT_ID == $document['id'])?"class=\"bold\"":"").((isNotNULL($document['description']))?" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=view&document_id=".$document['id']."&topic_id=".$document['topic_id']."'\"":"").">";
		if (DOCUMENT_ID == $document['id']) {
			print "<img src=\"images/next.png\" valign=\"bottom\" />";
		}
		print $document['name'];
		print "</span></td>";
		if (($user_permissions[getParentModule()][getChildModule()]['can_read']) and (($document['id'] > 0) or ($user_permissions[getParentModule()][getChildModule()]['can_admin']))) {
			print "<td width=\"16\" align=\"center\"><span title=\"Посмотреть документ\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=view&document_id=".$document['id']."&topic_id=".$document['topic_id']."');\"><img class=\"action\" src=\"images/default_icon.png\" /></span></td>";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_read']) and (($document['id'] > 0) or ($user_permissions[getParentModule()][getChildModule()]['can_admin']))) {
			print "<td width=\"16\" align=\"center\"><span title=\"Распечатать документ\" onClick=\"openWindow('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=print&media=print&document_id=".$document['id']."&topic_id=".$document['topic_id']."');\"><img class=\"action\" src=\"images/print.png\" /></span></td>";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_write']) and (($document['id'] > 0) or ($user_permissions[getParentModule()][getChildModule()]['can_admin']))) {
			print "<td width=\"16\" align=\"center\"><span title=\"Изменить документ\" onClick=\"document.location.href = '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&document_id=".$document['id']."&topic_id=".$document['topic_id']."'\"><img class=\"action\" src=\"images/edit_icon.png\" /></span></td>";
		}
		if (($user_permissions[getParentModule()][getChildModule()]['can_delete']) and (($document['id'] > 0) or ($user_permissions[getParentModule()][getChildModule()]['can_admin']))) {
			print "<td width=\"16\" align=\"center\"><span title=\"".($document['is_active']?"Удалить":"Восстановить")." документ\" onClick=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&document_id=".$document['id']."&topic_id=".$document['topic_id']."', '_top', true);\"><img class=\"action\" src=\"images/delete_icon.png\" /></span></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_admin']) {
			print "<td width=\"16\" align=\"center\"><span title=\"Удалить СОВСЕМ документ\" onClick=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=erase&document_id=".$document['id']."&topic_id=".$document['topic_id']."', '_top', true);\"><img class=\"action\" src=\"images/erase.png\" /></span></td>";
		}
		print "</tr>";
	}

	function printCurrentRow($document) {
		global $user_permissions;

		print "<li class=\"treeitem\">";
		print "<table width=\"auto\" cellspacing=\"1\" cellpadding=\"1\" style=\" margin-bottom : 2px; \">";
		printTableRow($document);
		print "</table>";
		printRow($document['id']);
		print "</li>\n";
	}

	function printRow($id = 0, $level = 0) {
		global $connection, $documents;

		foreach (getDocumentByParentID($documents, $id) as $document) {
			print "<ul>";
			printCurrentRow($document);
			print "</ul>";
		}
	}

	if ((!$user_permissions[getParentModule()][getChildModule()]['can_write']) and (!$user_permissions[getParentModule()][getChildModule()]['can_admin'])) {
		$where .= ((is_null($where)?"":" and ")).'(is_active = true)';
	}

	$documents = $connection->execute('select id, parent_id, topic_id, name, description, created_at, updated_at, created_by, updated_by, creatorname, updatorname, is_active, npp from public_documents_tree where topic_id = '.TOPIC_ID.((is_null($where)?"":" and ".$where)).' order by npp')->fetchAll();

?>
<? if ((defined('X_SEARCH_BY')) and (defined('X_SEARCH_VALUE')) and (X_SEARCH_VALUE <> '')): ?>
<?php 
	foreach ($documents as $document) {
		printTableRow($document);
	}

?>
<? else: ?>
	<tr>
		<td width="auto" align="center" colspan="2">
<?php
	print "<ul class=\"tree\" id=\"documents_tree\" style=\" margin-left : 5px; display : none; \" align=\"center\">\n";
	foreach (getDocumentByParentID($documents, 0) as $document) {
		print "<li class=\"roottreeitem\">";
		print "<table width=\"auto\" cellspacing=\"1\" cellpadding=\"1\" style=\" margin-bottom : 2px; \">";
		printTableRow($document);
		print "</table>";
		printRow($document['id']);
		print "</li>\n";
	}
	print "</ul>\n";
	?>
		</td>
	</tr>
<?php endif; ?>
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th width="auto" align="center" colspan="<?= $colspan; ?>">&nbsp;</th>
		<th width="16" align="center"><a title="Добавить документ" href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add&topic_id=<?= TOPIC_ID; ?>"><img src="images/create_icon.png" /></a></th>
	</tr>
<?php endif; ?>
</table>
<? if (!((defined('X_SEARCH_BY')) and (defined('X_SEARCH_VALUE')) and (X_SEARCH_VALUE <> ''))): ?>
<script>
<!--
	var documents_tree_options = {
			'theme' : { 'name' : 'SimpleTree' },
			closeSiblings : false,
			maxOpenDepth : <?= ((defined('DOCUMENT_ID'))?"10":"0"); ?>,
			flagClosedClass : 'close',
			toggleMenuOnClick : true,
			incrementalConvert : false,
			openTimeout : 0,
			closeTimeout: 0
	}
	var documents_tree = new CompleteMenuSolution;
	documents_tree.initMenu('documents_tree', documents_tree_options);
//-->
</script>
<?php endif; ?>
<?php endif; ?>
