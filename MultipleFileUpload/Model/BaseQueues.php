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

use Nette\Object;

/**
 * @property int $lifeTime Life time
 * @property int $cleanInterval Clean interval
 */
abstract class BaseQueues extends Object implements IQueues
{
	/**
	 * Life time
	 * @var int
	 */
	private $lifeTime;


	/**
	 * Gets life time of file
	 * @return int
	 */
	function getLifeTime()
	{
		return $this->lifeTime;
	}


	/**
	 * Sets life time of file
	 * @param int $time
	 */
	function setLifeTime($time)
	{
		$this->lifeTime = $time;
		return $this;
	}


	/**
	 * Clean interval
	 * @var int
	 */
	private $cleanInterval;


	/**
	 * Gets cleaning interval
	 * @return int
	 */
	function getCleanInterval()
	{
		return $this->cleanInterval;
	}


	/**
	 * Sets cleaning interval
	 * @param int $interval
	 */
	function setCleanInterval($interval)
	{
		$this->cleanInterval = $interval;
		return $this;
	}


}