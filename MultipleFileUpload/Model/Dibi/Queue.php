<?php

/**
 * This file is part of the MultipleFileUpload (https://github.com/jkuchar/MultipleFileUpload/)
 *
 * Copyright (c) 2013 Jan Kuchař (http://www.jankuchar.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace MultipleFileUpload\Model\Dibi;

use dibi,
	MultipleFileUpload\Model\BaseQueue,
	Nette\Environment,
	Nette\Http\FileUpload,
	Nette\InvalidStateException;

/**
 * Multiple File Uploader driver for Dibi
 *
 * @author  Martin Sadový (SodaE), Jan Kuchař (honzakuchar)
 * @license New BSD License
 */
class Queue extends BaseQueue
{

	/**
	 * Executes query
	 * @return DibiResult
	 */
	function query()
	{
		$params = func_get_args();
		return call_user_func_array(
			array($this->getQueuesModel(), 'query'),
			$params
		);
	}


	/**
	 * Adds file to queue
	 * @param FileUpload $file
	 */
	function addFile(FileUpload $file)
	{
		$file->move($this->getUniqueFilePath());
		$data = array(
			'queueID%s' => $this->getQueueID(),
			'created%i' => time(),
			'data%s' => base64_encode(serialize($file)), // workaround: http://forum.dibiphp.com/cs/1003-pgsql-a-znak-x00-oriznuti-zbytku-vstupu
			'name%s' => $file->getName()
		);

		$this->query('INSERT INTO [files]', $data);
	}


	function addFileManually($name, $chunk, $chunks)
	{

		$data = array(
			'queueID%s' => $this->getQueueID(),
			'created%i' => time(),
			'name%s' => $name,
			'chunk%i' => $chunk,
			'chunks%i' => $chunks
		);

		$this->query('INSERT INTO [files]', $data);
	}


	function updateFile($name, $chunk, FileUpload $file = null)
	{
		dibi::begin();
		$where = array(
			"queueID%s" => $this->getQueueID(),
			"name%s" => $name
		);
		$this->query('UPDATE files SET ', array("chunk" => $chunk), 'WHERE %and', $where);
		if ($file) {
			$this->query('UPDATE files SET ', array("data" => base64_encode(serialize($file))), 'WHERE %and', $where); // workaround: http://forum.dibiphp.com/cs/1003-pgsql-a-znak-x00-oriznuti-zbytku-vstupu
		}
		dibi::commit();
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

		foreach ($this->query('SELECT * FROM [files] WHERE [queueID] = %s', $this->getQueueID())->fetchAll() as $row) {
			$f = unserialize(base64_decode($row["data"]));
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
				// Delete only files left in temp directory [not moved by user]
				@unlink($file->getTemporaryFile()); // intentionally @
			}
		}

		$this->query("DELETE FROM [files] WHERE [queueID] = %s", $this->getQueueID());
	}


	/**
	 * When was queue last accessed?
	 * @return int timestamp
	 */
	function getLastAccess()
	{
		$lastAccess = (int) $this->query(
				"SELECT [created]
			FROM [files]
			WHERE [queueID] = %s", $this->getQueueID(), "ORDER BY [created] DESC"
			)->fetchSingle();
		return $lastAccess;
	}


}