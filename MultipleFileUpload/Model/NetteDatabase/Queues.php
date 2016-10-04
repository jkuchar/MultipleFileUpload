<?php

/**
 * This file is part of the MultipleFileUpload (https://github.com/jkuchar/MultipleFileUpload/)
 *
 * Copyright (c) 2013 Jan Kuchař (http://www.jankuchar.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace MultipleFileUpload\Model\NetteDatabase;

use MultipleFileUpload\Model\BaseQueues,
	MultipleFileUpload\Model\IQueue,
	Nette;

/**
 * Multiple File Uploader driver for Nette\Database
 *
 * @author  Zdeněk Jurka
 * @license New BSD License
 */
class Queues extends BaseQueues
{
	
	public static $filesTable = 'files';
		
	/**
	 * Path to directory of uploaded files (temp)
	 * @var string
	 */
	public static $uploadsTempDir;

	/**
	 * Initializes driver
	 */
	function initialize()
	{

	}

	/** @var Nette\Database\Context */
	private $database;
	
	
	public function getFilesTable(){
		return self::$filesTable;
	}
	
	public function __construct($container)
	{
		$parameters = $container->getParameters();
		self::$uploadsTempDir = $parameters['tempDir'];
		$temp = $parameters['tempDir'] . DIRECTORY_SEPARATOR . "uploads-MFU";
		$journal = new Nette\Caching\Storages\SQLiteJournal($temp);
		$cacheStorage = new Nette\Caching\Storages\FileStorage($temp, $journal);
		$structure = new Nette\Database\Structure($container->getService("database.default"), $cacheStorage);
		$this->database = new Nette\Database\Context($container->getService("database.default"), $structure);
	}
	
	// <editor-fold defaultstate="collapsed" desc="Database functions">

	/**
	 * @return \Nette\Database\Context 
	 */
	public function getConnection()
	{
		return $this->database;
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
		$this->getConnection()->beginTransaction();
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
		
		foreach ($this->getConnection()->table($this->getFilesTable())->select('queueID')->group('queueID') AS $row) {
			$queuesOut[] = $this->createQueueObj($row["queueID"]);
		}
		return $queuesOut;
	}


}