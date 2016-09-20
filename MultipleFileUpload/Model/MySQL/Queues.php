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

namespace MultipleFileUpload\Model\MySQL;

use MultipleFileUpload\Model\BaseQueues;
use MultipleFileUpload\Model\IQueue;
use Nette\DI\Container;
use Nette\InvalidStateException;

class Queues extends BaseQueues
{
	/**
	 * @var Nette\Database\Connection
	 */
	private $connection;

	/**
	 * Path to directory of uploaded files (temp)
	 * @var string
	 */
	public static $uploadsTempDir;

    /** @var Container */
    private static $container;

    public function __construct(Container $container, \Nette\Database\Connection $connection)
    {
        $parameters = $container->getParameters();
        self::$container = $container;
        self::$uploadsTempDir = $parameters['tempDir'] . DIRECTORY_SEPARATOR . "uploads-MFU";
        $this->connection = $connection;
    }


    /**
	 * Initialize driver
	 */
	function initialize()
	{

	}


	function getConnection()
	{
		return $this->connection;
	}


	/**
	 * Execute query
	 * @param string $sql
	 * @return Nette\Database\ResultSet
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
	    $parameters = self::$container->getParameters();
		$queue = new Queue($parameters['tempDir']);
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
		$this->query('START TRANSACTION');
		foreach ($this->getQueues() AS $queue) {
			if ($queue->getLastAccess() < time() - $this->getLifeTime()) {
				//$queue->delete();
			}
		}
		$this->query('COMMIT WORK');
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

        foreach($res->fetchPairs('queueID') as $row) {
            $queuesOut[] = $this->createQueueObj($row['queueID']);
        }

		return $queuesOut;
	}

}
