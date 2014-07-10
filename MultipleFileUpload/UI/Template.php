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

use Nette\Latte\Engine,
	Nette\Templating\FileTemplate;

class Template extends FileTemplate
{

	public function __construct($file = NULL)
	{
		parent::__construct($file);
		$this->onPrepareFilters[] = callback($this, "registerFilters");
	}


	public function registerFilters()
	{
		$this->registerFilter(new Engine());
	}


}