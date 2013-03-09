<?php

namespace MultipleFileUpload;

/**
 * Description of MFUUIUploadify
 *
 * @author Jan Kuchař
 */
class UploadifyInterface extends AbstractInterface {

	/**
	 * Getts interface base url
	 * @return type string
	 */
	function getBaseUrl() {
		return parent::getBaseUrl()."uploadify";
	}
	
	/**
	 * Is this upload your upload? (upload from this interface)
	 */
	public function isThisYourUpload() {
		return (
			\Nette\Environment::getHttpRequest()->getHeader('user-agent') === 'Shockwave Flash'
			AND isSet($_POST["sender"])
			AND $_POST["sender"] == "MFU-Uploadify"
			);
	}

	/**
	 * Handles uploaded files
	 * forwards it to model
	 */
	public function handleUploads() {
		if (!isset($_POST["token"])) {
			return;
		}

		/* @var $token string */
		$token = $_POST["token"];

		/* @var $file HttpUploadedFile */
		foreach (\Nette\Environment::getHttpRequest()->getFiles() AS $file) {
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
	public function render(MultipleFileUpload $upload) {
		$template = $this->createTemplate(dirname(__FILE__) . "/html.latte");
		$template->uploadifyId = $upload->getHtmlId() . "-uploadifyBox";
		return $template->__toString(TRUE);
	}

	/**
	 * Renders JavaScript body of function.
	 */
	public function renderInitJavaScript(MultipleFileUpload $upload) {
		$tpl = $this->createTemplate(dirname(__FILE__) . "/initJS.js");
		$tpl->sizeLimit = $upload->maxFileSize;
		$tpl->token = $upload->getToken();
		$tpl->maxFiles = $upload->maxFiles;
		$tpl->backLink = (string) $upload->form->action;
		$tpl->uploadifyId = $upload->getHtmlId() . "-uploadifyBox";
		$tpl->simUploadFiles = $upload->simUploadThreads;
		return $tpl->__toString(TRUE);
	}

	/**
	 * Renders JavaScript body of function.
	 */
	public function renderDestructJavaScript(MultipleFileUpload $upload) {
		return $this->createTemplate(dirname(__FILE__) . "/destructJS.js")->__toString(TRUE);
	}

	/**
	 * Renders set-up tags to <head> attribute
	 */
	public function renderHeadSection() {
		return $this->createTemplate(dirname(__FILE__) . "/head.latte")->__toString(TRUE);
	}

}