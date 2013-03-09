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

interface IQueuesModel {

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
	 * @return IQueueModel
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