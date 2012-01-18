<?php if (($user_permissions[getParentModule()][getParentChildModule()]['can_read']) and (defined('DIRECTORY_ID'))): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<?php
	$directory = $connection->execute('select * from cs_directory where id = '.DIRECTORY_ID)->fetch();
	
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				$dirinfo = new DirectoryInfo($engine, $directory['id']);
				$data = array();
				foreach ($dirinfo->getFields() as $field) {
					$data[$field->getProperty('name')] = $parameters['DIR_'.strtoupper($engine->getFormManager()->prepareForForm($field->getProperty('name')))];
				}

				$engine->editDirectoryRecord(array('directory' => $dirinfo, 'record' => NULL, 'data' => $data));
		
				break;
			case "change" :
				if (defined('RECORD_ID')) {
					$dirinfo = new DirectoryInfo($engine, $directory['id']);
					$data = array();
					foreach ($dirinfo->getFields() as $field) {
						$data[$field->getProperty('name')] = $parameters['DIR_'.strtoupper($engine->getFormManager()->prepareForForm($field->getProperty('name')))];
					}
	
					$engine->editDirectoryRecord(array('directory' => $dirinfo, 'record' => $dirinfo->getRecord(RECORD_ID), 'data' => $data));
				}
				break;
			case "delete" :
				if (defined('RECORD_ID')) {
					$record = $connection->getTable('CsDirectoryRecord')->find(RECORD_ID);
					$record->delete();
				}
				break;

			default:
				break;
		}
	}

	$contents = $engine->getDirectoryList(array_merge($engine->prepareDirectoryParameters($directory), array('directory_id' => $directory['id'], 'full' => true, 'custom' => $directory['custom'])));
	if (($directory['custom']) and (isNULL($dirinfo))) {
		$dirinfo = new DirectoryInfo($engine, $directory['id']);
	} elseif (($directory['custom']) and (isNotNULL($dirinfo)) and (defined('ACTION'))) {
		$dirinfo->reinitDirectory();
	}
?>
<table width="100%" align="center">
<caption class="caption" width="100%">Справочник: <?= $directory['name']; ?></caption>
<?php if (($user_permissions[getParentModule()][getParentChildModule()]['can_write']) and ($directory['custom'])): ?>
	<tr>
		<th colspan="<?= ($directory['custom']?count(array_keys($contents[0]))-1:(count(array_keys($contents[0]))/2)+1); ?>" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить запись"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add&directory_id=<?= DIRECTORY_ID; ?>"><img
			src="images/create_icon.png" /></a></th>
	</tr>
<?php endif; ?>
<?php
	print "<tr>";
	if ($directory['custom'] == Constants::FALSE) {
		if (isNotNULL($contents)) {
			foreach (array_keys($contents[0]) as $header) {
				if (!preg_match('/^\d+$/i', $header)) {
					print "<th width=\"auto\">".$header."</th>";
				}
			}
		}
	} else {
		if (isNotNULL($contents)) {
			foreach (array_keys($contents[0]) as $header) {
				if (!preg_match('/^\[.*\]$/i', $header)) {
					if ($dirinfo->getProperty('[fields]')->elementExists($header)) {
						print "<th width=\"auto\">".$dirinfo->getField($header)->getProperty('caption')."</th>";
					} else {
						print "<th width=\"auto\">".$header."</th>";
					}
				}
			}
		}
	}

	if ($directory['custom']) {
		print "<th width=\"auto\" colspan=\"2\">Действия</th>";
	}
	print "</tr>";

	foreach ($contents as $content) {
		print "<tr>";
		$record_id = NULL;
		foreach ($content as $key => $data) {
			if ($directory['custom'] == Constants::FALSE) {
				if (!preg_match('/^\d+$/i', $key)) {
					print "<td width=\"auto\">".(mb_strlen($data)>20?mb_substr($data, 0, 20)."...":$data)."</td>";
				}
			} else {
				if ($key == '[record_id]') {
					$record_id = $data; 
				}
				if (!preg_match('/^\[.*\]$/i', $key)) {
					if ((isNotNULL($record_id)) and ($dirinfo->getRecord($record_id)->getProperty('[values]')->elementExists($key)) and (isNotNULL($dirinfo->getRecord($record_id)->getValue($key)->getProperty('mime_type')))) {
						storeDirectoryToCache($dirinfo->getRecord($record_id)->getValue($key)->getProperty('id'), FILE_CACHE_PATH."/".$dirinfo->getRecord($record_id)->getValue($key)->getFileName());
						print "<td width=\"auto\" align=\"center\"><a href=\"".FILE_CACHE_PATH.DIRECTORY_SEPARATOR.$dirinfo->getRecord($record_id)->getValue($key)->getFileName()."\">Объект</a></td>";
					} else {
						if ($dirinfo->getField($key)->getProperty('type_id') == Constants::PROPERTY_TYPE_BOOL) {
							print "<td width=\"auto\" align=\"center\"><img src=\"images/".(($data == 'on')?"ok":"cancel").".png\" /></td>";
						} else {
							print "<td width=\"auto\" ".((($dirinfo->getField($key)->getProperty('type_id') == Constants::PROPERTY_TYPE_DATE) or
							              (($dirinfo->getField($key)->getProperty('type_id') == Constants::PROPERTY_TYPE_DATETIME) or
							              (($dirinfo->getField($key)->getProperty('type_id') == Constants::PROPERTY_TYPE_TIME))))
							                ?"align=\"center\""
							                :(($dirinfo->getField($key)->getProperty('type_id') == Constants::PROPERTY_TYPE_NUMBER)
							                  ?"align=\"right\""
							                  :"")).(mb_strlen($data)>20?" title=\"<p align='justify'>".mb_str_ireplace("\n", "<br />", $data)."</p>\"":"").">".(mb_strlen($data)>20?mb_substr($data, 0, 20)."...":$data)."</td>";
						}
					}
				}
			}		
		}
		if ($directory['custom']) {
			if ($user_permissions[getParentModule()][getParentChildModule()]['can_write']) {
				print "<td width=\"3%\" align=\"center\"><a title=\"Изменить запись\" href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&record_id=".($directory['custom']?$content['[record_id]']:$content['id'])."&directory_id=".$directory['id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
			}
			if ($user_permissions[getParentModule()][getParentChildModule()]['can_delete']) {
				print "<td width=\"3%\" align=\"center\"><a title=\"Удалить запись\" href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&record_id=".($directory['custom']?$content['[record_id]']:$content['id'])."&directory_id=".$directory['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
			}
		}
		print "</tr>\n";
	}
	?>
	</td>
	</tr>
<?php if (($user_permissions[getParentModule()][getParentChildModule()]['can_write']) and ($directory['custom'])): ?>
	<tr>
		<th colspan="<?= ($directory['custom']?count(array_keys($contents[0]))-1:(count(array_keys($contents[0]))/2)+1); ?>" align="center">&nbsp;</th>
		<th align="center"><a title="Добавить запись"
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add&directory_id=<?= DIRECTORY_ID; ?>"><img
			src="images/create_icon.png" /></a></th>
	</tr>
<?php endif; ?>
</table>
<?php endif; ?>
