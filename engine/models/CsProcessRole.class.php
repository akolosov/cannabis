<?php
/*
 * Base class; DO NOT EDIT
 *
 * auto-generated by the sfDoctrine plugin
 */
class CsProcessRole extends Doctrine_Record
{
  
  
  public function setTableDefinition()
  {
    $this->setTableName('cs_process_role');

    $this->hasColumn('process_id', 'integer', 11, array ());
    $this->hasColumn('role_id', 'integer', 11, array ());
    $this->hasColumn('account_id', 'integer', 11, array ());
  }
  

  
  public function setUp()
  {
    $this->hasOne('CsProcess as CsProcess', array('local' => 'process_id', 'foreign' => 'id'));
    $this->hasOne('CsRole as CsRole', array('local' => 'role_id', 'foreign' => 'id'));
    $this->hasOne('CsAccount as CsAccount', array('local' => 'account_id', 'foreign' => 'id'));
    $this->hasMany('CsProcessAction as CsProcessActions', array('local' => 'id', 'foreign' => 'role_id'));
  }
  
}
