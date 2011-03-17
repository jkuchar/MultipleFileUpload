<?php

interface IMFUQueueModel {

	/**
	 * getts queues model
	 * @return IMFUQueuesModel
	 */
	function getQueuesModel();

	/**
	 *setts queues model
	 * @param IMFUQueuesModel $model
	 */
	function setQueuesModel(IMFUQueuesModel $model);

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
	 * @param HttpUploadedFile $file
	 */
	function addFile(HttpUploadedFile $file);

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