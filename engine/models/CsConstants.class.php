<?php
/*
 * Base class; DO NOT EDIT
 *
 * auto-generated by the sfDoctrine plugin
 */
class CsConstants extends Doctrine_Record
{
  
  
  public function setTableDefinition()
  {
    $this->setTableName('cs_constants');

    $this->hasColumn('name', 'string', 150, array ());
    $this->hasColumn('description', 'string', 4000, array ());
    $this->hasColumn('value', 'string', 4000, array ());
    $this->hasColumn('fixed_name', 'boolean', null, array (  'default' => true,));
  }
  

  
  public function setUp()
  {
    
  }
  
}
