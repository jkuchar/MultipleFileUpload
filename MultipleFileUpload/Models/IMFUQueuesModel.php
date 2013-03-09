<?php

interface IMFUQueuesModel {

	/**
	 * getts life time of file
	 * @return int
	 */
	function getLifeTime();

	/**
	 * setts life time of file
	 * @param int $time
	 */
	function setLifeTime($time);

	/**
	 * getts cleaning interval
	 * @return int
	 */
	function getCleanInterval();

	/**
	 * setts cleaning interval
	 * @param int $interval
	 */
	function setCleanInterval($interval);

	/**
	 * Initializes driver
	 */
	function initialize();

	/**
	 * Getts queue (if needed create)
	 * @param string $token
	 * @return IMFUQueueModel
	 */
	function getQueue($id);

	/**
	 * Getts all queues
	 * @return array of IMFUQueueModel
	 */
	function getQueues();

	/**
	 * Executes cleanup
	 */
	function cleanup();
}