<?php

/**
 * Copyright (c) 2010, Jan Kuchař
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms,
 * with or without modification, are permitted provided
 * that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above
 *       copyright notice, this list of conditions and the following
 *       disclaimer in the documentation and/or other materials provided
 *       with the distribution.
 *     * Neither the name of the Mujserver.net nor the names of its
 *       contributors may be used to endorse or promote products derived
 *       from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author     Jan Kuchař
 * @copyright  Copyright (c) 2010 Jan Kuchař (http://mujserver.net)
 * @license    New BSD License
 * @link       http://nettephp.com/cs/extras/multiplefileupload
 */

/**
 * Registrator of user interfaces
 */
class MFUUIRegistrator extends Object {

	public $interfaces = array();

	public function register($interface) {
		if (is_object($interface)) {
			if (!$interface instanceof MFUUIInterface) {
				throw new InvalidArgumentException("Interface must implement MFUUIInterface!");
			}
			$this->interfaces[] = $interface;
		} elseif (is_string($interface)) {
			$this->interfaces[] = $interface;
		} else {
			throw new InvalidArgumentException("Not supported interface!");
		}
		return $this;
	}

	public function clear() {
		$this->interfaces = array();
		return $this;
	}

	public function getInterfaces() {
		$interfaces = $this->interfaces;
		foreach ($interfaces AS $key => $interface) {
			if (is_string($interface)) {
				$interface = $interfaces[$key] = new $interface;
			}
			if (!$interface instanceof MFUUIInterface) {
				throw new InvalidArgumentException($interface->reflection->name . " is not compatible with MFU!");
			}
		}
		$this->interfaces = $interfaces;
		return array_reverse($interfaces);
	}

}
