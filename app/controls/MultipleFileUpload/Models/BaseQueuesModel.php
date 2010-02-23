<?php


/**
 * @property int $lifeTime Life time
 * @property int $cleanInterval Clean interval
 */
abstract class MFUBaseQueuesModel extends Object implements IMFUQueuesModel {

	/**
	 * Life time
	 * @var int
	 */
	private $lifeTime;

	/**
	 * getts life time of file
	 * @return int
	 */
	function getLifeTime() {
		return $this->lifeTime;
	}

	/**
	 * setts life time of file
	 * @param int $time
	 */
	function setLifeTime($time) {
		$this->lifeTime = $time;
		return $this;
	}


	/**
	 * Clean interval
	 * @var int
	 */
	private $cleanInterval;

	/**
	 * getts cleaning interval
	 * @return int
	 */
	function getCleanInterval() {
		return $this->cleanInterval;
	}

	/**
	 * setts cleaning interval
	 * @param int $interval
	 */
	function setCleanInterval($interval) {
		$this->cleanInterval = $interval;
		return $this;
	}

}