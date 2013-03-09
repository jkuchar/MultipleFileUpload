<?php

/**
 * MultipleFileUpload (https://github.com/jkuchar/MultipleFileUpload/)
 *
 * Copyright (c) 2013 Jan Kuchař (http://www.jankuchar.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace MultipleFileUpload;

use Nette\Environment;
use Nette\Utils\Html;

use Nette\Forms;

use Nette\InvalidStateException;
use Nette\NotSupportedException;


class MultipleFileUpload extends Forms\Controls\UploadControl {

	const NAME = "Multiple File Uploader";
	const VERSION = '$Rev: 77 $ released on $Date: 2011-03-17 23:52:31 +0100 (čt, 17 3 2011) $';

	/**
	 * Is files handle uploads called?
	 * @var bool
	 * @see self::handleUploads()
	 */
	private static $handleUploadsCalled = false;

	/**
	 * Model
	 * @var Model\IQueues
	 * @see self::init()
	 */
	protected static $queuesModel;

	/**
	 * Validate file callback
	 * @var Callback
	 * @return bool
	 * @param \Nette\Http\FileUpload File to be checked
	 */
	public static $validateFileCallback;

	/**
	 * Interface registrator instance
	 * @var UI\Registrator
	 */
	public static $interfaceRegistrator;
	
	/**
	 * Root of mfu directory in public folder (used for serving js, css, ...)
	 * @var type string
	 */
	public static $baseWWWRoot = null;

	/**
	 * Initialize MFU
	 */
	public static function init() {

		// Init UI registrator
		$uiReg = self::$interfaceRegistrator = new UI\Registrator();
		$uiReg->register("MultipleFileUpload\\UI\\HTML4SingleUpload");
		$uiReg->register("MultipleFileUpload\\UI\\Plupload");

		// Set default check callback
		self::$validateFileCallback = callback(__CLASS__, "validateFile");
		
		// TODO: remove this magic
		self::$baseWWWRoot = Environment::getHttpRequest()->url->baseUrl . "MultipleFileUpload/";
	}

	/**
	 * Register MFU into Nette
	 */
	public static function register() {
		self::init();

		$application = Environment::getApplication();
		$application->onStartup[]  = callback(__CLASS__, "handleUploads");
		$application->onShutdown[] = callback(__CLASS__, "cleanCache");
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
		if (!$contentType and isset($_SERVER["CONTENT_TYPE"])) {
			$contentType = $_SERVER["CONTENT_TYPE"];
		}

		if ($req->getMethod() !== "POST") {
			return;
		}
				
		self::getQueuesModel()->initialize();

		foreach (self::getUIRegistrator()->getInterfaces() AS $interface) {
//			\Nette\Diagnostics\Debugger::log($interface->getReflection()->getName().": is this your upload? ".$interface->isThisYourUpload());
			if ($interface->isThisYourUpload()) {
				$ret = $interface->handleUploads();
				if ($ret === true)
					break;
			}
		}
	}

	/**
	 * Checks file if is ok and can be processed
	 * @param \Nette\Http\FileUpload $file
	 * @return bool
	 */
	public static function validateFile(\Nette\Http\FileUpload $file) {
		return $file->isOk();
	}

	/**
	 * Cleans cache
	 */
	public static function cleanCache() {
		if (!Environment::isProduction() or rand(1, 100) < 5) {
			self::getQueuesModel()->cleanup();
		}
	}

	/**
	 * 
	 * @return type
	 * @throws \Nette\InvalidStateException
	 */
	public static function getQueuesModel() {
		if (!self::$queuesModel) { // if nothing is set, setup sqlite model, which should work on all systems with SQLite
			self::setQueuesModel(new Model\SQLite\Queues());
		}

		if (!self::$queuesModel instanceof Model\IQueues) {
			throw new \Nette\InvalidStateException("Queues model is not instance of Model\IQueues!");
		}
		return self::$queuesModel;
	}

	/**
	 * Setts new queues model
	 * @param \MultipleFileUpload\Model\IQueues $model
	 */
	public static function setQueuesModel(Model\IQueues $model) {
		self::$queuesModel = $model;
		self::_doSetLifetime();
	}

	/**
	 * @return UI\Registrator
	 */
	public static function getUIRegistrator() {
		if (!self::$interfaceRegistrator instanceof UI\Registrator) {
			throw new InvalidStateException("Interface registrator is not instance of MFUUIRegistrator!");
		}
		return self::$interfaceRegistrator;
	}

	public static function getHead() {
		// TODO: Add MFUFallbackController?
		
		$out = "";
		foreach (self::getUIRegistrator()->getInterfaces() AS $interface) {
			$out .= $interface->renderHeadSection();
		}
		return $out;
	}

	/* *****************************************************************************
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
	public function __construct($label = NULL, $maxSelectedFiles = 25) {
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
		if ($component instanceof Nette\Application\UI\Form) {
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
		$control = Html::el('div class=MultipleFileUpload')->id($this->getHtmlId());

		// <section token field>
		$tokenField = Html::el('input type=hidden')->name($this->getHtmlName() . '[token]')->value($this->getToken());
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

		$template = new UI\Template();
		$template->setFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . "RegisterJS.latte");
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
		// TODO: Nepřesunout jako validační pravidlo?
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
	public function getToken($need = true) {
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
	 * @return Model\IQueue
	 */
	public function getQueue() {
		return self::getQueuesModel()->getQueue($this->getToken());
	}

	/**
	 * Destructors: makes fast cleanup
	 */
	public function __destruct() {
		if ($this->getForm()->isSubmitted()) {
			// comment out if you want to keep files after form submission as well, for cases of server errors so F5 can be used to refresh
			$this->getQueue()->delete();
		}
	}

	/* *****************************************************************************
	 * ***************************  Validators  **************************************
	 * ***************************************************************************** */

	/**
	 * Filled validator: has been any file uploaded?
	 * @param Forms\IControl
	 * @return bool
	 */
	public static function validateFilled(Forms\IControl $control) {
		$files = $control->getValue();
		return (count($files) > 0);
	}

	/**
	 * FileSize validator: is file size in limit?
	 * @param  Forms\Controls\UploadControl
	 * @param  int  file size limit
	 * @return bool
	 */
	public static function validateFileSize(Forms\Controls\UploadControl $control, $limit) {
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
	public static function validateMimeType(Forms\Controls\UploadControl $control, $mimeType) {
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
function FormContainer_addMultipleFileUpload(\Nette\Application\UI\Form $_this, $name, $label = NULL, $maxFiles = 25) {
	return $_this[$name] = new MultipleFileUpload($label, $maxFiles);
}

\Nette\Forms\Container::extensionMethod("\Nette\Forms\Container::addMultipleFileUpload", "MultipleFileUpload\FormContainer_addMultipleFileUpload");