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

interface IQueues
{

	/**
	 * Gets life time of file
	 * @return int
	 */
	function getLifeTime();

	/**
	 * Sets life time of file
	 * @param int $time
	 */
	function setLifeTime($time);

	/**
	 * Gets cleaning interval
	 * @return int
	 */
	function getCleanInterval();

	/**
	 * Sets cleaning interval
	 * @param int $interval
	 */
	function setCleanInterval($interval);

	/**
	 * Initializes driver
	 */
	function initialize();

	/**
	 * Gets queue (create if needed)
	 * @param string $id
	 * @return IQueue
	 */
	function getQueue($id);

	/**
	 * Gets all queues
	 * @return IQueue[]
	 */
	function getQueues();

	/**
	 * Executes cleanup
	 */
	function cleanup();
}