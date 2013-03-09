<?php

/**
 * This file is part of the MultipleFileUpload (https://github.com/jkuchar/MultipleFileUpload/)
 *
 * Copyright (c) 2013 Jan Kuchař (http://www.jankuchar.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */


namespace MultipleFileUpload;

class Template extends Nette\Templating\FileTemplate {

	function __construct() {
		parent::__construct();
		$this->onPrepareFilters[] = callback($this, "registerFilters");
	}

	function registerFilters() {
		$this->registerFilter(new \Nette\Latte\Engine());
	}

}