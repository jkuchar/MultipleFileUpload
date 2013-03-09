<?php

namespace MultipleFileUpload\Model;

/**
 * @property IMFUQueuesModel $queuesModel
 * @property string $queueID
 */
abstract class BaseQueueModel extends Nette\Object implements IQueueModel {

	/**
	 * Queues model
	 * @var IQueuesModel
	 */
	private $queuesModel;

	/**
	 * gets queues model
	 * @return IQueuesModel
	 */
	function getQueuesModel() {
		if (!$this->queuesModel)
			throw new InvalidStateException("Queues model is not set!");
		return $this->queuesModel;
	}

	/**
	 * sets queues model
	 * @param IQueuesModel $model
	 */
	function setQueuesModel(IQueuesModel $model) {
		$this->queuesModel = $model;
		return $this;
	}

	/**
	 * Queue ID (token)
	 * @var string
	 */
	private $queueID;

	/**
	 * Getts queue ID
	 * @return string
	 */
	function getQueueID() {
		return $this->queueID;
	}

	/**
	 * Setts queue ID
	 * @param string $queueID
	 */
	function setQueueID($queueID) {
		$this->queueID = $queueID;
		return $this;
	}

	/**
	 * Returns unique file name
	 * @return string
	 */
	protected function getUniqueFilePath() {
		return 
			$this->getUploadedFilesTemporaryPath()
			.DIRECTORY_SEPARATOR
			."upload-"
			.$this->getQueueID()
			."-"
			.uniqid()
			.".tmp";
	}

	/**
	 * Initialization
	 */
	function initialize() {
		if (!$this->queueID or !$this->queuesModel) {
			throw new InvalidStateException("queueID and queuesModel must be setup before call initialize()!");
		}
	}

}