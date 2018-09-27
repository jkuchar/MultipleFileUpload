<?php

/**
 * This file is part of the MultipleFileUpload (https://github.com/jkuchar/MultipleFileUpload/)
 *
 * Copyright (c) 2013 Roman Vykuka (http://forum.nette.org/cs/profile.php?id=2221)
 * Copyright (c) 2013 Jan Kuchař (http://www.jankuchar.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace MultipleFileUpload\UI\Swfupload;

use MultipleFileUpload\MultipleFileUpload,
	MultipleFileUpload\UI\AbstractInterface,
	Nette\Http\FileUpload;

/**
 * @author Roman Vykuka, Jan Kuchař
 */
class Controller extends AbstractInterface
{

	/**
	 * Gets interface base url
	 * @return type string
	 */
	function getBaseUrl()
	{
		return parent::getBaseUrl() . "swfupload";
	}


	/**
	 * Is this upload your upload? (upload from this interface)
	 */
	public function isThisYourUpload()
	{
		return $this->httpRequest->getPost('sender') === "MFU-Swfupload";
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

		/* @var $file FileUpload */
		foreach ($this->httpRequest->getFiles() AS $file) {
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
		$template = $this->createTemplate(dirname(__FILE__) . "/html.latte");
		$template->swfuId = $upload->getHtmlId() . "-swfuBox";
		return $template->__toString(TRUE);
	}


	/**
	 * Renders JavaScript body of function.
	 */
	public function renderInitJavaScript(MultipleFileUpload $upload)
	{
		$template = $this->createTemplate(dirname(__FILE__) . "/initJS.latte");
		$template->sizeLimit = ini_get('upload_max_filesize') . 'B';
		$template->token = $upload->getToken();
		$template->maxFiles = $upload->maxFiles;
		$template->backLink = (string) $upload->form->action;
		$template->swfuId = $upload->getHtmlId() . "-swfuBox";
		$template->simUploadFiles = $upload->simUploadThreads;
		$template->flash_url = $this->httpRequest->url->baseUrl . '/swf/swfupload.swf';
		$template->flash9_url = $this->httpRequest->url->baseUrl . '/swf/swfupload_fp9.swf';
		$template->button_image_url = $this->httpRequest->url->baseUrl . 'imgs/XPButtonUploadText_89x88.png';
		return $template->__toString(TRUE);
	}


	/**
	 * Renders JavaScript body of function.
	 */
	public function renderDestructJavaScript(MultipleFileUpload $upload)
	{
		return $this->createTemplate(dirname(__FILE__) . "/destructJS.js")->__toString(TRUE);
	}


	/**
	 * Renders set-up tags to <head> attribute
	 */
	public function renderHeadSection()
	{
		return $this->createTemplate(dirname(__FILE__) . "/head.latte")->__toString(TRUE);
	}


}