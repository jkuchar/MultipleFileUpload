<?php

namespace MultipleFileUpload;

class MFUTemplate extends Nette\Templating\FileTemplate {

	function __construct() {
		parent::__construct();
		$this->onPrepareFilters[] = callback($this, "registerFilters");
	}

	function registerFilters() {
		$this->registerFilter(new \Nette\Latte\Engine());
	}

}