<?php

class LockableObject extends Object {

	function getLockKey() {
		return $this->reflection->name."-lock";
	}

	function getLockModel() {
		return Environment::getCache("LockableObject-locks");
	}

	public function lock() {
		$cache = $this->getLockModel();
		while ($this->isLocked()) {
			$time = rand(5000,15000); // 1000000 = 1s => 5ms až 15ms
			usleep($time); // Počkáme náhodný čas
			//Environment::getService('Nette\Logger')->logMessage("Waiting to get lock... key: ".$this->getLockKey());
		}
		$cache[$this->getLockKey()] = true;
		return $this;
	}

	public function isLocked() {
		$cache = $this->getLockModel();
		return isset($cache[$this->getLockKey()]);
	}

	public function unlock() {
		$cache = $this->getLockModel();
		unset($cache[$this->getLockKey()]);
		return $this;
	}

	function  __destruct() {
		if($this->isLocked()) {
			$this->unlock();
		}
	}

}