<?php

class MFUQueueSQLite extends MFUBaseQueueModel {

	/**
	 * Executes query
	 * @param string $sql
	 * @return SQLiteResult
	 */
	function query($sql) {
		return $this->getQueuesModel()->query($sql);
	}

	/**
	 * Adds file to queue
	 * @param HttpUploadedFile $file
	 */
	function addFile(HttpUploadedFile $file) {
		$file->move($this->getUniqueFilePath());
		$this->query('INSERT INTO files (queueID, created, data) VALUES ("'.sqlite_escape_string($this->getQueueID()).'",'.time().',\''.sqlite_escape_string(serialize($file)).'\')');
	}

	/**
	 * Getts upload directory path
	 * @return string
	 */
	function getUploadedFilesTemporaryPath() {
		if(!MFUQueuesSQLite::$uploadsTempDir) {
			MFUQueuesSQLite::$uploadsTempDir = Environment::expand("%tempDir%".DIRECTORY_SEPARATOR."uploads-MFU");
		}

		if(!file_exists(MFUQueuesSQLite::$uploadsTempDir)) {
			mkdir(MFUQueuesSQLite::$uploadsTempDir,0777,true);
		}

		if(!is_writable(MFUQueuesSQLite::$uploadsTempDir)) {
			MFUQueuesSQLite::$uploadsTempDir = Environment::expand("%tempDir%");
		}

		if(!is_writable(MFUQueuesSQLite::$uploadsTempDir)) {
			throw new InvalidStateException("Directory for temp files is not writable!");
		}

		return MFUQueuesSQLite::$uploadsTempDir;
	}

	/**
	 * Getts files
	 * @return array of HttpUploadedFile
	 */
	function getFiles() {
		$out = array();
		$files = $this->query("SELECT * FROM files WHERE queueID = '".sqlite_escape_string($this->getQueueID())."'")->fetchAll();
		foreach($files AS $row) {
			$out[] = unserialize($row["data"]);
		}
		return $out;
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

		$this->query("DELETE FROM files  WHERE queueID = '".sqlite_escape_string($this->getQueueID())."'");
	}

	/**
	 * When was queue last accessed?
	 * @return int timestamp
	 */
	function getLastAccess() {
		$lastAccess = (int)$this->query("SELECT lastAccess FROM queues WHERE queueID = '".sqlite_escape_string($this->getQueueID())."'")->fetchSingle();
		return $lastAccess;
	}

}