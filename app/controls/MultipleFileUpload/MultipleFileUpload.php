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

	const NAME = "Multiple File Uploader";

	const REVISION = '$Rev$ released on $Date$';

	public static function init(){
		// init queue model
		self::$queuesModel = new MFUQueuesCache();

		// Auto cofing of lifeTime and cleanInterval
		$maxInputTime = (int)ini_get("max_input_time");
		if($maxInputTime < 0) { // Pokud není žádný maximální čas vstupu
			self::$lifeTime = 3600;
		}else{
			self::$lifeTime = $maxInputTime + 5; // Maximální čas vstupu + pár sekund
		}
		self::$cleanInterval = self::$lifeTime * 5;

	}

	public static function register() {
		self::init();

		$application = Environment::getApplication();
		$application->onStartup[]  = "MultipleFileUpload::handleUploads";
		$application->onShutdown[] = "MultipleFileUpload::cleanCache";
	}

	/* ##########  HANDLING UPLOADS  ########### */

	/**
	 * Lifetime of files in queue
	 * When clean up is running, it is watching if there is any added file in their lifetime.
	 *
	 * @var int Time in seconds
	 * @see self::init()
	 */
	public static $lifeTime;

	/**
	 * Cleaning up interval
	 * @var int In seconds
	 * @see self::init()
	 */
	public static $cleanInterval;

	/**
	 * Is files handle uploads called?
	 * @var bool
	 * @see self::handleUploads()
	 */
	protected static $handleUploadsCalled = false;

	/**
	 * Model
	 * @var MFUQueuesModel
	 * @see self::init()
	 */
	public static $queuesModel;

	/**
	 * Handles uploading files
	 */
	public static function handleUploads() {

		// Pokud už bylo voláno handleUploads - skonči
		if(self::$handleUploadsCalled === true) {
			return;
		}else {
			self::$handleUploadsCalled = true;
		}

		$req = Environment::getHttpRequest();

		// Workaround for: http://forum.nettephp.com/cs/3680-httprequest-getheaders-a-content-type
		$contentType = $req->getHeader("content-type");
		if(!$contentType AND isset($_SERVER["CONTENT_TYPE"])) {
			$contentType = $_SERVER["CONTENT_TYPE"];
		}
		
		if($req->getMethod() !== "POST" OR !stristr($contentType,"multipart")) {
			return;
		}

		// Zpracuj soubory
		if(self::isRequestFromFlash()) {
			self::proccessFilesFromUploadify();
		}else {
			self::proccessFilesFormStandardHttpTransfer();
		}

	}

	/**
	 * (internal) Processes sigle file
	 */
	protected static function processSingleFile($token, $file) {
		if($file instanceof HttpUploadedFile and $file->isOk()) {
			self::getQueuesModel()->getQueue($token,true)
				->addFile($file);
			return true;
		}
		return false;
	}

	/**
	 * Zpracuje soubory z Uploadify
	 */
	protected static function proccessFilesFromUploadify() {
		self::getQueuesModel()->lock();
		if(!isSet($_POST["token"])) return;
		$token = $_POST["token"];
		foreach(Environment::getHttpRequest()->getFiles() AS $file) {
			self::processSingleFile($token, $file);
		}
		self::getQueuesModel()->unlock();

		// Odpověď klientovi
		die("1");
	}

	/**
	 * Zpracuje soubory ze standardního HTTP požadavku
	 */
	protected static function proccessFilesFormStandardHttpTransfer() {
		self::getQueuesModel()->lock();
		foreach(Environment::getHttpRequest()->getFiles() AS $name => $controlValue) {
			if(is_array($controlValue) and isSet($controlValue["files"]) and isSet($_POST[$name]["token"])) {
				$token = $_POST[$name]["token"];
				foreach($controlValue["files"] AS $file) {
					self::processSingleFile($token,$file);
				}
			}//else zpracuje si to už formulář sám (nejspíš tam bude už HTTPUploadedFile, ale odeslán z klasického FileUpload políčka)
		}
		self::getQueuesModel()->unlock();
	}

	/**
	 * Cleans cache
	 * @return bool
	 */
	public static function cleanCache() {
		self::$queuesModel->clean(self::$lifeTime,self::$cleanInterval);
	}

	/**
	 * Is request from flash?
	 * @return bool
	 */
	protected static function isRequestFromFlash() {
		return (Environment::getHttpRequest()->getHeader('user-agent') === 'Shockwave Flash');
	}

	/**
	 * @return MFUQueuesModel
	 */
	public static function getQueuesModel(){
		if(!self::$queuesModel instanceof IMFUQueuesModel){
			throw new InvalidStateException("Queues model is not instance of IMFUQueuesModel!");
		}
		return self::$queuesModel;
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
	 * Maximum file size of single uploaded file
	 * @var int
	 */
	public $maxFileSize;

	/**
	 * How many threads will be used to upload files
	 * @var int
	 */
	public $simUploadThreads;

	/**
	 * Constructor
	 * @param string $label Label
	 */
	public function __construct($label = NULL,$maxSelectedFiles=999) {
		// Monitorování
		$this->monitor('Nette\Forms\Form');
		//$this->monitor('Nette\Application\Presenter');

		parent::__construct($label);
		
		if(!self::$handleUploadsCalled) {
			throw new InvalidStateException("MultipleFileUpload::handleUpload() has not been called. Call `MultipleFileUpload::register();` from your bootstrap before you call Applicaton::run();");
		};

		$this->maxFiles = $maxSelectedFiles;
		$this->control = Html::el("div"); // TODO: support for prototype
		$this->maxFileSize = self::parseIniSize(ini_get('upload_max_filesize'));
		$this->simUploadThreads = (self::getQueuesModel()->threadSafe) ? 10 : 1;

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
	protected function createSectionWithoutJS(Html $input) {
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
	protected function createSectionWithJS($uploadifyId,$token) {
		$template = new MFUTemplate();
		$template->setFile(dirname(__FILE__)."/MultipleFileUpload-withJS.phtml");
		$template->sizeLimit = $this->maxFileSize;
		$template->token = $this->getToken();
		$template->maxFiles = $this->maxFiles;
		$template->backLink = (string)$this->form->action;
		$template->uploadifyId = $uploadifyId;
		$template->simUploadFiles = $this->simUploadThreads;
		return $template->__toString();
	}

	/**
	 * Loads and process STANDARD http request. NOT uploadify requests!
	 */
	public function loadHttpData() {
		$name = strtr(str_replace(']', '', $this->getHtmlName()), '.', '_');
		$data = $this->getForm()->getHttpData();
		if (isset($data[$name])) {

			// TODO: zjistit co to přesně dělá
			// Pokud se správně pamatuji, tak to má za úkol zjitit token, pokud je posíláno bez JS
			// a je na stránce více MFU
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
		}else{
			throw new NotSupportedException('Value of MultiFileUpload component cannot be directly set.');
		}
	}

	/**
	 * Getts value
	 * @return array
	 */
	public function getValue() {
		$data = $this->getQueue()->getFiles();

		// Ořízneme soubory, kterých je více než maximální *počet* souborů
		$pocetPolozek = count($data);
		if($pocetPolozek > $this->maxFiles) {
			$rozdil = $pocetPolozek - $this->maxFiles;
			for($rozdil = $pocetPolozek - $this->maxFiles; $rozdil>0; $rozdil--) {
				array_pop($data);
			}
		}

		return $data;
	}

	/**
	 * Returns token
	 * @return string|null
	 */
	public function getToken($need=true) {
		// Load token from request
		if(!$this->token) {
			$this->loadHttpData(); // Calls self::handleUploads()
		}

		// Generate token
		if(!$this->form->isSubmitted() and !$this->token) {
			$this->token = uniqid(rand());
		}

		if(!$this->token AND $need){
			throw new InvalidStateException("Can't get a token!");
		}
		
		return $this->token;
	}

	public function getQueue($create=false){
		$queues = self::getQueuesModel()/*->lock()*/;
		$queue  = $queues->getQueue($this->getToken(),$create);
		//$queues->unlock();
		return $queue;
	}

	/**
	 * Destructors: makes fast cleanup
	 */
	public function  __destruct() {
		if($this->getForm()->isSubmitted()) {
			self::getQueuesModel()->lock();
			$this->getQueue()->delete();
			self::getQueuesModel()->unlock();
		}
	}

	/*******************************************************************************
	****************************  Validators  **************************************
	*******************************************************************************/

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
		return false;
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

