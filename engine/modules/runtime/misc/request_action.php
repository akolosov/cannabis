<?
$where = array();
$inwhere = array();
$inprop = NULL;

if (!defined('CURRENT_LIMIT')) {
	define('CURRENT_LIMIT', (defined('X_PROCESS_LIMIT')?((X_PROCESS_LIMIT <> 0)?X_PROCESS_LIMIT:1000000):DEFAULT_LIMIT));
}

if (defined('X_PROCESS_NAME') and (trim(X_PROCESS_NAME) <> '')) {
	$where[] = 'project_processes_instances_list.process_id = '.X_PROCESS_NAME;
}

if (defined('X_PROJECT_NAME') and (trim(X_PROJECT_NAME) <> '')) {
	if (!define('PROJECT_INSTANCE_ID')) {
		define('PROJECT_INSTANCE_ID', X_PROJECT_NAME);
	}
}

if (defined('PROJECT_INSTANCE_ID') and (trim(PROJECT_INSTANCE_ID) <> '')) {
	if (!define('X_PROJECT_NAME')) {
		define('X_PROJECT_NAME', PROJECT_INSTANCE_ID);
	}
}

if (defined('X_PERIOD_FROM') and (trim(X_PERIOD_FROM) <> '')) {
	if (MODULE <> "runtime/today/list") {
		$where[] = 'date(project_processes_instances_list.started_at) >= \''.X_PERIOD_FROM.'\'';
	} else {
		$where[] = 'date(account_today_list.started_at) >= \''.X_PERIOD_FROM.'\'';
	}
}

if (defined('X_PERIOD_TO') and (trim(X_PERIOD_TO) <> '')) {
	if (MODULE <> "runtime/today/list") {
		$where[] = 'date(project_processes_instances_list.started_at) <= \''.X_PERIOD_TO.'\'';
	} else {
		$where[] = 'date(account_today_list.started_at) <= \''.X_PERIOD_TO.'\'';
	}
}

if (defined('X_PROCESS_INITIATOR') and (trim(X_PROCESS_INITIATOR) <> '')) {
	$where[] = 'project_processes_instances_list.initiator_id = '.X_PROCESS_INITIATOR;
}

if (defined('X_PROCESS_PERFORMER') and (trim(X_PROCESS_PERFORMER) <> '')) {
	$inwhere[] = 'cs_process_current_action.performer_id = '.X_PROCESS_PERFORMER;
}

if (defined('X_PROCESS_OWNER') and (trim(X_PROCESS_OWNER) <> '')) {
	$where[] = 'account_today_list.account_id = '.X_PROCESS_OWNER;
} elseif (MODULE == "runtime/today/list") {
	$where[] = 'account_today_list.account_id = '.USER_CODE;
	define('X_PROCESS_OWNER', USER_CODE);
}

if (defined('X_SEARCH_NUM') and (trim(X_SEARCH_NUM) <> '')) {
	$search_nums = preg_replace('/[;\.]/', ',', X_SEARCH_NUM);
	if (MODULE <> "runtime/today/list") {
		$where[] = "(project_processes_instances_list.id in (".$search_nums."))";
	} else {
		$where[] = "(account_today_list.process_instance_id in (".$search_nums."))";
	}
}

if (defined('X_SEARCH_VALUE') and (trim(X_SEARCH_VALUE) <> '')) {
	$search_value = preg_replace('/\s+/', '%', X_SEARCH_VALUE);
	if (MODULE <> "runtime/today/list") {
		$inprop = "(project_processes_instances_list.id in (select instance_id from process_instance_properties_list where (process_instance_properties_list.instance_id = project_processes_instances_list.id) and ((upper(process_instance_properties_list.value) like '%".mb_strtoupper($search_value)."%'))))";
	} else {
		$inprop = "(account_today_list.process_instance_id in (select instance_id from process_instance_properties_list where (process_instance_properties_list.instance_id = account_today_list.process_instance_id) and ((upper(process_instance_properties_list.value) like '%".mb_strtoupper($search_value)."%'))))";
	}
}
?>
