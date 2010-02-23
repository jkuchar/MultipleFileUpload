<?php

class MFUQueueLog extends MFUBaseQueueModel {

	/**
	 * Initializes driver
	 */
	function initialize() {
		Environment::getService('Nette\Logger')->logMessage("queues: initialize");
	}

	/**
	 * Adds file to queue
	 * @param HttpUploadedFile $file
	 */
	function addFile(HttpUploadedFile $file) {
		Environment::getService('Nette\Logger')->logMessage("addFile");
	}

	function getUploadedFilesTemporaryPath() {
		Environment::getService('Nette\Logger')->logMessage("getUploadedFilesTemporaryPath");
		return " ";
	}

	function getLastAccess() {
		Environment::getService('Nette\Logger')->logMessage("getLastAccess");
		return time();
	}

	/**
	 * Getts files
	 * @return array of HttpUploadedFile
	 */
	function getFiles() {
		Environment::getService('Nette\Logger')->logMessage("getFiles");
		return array();
	}

	function delete() {
		Environment::getService('Nette\Logger')->logMessage("deletes queue");
	}

}