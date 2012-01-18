<?php
	print "<title>";
	if (MODULE == 'common/authorize') {
		print "[Общие/Авторизация]";
	} elseif (MODULE == 'common/authorized') {
		print "[Общие/Описание]";
	} elseif (MODULE == 'common/forum') {
		print "[Общие/Форум]";
	} else {
		print "[".$user_permissions[getParentModule()]['display']."/".$user_permissions[getParentModule()][getParentChildModule()]['display'].($user_permissions[getParentModule()][getModuleByLevel(3)]['display']?"/".$user_permissions[getParentModule()][getModuleByLevel(3)]['display']:"")."]";
	}
	print "</title>";
?>