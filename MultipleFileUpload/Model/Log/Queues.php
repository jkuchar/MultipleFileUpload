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

use MultipleFileUpload\Model\BaseQueues,
	Tracy\Debugger;

class Queues extends BaseQueues
{

	/**
	 * Initializes driver
	 */
	function initialize()
	{
		$a = func_get_args();
		Debugger::log(__CLASS__ . ": " . __METHOD__ . "; args: " . print_r($a, true));
	}


	/**
	 * Gets queue
	 * @param string $token
	 * @return Queue
	 */
	function getQueue($token)
	{
		$a = func_get_args();
		Debugger::log(__CLASS__ . ": " . __METHOD__ . "; args: " . print_r($a, true));

		$q = new Queue();
		$q->setQueueID($token);
		$q->setQueuesModel($this);
		return $q;
	}


	function getQueues()
	{
		$a = func_get_args();
		Debugger::log(__CLASS__ . ": " . __METHOD__ . "; args: " . print_r($a, true));
	}


	/**
	 * Executes cleanup
	 */
	function cleanup()
	{
		$a = func_get_args();
		Debugger::log(__CLASS__ . ": " . __METHOD__ . "; args: " . print_r($a, true));
	}


}