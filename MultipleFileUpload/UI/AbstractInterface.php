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
use Nette\Object;
use Nette\Http\FileUpload;

/**
 * Abstract UI Controller
 */
abstract class AbstractInterface extends Object implements IUserInterface {
	
	/**
	 * Getts interface base url
	 * @return type string
	 */
	function getBaseUrl() {
		return MultipleFileUpload::$baseWWWRoot;
	}
	
	/**
	 * Process single file
	 * @param string $token
	 * @param FileUpload $file
	 * @return bool
	 */
	function processFile($token, $file) {
		// Why not in one condition?
		// @see http://forum.nettephp.com/cs/viewtopic.php?pid=29556#p29556
		if (!$file instanceof FileUpload) {
			return false;
		}

		/* @var $validateCallback Callback */
		$validateCallback = MultipleFileUpload::$validateFileCallback;

		/* @var $isValid bool */
		$isValid = $validateCallback->invoke($file);

		if ($isValid) {
			MultipleFileUpload::getQueuesModel() // returns: IMFUQueuesModel
				->getQueue($token) // returns: IMFUQueueModel
				->addFile($file);
		}
		return $isValid;
	}

	/**
	 * @return Template
	 */
	protected function createTemplate($file = null) {
		$template = new Template($file);

		$template->baseUrl = \Nette\Environment::getHttpRequest()->url->baseUrl;
		$template->basePath = rtrim($template->baseUrl, '/');
		$template->interface = $this;

		$template->registerHelperLoader('Nette\Templating\Helpers::loader');

		return $template;
	}

}