<?php
/*
 * Base class; DO NOT EDIT
 *
 * auto-generated by the sfDoctrine plugin
 */
class CsDirectoryBlob extends Doctrine_Record
{
  
  
  public function setTableDefinition()
  {
    $this->setTableName('cs_directory_blob');

    $this->hasColumn('value_id', 'integer', 20, array ());
    $this->hasColumn('blob', 'clob', null, array ());
  }
  

  
  public function setUp()
  {
    $this->hasOne('CsDirectoryValue as CsDirectoryValue', array('local' => 'value_id', 'foreign' => 'id'));
  }
  
}
