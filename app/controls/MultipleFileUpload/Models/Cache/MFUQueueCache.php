<?php

/**
 * @property-read string $token Token of the queue
 */
class MFUQueueCache extends LockableObject implements IMFUQueueModel {

	/**
	 * Directory for temporary uploads
	 * @var string
	 */
	public static $uploadFileDirectory = "%tempDir%/MultipleFileUpload";

	/**
	 * When $uploadFileDirectory has not been writable. System will write directly to %tempDir%
	 * @var bool
	 */
	public static $allowWriteToRootOfTemp = TRUE;

	/**
	 * Token of the queue
	 * @var string
	 */
	private $token;

	/**
	 * MFU queues model
	 * @var IMFUQueuesModel
	 */
	private $queuesModel;



	function  __construct($token,IMFUQueuesModel $MFUQM) {
		$this->token = $token;
		$this->queuesModel = $MFUQM;
	}

	/**
	 * Getts token
	 * @return string
	 */
	function getToken() {
		return $this->token;
	}

	/**
	 * Getts files
	 * @return array
	 */
	function getFiles() {
		return $this->getQueueData(false);
	}

	/**
	 * Adds file to queue
	 * @param HttpUploadedFile $file
	 */
	function addFile(HttpUploadedFile $file) {
		
		// Pokud ještě nebyl přesunut (pokud by nebyl přesunut, byl by PHPkem automaticky smazán)
		$file->move($this->getUniqueFilePath());

		$data = $this->getQueueData();
		$data[] = $file;
		$this->saveQueueData($data);
	}

	static function getCache() {
		return Environment::getCache("MultipleFileUpload");
	}

	/**
	 * Getts cache
	 * @return Cache
	 */
	protected function getQueueData($lock=true) {
		if($lock) $this->lock();
		$cache = self::getCache();
		return $cache[$this->token];
	}

	protected function saveQueueData($data) {
		$cache = self::getCache();
		$cache[$this->token] = $data;
		$this->unlock();

		$this->setAccessTime(time());
	}


	/**
	 * Returns unique file name
	 *
	 * self::$token must be set!
	 *
	 * @return string
	 */
	protected function getUniqueFilePath() {
		return $this->getDirectory() . DIRECTORY_SEPARATOR . "upload-" . $this->token  ."-" . uniqid() . ".tmp";
	}


	public function getDirectory() {
		$dir = Environment::expand(self::$uploadFileDirectory);

		// Vytvoříme složku a ověříme jestli je zapisovatelná
		if(!file_exists($dir)) {
			mkdir($dir,0777,true);
		}

		if(!is_writable($dir) and self::$allowWriteToRootOfTemp) {
			$dir = self::$uploadFileDirectory = Environment::expand("%tempDir%");
		}

		if(!is_writable($dir)) {
			throw new InvalidStateException($dir." is not writable!");
		}

		return $dir;
	}

	public function delete() {
		$this->lock();
		//Environment::getService('Nette\Logger')
		//	->logMessage("delete queue: ".$this->getToken());

		$dir = $this->getDirectory();
		foreach($this->getFiles() AS $file) {
			//if($file instanceof HttpUploadedFile) { // Platí vždy - jen kvůli IDE
			$fileDir = dirname($file->getTemporaryFile());
			if($fileDir == $dir and file_exists($file->getTemporaryFile())) {
				// Soubor smažeme poze pokud zůstal ve složce s tempy.
				// Pokud ho už uživatel přesunul, tak mu ho mazat nebudeme.
				@unlink($file->getTemporaryFile()); // intentionally @
			}
			//}
		}

		$cache = self::getCache();

		$this->queuesModel->lock();
		$queues = $cache["queues"];
		unset ($queues[$this->getToken()]);
		$cache["queues"] = $queues;
		$this->queuesModel->unlock();

		unset($cache[$this->getToken()]);
		$this->unlock();
	}

	public function getLockKey(){
		return $this->token."-lock";
	}

	public function getAccessTime() {
		$cache = self::getCache();
		$queues = $cache["queues"];
		return $queues[$this->token];
	}

	public function setAccessTime($time=null) {
		if(!$time) $time = time();
		$cache = self::getCache();

		$this->queuesModel->lock();
		$queues = $cache["queues"];
		$queues[$this->token] = $time;
		$cache["queues"] = $queues;
		$this->queuesModel->unlock();
	}

}