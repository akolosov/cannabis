<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<table width="100%" align="center">
<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="13" align="center">&nbsp;</th>
		<th align="center"><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
	<tr>
		<th width="5%">Код</th>
		<th>Наименование</th>
		<th>Автор</th>
		<th>Создан</th>
		<th>Активен</th>
		<th>Постоянный</th>
		<th>Системный</th>
		<th colspan="7" width="15%">Действия</th>
	</tr>
	<?php
	if (defined("ACTION")) {
		switch (ACTION) {
			case "add" :
				$project = $connection->getTable('CsProject')->create();
				$project['name'] = prepareForSave(X_PROJECT_NAME);
				$project['description'] = prepareForSave(X_PROJECT_DESCR);
				$project['version'] = 1.0;
				$project['created_at'] = strftime("%Y-%m-%d %H:%M:%S", time());
				$project['is_permanent'] = (X_PROJECT_PERMANENT == 'on'?true:false);
				$project['is_system'] = (X_PROJECT_SYSTEM == 'on'?true:false);
				$project['is_active'] = false;
				$project['author_id'] = USER_CODE;
				$project->save();
				break;
			case "change" :
				$project = $connection->getTable('CsProject')->find(X_PROJECT_ID);
				$project['name'] = prepareForSave(X_PROJECT_NAME);
				$project['description'] = prepareForSave(X_PROJECT_DESCR);
				$project['is_active'] = (X_PROJECT_ACTIVE == 'on'?true:false);
				$project['is_permanent'] = (X_PROJECT_PERMANENT == 'on'?true:false);
				$project['is_system'] = (X_PROJECT_SYSTEM == 'on'?true:false);
				if ($project['is_active']) {
					$project['activated_at'] = strftime("%Y-%m-%d %H:%M:%S", time());
				} else {
					$project['activated_at'] = NULL;
				}
				$project->save();
				break;
			case "delete" :
				$project = $connection->getTable('CsProject')->find(PROJECT_ID);
				$project->delete();
				break;
			case "create_project_instance" :
				if (defined('PROJECT_ID')) {
					if ($user_permissions[getParentModule()][getChildModule()]['can_admin']) {
						$result = $connection->execute('select create_project_instance('.PROJECT_ID.', '.USER_CODE.');')->fetch();
						if ($result['create_project_instance'] > 0) {
							$process = new Project($engine, PROJECT_ID);
							logMessage('создан экземпляр проекта "'.$process->getProperty('name').'" (идентификатор экземпляра: '.$result['create_project_instance'].')');
							$process = NULL;
						} else {
							$process = new Project($engine, PROJECT_ID);
							logMessage('экземпляр проекта "'.$process->getProperty('name').'" не создан (возможно уже существует или неактивен)');
							$process = NULL;
						}
					}
				}
				break;
			default:
				break;
		}
	}

	$projects = $connection->execute('select cs_project.*, cs_account.name as authorname from cs_project, cs_account where cs_project.author_id = cs_account.id order by cs_project.id, cs_project.name');
	foreach ($projects as $project) {
		print "<tr>";
		print "<td align=\"center\">".$project['id']."</td>";
		print "<td title=\"".$project['description']."\">".$project['name']."</td>";
		print "<td width=\"10%\" align=\"center\" class=\"small\">".$project['authorname']."</td>";
		print "<td width=\"15%\" align=\"center\" class=\"small\" ".($project['activated_at']?"title=\"Активирован: ".$project['activated_at']."\"":"&nbsp;").">".$project['created_at']."</td>";
		print "<td width=\"5%\" align=\"center\">".(($project['is_active'] == 1)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "<td width=\"5%\" align=\"center\">".(($project['is_permanent'] == 1)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		print "<td width=\"5%\" align=\"center\">".(($project['is_system'] == 1)?"<img src=\"images/ok.png\" />":"<img src=\"images/cancel.png\" />")."</td>";
		if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
			print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."roles/list&project_id=".$project['id']."\"><img src=\"images/roles.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
			print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."properties/list&project_id=".$project['id']."\"><img src=\"images/list.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
			print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."processes/list&project_id=".$project['id']."\"><img src=\"images/processes.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_read']) {
			print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."graph&project_id=".$project['id']."\"><img src=\"images/memory.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_admin']) {
			print "<td width=\"3%\" align=\"center\"><a href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=create_project_instance&project_id=".$project['id']."', '_top', true);\"><img src=\"images/play.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
			print "<td width=\"3%\" align=\"center\"><a href=\"?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."edit&action=change&project_id=".$project['id']."\"><img src=\"images/edit_icon.png\" /></a></td>";
		}
		if ($user_permissions[getParentModule()][getChildModule()]['can_delete']) {
			print "<td width=\"3%\" align=\"center\"><a href=\"javascript:confirmIt('?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=delete&project_id=".$project['id']."', '_top', true);\"><img src=\"images/delete_icon.png\" /></a></td>";
		}
		print "</tr>\n";
	}
	?>
	</td>
	</tr>
	<?php if ($user_permissions[getParentModule()][getChildModule()]['can_write']): ?>
	<tr>
		<th colspan="13" align="center">&nbsp;</th>
		<th align="center"><a
			href="?module=<?= getParentModule(); ?>/<?= getChildModule(); ?>/edit&action=add"><img
			src="images/create_icon.png" /></a></th>
	</tr>
	<?php endif; ?>
</table>
	<?php endif; ?>
