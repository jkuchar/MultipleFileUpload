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

use MultipleFileUpload\Model\BaseQueue,
	Nette\Http\FileUpload,
	Tracy\Debugger;

class Queue extends BaseQueue
{

	/**
	 * Initializes driver
	 */
	function initialize()
	{
		$a = func_get_args();
		Debugger::log(__CLASS__ . ": " . __METHOD__ . "; args: " . print_r($a, true));
	}


	/**
	 * Adds file to queue
	 * @param FileUpload $file
	 */
	function addFile(FileUpload $file)
	{
		$a = func_get_args();
		Debugger::log(__CLASS__ . ": " . __METHOD__ . "; args: " . print_r($a, true));
	}


	function updateFile($name, $chunk, FileUpload $file = null)
	{
		$a = func_get_args();
		Debugger::log(__CLASS__ . ": " . __METHOD__ . "; args: " . print_r($a, true));
	}


	function addFileManually($name, $chunk, $chunks)
	{
		$a = func_get_args();
		Debugger::log(__CLASS__ . ": " . __METHOD__ . "; args: " . print_r($a, true));
	}


	function getUploadedFilesTemporaryPath()
	{
		$a = func_get_args();
		Debugger::log(__CLASS__ . ": " . __METHOD__ . "; args: " . print_r($a, true));
		return " ";
	}


	function getLastAccess()
	{
		$a = func_get_args();
		Debugger::log(__CLASS__ . ": " . __METHOD__ . "; args: " . print_r($a, true));
		return time();
	}


	/**
	 * Gets files
	 * @return FileUpload[]
	 */
	function getFiles()
	{
		$a = func_get_args();
		Debugger::log(__CLASS__ . ": " . __METHOD__ . "; args: " . print_r($a, true));
		return array();
	}


	function delete()
	{
		$a = func_get_args();
		Debugger::log(__CLASS__ . ": " . __METHOD__ . "; args: " . print_r($a, true));
	}


}