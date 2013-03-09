<?php

use Nette\Environment;

namespace MultipleFileUpload;

class QueuesLog extends BaseQueuesModel {

	/**
	 * Initializes driver
	 */
	function initialize() {
		Environment::getService('Nette\Logger')->logMessage("initialize");
	}

	/**
	 * Getts queue
	 * @param string $token
	 * @return QueueSQLite
	 */
	function getQueue($token) {
		Environment::getService('Nette\Logger')->logMessage("getQueue");
		$q = new QueueLog();
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