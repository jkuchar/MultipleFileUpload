<?php


class MFUQueuesLog extends MFUBaseQueuesModel {

	/**
	 * Initializes driver
	 */
	function initialize() {
		Environment::getService('Nette\Logger')->logMessage("initialize");
	}

	/**
	 * Getts queue
	 * @param string $token
	 * @return MFUQueueSQLite
	 */
	function getQueue($token) {
		Environment::getService('Nette\Logger')->logMessage("getQueue");
		$q = new MFUQueueLog();
		$q->setQueueID($token);
		$q->setQueuesModel($this);
		return $q;
	}

	function getQueues() {
		Environment::getService('Nette\Logger')->logMessage("getQueues");
	}

	/**
	 * Executes cleanup
	 */
	function cleanup() {
		Environment::getService('Nette\Logger')->logMessage("cleanup");
	}

}