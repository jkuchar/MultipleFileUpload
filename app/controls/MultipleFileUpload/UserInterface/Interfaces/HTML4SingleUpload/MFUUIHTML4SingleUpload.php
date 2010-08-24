<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MFUUIHTML4SingleUpload
 *
 * @author Jan Kuchař
 */
class MFUUIHTML4SingleUpload extends MFUUIBase {

	/**
	 * Is this upload your upload? (upload from this interface)
	 */
	public function isThisYourUpload() {
		return !(Environment::getHttpRequest()->getHeader('user-agent') === 'Shockwave Flash');
	}

    	/**
	 * Handles uploaded files
	 * forwards it to model
	 */
	public function handleUploads() {
		// Iterujeme nad přijatými soubory
		foreach(Environment::getHttpRequest()->getFiles() AS $name => $controlValue) {

			// MFU vždy posílá soubory v této struktuře:
			//
			// array(
			//	"token" => "blablabla",
			//	"files" => array(
			//		0 => HttpUploadedFile(...),
			//		...
			//	)
			// )

			$isFormMFU = (
				is_array($controlValue) and
					isset($controlValue["files"]) and
					isset($_POST[$name]["token"])
			);

			if($isFormMFU) {
				$token = $_POST[$name]["token"];
				foreach($controlValue["files"] AS $file) {
					self::processFile($token,$file);
				}
			}

			// soubory, které se netýkají MFU nezpracujeme -> zpracuje si je standardním způsobem formulář
		}

		return true; // Skip all next
	}

	/**
	 * Renders interface to <div>
	 */
	public function render(MultipleFileUpload $upload) {
		$template = $this->createTemplate(dirname(__FILE__)."/html.phtml");
		$template->mfu = $upload;
		return $template->__toString(TRUE);
	}

	/**
	 * Renders JavaScript body of function.
	 */
	public function renderInitJavaScript(MultipleFileUpload $upload) {
		return $this->createTemplate(dirname(__FILE__)."/initJS.js")->__toString(TRUE);
	}

	/**
	 * Renders JavaScript body of function.
	 */
	public function renderDestructJavaScript(MultipleFileUpload $upload) {
		return true;
	}

	/**
	 * Renders set-up tags to <head> attribute
	 */
	public function renderHeadSection() {
		return "";
	}
}
