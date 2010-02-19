<?php

/**
 * Copyright (c) 2009, Jan Kuchař
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
 * @copyright  Copyright (c) 2009 Jan Kuchař (http://mujserver.net)
 * @license    New BSD License
 * @link       http://nettephp.com/cs/extras/multiplefileupload
 */

class MultipleFileUpload extends FileUpload {

	// TODO: dodělat JavaScriptovou validaci - hidden field kde se budou cpát hodnoty pro Nettí validátor
	// TODO: Thread-safe cache pro seznamy souborů ve frontě. Tip: http://forum.nettephp.com/cs/viewtopic.php?pid=16547#p16547
	//  Už to mám! :) Vytvořit další soubor v cache s funkcí lock-unlock.
	// TODO: Vytvořit nějaký obecný Storage na data, který je thread-safe a bude nahrozovat časté nesprávné použití cache.
	// TODO: Nejdříve vygenerovat nějaký jednorázový token, kterým se uživatel následně ověří, že je to opravdu on.

	static function register() {
		$application = Environment::getApplication();
		$application->onStartup[]  = "MultipleFileUpload::handleUploads";
		$application->onShutdown[] = "MultipleFileUpload::cleanCache";
	}

	/* ##########  HANDLING UPLOADS  ########### */


	/**
	 * Directory for temporary uploads
	 * @var string
	 */
	public static $uploadFileDirectory = "%tempDir%/MultipleFileUpload-uploads";

	/**
	 * When $uploadFileDirectory has not been writable. System will write directly to %tempDir%
	 * @var bool
	 */
	public static $allowWriteToRootOfTemp = TRUE;

	/**
	 * Cache object
	 * @var Cache
	 */
	private static $cache;

	/**
	 * Is files proccessed?
	 * @var bool
	 */
	private static $filesProccessed = false;

	/**
	 * Check if handleUploads was called
	 * @var bool
	 */
	public static $handleUploadsCheck = false;

	/**
	 * Lifetime of files in queue
	 * When clean up is running, it is watching if there is any added file in their lifetime.
	 *
	 * @var int Time in seconds
	 */
	public static $lifeTime = 3600; // 1 hour

	/**
	 * Cleaning up interval
	 * @var int In seconds
	 */
	public static $cleanInterval = 18000; // 5 hours

	/**
	 * Handles uploading files
	 */
	static function handleUploads() {


		// Checks
		self::$handleUploadsCheck = true;
		if(self::$filesProccessed === true) return;

		$req = Environment::getHttpRequest();
		if(!$req->getMethod() === "POST" OR !stristr($req->getHeader("Content-type"),"multipart/form-data"))
			return;

		// V PHP 5.3 zhodí Apache!
		if(self::isRequestFromFlash())
			Debug::enable(Debug::PRODUCTION);

		$dir = Environment::expand(self::$uploadFileDirectory);

		// Vytvoříme složku a ověříme jestli je zapisovatelná
		if(!file_exists($dir))
			mkdir($dir,0777);

		if(!is_writable($dir) and self::$allowWriteToRootOfTemp) {
			$dir = self::$uploadFileDirectory = Environment::expand("%tempDir%");
		}

		if(!is_writable($dir)) {
			throw new InvalidStateException($dir." is not writable!");
		}

		// Zpracuj soubory
		if(self::isRequestFromFlash()) {
			//throw new Exception();
			// Uploadify
			/*
             * Dostaneme soubor Filedata
			*/
			if(!isSet($_POST["token"])) return;
			$token = $_POST["token"];
			$store = self::getData($token);
			foreach(Environment::getHttpRequest()->getFiles() AS $file) {
				self::processSingleFile($token, $file, $store);
			}
			self::saveData($token, $store);

		}else {
			// Standardní HTTP požadavek
			/*
			* Dojde něco ve stylu:
			*   Array
			*   (
			*       [testUpload] => Array
			*           (
			*               [files] => Array
			*                   (
			*                       [0] => HttpUploadedFile Object
			*                           (
			*                               [name:private] => anchor.png
			*                               [type:private] =>
			*                               [size:private] => 523
			*                               [tmpName:private] => C:\Program Files\xampp\tmp\phpBBF2.tmp
			*                               [error:private] => 0
			*                           )
			*
			*                       [1] => HttpUploadedFile Object
			*                           (
			*                               [name:private] => application_edit.png
			*                               [type:private] =>
			*                               [size:private] => 703
			*                               [tmpName:private] => C:\Program Files\xampp\tmp\phpBC03.tmp
			*                               [error:private] => 0
			*                           )
			*                   )
			*
			*           )
			*
			*   )
			*/
			foreach(Environment::getHttpRequest()->getFiles() AS $name => $controlValue) {
				if(is_array($controlValue) and isSet($controlValue["files"]) and isSet($_POST[$name]["token"])) {
					$token = $_POST[$name]["token"];
					$store = self::getData($token);
					foreach($controlValue["files"] AS $file) {
						self::processSingleFile($token,$file, $store);
					}
					self::saveData($token,$store);
				}//else zpracuje si to už formulář sám (nejspíš tam bude už HTTPUploadedFile, ale odeslán z klasického FileUpload políčka)
			}
		}

		// Pokud všechno proběhlo ok a soubor byl odeslán z flashe
		if(self::isRequestFromFlash()) {
			echo "1";

			// Voláno ještě před spustěním $application-run() -> abort exception by způsobilo akorát nezachycenou výjimku
			die();
		}

		self::$filesProccessed = true;
	}

