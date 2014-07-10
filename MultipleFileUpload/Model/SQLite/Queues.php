<?php

/**
 * This file is part of the MultipleFileUpload (https://github.com/jkuchar/MultipleFileUpload/)
 *
 * Copyright (c) 2013 Jan Kuchař (http://www.jankuchar.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace MultipleFileUpload\Model\SQLite;

use MultipleFileUpload\Model\BaseQueues,
	MultipleFileUpload\Model\IQueue,
	Nette\Environment,
	Nette\InvalidStateException;

class Queues extends BaseQueues
{
	/**
	 * @var \SQLiteDatabase
	 */
	private $connection;

	/**
	 * Path to SQLite database file
	 * @var string|null
	 */
	public static $databasePath;

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


	// <editor-fold defaultstate="collapsed" desc="Database functions">

	function getConnection()
	{
		if (!$this->connection) {
			$this->connection = $this->openDB();

			// load database
			if (filesize(self::$databasePath) == 0) {
				//$this->beginTransaction();
				$this->connection->queryExec(file_get_contents(dirname(__FILE__) . "/setupDB.sql"), $error);
				if ($error) {
					throw new InvalidStateException("Can't create SQLite database: " . $error);
				}
				//$this->endTransaction();
			}
		}
		return $this->connection;
	}


	/**
	 * Executes query
	 * @param string $sql
	 * @return SQLiteResult
	 * @throws InvalidStateException
	 */
	function query($sql)
	{
		$r = $this->getConnection()->query($sql, SQLITE_ASSOC, $error);
		if ($error) {
			throw new InvalidStateException("Can't execute queury: '" . $sql . "'. error: " . $error);
		}
		return $r;
	}


	/* function beginTransaction() {
	  $this->query("BEGIN TRANSACTION");
	  }

	  function endTransaction() {
	  $this->query("END TRANSACTION");
	  } */

	/**
	 * Open SQLite file
	 * @return SQLiteDatabase
	 * @throws InvalidStateException
	 */
	function openDB()
	{

		if (!($connection = new \SQLiteDatabase(self::$databasePath, 0777, $error))) {
			throw new InvalidStateException("Can't create sqlite database: " . $error);
		}

		return $connection;
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
		$this->query("BEGIN TRANSACTION");
		foreach ($this->getQueues() AS $queue) {
			if ($queue->getLastAccess() < time() - $this->getLifeTime()) {
				$queue->delete();
			}
		}
		$this->query("END TRANSACTION");

		// physically delete files marked for deletion
		$this->query("VACUUM");
	}


	/**
	 * Gets all queues
	 * @return IQueue[]
	 */
	function getQueues()
	{
		$queuesOut = array();
		$qs = $this->query("SELECT queueID
		  FROM files
		  GROUP BY queueID")->fetchAll();

		foreach ($qs AS $row) {
			$queuesOut[] = $this->createQueueObj($row["queueID"]);
		}

		return $queuesOut;
	}


	static function init()
	{
		// TODO: remove this magic
		$config = Environment::getConfig("MultipleFileUploader", array(
				"databasePath" => dirname(__FILE__) . "/database.sdb",
				"uploadsTempDir" => ""
		));

		foreach ($config AS $key => $val) {
			self::$$key = $val;
		}
	}


}
Queues::init();
