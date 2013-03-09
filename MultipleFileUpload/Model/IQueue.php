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

interface IQueue {

	/**
	 * getts queues model
	 * @return IQueues
	 */
	function getQueuesModel();

	/**
	 *setts queues model
	 * @param IQueues $model
	 */
	function setQueuesModel(IQueues $model);

	/**
	 * Getts queue ID
	 * @return string
	 */
	function getQueueID();

	/**
	 * Setts queue ID
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
	 * @param Nette\Http\FileUpload $file
	 */
	function addFile(\Nette\Http\FileUpload $file);

	/**
	 * Getts all files in queue
	 * @return array of HttpUploadedFile
	 */
	function getFiles();

	/**
	 * Deletes queue
	 */
	function delete();

	/**
	 * Getts WRITABLE path to write temps of this upload queue
	 */
	function getUploadedFilesTemporaryPath();

};