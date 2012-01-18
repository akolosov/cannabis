<?php

// $Id$

	class Account extends Core {

		public	$_account = array();

		function __construct($owner = NULL, $id = 0) {
			if ($owner <> NULL) {
				parent::__construct($owner, $id, $owner->getConnection());

				$account = $this->getConnection()->execute('select * from accounts_tree where id = '.$this->_id)->fetch();
				foreach ($account as $key => $data) {
					$this->_account[$key] = $data;
				}

				$this->_account['[permissions]']	= new Permission($this, $this->getProperty('permission_id'));
				$this->_account['[divisions]']		= new Collection($this);
				$this->_account['[posts]']			= new Collection($this);
				$this->_account['[maindivision]']	= NULL;
				
				
				$divisions = $this->getConnection()->execute('select * from divisions_tree where id in (select division_id from cs_account_division where account_id = '.$this->_id.') order by level')->fetchAll();
				foreach ($divisions as $division) {
					$this->_account['[divisions]']->setElement($division['name'], new Division($this, $division['id'], array('data' => $division)));
					if ($this->getProperty('division_id') == $division['id']) {
						$this->_account['[maindivision]']	= new Division($this, $this->getProperty('division_id'), array('data' => $division));
					}
				}

				$posts = $this->getConnection()->execute('select * from cs_post where id in (select post_id from cs_account_post where account_id = '.$this->_id.')')->fetchAll();
				foreach ($posts as $post) {
					$this->_account['[posts]']->setElement($post['name'], new Post($this, $post['id'], array('data' => $post)));
				}

				$this->_account['[model]']		= "CsAccount";
			}
		}

		function getProperty($name) {
			return $this->_account[$name];
		}

		function getDivision($name) {
			return $this->_account['[divisions]']->getElement($name);
		}

		function getDivisions() {
			return $this->_account['[divisions]']->getElements();
		}

		function getPost($name) {
			return $this->_account['[posts]']->getElement($name);
		}

		function getPosts() {
			return $this->_account['[posts]']->getElements();
		}

		function getLowerDivision() {
			return $this->_account['[divisions]']->getLastElement();
		}

		function getMainDivision() {
			return (is_null($this->_account['[maindivision]'])?$this->getLowerDivision():$this->_account['[maindivision]']);
		}

		function getHigherDivision() {
			return $this->_account['[divisions]']->getFirstElement();
		}

		function getDivisionsList() {
			$result = array();
			foreach ($this->getDivisions() as $division) {
				$result[] = (isNotNULL($division->getProperty('id'))?$division->getProperty('id'):0);
			}
			return $result;
		}

		function getPostsList() {
			$result = array();
			foreach ($this->getPosts() as $post) {
				$result[] = (isNotNULL($post->getProperty('id'))?$post->getProperty('id'):0);
			}
			return $result;
		}

		function getPermissionsName() {
			return $this->_account['[permissions]']->getProperty('name');
		}
		
		function getPermissions() {
			$permissionsarray = array();

			$modules = $this->getConnection()->execute("select * from modules_tree order by parent_id, id, caption")->fetchAll();
			foreach ($modules as $module) {
				$permissions = $this->getConnection()->execute('select * from permissions_list where module_id = '.$module['id'].' and permission_id = '.$this->getProperty('permission_id').' order by id')->fetchAll();
				foreach ($permissions as $permission) {
					$permissionsarray[$module['parentname']]['name'] = $module['name'];
					$permissionsarray[$module['parentname']]['display'] = $module['parentcaption'];
					$permissionsarray[$module['parentname']]['is_hidden'] = $module['is_hidden'];
					$permissionsarray[$module['parentname']][$module['name']] = array(
										'can_read'		=> (empty($permission['can_read'])?false:true),
										'can_write'		=> (empty($permission['can_write'])?false:true),
										'can_delete'	=> (empty($permission['can_delete'])?false:true),
										'can_admin'		=> (empty($permission['can_admin'])?false:true),
										'can_review'	=> (empty($permission['can_review'])?false:true),
										'can_observe'	=> (empty($permission['can_observe'])?false:true),
										'is_hidden'		=> $permission['is_hidden'],
										'display'		=> ($module['caption']?$module['caption']:$module['name']),
										'description'	=> $module['description'],
										'name'			=> $module['name']);
				}
			}			
			return $permissionsarray;
		}
	}
?>