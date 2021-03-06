<?php
/*
 * Base class; DO NOT EDIT
 *
 * auto-generated by the sfDoctrine plugin
 */
class CsEvent extends Doctrine_Record
{
  
  
  public function setTableDefinition()
  {
    $this->setTableName('cs_event');

    $this->hasColumn('name', 'string', 150, array ());
    $this->hasColumn('description', 'string', 4000, array ());
  }
  

  
  public function setUp()
  {
    $this->hasMany('CsProcessActionTransport as CsProcessActionTransports', array('local' => 'id', 'foreign' => 'event_id'));
    $this->hasMany('CsProcessTransport as CsProcessTransports', array('local' => 'id', 'foreign' => 'event_id'));
  }
  
}
