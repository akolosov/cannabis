<?php
/*
 * Base class; DO NOT EDIT
 *
 * auto-generated by the sfDoctrine plugin
 */
class CsCalendarEvent extends Doctrine_Record
{
  
  
  public function setTableDefinition()
  {
    $this->setTableName('cs_calendar_event');

    $this->hasColumn('calendar_id', 'integer', 11, array ());
    $this->hasColumn('status_id', 'integer', 11, array ());
    $this->hasColumn('author_id', 'integer', 11, array ());
    $this->hasColumn('started_at', 'timestamp', null, array ());
    $this->hasColumn('ended_at', 'timestamp', null, array ());
    $this->hasColumn('subject', 'string', 250, array ());
    $this->hasColumn('event', 'string', 4000, array ());
    $this->hasColumn('is_periodic', 'boolean', null, array ());
    $this->hasColumn('priority_id', 'integer', 11, array ());
    $this->hasColumn('created_at', 'timestamp', null, array ());
    $this->hasColumn('is_deleted', 'boolean', null, array (  'default' => false,));
    $this->hasColumn('is_erased', 'boolean', null, array (  'default' => false,));
    $this->hasColumn('parent_id', 'integer', 20, array ());
  }
  

  
  public function setUp()
  {
    $this->hasOne('CsCalendar as CsCalendar', array('local' => 'calendar_id', 'foreign' => 'id'));
    $this->hasOne('CsEventStatus as CsEventStatus', array('local' => 'status_id', 'foreign' => 'id'));
    $this->hasOne('CsAccount as CsAccount', array('local' => 'author_id', 'foreign' => 'id'));
    $this->hasOne('CsEventPriority as CsEventPriority', array('local' => 'priority_id', 'foreign' => 'id'));
    $this->hasOne('CsCalendarEvent as CsCalendarEvent', array('local' => 'parent_id', 'foreign' => 'id'));
    $this->hasMany('CsCalendarEvent as CsCalendarEvents', array('local' => 'id', 'foreign' => 'parent_id'));
    $this->hasMany('CsCalendarEventAlarm as CsCalendarEventAlarms', array('local' => 'id', 'foreign' => 'event_id'));
    $this->hasMany('CsCalendarEventBlob as CsCalendarEventBlobs', array('local' => 'id', 'foreign' => 'event_id'));
    $this->hasMany('CsCalendarEventPeriod as CsCalendarEventPeriods', array('local' => 'id', 'foreign' => 'event_id'));
    $this->hasMany('CsCalendarEventReciever as CsCalendarEventRecievers', array('local' => 'id', 'foreign' => 'event_id'));
  }
  
}
