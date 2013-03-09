<?php

namespace MultipleFileUpload\Model\Log;

use \Nette\Environment;
use \MultipleFileUpload\Model\BaseQueuesModel;

class Queues extends BaseQueuesModel {

	/**
	 * Initializes driver
	 */
	function initialize() {
		Environment::getService('Nette\Logger')->logMessage("initialize");
	}

	/**
	 * Getts queue
	 * @param string $token
	 * @return Queue
	 */
	function getQueue($token) {
		Environment::getService('Nette\Logger')->logMessage("getQueue");
		$q = new Queue();
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