<?php


/**
 * @property IMFUQueuesModel $queuesModel
 * @property string $queueID
 */
abstract class MFUBaseQueueModel extends Object implements IMFUQueueModel {

	/**
	 * Queues model
	 * @var IMFUQueuesModel
	 */
	private $queuesModel;

	/**
	 * getts queues model
	 * @return IMFUQueuesModel
	 */
	function getQueuesModel() {
		if(!$this->queuesModel)
			throw new InvalidStateException("Queues model is not set!");
		return $this->queuesModel;
	}

	/**
	 *setts queues model
	 * @param IMFUQueuesModel $model
	 */
	function setQueuesModel(IMFUQueuesModel $model) {
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
		return $this->getUploadedFilesTemporaryPath() . DIRECTORY_SEPARATOR . "upload-" . $this->getQueueID()  ."-" . uniqid() . ".tmp";
	}

	/**
	 * Initialization
	 */
	function initialize() {
		if(!$this->queueID or !$this->queuesModel) {
			throw new InvalidStateException("queueID and queuesModel must be setup before call initialize()!");
		}
	}

}