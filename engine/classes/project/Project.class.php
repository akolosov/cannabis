<?php

// $Id$

	class Project extends Core {

		public $_project = array();

		function __construct($owner = NULL, $id = 0, $options = array('data' => NULL)) {
			if (($id > 0) && ($owner <> NULL)) {

				parent::__construct($owner, $id, $owner->getConnection());

				if (is_null($options['data'])) {
					$project = $this->getConnection()->execute('select cs_project.*, cs_account.name as authorname from cs_project, cs_account where cs_project.author_id = cs_account.id and cs_project.id = '.$this->_id)->fetch();
				} else {
					$project = $options['data'];
				}
				foreach ($project as $key => $data) {
					$this->_project[$key] = $data;
				}

				$this->_project['[processes]']	= new Collection($this);
				$this->_project['[properties]']	= new Collection($this);
				$this->_project['[roles]']	= new Collection($this);
				$this->_project['[model]']	= "CsProject";

				$this->initProcesses();
				$this->initProperties();
				$this->initRoles();
				
				if (isNotNULL($this->getEngine()) and isNotNULL($this->getEngine()->getTemplate())) {
					$this->getEngine()->getTemplate()->setValueTo('PROJECT_DIVISIONS', implode(', ', $this->getDivisionsList()));
				}
			}
		}

		function getProperty($name) {
			return $this->_project[$name];
		}

		function getProjectProcess($name) {
			return $this->_project['[processes]']->getElement($name);
		}

		function getProjectProcesses() {
			return $this->_projects['[processes]']->getElements();
		}

		function getProjectProperty($name) {
			return $this->_project['[properties]']->getElement($name);
		}

		function getProjectProperties() {
			return $this->_projects['[properties]']->getElements();
		}
		
		function getDivisionsList() {
			$result = array();
			foreach ($this->_project['[roles]']->getElements() as $role) {
				$result[] = (isNotNULL($role->getProperty('division_id'))?$role->getProperty('division_id'):0);
			}
			return $result;
		}

		function initProcesses() {
			$processes = $this->getConnection()->execute('select * from project_processes_tree where project_id = '.$this->_id.' order by id')->fetchAll();
			foreach ($processes as $process) {
				$this->_project['[processes]']->setElement($process['name'], new Process($this, $process['id]'], array('data' => $process)));
			}
		}

		function initProperties() {
			$properties = $this->getConnection()->execute('select cs_project_property.*, cs_sign.name as signname, cs_property_type.name as typename from cs_project_property, cs_sign, cs_property_type where cs_project_property.sign_id = cs_sign.id and cs_project_property.type_id = cs_property_type.id and cs_project_property.project_id = '.$this->_id.' order by id')->fetchAll();
			foreach ($properties as $property) {
				$this->_project['[properties]']->setElement($property['name'], new ProjectProperty($this, $property['id]'], array('data' => $property)));
			}
		}

		function initRoles() {
			$roles = $this->getConnection()->execute('select * from project_roles where project_id = '.$this->_id)->fetchAll();
			foreach ($roles as $role) {
				$this->_project['[roles]']->setElement($role['rolename'], new ProjectRole($this, $role['id'], array('data' => $role)));
			}
		}
	}
?>