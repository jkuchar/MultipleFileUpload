<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MFUUIBase
 *
 * @author Honza
 */
abstract class MFUUIBase extends Object implements MFUUIInterface {

	/**
	 * Process single file
	 * @param string $token
	 * @param HttpUploadedFile $file
	 * @return bool
	 */
	function processFile($token, $file) {
		// Why not in one condition?
		// @see http://forum.nettephp.com/cs/viewtopic.php?pid=29556#p29556
		if (!$file instanceof HttpUploadedFile) {
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
	protected function createTemplate($file = null)
	{
		$template = new Template($file);
		//$presenter = Environment::getApplication()->getPresenter();
		$template->onPrepareFilters[] = array($this, 'templatePrepareFilters');

		// default parameters
		//$template->component = $this; // DEPRECATED!
		//$template->control = $this;
		//$template->presenter = $presenter;
		$template->baseUri = Environment::getVariable('baseUri');
		$template->basePath = rtrim($template->baseUri, '/');

		// flash message
		/*if ($presenter !== NULL && $presenter->hasFlashSession()) {
			$id = $this->getParamId('flash');
			$template->flashes = $presenter->getFlashSession()->$id;
		}
		if (!isset($template->flashes) || !is_array($template->flashes)) {
			$template->flashes = array();
		}*/

		// default helpers
		/*$template->registerHelper('escape', 'Nette\Templates\TemplateHelpers::escapeHtml');
		$template->registerHelper('escapeUrl', 'rawurlencode');
		$template->registerHelper('stripTags', 'strip_tags');
		$template->registerHelper('nl2br', 'nl2br');
		$template->registerHelper('substr', 'iconv_substr');
		$template->registerHelper('repeat', 'str_repeat');
		$template->registerHelper('implode', 'implode');
		$template->registerHelper('number', 'number_format');*/
		$template->registerHelperLoader('Nette\Templates\TemplateHelpers::loader');

		return $template;
	}

	/**
	 * Descendant can override this method to customize template compile-time filters.
	 * @param  Template
	 * @return void
	 */
	public function templatePrepareFilters($template)
	{
		// default filters
		$template->registerFilter(new LatteFilter);
	}

}