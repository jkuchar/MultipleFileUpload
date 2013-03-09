<?php

namespace MultipleFileUpload;

interface IQueueModel {

	/**
	 * getts queues model
	 * @return IQueuesModel
	 */
	function getQueuesModel();

	/**
	 *setts queues model
	 * @param IQueuesModel $model
	 */
	function setQueuesModel(IQueuesModel $model);

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
	function addFile(Nette\Http\FileUpload $file);

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