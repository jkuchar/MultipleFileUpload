<?php

/**
 * Multiple File Uploader driver for Dibi
 *
 * @author  Martin Sadový (SodaE), Jan Kuchař (honzakuchar)
 * @license New BSD License
 */
class MFUQueuesDibi extends MFUBaseQueuesModel {

	/**
	 * Path to director of uploaded files (temp)
	 * @var string
	 */
	public static $uploadsTempDir;

	/**
	 * Connection
	 * @var DibiConnection
	 */
	public static $dibiConnection;

	/**
	 * Initializes driver
	 */
	function initialize() {

	}

	// <editor-fold defaultstate="collapsed" desc="Database functions">

	/**
	 * Getts dibi connection
	 * @return DibiConnection
	 */
	function getConnection() {
		if(!self::$dibiConnection) {
			self::$dibiConnection = dibi::getConnection();
		}
		return self::$dibiConnection;
	}

	/**
	 * Executes query
	 * @return DibiResult
	 * @throws InvalidStateException
	 */
	function query() {
		$params = func_get_args(); // arguments
		return call_user_func_array(
			array($this->getConnection(), 'query'),
			$params
		);
	}

	// </editor-fold>

	/**
	 * Getts queue (if needed create)
	 * @param string $id
	 * @return MFUQueueDibi
	 */
	function getQueue($id) {
		return $this->createQueueObj($id);
	}

	/**
	 * Factory for MFUQueueDibi
	 * @param string $queueID
	 * @return MFUQueueDibi
	 */
	function createQueueObj($queueID) {
		$queue = new MFUQueueDibi();
		$queue->setQueuesModel($this);
		$queue->setQueueID($queueID);
		$queue->initialize();
		return $queue;
	}

	/**
	 * Executes cleanup
	 */
	function cleanup() {
		$this->getConnection()->begin();
		foreach($this->getQueues() AS $queue) {
			if($queue->getLastAccess() < time() - $this->getLifeTime()) {
				$queue->delete();
			}
		}
		$this->getConnection()->commit();
	}

	/**
	 * Getts all queues
	 * @return array of IMFUQueueModel
	 */
	function getQueues() {
		$queuesOut = array();
		$qs = $this->query("SELECT [queueID]
		  FROM [files]
		  GROUP BY [queueID]")->fetchAll();

		foreach($qs AS $row) {
			$queuesOut[] = $this->createQueueObj($row["queueID"]);
		}
		return $queuesOut;
	}
}