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

use DibiConnection,
	MultipleFileUpload\Model\BaseQueues,
	MultipleFileUpload\Model\IQueue;

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
	public $uploadsTempDir;

	/**
	 * Connection
	 * @var DibiConnection
	 */
	public $connection;


	/**
	 * Initializes driver
	 */
	function initialize()
	{

	}

	public function __construct(string $tempDir, DibiConnection $conection)
	{
		$this->uploadsTempDir = $tempDir;
		if(!file_exists($this->uploadsTempDir)) {
			mkdir($this->uploadsTempDir, 0775, TRUE);
		}
		$this->conection = $conection;
	}

	/**
	 * @return DibiConnection 
	 */
	public function getConnection()
	{
		return $this->database;
	}
	
	
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
		$queue = new Queue(); // todo: remove setQueuesModel --> move to __construct
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
		$this->connection->begin();
		foreach ($this->getQueues() AS $queue) {
			if ($queue->getLastAccess() < time() - $this->getLifeTime()) {
				$queue->delete();
			}
		}
		$this->connection->commit();
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
