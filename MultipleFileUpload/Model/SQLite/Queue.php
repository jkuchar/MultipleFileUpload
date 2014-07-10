<?php

/**
 * This file is part of the MultipleFileUpload (https://github.com/jkuchar/MultipleFileUpload/)
 *
 * Copyright (c) 2013 Jan Kuchař (http://www.jankuchar.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace MultipleFileUpload\Model\SQLite;

use MultipleFileUpload\Model\BaseQueue,
	Nette\Environment,
	Nette\Http\FileUpload,
	Nette\InvalidStateException;

class Queue extends BaseQueue
{

	/**
	 * Executes query
	 * @param string $sql
	 * @return SQLiteResult
	 */
	function query($sql)
	{
		return $this->getQueuesModel()->query($sql);
	}


	/**
	 * Adds file to queue
	 * @param FileUpload $file
	 */
	function addFile(FileUpload $file)
	{
		$file->move($this->getUniqueFilePath());
		$this->query('INSERT INTO files (queueID, created, data, name) VALUES ("' . sqlite_escape_string($this->getQueueID()) . '",' . time() . ',\'' . sqlite_escape_string(serialize($file)) . '\', \'' . sqlite_escape_string($file->getName()) . '\')');
	}


	// TODO: rename!!!
	function addFileManually($name, $chunk, $chunks)
	{
		$this->query('INSERT INTO files (queueID, created, name, chunk, chunks) VALUES ("' . sqlite_escape_string($this->getQueueID()) . '",' . time() . ',\'' . sqlite_escape_string($name) . '\', \'' . sqlite_escape_string($chunk) . '\', \'' . sqlite_escape_string($chunks) . '\')');
	}


	function updateFile($name, $chunk, FileUpload $file = null)
	{
		$this->query("BEGIN TRANSACTION");
		$where = 'queueID = \'' . sqlite_escape_string($this->getQueueID()) . '\' AND name = \'' . sqlite_escape_string($name) . '\'';
		$this->query('UPDATE files SET chunk = \'' . sqlite_escape_string($chunk) . '\' WHERE ' . $where);
		if ($file) {
			$this->query('UPDATE files SET data = \'' . sqlite_escape_string(serialize($file)) . '\' WHERE ' . $where);
		}
		$this->query("END TRANSACTION");
	}


	/**
	 * Gets upload directory path
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
	 * Gets files
	 * @return FileUpload[]
	 */
	function getFiles()
	{
		$files = array();

		foreach ($this->query("SELECT * FROM files WHERE queueID = '" . sqlite_escape_string($this->getQueueID()) . "'")->fetchAll() AS $row) {
			$f = unserialize($row["data"]);
			if (!$f instanceof FileUpload)
				continue;
			$files[] = $f;
		}
		return $files;
	}


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

		$this->query("DELETE FROM files  WHERE queueID = '" . sqlite_escape_string($this->getQueueID()) . "'");
	}


	/**
	 * When was queue last accessed?
	 * @return int timestamp
	 */
	function getLastAccess()
	{
		$lastAccess = (int) $this->query("SELECT created
			FROM files
			WHERE queueID = '" . sqlite_escape_string($this->getQueueID()) . "'
			ORDER BY created DESC")->fetchSingle();
		return $lastAccess;
	}


}