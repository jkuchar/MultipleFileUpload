<?php

/**
 * This file is part of the MultipleFileUpload (https://github.com/jkuchar/MultipleFileUpload/)
 *
 * Copyright (c) 2013 Jan Kuchař (http://www.jankuchar.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace MultipleFileUpload\Model\NetteDatabase;

use MultipleFileUpload\Model\BaseQueue,
	Nette\Environment,
	Nette\Http\FileUpload,
	Nette\InvalidStateException;

/**
 * Multiple File Uploader driver for Nette\Database
 *
 * @author  Zdeněk Jurka
 * @license New BSD License
 */
class Queue extends BaseQueue
{

	/**
	 * Adds file to queue
	 * @param FileUpload $file
	 */
	function addFile(FileUpload $file)
	{
		$file->move($this->getUniqueFilePath());
		$data = array(
			'queueID' => $this->getQueueID(),
			'created' => time(),
			'data' => base64_encode(serialize($file)), // workaround: http://forum.dibiphp.com/cs/1003-pgsql-a-znak-x00-oriznuti-zbytku-vstupu
			'name' => $file->getName()
		);
		$this->getConnection()->table($this->getFilesTable())->insert($data);
	}


	/**
	 * @return \Nette\Database\Context 
	 */
	protected function getConnection(){
		return $this->getQueuesModel()->getConnection();
	}
	
	protected function getFilesTable(){
		return $this->getQueuesModel()->getFilesTable();
	}
	
	function addFileManually($name, $chunk, $chunks)
	{
		$data = array(
			'queueID' => $this->getQueueID(),
			'created' => time(),
			'name' => $name,
			'chunk' => $chunk,
			'chunks' => $chunks
		);
		$this->getQueuesModel()->getConnection()->table($this->getFilesTable())->insert($data);
	}


	function updateFile($name, $chunk, FileUpload $file = null)
	{
		$this->getConnection()->beginTransaction();
		$selection = $this->getConnection()->table($this->getFilesTable())->where('queueID', $this->getQueueID())->where('name', $name);

		$data = array("chunk" => $chunk);
		if ($file){
			// workaround: http://forum.dibiphp.com/cs/1003-pgsql-a-znak-x00-oriznuti-zbytku-vstupu
			$data["data"] =  base64_encode(serialize($file));
		}
		foreach ($selection as $row){
			$row->update($data);
		}
		$this->getConnection()->commit();
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

		foreach ($this->getConnection()->table($this->getFilesTable())->where('queueID', $this->getQueueID()) as $row) {
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
		$this->getConnection()->table($this->getFilesTable())->where('queueID', $this->getQueueID())->delete();
	}


	/**
	 * When was queue last accessed?
	 * @return int timestamp
	 */
	function getLastAccess()
	{
		return $this->getConnection()->table($this->getFilesTable())->select('created')->where('queueID', $this->getQueueID())->order('created DESC')->fetch()->created;
	}


}