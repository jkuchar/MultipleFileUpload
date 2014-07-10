<?php

/**
 * This file is part of the MultipleFileUpload (https://github.com/jkuchar/MultipleFileUpload/)
 *
 * Copyright (c) 2013 Jan Kuchař (http://www.jankuchar.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace MultipleFileUpload\UI;

use MultipleFileUpload\MultipleFileUpload;

/**
 * User Interface server representation interface
 * @author Jan Kuchař
 */
interface IUserInterface
{

	/**
	 * Is this upload your upload? (upload from this interface)
	 */
	public function isThisYourUpload();

	/**
	 * Handles uploaded files
	 * forwards it to model
	 */
	public function handleUploads();

	/**
	 * Renders interface to <div>
	 */
	public function render(MultipleFileUpload $upload);

	/**
	 * Renders JavaScript body of function.
	 */
	public function renderInitJavaScript(MultipleFileUpload $upload);

	/**
	 * Renders JavaScript body of function.
	 */
	public function renderDestructJavaScript(MultipleFileUpload $upload);

	/**
	 * Renders set-up tags to <head> attribute
	 */
	public function renderHeadSection();
}