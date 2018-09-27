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

use Nette\InvalidArgumentException,
	Nette\Http\Request,
	Nette\Object;

/**
 * Registrator of user interfaces used to set priorities of interfaces
 */
class Registrator extends Object
{
	public $interfaces = array();


	private $productionMode;


	public function __construct($productionMode, HTML4SingleUpload\Controller $htmlUpload, Plupload\Controller $plupload)
	{
		$this->productionMode = $productionMode;
		$this->interfaces[] = $htmlUpload;
		$this->interfaces[] = $plupload;
	}


	public function getProductionMode() {
		return $this->productionMode;
	}


	public function register($interface)
	{
		if (is_object($interface)) {
			if (!$interface instanceof IUserInterface) {
				throw new InvalidArgumentException("Interface must implement MultipleFileUpload\UI\IUserInterface!");
			}
			$this->interfaces[] = $interface;
		} elseif (is_string($interface)) {
			// User gives us only namespace
			$this->interfaces[] = $interface . "\\Controller";
		} else {
			throw new InvalidArgumentException("Not supported interface!");
		}
		return $this;
	}


	public function clear()
	{
		$this->interfaces = array();
		return $this;
	}


	public function getInterfaces(Request $request)
	{
		$interfaces = $this->interfaces;
		foreach ($interfaces AS $key => $interface) {
			if (is_string($interface)) {
				$interface = $interfaces[$key] = new $interface($request);
			}
			if (!$interface instanceof IUserInterface) {
				throw new InvalidArgumentException($interface->reflection->name . " is not compatible with MFU!");
			}
		}
		$this->interfaces = $interfaces;
		return array_reverse($interfaces);
	}


}