	/**
	 * Cleans cache
	 * @return bool
	 */
	static function cleanCache() {
		$cache  = self::getCache();

		// Pokud ještě není čas
		if(isSet($cache["lastCleanup"]) and $cache["lastCleanup"] > (time()-self::$cleanInterval))
			return;

		// Pokud už jiné vlákno čistí...
		if(isSet($cache["cleaning"])) return;

		// Teď čistím já...
		$cache["cleaning"]=true;

		// Šílený mechanizmus, který si má řešit model a snad někdy taky bude
		$queues = $cache["queues"];
		if(is_array($queues)) {
			foreach($queues AS $queueID => $true) {
				if(isSet($cache[$queueID])) {
					$lastWriteTime = $queues[$queueID];
					if($lastWriteTime < (time()-self::$lifeTime)) {
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
					}else // Soubor ještě nepřesáhl maximální věk, nemaž ho:
						continue;
				}
				unset($queues[$queueID]);
			}
			$cache["queues"] = $queues;
		}

		// Už jsem dočistil
		$cache["lastCleanup"] = time();
		$cache["cleaning"]=null;
	}

	/**
	 * Getts cache
	 * @return Cache
	 */
	private static function getCache() {
		if(!self::$cache) {
			self::$cache = Environment::getCache("MultipleFileUpload");
		}
		return self::$cache;
	}

	/**
	 * (internal) Processes sigle file
	 */
	private static function processSingleFile($token, $file, &$store) {
		if($file instanceof HttpUploadedFile and $file->isOk()) {
			$file->move(self::getUniqueFilePath($token));
			$store[] = $file;
			return true;
		}else {
			return false;
		}
	}

	/**
	 * Is request from flash?
	 * @return bool
	 */
	static function isRequestFromFlash() {
		return (Environment::getHttpRequest()->getHeader('user-agent') === 'Shockwave Flash');
	}

	/**
	 * Returns unique file name
	 *
	 * self::$token must be set!
	 *
	 * @return string
	 */
	static function getUniqueFilePath($token) {
		return Environment::expand( self::$uploadFileDirectory . DIRECTORY_SEPARATOR . "upload-" . $token  ."-" . uniqid() . ".tmp" );
	}


	/**
	 * Returns data from cache
	 *
	 * self::$token must be set!
	 *
	 * @return mixed
	 */
	static function getData($token,$create=false) {
		$cache = self::getCache();
		if(!isSet($cache[$token])) {
			$cache[$token] = array();
		}

		// Přidáme frontu do seznamu front
		if(!isSet($cache["queues"])) {
			$cache["queues"] = array();
		}
		$queues = $cache["queues"];
		if(!isSet($queues[$token])) {
			$queues[$token] = time();
		}
		$cache["queues"] = $queues;

		return $cache[$token];
	}

