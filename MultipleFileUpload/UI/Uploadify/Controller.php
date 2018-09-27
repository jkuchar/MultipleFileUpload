<?php

/**
 * This file is part of the MultipleFileUpload (https://github.com/jkuchar/MultipleFileUpload/)
 *
 * Copyright (c) 2013 Jan Kuchař (http://www.jankuchar.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace MultipleFileUpload\UI\Uploadify;

use MultipleFileUpload\MultipleFileUpload;

/**
 * @author Jan Kuchař
 */
class Controller extends \MultipleFileUpload\UI\AbstractInterface
{

	/**
	 * Gets interface base url
	 * @return type string
	 */
	function getBaseUrl()
	{
		return parent::getBaseUrl() . "uploadify";
	}


	/**
	 * Is this upload your upload? (upload from this interface)
	 */
	public function isThisYourUpload()
	{
		// @see initJS.latte
		return $this->httpRequest->getPost('sender') === "MFU-Uploadify"; 
	}


	/**
	 * Handles uploaded files
	 * forwards it to model
	 */
	public function handleUploads()
	{
		$token = $this->httpRequest->getPost('token');
		if (!$token) {
			return;
		}

		foreach ($this->httpRequest->getFiles() AS $file) {
			/* @var $file \Nette\Http\FileUpload */
			self::processFile($token, $file);
		}

		// Response to client
		echo "1";

		// End the script
		exit;
	}


	/**
	 * Renders interface to <div>
	 */
	public function render(MultipleFileUpload $upload)
	{
        $template = parent::createTemplate();
        $template->setFile(__DIR__ . "/html.latte");
		$template->uploadifyId = $upload->getHtmlId() . "-uploadifyBox";
		return $template->__toString(TRUE);
	}


	/**
	 * Renders JavaScript body of function.
	 */
	public function renderInitJavaScript(MultipleFileUpload $upload)
	{
        $template = parent::createTemplate();
        $template->setFile(__DIR__ . "/initJS.latte");
        $template->sizeLimit = $upload->maxFileSize;
        $template->token = $upload->getToken();
        $template->maxFiles = $upload->maxFiles;
        $template->backLink = (string) $upload->form->action;
        $template->uploadifyId = $upload->getHtmlId() . "-uploadifyBox";
        $template->simUploadFiles = $upload->simUploadThreads;
        return $template->__toString(TRUE);
	}


	/**
	 * Renders JavaScript body of function.
	 */
	public function renderDestructJavaScript(MultipleFileUpload $upload)
	{
        $template = parent::createTemplate();
        return $template->setFile(__DIR__ . "/destructJS.js")->__toString(TRUE);
	}


	/**
	 * Renders set-up tags to <head> attribute
	 */
	public function renderHeadSection()
	{
        $template = parent::createTemplate();
        $template->setFile(__DIR__ . "/head.latte")->__toString(TRUE);
	}


}