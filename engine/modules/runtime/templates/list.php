<?php if ($user_permissions[getParentModule()][getChildModule()]['can_read']): ?>
<?php require_once(COMMON_MODULES_PATH.DIRECTORY_SEPARATOR."public.php"); ?>
<?php
  if (defined("ACTION")) {
	switch (ACTION) {
		case "create" :
			if (defined('PROJECT_INSTANCE_ID') && defined('PROCESS_ID')) {
				if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
					$result = $connection->execute('select create_process_instance('.PROJECT_INSTANCE_ID.', '.PROCESS_ID.', '.USER_CODE.', NULL);')->fetch();
					$process = new Process($engine, PROCESS_ID, false);
					logMessage('создан экземпляр документа "'.$process->getProperty('name').'" (идентификатор экземпляра: '.$result['create_process_instance'].')');
					if (isNotNULL($result['create_process_instance'])) {
						setLocation("/?module=runtime/drafts/list&action=execute&process_instance_id=".$result['create_process_instance']."&project_instance_id=".PROJECT_INSTANCE_ID); 
					}
					$process = NULL;
				}
			}
			break;

		default:
			break;
	}
  }
  
  function printTemplateItem($project_id, $parent_id = 0, $instance_id = 0) {
	global $connection, $user_permissions;

	$processes = $connection->execute('select * from project_active_processes_tree where (project_id = '.$project_id.' and parent_id'.($parent_id == 0?" is null":" = ".$parent_id).') order by id, name')->fetchAll();
	foreach ($processes as $process) {
		print "<tr><td width=\"98%\" title=\"".str_replace("\"", "'", $process['description'])."\"".(($user_permissions[getParentModule()][getChildModule()]['can_write'])?" class=\"bold_small action\" onClick=\"confirmItMessage('Новый документ будет добавлен в \'Черновики\' пока Вы его не отправите дальше! Создать новый документ \'".$process['name']."\'?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=create&project_instance_id=".$instance_id."&project_id=".$process['project_id']."&process_id=".$process['id']."');\"":"")."><a href=\"#\"></a>".$process['name']."</td>";
		print "<td width=\"16\" align=\"right\">";
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
 			print "<span class=\"small action\" title=\"Создать новый документ '".$process['name']."'\" onClick=\"confirmItMessage('Новый документ будет добавлен в \'Черновики\' пока Вы его не отправите дальше! Создать новый документ \'".$process['name']."\'?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=create&project_instance_id=".$instance_id."&project_id=".$process['project_id']."&process_id=".$process['id']."');\"><img src=\"images/add.png\" /></span>";
 		}
		print "</td></tr>\n";
	}
  }
  
  function printPublicTemplateItem($project_id = 0, $parent_id = 0, $instance_id = 0) {
	global $connection, $user_permissions;

	$processes = $connection->execute('select * from public_active_processes_tree where (parent_id'.($parent_id == 0?" is null":" = ".$parent_id).') order by id, name')->fetchAll();
	foreach ($processes as $process) {
		print "<tr><td width=\"98%\" title=\"".str_replace("\"", "'", $process['description'])."\"".(($user_permissions[getParentModule()][getChildModule()]['can_write'])?" class=\"bold_small action\" onClick=\"confirmItMessage('Новый документ будет добавлен в \'Черновики\' пока Вы его не отправите дальше! Создать новый документ \'".$process['name']."\'?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=create&project_instance_id=".$instance_id."&project_id=".$process['project_id']."&process_id=".$process['id']."');\"":"")."><a href=\"#\"></a>".$process['name']."</td>";
		print "<td width=\"16\" align=\"right\">";
		if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
 			print "<span class=\"small action\" title=\"Создать новый документ '".$process['name']."'\" onClick=\"confirmItMessage('Новый документ будет добавлен в \'Черновики\' пока Вы его не отправите дальше! Создать новый документ \'".$process['name']."\'?', '?module=".getParentModule().DIRECTORY_SEPARATOR.getChildModule().DIRECTORY_SEPARATOR."list&action=create&project_instance_id=".$instance_id."&project_id=".$project_id."&process_id=".$process['id']."');\"><img src=\"images/add.png\" /></span>";
 		}
		print "</td></tr>\n";
	}
  }
?>
<?php
  $projects = $connection->execute('select * from projects_instances where is_active = true'.(defined('PROJECT_INSTANCE_ID')?' and id = '.PROJECT_INSTANCE_ID:'').(($user_permissions[getParentModule()][getChildModule()]['can_admin'])?'':' and is_system = false').' and (project_id in (select project_id from cs_project_role where division_id in ('.implode(', ', $engine->getAccount()->getDivisionsList()).'))) order by id')->fetchAll();
  print "<div class=\"caption\">Шаблоны документов".(defined('PROJECT_INSTANCE_ID')?": ".$projects[0]['name']:"")."</div>\n";
  print "<ul class=\"tree\" id=\"projects_tree\" style=\" display : none; \">\n";
  foreach ($projects as $project) {

	print "<li class=\"roottreeitem\">";
	print "<a href=\"#\"></a>Предприятие: ".$project['name']." (".$project['description'].")";
	if ($user_permissions[getParentModule()][getChildModule()]['can_write']) {
		print "<ul>\n";
		print "<li class=\"treeitem\">";
		print "<table width=\"100%\" cellspacing=\"1\" cellpadding=\"1\" border=\"0\" class=\"treeitem\">";
		printTemplateItem($project['project_id'], 0, $project['id']);
		printPublicTemplateItem($project['project_id'], 0, $project['id']);
		print "</table>";
		print "</li>\n";
		print "</ul>\n";
	}
  	print "</li>\n";
  }
  print "</ul>\n";
?>
 </table>
<script>
<!--
	var projects_tree_options = {
			'theme' : { 'name' : 'SimpleTree' },
			closeSiblings : false,
			maxOpenDepth : 2,
			flagClosedClass : 'close',
			toggleMenuOnClick : true,
			incrementalConvert : false,
			openTimeout : 0,
			closeTimeout: 0
	}
	var projects_tree = new CompleteMenuSolution;
	projects_tree.initMenu('projects_tree', projects_tree_options);
//-->
</script>
<?php endif; ?>