	/**
	 * Saves cache data
	 *
	 * self::$token must be set!
	 *
	 * @param mixed $store
	 * @return bool
	 */
	static function saveData($token,$store) {
		$cache = self::getCache();
		$cache[$token] = $store;

		// Fronta je aktuální - nastavíme jí aktuální čas
		$cache["queues"][$token] = time();

		return true;
	}

	/**
	 * Deletes data from cache
	 * @param string $token
	 */
	static function deleteData($token) {
		$cache = self::getCache();
		unset($cache[$token]);

		$queues = $cache["queues"];
		unset($queues[$token]);
		$cache["queues"] = $queues;
	}


	/*******************************************************************************
	**************************  Form Control  **************************************
	*******************************************************************************/

	/**
	 * Unique identifier
	 * @var string
	 */
	public $token;

	/**
	 * Maximum selected files in one input
	 * @var int
	 */
	public $maxFiles;

	/**
	 * Constructor
	 * @param string $label Label
	 */
	public function __construct($label = NULL,$maxSelectedFiles=999) {
		// Monitorování
		$this->monitor('Nette\Forms\Form');
		//$this->monitor('Nette\Application\Presenter');
		parent::__construct($label);

		if(!self::$handleUploadsCheck) {
			throw new InvalidStateException("MultipleFileUpload::handleUpload() has not been called. Call `MultipleFileUpload::register();` from your bootstrap before you call Applicaton::run();");
		};

		$this->maxFiles = $maxSelectedFiles;
		$this->control = Html::el("div");
	}

	/**
	 * Monitoring
	 * @param mixed $component
	 */
	protected function attached($component) {
		if ($component instanceof Form) {
			$component->getElementPrototype()->enctype = 'multipart/form-data';
			$component->getElementPrototype()->method  = 'post';
		}
	}

	/**
	 * Generates control
	 * @return Html
	 */
	public function getControl() {
		$this->setOption('rendered', TRUE);

		if(!$this->form->isSubmitted() and !$this->token) {
			$this->token = uniqid(rand());
		}

		// Create control
		$control = Html::el('div class=MultipleFileUpload')
			->id($this->getHtmlId());

		// <section token field>
		$tokenField = Html::el('input type=hidden')
			->name($this->getHtmlName() . '[token]')
			->value($this->getToken());
		$control->add($tokenField);
		// </section token field>

		// <section without JavaScript>
		$withoutJS  = Html::el("div class=withoutJS");
		$standardFileInput = Html::el("input type=file")
			->name($this->getHtmlName() . '[files][]');
		$withoutJS->add($this->createSectionWithoutJS($standardFileInput));
		$control->add($withoutJS);
		// </section without JavaScript>

		// <section with JavaScript>
		$withJS = Html::el("div class=withJS");
		$uploadifyID = $this->getHtmlId()."-uploadifyBox";
		$withJS->add($this->createSectionWithJS($uploadifyID,$this->getToken()));
		$control->add($withJS);
		// </section with JavaScript>

		// Pokud už byla volána metoda handleUploads -
		/*if(self::$handleUploadsCheck){
		    $control->add(Html::el('script type=text/javascript')->add(
			'jQuery("#' . $uploadifyID . '").uploadify(' . json_encode($this->uploaderOptions) . ');'
		    ));
		};*/

		return $control;
	}

	/**
	 * Creates sections withoutJS
	 * @param Html $input
	 * @return string
	 */
	function createSectionWithoutJS(Html $input) {
		$template = new MFUTemplate();
		$template->setFile(dirname(__FILE__).DIRECTORY_SEPARATOR."MultipleFileUpload-withoutJS.phtml");
		$template->input = $input;
		return $template->__toString();
	}

	/**
	 * Creates section withJS
	 * @param int $uploadifyId
	 * @param string $token
	 * @return string
	 */
	function createSectionWithJS($uploadifyId,$token) {
		$template = new MFUTemplate();
		$template->setFile(dirname(__FILE__)."/MultipleFileUpload-withJS.phtml");
		$template->sizeLimit = self::parseIniSize(ini_get('upload_max_filesize'));
		$template->token = $this->getToken();
		$template->maxFiles = $this->maxFiles;
		$template->backLink = (string)$this->form->action;
		$template->uploadifyId = $uploadifyId;
		return $template->__toString();
	}

