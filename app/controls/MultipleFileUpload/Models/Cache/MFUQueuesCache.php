<?php

class MFUQueuesCache extends LockableObject implements IMFUQueuesModel {

	public static $cache;

	public $threadSafe = false;

	//private $locked = false;

	function getQueue($token,$create=false) {
		/*if(!$this->locked) {
			$this->lock();
			$this->locked = true;
		}*/
		$cache = self::getCache();

		if(!isSet($cache["queues"])) {
			$cache["queues"] = array();
		}

		$queues = $cache["queues"];
		if(!isSet($queues[$token])) {
			if(!$create) {
				throw new InvalidStateException("Queue is not exists!");
			}
			$queues[$token] = time();
			$cache["queues"] = $queues;
		}
		//$this->unlock();
		return new MFUQueueCache($token,$this);
	}

	/**
	 * Getts cache
	 * @return Cache
	 */
	protected static function getCache() {
		if(!self::$cache) {
			self::$cache = Environment::getCache("MultipleFileUpload");
		}
		return self::$cache;
	}

	/**
	 * Cleans cache
	 * @return bool
	 */
	public static function clean($lifeTime,$cleanInterval) {
		$cache  = self::getCache();

		// Pokud ještě není čas
		if(isSet($cache["lastCleanup"]) and $cache["lastCleanup"] > (time()-$cleanInterval))
			return;

		// Pokud už jiné vlákno čistí...
		if(isSet($cache["cleaning"])) return;

		// Teď čistím já...
		$cache["cleaning"]=true;

		//Environment::getService('Nette\Logger')
		//		->logMessage("cleaning...");

		$queues = $cache["queues"];
		if(is_array($queues)) {
			foreach($queues AS $queueID => $true) {
				$lastWriteTime = $queues[$queueID];
				if($lastWriteTime < (time()-$lifeTime)) {
					if(isSet($cache[$queueID])) {
						foreach($cache[$queueID] AS $key => $file) {
							$tmpName = $file->getTemporaryFile();
							if(@unlink($tmpName)) {
								$c = $cache[$queueID];
								unset($c[$key]);
								$cache[$queueID] = $c;
								unset ($c);
							}else continue 2;
						}
						unset($cache[$queueID]);
					}
					unset($queues[$queueID]);
				}else // Soubor ještě nepřesáhl maximální věk, nemaž ho:
					continue;
			}
			$cache["queues"] = $queues;
		}

		// Už jsem dočistil
		$cache["lastCleanup"] = time();
		$cache["cleaning"]=null;
	}

}