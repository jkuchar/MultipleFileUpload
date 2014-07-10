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

use MultipleFileUpload\Model\BaseQueue,
	Nette\Environment,
	Nette\Http\FileUpload,
	Nette\InvalidStateException,
	SQLite3,
	SQLite3Result,
	SQLite3Stmt;

class Queue extends BaseQueue
{

	/**
	 * Execute query
	 * @param string $sql
	 * @return SQLite3Result
	 */
	function query($sql)
	{
		return $this->getQueuesModel()->query($sql);
	}


	/**
	 * Prepare query
	 * @param string $sql
	 * @return SQLite3Stmt
	 */
	function prepare($sql)
	{
		return $this->getQueuesModel()->prepare($sql);
	}


	/**
	 * Add file to queue
	 * @param FileUpload $file
	 */
	function addFile(FileUpload $file)
	{
		$file->move($this->getUniqueFilePath());
		$this->query("
            INSERT INTO files (queueID, created, data, name) VALUES (
            '" . SQLite3::escapeString($this->getQueueID()) . "'," .
			time() . "," .
			"X'" . bin2hex(serialize($file)) . "'," .
			"'" . SQLite3::escapeString($file->getName()) . "')
        ");
	}


	// TODO: rename!!!
	/**
	 * Add file to queue manually
	 * @param FileUpload $file
	 * @param int $chunk
	 * @param int $chunks
	 */
	function addFileManually($name, $chunk, $chunks)
	{
		$this->query("
		    INSERT INTO files (queueID, created, name, chunk, chunks) VALUES (
		    '" . SQLite3::escapeString($this->getQueueID()) . "'," .
			time() . "," .
			"'" . SQLite3::escapeString($name) . "'," .
			SQLite3::escapeString($chunk) . "," .
			SQLite3::escapeString($chunks) . ")
        ");
	}


	/**
	 * Update file
	 * @param string $name
	 * @param int $chunk
	 * @param FileUpload $file
	 */
	function updateFile($name, $chunk, FileUpload $file = null)
	{
		$this->query("BEGIN TRANSACTION");
		$where = "queueID = '" . SQLite3::escapeString($this->getQueueID()) . "' AND name = '" . SQLite3::escapeString($name) . "'";
		$this->query("UPDATE files SET chunk = " . SQLite3::escapeString($chunk) . " WHERE " . $where);
		if ($file) {
			// use blob for storing serialized object, see https://bugs.php.net/bug.php?id=63419 and https://bugs.php.net/bug.php?id=62361
			$stmt = $this->prepare("UPDATE files SET data = :data WHERE " . $where);
			$stmt->bindValue(':data', serialize($file), SQLITE3_BLOB);
			$stmt->execute();
		}
		$this->query("END TRANSACTION");
	}


	/**
	 * Get upload directory path
	 * @return string
	 */
	function getUploadedFilesTemporaryPath()
	{
		if (!Queues::$uploadsTempDir) {
			Queues::$uploadsTempDir = Environment::expand("%tempDir%" . DIRECTORY_SEPARATOR . "uploads-MFU");
		}

		if (!file_exists(Queues::$uploadsTempDir)) {
			mkdir(Queues::$uploadsTempDir, 0777, true);
		}

		if (!is_writable(Queues::$uploadsTempDir)) {
			Queues::$uploadsTempDir = Environment::expand("%tempDir%");
		}

		if (!is_writable(Queues::$uploadsTempDir)) {
			throw new InvalidStateException("Directory for temp files is not writable!");
		}

		return Queues::$uploadsTempDir;
	}


	/**
	 * Get files
	 * @return FileUpload[]
	 */
	function getFiles()
	{
		$files = array();

		$result = $this->query("SELECT * FROM files WHERE queueID = '" . SQLite3::escapeString($this->getQueueID()) . "'");
		while (($row = $result->fetchArray(SQLITE3_ASSOC)) !== FALSE) {
			$f = unserialize($row["data"]);
			if (!$f instanceof FileUpload) {
				continue;
			}
			$files[] = $f;
		}
		return $files;
	}


	/**
	 * Delete temporary files
	 */
	function delete()
	{
		$dir = realpath($this->getUploadedFilesTemporaryPath());
		foreach ($this->getFiles() AS $file) {
			$fileDir = dirname($file->getTemporaryFile());
			if (realpath($fileDir) == $dir and file_exists($file->getTemporaryFile())) {
				// Delete file only if user leaved file in temp directory
				@unlink($file->getTemporaryFile()); // intentionally @
			}
		}

		$this->query("DELETE FROM files WHERE queueID = '" . SQLite3::escapeString($this->getQueueID()) . "'");
	}


	/**
	 * When was queue last accessed?
	 * @return int timestamp
	 */
	function getLastAccess()
	{
		$lastAccess = (int) $this->getQueuesModel()->getConnection()->querySingle("
		    SELECT created
				FROM files
				WHERE queueID = '" . SQLite3::escapeString($this->getQueueID()) . "'
				ORDER BY created DESC
		");
		return $lastAccess;
	}


}