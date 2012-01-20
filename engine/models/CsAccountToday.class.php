<?php
/*
 * Base class; DO NOT EDIT
 *
 * auto-generated by the sfDoctrine plugin
 */
class CsAccountToday extends Doctrine_Record
{
  
  
  public function setTableDefinition()
  {
    $this->setTableName('cs_account_today');

    $this->hasColumn('account_id', 'integer', 11, array ());
    $this->hasColumn('process_instance_id', 'integer', 20, array ());
    $this->hasColumn('action_instance_id', 'integer', 20, array ());
    $this->hasColumn('status_id', 'integer', 11, array ());
    $this->hasColumn('started_at', 'timestamp', null, array ());
    $this->hasColumn('ended_at', 'timestamp', null, array ());
    $this->hasColumn('confirm', 'boolean', null, array (  'default' => true,));
  }
  

  
  public function setUp()
  {
    $this->hasOne('CsAccount as CsAccount', array('local' => 'account_id', 'foreign' => 'id'));
    $this->hasOne('CsProcessInstance as CsProcessInstance', array('local' => 'process_instance_id', 'foreign' => 'id'));
    $this->hasOne('CsProcessCurrentAction as CsProcessCurrentAction', array('local' => 'action_instance_id', 'foreign' => 'id'));
    $this->hasOne('CsStatus as CsStatus', array('local' => 'status_id', 'foreign' => 'id'));
  }
  
}