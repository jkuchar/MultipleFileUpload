<?php

/**
 * This file is part of the MultipleFileUpload (https://github.com/jkuchar/MultipleFileUpload/)
 *
 * Copyright (c) 2014 Ciki (https://github.com/Ciki)
 * Copyright (c) 2014 Jan Kuchař (http://www.jankuchar.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace MultipleFileUpload\Model\SQLite3;

use MultipleFileUpload\Model\BaseQueues,
	MultipleFileUpload\Model\IQueue,
	Nette\Environment,
	Nette\InvalidStateException,
	SQLite3,
	SQLite3Result,
	SQLite3Stmt;

class Queues extends BaseQueues
{
	/**
	 * @var \SQLite3
	 */
	private $connection;

	/**
	 * Path to SQLite3 database file
	 * @var string|null
	 */
	public static $databasePath;

	/**
	 * Path to directory of uploaded files (temp)
	 * @var string
	 */
	public static $uploadsTempDir;


	/**
	 * Initialize driver
	 */
	function initialize()
	{

	}


	// <editor-fold defaultstate="collapsed" desc="Database functions">

	function getConnection()
	{
		if (!$this->connection) {
			$this->connection = $this->openDB();

			// Nahraj databázi
			if (filesize(self::$databasePath) === 0) {
				//$this->beginTransaction();
				$res = $this->connection->query(file_get_contents(__DIR__ . '/setupDB.sql'));
				if (!$res) {
					throw new InvalidStateException("Can't create SQLite3 database: " . $this->getConnection()->lastErrorMsg());
				}
				//$this->endTransaction();
			}
		}
		return $this->connection;
	}


	/**
	 * Execute query
	 * @param string $sql
	 * @return SQLite3Result
	 * @throws InvalidStateException
	 */
	function query($sql)
	{
		$res = $this->getConnection()->query($sql);
		if (!$res) {
			throw new InvalidStateException("Can't execute query: '" . $sql . "'. error: " . $this->getConnection()->lastErrorMsg());
		}
		return $res;
	}


	/**
	 * Prepare query
	 * @param string $sql
	 * @return SQLite3Stmt
	 * @throws InvalidStateException
	 */
	function prepare($sql)
	{
		$res = $this->getConnection()->prepare($sql);
		if (!$res) {
			throw new InvalidStateException("Can't prepare query: '" . $sql . "'. error: " . $this->getConnection()->lastErrorMsg());
		}
		return $res;
	}


	/**
	 * Open SQLite file
	 * @return SQLite3
	 * @throws InvalidStateException
	 */
	function openDB()
	{

		if (!($connection = new SQLite3(self::$databasePath))) {
			throw new InvalidStateException("Can't create sqlite3 database: ");
		}

		return $connection;
	}


	// </editor-fold>

	/**
	 * Get queue (create if needed)
	 * @param string $id
	 * @return Queue
	 */
	function getQueue($id)
	{
		return $this->createQueueObj($id);
	}


	/**
	 * Factory for Queue
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
	 * Execute cleanup
	 */
	function cleanup()
	{
		$this->query('BEGIN TRANSACTION');
		foreach ($this->getQueues() AS $queue) {
			if ($queue->getLastAccess() < time() - $this->getLifeTime()) {
				$queue->delete();
			}
		}
		$this->query('END TRANSACTION');

		// physically delete files marked for deletion
		$this->query('VACUUM');
	}


	/**
	 * Get all queues
	 * @return IQueue[]
	 */
	function getQueues()
	{
		$queuesOut = array();
		$res = $this->query('
            SELECT queueID
            FROM files
            GROUP BY queueID
        ');

		while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
			$queuesOut[] = $this->createQueueObj($row['queueID']);
		}

		return $queuesOut;
	}


	static function init()
	{
		// TODO: remove this magic
		$config = Environment::getConfig('MultipleFileUploader', array(
				'databasePath' => dirname(__FILE__) . '/database.sqlite3',
				'uploadsTempDir' => ''
		));

		foreach ($config AS $key => $val) {
			self::$$key = $val;
		}
	}


}
Queues::init();
