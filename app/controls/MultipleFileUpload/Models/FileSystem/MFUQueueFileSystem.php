<?php

/**
 * @property-read string $token Token of the queue
 */
class MFUQueueFileSystem extends Object implements IMFUQueueModel {

	/**
	 * Directory for temporary uploads
	 * @var string
	 */
	public static $uploadDirectory = "%tempDir%/MultipleFileUpload";
	
	public static $queueDirectory = "%tempDir%/queues/";

	/**
	 * When $uploadFileDirectory has not been writable. System will write directly to %tempDir%
	 * @var bool
	 */
	public static $allowWriteToRootOfTemp = TRUE;

	/**
	 * Token of the queue
	 * @var string
	 */
	protected $token;

	/**
	 * MFU queues model
	 * @var IMFUQueuesModel
	 */
	protected $queuesModel;



	function  __construct($token,IMFUQueuesModel $MFUQM) {
		$this->token = $token;
		$this->queuesModel = $MFUQM;

		if(!file_exists($this->getQueuePath())) {
			file_put_contents($this->getQueuePath(), serialize(array()));
		}
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
		return unserialize(file_get_contents($this->getQueuePath()));
	}

	function setFiles() {
		
	}

	/**
	 * Adds file to queue
	 * @param HttpUploadedFile $file
	 */
	function addFile(HttpUploadedFile $file) {
		// Pokud ještě nebyl přesunut (pokud by nebyl přesunut, byl by PHPkem automaticky smazán)
		$file->move($this->getUniqueUploadPath());

		$file = $this->getQueuePath();
		$data = $this->getFiles();
//		$data = $this->getQueueData();
//		$data[] = $file;
//		$this->saveQueueData($data);
	}

	protected function getUniqueUploadPath() {
		return "safe://" . $this->getDirectory("upload") . DIRECTORY_SEPARATOR . "upload-" . $this->token  ."-" . uniqid() . ".tmp";
	}

	protected function getQueuePath() {
		return "safe://" . $this->getDirectory("queue") . DIRECTORY_SEPARATOR . $this->token  .".queue";
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

		$queues = $cache["queues"];
		unset ($queues[$this->getToken()]);
		$cache["queues"] = $queues;

		unset($cache[$this->getToken()]);
		$this->unlock();
	}

	public function getAccessTime() {
		
	}

	public function setAccessTime($time=null) {

	}


	public function getDirectory($directory="upload") {
		$propName = $directory."Directory";
		$dir = Environment::expand(self::$$propName);

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
}

SafeStream::register();