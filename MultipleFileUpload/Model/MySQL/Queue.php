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
	 * Add file to queue
	 * @param FileUpload $file
	 */
	function addFile(FileUpload $file)
	{
		$file->move($this->getUniqueFilePath());
		$this->getQueuesModel()->getConnection()->query("
            INSERT INTO files (queueID, created, data, name) VALUES (
            ?,?,?,?)", $this->getQueueID(), time(), "X'" . bin2hex(serialize($file)) . "'", $file->getName());
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
		$this->getQueuesModel()->getConnection()->query("
		    INSERT INTO files (queueID, created, name, chunk, chunks) VALUES (?, ?, ?, ?, ?)
        ", $this->getQueueID(), time(), $name, $chunk, $chunks);
	}


	/**
	 * Update file
	 * @param string $name
	 * @param int $chunk
	 * @param FileUpload $file
	 */
	function updateFile($name, $chunk, FileUpload $file)
	{
		$this->query("START TRANSACTION");
		$this->getQueuesModel()->getConnection()->query("UPDATE files SET chunk = ? WHERE queueID = ? AND name = ?", $chunk, $this->getQueueID(), $name);
		if ($file) {
			// use blob for storing serialized object, see https://bugs.php.net/bug.php?id=63419 and https://bugs.php.net/bug.php?id=62361
            //$blob = fopen($file->getTemporaryFile(), 'rb');
            $blob = serialize($file);
            $pdo = $this->getQueuesModel()->getConnection()->getPdo();
            $stmt = $pdo->prepare("UPDATE files SET data = :data WHERE queueID = :queueID AND name = :name;");
            $stmt->bindParam(':data', $blob, \PDO::PARAM_LOB);
            $queueID = $this->getQueueID();
            $stmt->bindParam(':queueID', $queueID);
            $stmt->bindParam(':name', $name);
            $stmt->execute();
		}
		$this->query("COMMIT WORK");
	}


	/**
	 * Get upload directory path
	 * @return string
	 */
	function getUploadedFilesTemporaryPath()
	{
		if (!Queues::$uploadsTempDir) {
            throw new InvalidStateException("Directory for temp files is not set.");
		}

		if (!file_exists(Queues::$uploadsTempDir)) {
			mkdir(Queues::$uploadsTempDir, 0777, true);
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
	    $files = [];
		foreach($this->getQueuesModel()->getConnection()->query("SELECT * FROM files WHERE queueID = ?", $this->getQueueID())->fetchPairs('id', 'data') as $row) {
		    if(($file = unserialize($row)) instanceof FileUpload) {
		        $files[] = $file;
            }
        };
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

		$this->getQueuesModel()->getConnection()->query("DELETE FROM files WHERE queueID = ?", $this->getQueueID());
	}


	/**
	 * When was queue last accessed?
	 * @return int timestamp
	 */
	function getLastAccess()
	{
		$lastAccess = (int) $this->getQueuesModel()->getConnection()->query("
		    SELECT created
				FROM files
				WHERE queueID = ?
				ORDER BY created DESC
		", $this->getQueueID())->fetch()->created;
		return $lastAccess;
	}


}