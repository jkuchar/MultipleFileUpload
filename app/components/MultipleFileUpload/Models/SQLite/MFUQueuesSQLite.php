<?php


class MFUQueuesSQLite extends MFUBaseQueuesModel {

	/**
	 * @var SQLiteDatabase
	 */
	private $connection;

	/**
	 * Path to SQLite database file
	 * @var string|null
	 */
	public static $databasePath;

	/**
	 * Path to director of uploaded files (temp)
	 * @var string
	 */
	public static $uploadsTempDir;

	/**
	 * Initializes driver
	 */
	function initialize() {

	}

	// <editor-fold defaultstate="collapsed" desc="Database functions">

	function getConnection() {
		if(!$this->connection) {
			$this->connection = $this->openDB();

			// Nahraj databázi
			if(filesize(self::$databasePath) == 0) {
				//$this->beginTransaction();
				$this->connection->queryExec(file_get_contents(dirname(__FILE__)."/setupDB.sql"),$error);
				if($error) {
					throw new InvalidStateException("Can't create SQLite database: ".$error);
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
	function query($sql) {
		$r = $this->getConnection()->query($sql,SQLITE_ASSOC, $error);
		if($error) {
			throw new InvalidStateException("Can't execute queury: '".$sql."'. error: ".$error);
		}
		return $r;
	}

	/*function beginTransaction() {
		$this->query("BEGIN TRANSACTION");
	}

	function endTransaction() {
		$this->query("END TRANSACTION");
	}*/

	/**
	 * Open SQLite file
	 * @return SQLiteDatabase
	 * @throws InvalidStateException
	 */
	function openDB() {

		if(!($connection = new SQLiteDatabase(self::$databasePath, 0777, $error))) {
			throw new InvalidStateException("Can't create sqlite database: ".$error);
		}

		return $connection;
	}
	// </editor-fold>

	/**
	 * Getts queue (if needed create)
	 * @param string $id
	 * @return MFUQueueSQLite
	 */
	function getQueue($id) {
		return $this->createQueueObj($id);
	}

	/**
	 * Factory for MFUQueueSQLite
	 * @param string $queueID
	 * @return MFUQueueSQLite
	 */
	function createQueueObj($queueID) {
		$queue = new MFUQueueSQLite();
		$queue->setQueuesModel($this);
		$queue->setQueueID($queueID);
		$queue->initialize();
		return $queue;
	}

	/**
	 * Executes cleanup
	 */
	function cleanup() {
		$this->query("BEGIN TRANSACTION");
		foreach($this->getQueues() AS $queue) {
			if($queue->getLastAccess() < time() - $this->getLifeTime()) {
				$queue->delete();
			}
		}
		$this->query("END TRANSACTION");

		// Jedou za čas - promaže fyzicky smazané řádky
		$this->query("VACUUM");
	}

	/**
	 * Getts all queues
	 * @return array of IMFUQueueModel
	 */
	function getQueues() {
		$queuesOut = array();
		$qs = $this->query("SELECT queueID
		  FROM files
		  GROUP BY queueID")->fetchAll();

		foreach($qs AS $row) {
			$obj = $queuesOut[] = $this->createQueueObj($row["queueID"]);
		}

		return $queuesOut;
	}

	static function init() {
		$config =\Nette\Environment::getConfig("MultipleFileUploader",array(
			"databasePath" => dirname(__FILE__)."/database.sdb",
			"uploadsTempDir" => ""
		));

		foreach($config AS $key => $val) {
			self::$$key = $val;
		}
	}

}

MFUQueuesSQLite::init();