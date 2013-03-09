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

/**
 * @property int $lifeTime Life time
 * @property int $cleanInterval Clean interval
 */
abstract class BaseQueues extends \Nette\Object implements IQueues {

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