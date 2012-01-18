<?php if (($user_permissions[getParentModule()][getChildModule()]['can_read']) and (defined("TOPIC_ID"))): ?>
	<? if ((ACTION == 'view') and (defined("DOCUMENT_ID"))): ?>
	<?php
		print "<div class=\"process_info\" id=\"topics_data\"><img src=\"images/close.png\" style=\" float: right; \" onClick=\"hideIt('topics_data')\" title=\"Закрыть оглавление раздела\" />";
		require_once(MODULES_PATH.DIRECTORY_SEPARATOR.getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list.php");
		print "</div>";
		$document = prepareForView($connection->execute('select * from cs_public_document where id = '.DOCUMENT_ID)->fetch());
		print "<div class=\"error\">Если страница отображается некорректно, попробуйте нажать Ctrl+F5</div><br />";
		print "<div class=\"caption\">";
		print "<img src=\"images/template.png\" style=\" float : right;\" onClick=\"hideIt('topics_data')\" title=\"Оглавление раздела\" />";
		print $document['name'];
		print "</div><br />";
		print html_entity_decode($document['description'], ENT_COMPAT, DEFAULT_CHARSET);
	?>
	<? elseif ((ACTION == 'print') and (defined("DOCUMENT_ID"))): ?>
	<?php
		$document = prepareForView($connection->execute('select * from cs_public_document where id = '.DOCUMENT_ID)->fetch());
		print "<h4>".$document['name']."</h4><br />";
		print html_entity_decode($document['description'], ENT_COMPAT, DEFAULT_CHARSET);
	?>
	<? elseif ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
		<?php
		$parentdocuments = $connection->execute('select * from public_documents_tree where id >= 0')->fetchAll();
		if ((ACTION == "change") and (defined("DOCUMENT_ID"))) {
			$document = prepareForView($connection->execute('select * from cs_public_document where id = '.DOCUMENT_ID)->fetch());
		} else {
			$document = array('topic_id' => TOPIC_ID);
			if (defined('PARENT_ID')) {
				$document['parent_id'] = PARENT_ID;
			}
		}
		?>
		<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
		<form width="100%" height="100%" align="right" action="/?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/list&action=<?= ACTION; ?>&topic_id=<?= TOPIC_ID; ?>" method="POST">
			<input type="hidden" name="x_document_id" value="<?= ($document['id']?$document['id']:NULL); ?>" />
			<input type="hidden" name="x_parent_id" value="<?= ($document['parent_id']?$document['parent_id']:NULL); ?>" />
			<input type="hidden" name="x_topic_id" value="<?= ($document['topic_id']?$document['topic_id']:TOPIC_ID); ?>" />
			<table width="100%">
				<tr>
					<td align="right" valign="top">Наименование:</td>
					<td align="left" valign="top"><input type="text" name="x_document_name" value="<?= $document['name']; ?>" size="35" style=" width : 100%; " /></td>
					<td align="right" valign="top" width="10%">Активный:</td>
					<td align="left" valign="top" width="3%"><input type="checkbox" name="x_document_is_active" <?= $document['is_active']?"checked":""; ?> style=" width : 100%; " /></td>
				</tr>
				<tr>
					<td align="right" valign="top" width="15%">Родитель:</td>
					<td align="left" valign="top" colspan="3">
						<select name="x_document_parent_id" style=" width : 100%; ">
							<option value="" />
							<?php foreach ($parentdocuments as $parentdocument): ?>
							<?php if (($parentdocument['id'] <> $document['id']) and ((isNull($document['id'])) or (!isParentOf('cs_public_document', $document['id'], $parentdocument['id'])))): ?>
							<option value="<?= $parentdocument['id']; ?>" <?= (($parentdocument['id'] == $document['parent_id']) || ($parentdocument['id'] == PARENT_ID)?"selected":""); ?> /><?= str_pad_html("", $parentdocument['level']).trim($parentdocument['name']); ?>
							<? endif; ?>
							<?php endforeach; ?>
						</select>
					</td>
				</tr>
				<tr>
					<td align="right" valign="top">Текст:</td>
					<td align="left" valign="top" width="auto" colspan="3">
						<?php
							$sBasePath = $_SERVER['PHP_SELF'];
							$sBasePath = substr($sBasePath, 0, strpos($sBasePath, "index.php"))."engine/javascripts/fckeditor/";
							$oFCKeditor = new FCKeditor('x_document_descr') ;
							$oFCKeditor->BasePath = $sBasePath;
							$oFCKeditor->Config["CustomConfigurationsPath"] = $sBasePath.'custom_fckconfig.js';
							$oFCKeditor->Config['AutoDetectLanguage'] = false;
							$oFCKeditor->Config['DefaultLanguage'] = 'ru';
							$oFCKeditor->Config['SkinPath'] = $sBasePath.'editor/skins/default/';
							$oFCKeditor->Config['UserFilesPath'] = FILE_CACHE_PATH;
							$oFCKeditor->Config['Enabled'] = true;
							$oFCKeditor->ToolbarSet = 'Custom';
							$oFCKeditor->Value = html_entity_decode($document['description'], ENT_COMPAT, DEFAULT_CHARSET);
							$oFCKeditor->Height = "600";
							$oFCKeditor->Create();
						?>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td align="left" colspan="3"><input title="Принять внесенные изменения" type="submit" name="submit" value=" Принять " style=" width : 100%; " /></td>
				</tr>
			</table>
		</form>
	<?php endif; ?>
<?php endif; ?>