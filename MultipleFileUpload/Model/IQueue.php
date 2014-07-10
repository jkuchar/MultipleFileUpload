<?php

/**
 * This file is part of the MultipleFileUpload (https://github.com/jkuchar/MultipleFileUpload/)
 *
 * Copyright (c) 2013 Jan Kuchař (http://www.jankuchar.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace MultipleFileUpload\Model;

use MultipleFileUpload\Model\IQueues,
	Nette\Http\FileUpload;

interface IQueue
{

	/**
	 * Gets queues model
	 * @return IQueues
	 */
	function getQueuesModel();

	/**
	 * Sets queues model
	 * @param IQueues $model
	 */
	function setQueuesModel(IQueues $model);

	/**
	 * Gets queue ID
	 * @return string
	 */
	function getQueueID();

	/**
	 * Sets queue ID
	 * @param string $queueID
	 */
	function setQueueID($queueID);

	/**
	 * When was queue last accessed?
	 * @return int timestamp
	 */
	function getLastAccess();

	/**
	 * Initializes driver
	 */
	function initialize();

	/**
	 * Adds file to queue
	 * @param FileUpload $file
	 */
	function addFile(FileUpload $file);

	/**
	 * TODO
	 * @param type $name
	 * @param type $chunk
	 * @param type $chunks
	 */
	function addFileManually($name, $chunk, $chunks);

	/**
	 * Updates file information (useful when processing chunked upload)
	 * @param type $name
	 * @param type $chunk
	 * @param FileUpload $file
	 */
	function updateFile($name, $chunk, FileUpload $file = null);

	/**
	 * Gets all files in queue
	 * @return FileUpload[]
	 */
	function getFiles();

	/**
	 * Deletes queue
	 */
	function delete();

	/**
	 * Gets WRITABLE path to write temps of this upload queue
	 */
	function getUploadedFilesTemporaryPath();
}