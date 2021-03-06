<?php
/*
 * Base class; DO NOT EDIT
 *
 * auto-generated by the sfDoctrine plugin
 */
class CsFile extends Doctrine_Record
{
  
  
  public function setTableDefinition()
  {
    $this->setTableName('cs_file');

    $this->hasColumn('parent_id', 'integer', 20, array ());
    $this->hasColumn('name', 'string', 250, array ());
    $this->hasColumn('description', 'string', 4000, array ());
    $this->hasColumn('created_at', 'timestamp', null, array ());
    $this->hasColumn('updated_at', 'timestamp', null, array ());
    $this->hasColumn('owner_id', 'integer', 11, array ());
    $this->hasColumn('updated_by', 'integer', 11, array ());
    $this->hasColumn('is_folder', 'boolean', null, array (  'default' => false,));
    $this->hasColumn('is_deleted', 'boolean', null, array (  'default' => false,));
    $this->hasColumn('mime', 'string', 150, array ());
  }
  

  
  public function setUp()
  {
    $this->hasOne('CsFile as CsFile', array('local' => 'parent_id', 'foreign' => 'id'));
    $this->hasOne('CsAccount as CsAccount', array('local' => 'owner_id', 'foreign' => 'id'));
    $this->hasOne('CsAccount as CsAccount', array('local' => 'updated_by', 'foreign' => 'id'));
    $this->hasMany('CsFile as CsFiles', array('local' => 'id', 'foreign' => 'parent_id'));
    $this->hasMany('CsFileBlob as CsFileBlobs', array('local' => 'id', 'foreign' => 'file_id'));
    $this->hasMany('CsFilePermission as CsFilePermissions', array('local' => 'id', 'foreign' => 'file_id'));
  }
  
}
