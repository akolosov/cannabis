<?php

// $Id$

class RecordsDebugger extends Doctrine_Record_Listener {
	public function preInsert(Doctrine_Event $event) {
		$event->start();
		logDebug('[SQL: preInsert] inserting a record');
	}

	public function postInsert(Doctrine_Event $event) {
		global $parameters;

		$event->end();
		logDebug('[SQL: postInsert] record inserted ('.round($event->getElapsedSecs(), 3).'sec)');

		$parameters['insertqueriescount']++;
		$parameters['totalqueriescount']++;
	}

	public function preUpdate(Doctrine_Event $event) {
		$event->start();
		logDebug('[SQL: preUpdate] updating record');
	}

	public function postUpdate(Doctrine_Event $event) {
		global $parameters;

		$event->end();
		logDebug('[SQL: postUpdate] record updated ('.round($event->getElapsedSecs(), 3).'sec)');

		$parameters['updatequeriescount']++;
		$parameters['totalqueriescount']++;
	}

	public function preDelete(Doctrine_Event $event) {
		$event->start();
		logDebug('[SQL: preDelete] deleting record');
	}

	public function postDelete(Doctrine_Event $event) {
		global $parameters;

		$event->end();
		logDebug('[SQL: postDelete] record deleted ('.round($event->getElapsedSecs(), 3).'sec)');

		$parameters['deletequeriescount']++;
		$parameters['totalqueriescount']++;
	}

	public function preSave(Doctrine_Event $event) {
		$event->start();
		logDebug('[SQL: preSave] saving record ('.round($event->getElapsedSecs(), 3).'sec)');
	}

	public function postSave(Doctrine_Event $event) {
		$event->end();
		logDebug('[SQL: postSave] record saved ('.round($event->getElapsedSecs(), 3).'sec)');
	}

	public function preValidate(Doctrine_Event $event) {
		$event->start();
		logDebug('[SQL: preValidate] validating record');
	}

	public function postValidate(Doctrine_Event $event) {
		$event->end();
		logDebug('[SQL: postValidate] record validated ('.round($event->getElapsedSecs(), 3).'sec)');
	}

}

class QueriesDebugger extends Doctrine_EventListener {
	public function preConnect(Doctrine_Event $event) {
		$event->start();
		logDebug('[SQL: preConnect] connecting to DB');
	}

	public function postConnect(Doctrine_Event $event) {
		$event->end();
		logDebug('[SQL: postConnect] connected ('.round($event->getElapsedSecs(), 3).'sec)');
	}

	public function preQuery(Doctrine_Event $event) {
		$event->start();
		logDebug('[SQL: preQuery] executing query: '.$event->getQuery());
	}

	public function postQuery(Doctrine_Event $event) {
		global $parameters;

		$event->end();
		logDebug('[SQL: postQuery] query executed ('.round($event->getElapsedSecs(), 3).'sec)');

		$parameters['selectqueriescount']++;
		$parameters['totalqueriescount']++;
	}

	public function preExec(Doctrine_Event $event) {
		$event->start();
		logDebug('[SQL: preExec] executing query: '.$event->getQuery());
	}

	public function postExec(Doctrine_Event $event) {
		global $parameters;

		$event->end();
		logDebug('[SQL: postExec] query executed ('.round($event->getElapsedSecs(), 3).'sec)');

		$parameters['selectqueriescount']++;
		$parameters['totalqueriescount']++;
	}

	public function prePrepare(Doctrine_Event $event) {
		$event->start();
		logDebug('[SQL: prePrepare] preparing query: '.$event->getQuery());
	}

	public function postPrepare(Doctrine_Event $event) {
		$event->end();
		logDebug('[SQL: postPrepare] query prepared ('.round($event->getElapsedSecs(), 3).'sec)');
	}

	public function preStmtExecute(Doctrine_Event $event) {
		$event->start();
		logDebug('[SQL: preStmtExecute] executing query: '.$event->getQuery());
	}

	public function postStmtExecute(Doctrine_Event $event) {
		global $parameters;

		$event->end();
		logDebug('[SQL: postStmtExecute] query executed ('.round($event->getElapsedSecs(), 3).'sec)');

		$parameters['selectqueriescount']++;
		$parameters['totalqueriescount']++;
	}

	public function preExecute(Doctrine_Event $event) {
		$event->start();
		logDebug('[SQL: preExecute] executing query: '.$event->getQuery());
	}

	public function postExecute(Doctrine_Event $event) {
		global $parameters;

		$event->end();
		logDebug('[SQL: postExecute] query executed ('.round($event->getElapsedSecs(), 3).'sec)');

		$parameters['selectqueriescount']++;
		$parameters['totalqueriescount']++;
	}

	public function preFetch(Doctrine_Event $event) {
		$event->start();
		logDebug('[SQL: preFetch] fetching record: '.$event->getQuery());
	}

	public function postFetch(Doctrine_Event $event) {
		$event->end();
		logDebug('[SQL: postFetch] record fetched ('.round($event->getElapsedSecs(), 3).'sec)');
	}

	public function preFetchAll(Doctrine_Event $event) {
		$event->start();
		logDebug('[SQL: preFetchAll] fetching all records: '.$event->getQuery());
	}

	public function postFetchAll(Doctrine_Event $event) {
		$event->end();
		logDebug('[SQL: postFetchAll] all records fetched ('.round($event->getElapsedSecs(), 3).'sec)');
	}

	public function preHydrate(Doctrine_Event $event) {
		$event->start();
		logDebug('[SQL: preHydrate] hydrating query: '.$event->getQuery());
	}

	public function postHydrate(Doctrine_Event $event) {
		$event->end();
		logDebug('[SQL: postHydrate] query hydrated ('.round($event->getElapsedSecs(), 3).'sec)');
	}

	public function preBuildQuery(Doctrine_Event $event) {
		$event->start();
		logDebug('[SQL: preBuildQuery] building query: '.$event->getQuery());
	}

	public function postBuildQuery(Doctrine_Event $event) {
		$event->end();
		logDebug('[SQL: postBuildQuery] query builded ('.round($event->getElapsedSecs(), 3).'sec)');
	}
}

?>