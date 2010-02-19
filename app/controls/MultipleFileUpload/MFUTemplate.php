<?php

class MFUTemplate extends Template {

	function  __construct() {
		parent::__construct();
		$this->onPrepareFilters[] = callback($this, "registerFilters");
	}

	function registerFilters() {
		$this->registerFilter(new LatteFilter());
	}

}