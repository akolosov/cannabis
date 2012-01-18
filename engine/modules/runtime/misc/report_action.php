<?
  $where = array();
  $inwhere = array();
  $report_params = array();

  if ((count($parameters['X_PROCESS_ID']) > 0) and (trim(implode('', $parameters['X_PROCESS_ID'])) <> '')) {
   	$report_params['by_process_id'] = $parameters['X_PROCESS_ID'];
  	$where[] = 'project_processes_instances_list.process_id in ('.implode(', ', $report_params['by_process_id']).')';
  } else {
  	if ((REPORT_NAME == 'first') or (REPORT_NAME == 'third') or (REPORT_NAME == 'fourth') or (REPORT_NAME == 'fifth')) {
	   	$report_params['by_process_id'] = array(8, 9, 10, 13, 15);
	  	$where[] = 'project_processes_instances_list.process_id in ('.implode(', ', $report_params['by_process_id']).')';
 	} elseif (REPORT_NAME == 'sixth') {
	   	$report_params['by_process_id'] = array(14);
	  	$where[] = 'project_processes_instances_list.process_id in ('.implode(', ', $report_params['by_process_id']).')';
 	}
  }

  if ((count($parameters['X_PROJECT_ID']) > 0) and (trim(implode('', $parameters['X_PROJECT_ID'])) <> '')) {
   	$report_params['by_project_id'] = $parameters['X_PROJECT_ID'];
  	$where[] = 'project_processes_instances_list.project_instance_id in ('.implode(', ', $report_params['by_project_id']).')';
  }

  if (defined('X_PERIOD_FROM') and (trim(X_PERIOD_FROM) <> '')) {
  	$report_params['by_period_from'] = X_PERIOD_FROM;
  	$where[] = 'date(project_processes_instances_list.started_at) >= \''.X_PERIOD_FROM.'\'';
  }

  if (defined('X_PERIOD_TO') and (trim(X_PERIOD_TO) <> '')) {
  	$report_params['by_period_to'] = X_PERIOD_TO;
  	$where[] = 'date(project_processes_instances_list.started_at) <= \''.X_PERIOD_TO.'\'';
  }
  
  if ((count($parameters['X_INITIATOR_ID']) > 0) and (trim(implode('', $parameters['X_INITIATOR_ID'])) <> '')) {
  	$report_params['by_initiator_id'] = $parameters['X_INITIATOR_ID'];
  	$where[] = 'project_processes_instances_list.initiator_id in ('.implode(', ', $report_params['by_initiator_id']).')';
  }

  if ((count($parameters['X_PERFORMER_ID']) > 0) and (trim(implode('', $parameters['X_PERFORMER_ID'])) <> '')) {
   	$report_params['by_performer_id'] = $parameters['X_PERFORMER_ID'];
  	$inwhere[] = '(cs_process_current_action.performer_id in ('.implode(', ', $report_params['by_performer_id']).')) or (cs_process_current_action.initiator_id in ('.implode(', ', $report_params['by_performer_id']).'))';
  }

  if (defined('X_STATUS_ID') and (trim(X_STATUS_ID) <> '') and (REPORT_NAME <> 'third')) {
  	$report_params['by_status_id'] = X_STATUS_ID;
  	$where[] = 'project_processes_instances_list.status_id in ('.(X_STATUS_ID == 'completed'?Constants::PROCESS_STATUS_COMPLETED.', '.Constants::PROCESS_STATUS_CHILD_COMPLETED:Constants::PROCESS_STATUS_IN_PROGRESS.', '.Constants::PROCESS_STATUS_CHILD_IN_PROGRESS.', '.Constants::PROCESS_STATUS_WAITING.', '.Constants::PROCESS_STATUS_CHILD_WAITING).')';
  } elseif (REPORT_NAME == 'third') {
  	$where[] = 'project_processes_instances_list.status_id in ('.Constants::PROCESS_STATUS_IN_PROGRESS.', '.Constants::PROCESS_STATUS_CHILD_IN_PROGRESS.', '.Constants::PROCESS_STATUS_WAITING.', '.Constants::PROCESS_STATUS_CHILD_WAITING.')';
  } elseif (REPORT_NAME == 'fifth') {
  	$where[] = 'project_processes_instances_list.status_id in ('.Constants::PROCESS_STATUS_IN_PROGRESS.', '.Constants::PROCESS_STATUS_CHILD_IN_PROGRESS.', '.Constants::PROCESS_STATUS_WAITING.', '.Constants::PROCESS_STATUS_CHILD_WAITING.', '.Constants::PROCESS_STATUS_COMPLETED.', '.Constants::PROCESS_STATUS_CHILD_COMPLETED.')';
  }
  
  if (defined('X_GROUP_BY') and (trim(X_GROUP_BY) <> '')) {
  	$report_params['by_group'] = X_GROUP_BY;
  }

?>
