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
		$this->query('INSERT INTO files (queueID, created, data, name) VALUES ("'.sqlite_escape_string($this->getQueueID()).'",'.time().',\''.sqlite_escape_string(serialize($file)).'\', \''.sqlite_escape_string($file->getName()).'\')');
	}

	function addFileManually($name, $chunk,$chunks) {
		$this->query('INSERT INTO files (queueID, created, name, chunk, chunks) VALUES ("'.sqlite_escape_string($this->getQueueID()).'",'.time().',\''.sqlite_escape_string($name).'\', \''.sqlite_escape_string($chunk).'\', \''.sqlite_escape_string($chunks).'\')');
	}

	function updateFile($name, $chunk, HttpUploadedFile $file = null) {
		$this->query("BEGIN TRANSACTION");
		$where = 'queueID = \''.sqlite_escape_string($this->getQueueID()).'\' AND name = \''.sqlite_escape_string($name).'\'';
		$this->query('UPDATE files SET chunk = \''.sqlite_escape_string($chunk).'\' WHERE '.$where);
		if($file) {
			$this->query('UPDATE files SET data = \''.sqlite_escape_string(serialize($file)).'\' WHERE '.$where);
		}
		$this->query("END TRANSACTION");
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
		$files = array();

		foreach($this->query("SELECT * FROM files WHERE queueID = '".sqlite_escape_string($this->getQueueID())."'")->fetchAll() AS $row) {
			$f = unserialize($row["data"]);
			if(!$f instanceof HttpUploadedFile) continue;
			$files[] = $f;
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

		$this->query("DELETE FROM files  WHERE queueID = '".sqlite_escape_string($this->getQueueID())."'");
	}

	/**
	 * When was queue last accessed?
	 * @return int timestamp
	 */
	function getLastAccess() {
		$lastAccess = (int)$this->query("SELECT created
			FROM files
			WHERE queueID = '".sqlite_escape_string($this->getQueueID())."'
			ORDER BY created DESC")->fetchSingle();
		return $lastAccess;
	}

}