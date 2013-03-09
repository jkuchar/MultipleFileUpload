<?php

/**
 * This file is part of the MultipleFileUpload (https://github.com/jkuchar/MultipleFileUpload/)
 *
 * Copyright (c) 2013 Jan Kuchař (http://www.jankuchar.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */


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