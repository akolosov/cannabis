<?php
	if (defined("ACTION")) {
		switch (ACTION) {

			case "changeproperty" :
				if ((defined('TYPE_ID')) and (defined('VALUE_ID')) and ((defined('X_PROPERTY_VALUE')) or (is_array($parameters['X_PROPERTY_VALUE'])) or (is_array($_FILES['x_property_value'])))) {
					if (TYPE_ID <> Constants::PROPERTY_TYPE_OBJECT) {
						$value = new PropertyValue($engine, VALUE_ID);
						if (MULTIPLE == 'true') {
							$value->setProperty('value', implode('||', $parameters['X_PROPERTY_VALUE']));
						} else {
							$value->setProperty('value', X_PROPERTY_VALUE);
						}
						$value = NULL;
					} else {
						if (is_array($_FILES['x_property_value']) and (in_array($_FILES['x_property_value']['type'], $mime_names)) and ($_FILES['x_property_value']['size'] <= MAX_FILE_SIZE) and (is_uploaded_file($_FILES['x_property_value']['tmp_name']))) {
							$value = new PropertyValue($engine, VALUE_ID);
							if (file_exists(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.($value->getFileName()))) {
								unlink(FILE_CACHE_PATH.DIRECTORY_SEPARATOR.($value->getFileName()));
							}

							$value->setProperty('mime_type', $_FILES['x_property_value']['type'].'|'.stripNonAlpha(USER_NAME).'_('.strftime("%d_%m_%Y_%H_%M", time()).')_'.basename($_FILES['x_property_value']['name']));
							$value->setProperty('value', NULL);
							
							$blob = Blob::getBlob($value, VALUE_ID);
							$blob->setProperty('blob', base64_encode(file_get_contents($_FILES['x_property_value']['tmp_name'])));
							$blob->save();

							move_uploaded_file($_FILES['x_property_value']['tmp_name'], FILE_CACHE_PATH.DIRECTORY_SEPARATOR.stripNonAlpha(USER_NAME).'_('.strftime("%d_%m_%Y_%H_%M", time()).')_'.basename($_FILES['x_property_value']['name']));
							
							$blob = NULL;
							$value = NULL;
						}
					}
				}
				break;

			case "changeaction" :
				if (defined('ACTION_ID')) {
					$action = $engine->getConnection()->getTable('CsProcessCurrentAction')->find(ACTION_ID);

					if (isNotNULL($action)) {
						$action['status_id'] = (isNotNULL($_POST['x_action_status_'.ACTION_ID])?$_POST['x_action_status_'.ACTION_ID]:NULL);
						$action['initiator_id'] = (isNotNULL($_POST['x_action_initiator_'.ACTION_ID])?$_POST['x_action_initiator_'.ACTION_ID]:NULL);
						$action['performer_id'] = (isNotNULL($_POST['x_action_performer_'.ACTION_ID])?$_POST['x_action_performer_'.ACTION_ID]:NULL);
						$action['started_at'] = (isNotNULL($_POST['x_action_started_'.ACTION_ID])?$_POST['x_action_started_'.ACTION_ID]:NULL);
						$action['ended_at'] = (isNotNULL($_POST['x_action_ended_'.ACTION_ID])?$_POST['x_action_ended_'.ACTION_ID]:NULL);
						$action->save();
					}
				}
				break;

			default:
				break;
		}
	}
				
?>