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

use \MultipleFileUpload\MultipleFileUpload;

/**
 * Description of AbstractInterface
 *
 * @author Honza
 */
abstract class AbstractInterface extends \Nette\Object implements IUserInterface {
	
	/**
	 * Getts interface base url
	 * @return type string
	 */
	function getBaseUrl() {
		return \MultipleFileUpload\MultipleFileUpload::$baseWWWRoot;
	}
	
	/**
	 * Process single file
	 * @param string $token
	 * @param HttpUploadedFile $file
	 * @return bool
	 */
	function processFile($token, $file) {
		// Why not in one condition?
		// @see http://forum.nettephp.com/cs/viewtopic.php?pid=29556#p29556
		if (!$file instanceof Nette\Http\FileUpload) {
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
	 * @return ITemplate
	 */
	protected function createTemplate($file = null) {
		$template = new Template($file);
		//$presenter = Environment::getApplication()->getPresenter();

		// default parameters
		//$template->component = $this; // DEPRECATED!
		//$template->control = $this;
		//$template->presenter = $presenter;
		$template->baseUrl = \Nette\Environment::getHttpRequest()->url->baseUrl;
		$template->basePath = rtrim($template->baseUrl, '/');
		$template->interface = $this;

		// flash message
		/* if ($presenter !== NULL && $presenter->hasFlashSession()) {
		  $id = $this->getParamId('flash');
		  $template->flashes = $presenter->getFlashSession()->$id;
		  }
		  if (!isset($template->flashes) || !is_array($template->flashes)) {
		  $template->flashes = array();
		  } */

		// default helpers
		/* $template->registerHelper('escape', 'Nette\Templates\TemplateHelpers::escapeHtml');
		  $template->registerHelper('escapeUrl', 'rawurlencode');
		  $template->registerHelper('stripTags', 'strip_tags');
		  $template->registerHelper('nl2br', 'nl2br');
		  $template->registerHelper('substr', 'iconv_substr');
		  $template->registerHelper('repeat', 'str_repeat');
		  $template->registerHelper('implode', 'implode');
		  $template->registerHelper('number', 'number_format'); */
		$template->registerHelperLoader('Nette\Templating\Helpers::loader');

		return $template;
	}

}