	/**
	 * Loads and process STANDARD http request. NOT uploadify requests!
	 */
	public function loadHttpData() {
		$name = strtr(str_replace(']', '', $this->getHtmlName()), '.', '_');
		$data = $this->getForm()->getHttpData();
		if (isset($data[$name])) {

			if (isset($data[$name]["token"])) {
				$this->token = $data[$name]["token"];
			}else
				throw new InvalidStateException("Token has not been received! Without token MultipleFileUploader can't identify files.");

			self::handleUploads();
		}
	}

	/**
	 * Setts value
	 * @param mixed $value
	 */
	public function setValue($value) {
		if($value === null) {
			// pole se vymaže samo v destructoru
		}else
			throw new NotSupportedException('Value of MultiFileUpload component cannot be directly set.');
	}

	/**
	 * Getts value
	 * @return array
	 */
	public function getValue() {
		$data = self::getData($this->getToken());

		// Ořízneme data navíc
		$pocetPolozek = count($data);
		if($pocetPolozek > $this->maxFiles) {
			$rozdil = $pocetPolozek - $this->maxFiles;
			for($rozdil = $pocetPolozek - $this->maxFiles;$rozdil>0;$rozdil--) {
				array_pop($data);
			}
		}

		return $data;
	}

	/**
	 * Returns token
	 * @return string
	 */
	public function getToken() {
		if(!$this->token)
			$this->loadHttpData();
		return $this->token;
	}

	/**
	 * Destructors: makes fast cleanup
	 */
	public function  __destruct() {
		if($this->form->isSubmitted()) {
			$data = self::getData($this->getToken());
			$dir = Environment::expand(self::$uploadFileDirectory);
			foreach($data AS $file) {
				$tmpFile = $file->getTemporaryFile();
				$tmpFileDir = dirname($tmpFile);
				if($dir == $tmpFileDir and file_exists($tmpFile)) {
					// Pokud soubor nebyl zpracován (nebyl přesunut do jiného umístění)
					@unlink($tmpFile);
				}
			}
			self::deleteData($this->getToken());
		}
	}

	/**
	 * Filled validator: has been any file uploaded?
	 * @param  IFormControl
	 * @return bool
	 */
	public static function validateFilled(IFormControl $control) {
		$files = $control->getValue();
		return (count($files)>0);
	}



	/**
	 * FileSize validator: is file size in limit?
	 * @param  MultipleFileUpload
	 * @param  int  file size limit
	 * @return bool
	 */
	public static function validateFileSize(FileUpload $control, $limit) {
		$files = $control->getValue();
		$size=0;
		foreach($files AS $file) {
			$size += $file->getSize();
		}
		return $size <= $limit;
	}

	/**
	 * MimeType validator: has file specified mime type?
	 * @param  FileUpload
	 * @param  array|string  mime type
	 * @return bool
	 */
	public static function validateMimeType(FileUpload $control, $mimeType) {
		throw new NotSupportedException("Can't validate mime type on multiple files!");
		return FALSE;
	}

	/********************* Helpers *********************/

	protected static $unitScale = array('k' => 1024, 'm' => 1048576, 'g' => 1073741824);

	/**
	 * Parses ini size
	 * @param string $value
	 * @return int
	 */
	public static function parseIniSize($value) {
		$unit = strtolower(substr($value, -1));

		if (is_numeric($unit) || !isset(self::$unitScale[$unit]))
			return $value;

		return ((int) $value) * self::$unitScale[$unit];
	}
}


/**
 * Extension method for FormContainer
 */
function FormContainer_addMultipleFileUpload(Form $_this,$name, $label = NULL,$maxFiles=999) {
	return $_this[$name] = new MultipleFileUpload($label,$maxFiles);
}
FormContainer::extensionMethod("FormContainer::addMultipleFileUpload", "FormContainer_addMultipleFileUpload");
