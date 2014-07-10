<?php

/**
 * This file is part of the MultipleFileUpload (https://github.com/jkuchar/MultipleFileUpload/)
 *
 * Copyright (c) 2013 Jan Kuchař (http://www.jankuchar.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace MultipleFileUpload\Model\Dibi;

use dibi,
	MultipleFileUpload\Model\BaseQueues,
	MultipleFileUpload\Model\IQueue,
	Nette\InvalidStateException;

/**
 * Multiple File Uploader driver for Dibi
 *
 * @author  Martin Sadový (SodaE), Jan Kuchař (honzakuchar)
 * @license New BSD License
 */
class Queues extends BaseQueues
{
	/**
	 * Path to directory of uploaded files (temp)
	 * @var string
	 */
	public static $uploadsTempDir;

	/**
	 * Connection
	 * @var \DibiConnection
	 */
	public static $dibiConnection;


	/**
	 * Initializes driver
	 */
	function initialize()
	{

	}


	// <editor-fold defaultstate="collapsed" desc="Database functions">

	/**
	 * Gets dibi connection
	 * @return \DibiConnection
	 */
	function getConnection()
	{
		if (!self::$dibiConnection) {
			self::$dibiConnection = dibi::getConnection();
		}
		return self::$dibiConnection;
	}


	/**
	 * Executes query
	 * @return \DibiResult
	 * @throws InvalidStateException
	 */
	function query()
	{
		$params = func_get_args();
		return call_user_func_array(
			array($this->getConnection(), 'query'), $params
		);
	}


	// </editor-fold>

	/**
	 * Gets queue (create if needed)
	 * @param string $id
	 * @return Queue
	 */
	function getQueue($id)
	{
		return $this->createQueueObj($id);
	}


	/**
	 * Queue factory.
	 * @param string $queueID
	 * @return Queue
	 */
	function createQueueObj($queueID)
	{
		$queue = new Queue();
		$queue->setQueuesModel($this);
		$queue->setQueueID($queueID);
		$queue->initialize();
		return $queue;
	}


	/**
	 * Executes cleanup
	 */
	function cleanup()
	{
		$this->getConnection()->begin();
		foreach ($this->getQueues() AS $queue) {
			if ($queue->getLastAccess() < time() - $this->getLifeTime()) {
				$queue->delete();
			}
		}
		$this->getConnection()->commit();
	}


	/**
	 * Gets all queues
	 * @return IQueue[]
	 */
	function getQueues()
	{
		$queuesOut = array();
		$qs = $this->query("SELECT [queueID]
		  FROM [files]
		  GROUP BY [queueID]")->fetchAll();

		foreach ($qs AS $row) {
			$queuesOut[] = $this->createQueueObj($row["queueID"]);
		}
		return $queuesOut;
	}


}