<?php
/*
 * Base class; DO NOT EDIT
 *
 * auto-generated by the sfDoctrine plugin
 */
class CsEventPeriod extends Doctrine_Record
{
  
  
  public function setTableDefinition()
  {
    $this->setTableName('cs_event_period');

    $this->hasColumn('name', 'string', 150, array ());
    $this->hasColumn('description', 'string', 4000, array ());
  }
  

  
  public function setUp()
  {
    $this->hasMany('CsCalendarEventPeriod as CsCalendarEventPeriods', array('local' => 'id', 'foreign' => 'period_id'));
    $this->hasMany('CsEventPeriodCondition as CsEventPeriodConditions', array('local' => 'id', 'foreign' => 'period_id'));
  }
  
}