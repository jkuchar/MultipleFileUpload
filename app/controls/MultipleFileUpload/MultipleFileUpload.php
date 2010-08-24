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

	const VERSION = '$Rev$ released on $Date$';

	/**
	 * Is files handle uploads called?
	 * @var bool
	 * @see self::handleUploads()
	 */
	private static $handleUploadsCalled = false;
	/**
	 * Model
	 * @var IMFUQueuesModel
	 * @see self::init()
	 */
	protected static $queuesModel;
	/**
	 * Validate file callback
	 * @var Callback
	 * @return bool
	 * @param HttpUploadedFile File to be checked
	 */
	public static $validateFileCallback;
	/**
	 * Interface registrator instance
	 * @var MFUUIRegistrator
	 */
	public static $interfaceRegistrator;

	/**
	 * Initialize MFU
	 */
	public static function init() {

		// Init UI registrator
		$uiReg = self::$interfaceRegistrator = new MFUUIRegistrator();
		$uiReg->register("MFUUIHTML4SingleUpload");
		$uiReg->register("MFUUIUploadify");

		// Set default check callback
		self::$validateFileCallback = callback(__CLASS__, "validateFile");

	}


	/**
	 * Register MFU into Nette
	 */
	public static function register() {
		self::init();

		$application = Environment::getApplication();
		$application->onStartup[] = callback("MultipleFileUpload::handleUploads");
		$application->onShutdown[] = callback("MultipleFileUpload::cleanCache");
	}

	/* ##########  HANDLING UPLOADS  ########### */

	/**
	 * Setts life time of files in queue (shortcut for self::getQueuesModel()->setLifeTime)
	 * @param int $lifeTime Time in seconds
	 */
	static function setLifeTime($lifeTime) {
		self::getQueuesModel()
			->setLifeTime((int) $lifeTime);
	}

	protected static function _doSetLifetime() {
		// Auto cofing of lifeTime
		$maxInputTime = (int) ini_get("max_input_time");
		if ($maxInputTime < 0) { // Pokud není žádný maximální čas vstupu (-1)
			$lifeTime = 3600;
		} else {
			$lifeTime = $maxInputTime + 5; // Maximální čas vstupu + pár sekund
		}

		self::setLifeTime($lifeTime);
	}

	/**
	 * Handles uploading files
	 */
	public static function handleUploads() {
		// Pokud už bylo voláno handleUploads -> skonči
		if (self::$handleUploadsCalled === true) {
			return;
		} else {
			self::$handleUploadsCalled = true;
		}

		$req = Environment::getHttpRequest();

		// Workaround for: http://forum.nettephp.com/cs/3680-httprequest-getheaders-a-content-type
		$contentType = $req->getHeader("content-type");
		if (!$contentType AND isset($_SERVER["CONTENT_TYPE"])) {
			$contentType = $_SERVER["CONTENT_TYPE"];
		}

		if ($req->getMethod() !== "POST" OR !stristr($contentType, "multipart")) {
			return;
		}

		self::getQueuesModel()
			->initialize();

		foreach (self::getUIRegistrator()->getInterfaces() AS $interface) {
			if ($interface->isThisYourUpload()) {
				$ret = $interface->handleUploads();
				if ($ret === true)
					break;
			}
		}
	}

	/**
	 * Checks file if is ok and can be processed
	 * @param HttpUploadedFile $file
	 * @return bool
	 */
	public static function validateFile(HttpUploadedFile $file) {
		return $file->isOk();
	}

	/**
	 * Cleans cache
	 */
	public static function cleanCache() {
		if (!Environment::isProduction() OR rand(1, 100) < 5) {
			self::getQueuesModel()->cleanup();
		}
	}

	/**
	 * @return IMFUQueuesModel
	 */
	public static function getQueuesModel() {
		if(!self::$queuesModel) { // if nothing is set, setup sqlite model, which should work on all systems with SQLite
			self::setQueuesModel(new MFUQueuesSQLite());
		}

		if (!self::$queuesModel instanceof IMFUQueuesModel) {
			throw new InvalidStateException("Queues model is not instance of IMFUQueuesModel!");
		}
		
		return self::$queuesModel;
	}

	public static function setQueuesModel(IMFUQueuesModel $model) {
		self::$queuesModel = $model;
		self::_doSetLifetime();
	}

	/**
	 * @return MFUUIRegistrator
	 */
	public static function getUIRegistrator() {
		if (!self::$interfaceRegistrator instanceof MFUUIRegistrator) {
			throw new InvalidStateException("Interface registrator is not instance of MFUUIRegistrator!");
		}
		return self::$interfaceRegistrator;
	}

	public static function getHead() {
		$out = "";
		foreach(self::getUIRegistrator()->getInterfaces() AS $interface) {
			$out .= $interface->renderHeadSection();
		}
		return $out;
	}

	/*	 * *****************************************************************************
	 * *************************  Form Control  **************************************
	 * ***************************************************************************** */

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
	public function __construct($label = NULL, $maxSelectedFiles=999) {
		// Monitorování
		$this->monitor('Nette\Forms\Form');
		//$this->monitor('Nette\Application\Presenter');

		parent::__construct($label);

		if (!self::$handleUploadsCalled) {
			throw new InvalidStateException("MultipleFileUpload::handleUpload() has not been called. Call `MultipleFileUpload::register();` from your bootstrap before you call Applicaton::run();");
		};

		$this->maxFiles = $maxSelectedFiles;
		$this->control = Html::el("div"); // TODO: support for prototype
		$this->maxFileSize = self::parseIniSize(ini_get('upload_max_filesize'));
		$this->simUploadThreads = 5;
	}

	/**
	 * Monitoring
	 * @param mixed $component
	 */
	protected function attached($component) {
		if ($component instanceof Form) {
			$component->getElementPrototype()->enctype = 'multipart/form-data';
			$component->getElementPrototype()->method = 'post';
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

		$fallbacks = array();

		$interfaces = self::getUIRegistrator()->getInterfaces();
		$num = count($interfaces);
		$cnt = 1;
		foreach ($interfaces AS $interface) {
			$html = $interface->render($this);
			$init = $interface->renderInitJavaScript($this);
			$desctruct = $interface->renderDestructJavaScript($this);
			$id = $this->getHtmlId() . "-MFUInterface-" . $interface->reflection->name;
			
			$fallback = (object) array(
			    "id" => $id,
			    "init" => $init,
			    "destruct" => $desctruct
			);
			$fallbacks[] = $fallback;

			$container = Html::el("div");
			$container->setHtml($html);
			$container->id = $id;

			if ($cnt == $num) { // Last (will be rendered as JavaScript-disabled capable)
				$container->style["display"] = "block";
			} else {
				$container->style["display"] = "none";
			}
			$cnt++;

			$control->add($container);
		}

		$template = new MFUTemplate();
		$template->setFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . "RegisterJS.phtml");
		$template->id = $this->getHtmlId();
		$template->fallbacks = $fallbacks;
		$control->add($template->__toString(TRUE));
		/*
		  // <section without JavaScript>
		  $withoutJS = Html::el("div class=withoutJS");
		  $standardFileInput = Html::el("input type=file")
		  ->name($this->getHtmlName() . '[files][]');
		  $withoutJS->add($this->createSectionWithoutJS($standardFileInput));
		  $control->add($withoutJS);
		  // </section without JavaScript>
		  // <section with JavaScript>
		  $withJS = Html::el("div class=withJS");
		  $uploadifyID = $this->getHtmlId() . "-uploadifyBox";
		  $withJS->add($this->createSectionWithJS($uploadifyID, $this->getToken()));
		  $control->add($withJS);
		  // </section with JavaScript>
		 */

		// Pokud už byla volána metoda handleUploads -
		/* if(self::$handleUploadsCheck){
		  $control->add(Html::el('script type=text/javascript')->add(
		  'jQuery("#' . $uploadifyID . '").uploadify(' . json_encode($this->uploaderOptions) . ');'
		  ));
		  }; */

		return $control;
	}

	/**
	 * Loads and process STANDARD http request. NOT uploadify requests!
	 */
	public function loadHttpData() {
		$name = strtr(str_replace(']', '', $this->getHtmlName()), '.', '_');
		$data = $this->getForm()->getHttpData();
		if (isset($data[$name])) {
			// Zjistí token fronty souborů, kterou jsou soubory doručeny
			//  -> Jak JS tak bez JS (akorát s JS už dorazí pouze token - nic jiného)
			if (isset($data[$name]["token"])) {
				$this->token = $data[$name]["token"];
			} else {
				throw new InvalidStateException("Token has not been received! Without token MultipleFileUploader can't identify which files has been received.");
			}
		}
	}

	/**
	 * Setts value
	 * @param mixed $value
	 */
	public function setValue($value) {
		if ($value === null) {
			// pole se vymaže samo v destructoru
		} else {
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
		// TODO: Nepřesunot jako validační pravidlo?
		$pocetPolozek = count($data);
		if ($pocetPolozek > $this->maxFiles) {
			$rozdil = $pocetPolozek - $this->maxFiles;
			for ($rozdil = $pocetPolozek - $this->maxFiles; $rozdil > 0; $rozdil--) {
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
		if (!$this->token) {
			$this->loadHttpData();
		}

		// If upload do not start, generate queueID
		if (!$this->token and !$this->form->isSubmitted()) {
			$this->token = uniqid(rand());
		}

		if (!$this->token AND $need) {
			throw new InvalidStateException("Can't get a token!");
		}

		return $this->token;
	}

	/**
	 * Getts queue model
	 * @return IMFUQueueModel
	 */
	public function getQueue() {
		return self::getQueuesModel()->getQueue($this->getToken());
	}

	/**
	 * Destructors: makes fast cleanup
	 */
	public function __destruct() {
		if ($this->getForm()->isSubmitted()) {
			$this->getQueue()->delete();
		}
	}

	/*	 * *****************************************************************************
	 * ***************************  Validators  **************************************
	 * ***************************************************************************** */

	/**
	 * Filled validator: has been any file uploaded?
	 * @param  IFormControl
	 * @return bool
	 */
	public static function validateFilled(IFormControl $control) {
		$files = $control->getValue();
		return (count($files) > 0);
	}

	/**
	 * FileSize validator: is file size in limit?
	 * @param  MultipleFileUpload
	 * @param  int  file size limit
	 * @return bool
	 */
	public static function validateFileSize(FileUpload $control, $limit) {
		$files = $control->getValue();
		$size = 0;
		foreach ($files AS $file) {
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
		throw new NotSupportedException("Can't validate mime type! This is MULTIPLE file upload control.");
	}

	/*	 * ******************* Helpers ******************** */

	/**
	 * Parses ini size
	 * @param string $value
	 * @return int
	 */
	public static function parseIniSize($value) {
		$units = array('k' => 1024, 'm' => 1048576, 'g' => 1073741824);

		$unit = strtolower(substr($value, -1));

		if (is_numeric($unit) || !isset($units[$unit]))
			return $value;

		return ((int) $value) * $units[$unit];
	}

}

/**
 * Extension method for FormContainer
 */
function FormContainer_addMultipleFileUpload(Form $_this, $name, $label = NULL, $maxFiles=999) {
	return $_this[$name] = new MultipleFileUpload($label, $maxFiles);
}

FormContainer::extensionMethod("FormContainer::addMultipleFileUpload", "FormContainer_addMultipleFileUpload");

