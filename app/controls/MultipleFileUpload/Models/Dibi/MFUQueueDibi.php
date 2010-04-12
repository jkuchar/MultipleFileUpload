<?php

/**
 * Multiple File Uploader driver for Dibi
 *
 * @author  Martin Sadový (SodaE), Jan Kuchař (honzakuchar)
 * @license New BSD License
 */
class MFUQueueDibi extends MFUBaseQueueModel {

	/**
	 * Executes query
	 * @return DibiResult
	 */
	function query() {
		$params = func_get_args(); // arguments
		return call_user_func_array(
			array($this->getQueuesModel(), 'query'), // what
			$params
		);
	}

	/**
	 * Adds file to queue
	 * @param HttpUploadedFile $file
	 */
	function addFile(HttpUploadedFile $file) {
		$file->move($this->getUniqueFilePath());
		//Debug::dump(serialize($file));
		$data = array(
			'queueID%s' => $this->getQueueID(),
			'created%i' => time(),
			'data%s'    => base64_encode(serialize($file)) // workaround: http://forum.dibiphp.com/cs/1003-pgsql-a-znak-x00-oriznuti-zbytku-vstupu
		);

		$this->query('INSERT INTO [files]', $data);
	}

	/**
	 * Getts upload directory path
	 * @return string
	 */
	function getUploadedFilesTemporaryPath() {
		if(!MFUQueuesDibi::$uploadsTempDir) {
			MFUQueuesDibi::$uploadsTempDir = Environment::expand("%tempDir%".DIRECTORY_SEPARATOR."uploads-MFU");
		}

		if(!file_exists(MFUQueuesDibi::$uploadsTempDir)) {
			mkdir(MFUQueuesDibi::$uploadsTempDir,0777,true);
		}

		if(!is_writable(MFUQueuesDibi::$uploadsTempDir)) {
			MFUQueuesDibi::$uploadsTempDir = Environment::expand("%tempDir%");
		}

		if(!is_writable(MFUQueuesDibi::$uploadsTempDir)) {
			throw new InvalidStateException("Directory for temp files is not writable!");
		}

		return MFUQueuesDibi::$uploadsTempDir;
	}

	/**
	 * Getts files
	 * @return array of HttpUploadedFile
	 */
	function getFiles() {
		$files = array();

		foreach($this->query('SELECT * FROM [files] WHERE [queueID] = %s', $this->getQueueID())->fetchAll() as $row) {
			$files[] = unserialize(base64_decode($row->data)); // workaround: http://forum.dibiphp.com/cs/1003-pgsql-a-znak-x00-oriznuti-zbytku-vstupu
		}

		return $files;
	}

	function delete() {
		$dir = realpath($this->getUploadedFilesTemporaryPath());
		foreach($this->getFiles() AS $file) {
			$fileDir = dirname($file->getTemporaryFile());
			if(realpath($fileDir) == $dir and file_exists($file->getTemporaryFile())) {
				// Soubor smažeme poze pokud zůstal ve složce s tempy.
				// Pokud ho už uživatel přesunul, tak mu ho mazat nebudeme.
				@unlink($file->getTemporaryFile()); // intentionally @
			}
		}

		$this->query("DELETE FROM [files] WHERE [queueID] = %s", $this->getQueueID());
	}

	/**
	 * When was queue last accessed?
	 * @return int timestamp
	 */
	function getLastAccess() {
		$lastAccess = (int)$this->query(
			"SELECT [created]
			FROM [files]
			WHERE [queueID] = %s", $this->getQueueID(),
			"ORDER BY [created] DESC"
			)->fetchSingle();
		return $lastAccess;
	}

}