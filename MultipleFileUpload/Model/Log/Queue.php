<?php

/**
 * This file is part of the MultipleFileUpload (https://github.com/jkuchar/MultipleFileUpload/)
 *
 * Copyright (c) 2013 Jan Kuchař (http://www.jankuchar.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */


namespace MultipleFileUpload\Model\Log;

use Nette\Environment;
use \MultipleFileUpload\Model\BaseQueueModel;

class Queue extends BaseQueueModel {

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