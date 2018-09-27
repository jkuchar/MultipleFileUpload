<?php

/**
 * MultipleFileUpload (https://github.com/jkuchar/MultipleFileUpload/)
 *
 * Copyright (c) 2014 Jan Kuchař (https://github.com/jkuchar), Ciki (https://github.com/Ciki)
 * and contributors https://github.com/jkuchar/MultipleFileUpload/graphs/contributors
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace MultipleFileUpload;

use MultipleFileUpload\Model\IQueue,
	MultipleFileUpload\Model\IQueues,
	MultipleFileUpload\UI\Registrator,
	Latte,
	Nette,
	Nette\Bridges,
	Nette\Forms,
	Nette\Forms\Container,
	Nette\Forms\Controls\UploadControl,
	Nette\Http\FileUpload,
	Nette\InvalidStateException,
	Nette\NotSupportedException,
	Nette\Utils\Html,
	Nette\Utils\Strings;

class MultipleFileUpload extends UploadControl
{
	/**
	 * Is files handle uploads called?
	 * @var bool
	 * @see self::handleUploads()
	 */
	private static $handleUploadsCalled = false;

	/**
	 * Model
	 * @var IQueues
	 * @see self::init()
	 */
	protected static $queuesModel;

	/**
	 * Validate file callback
	 * @var Callback
	 * @return bool
	 * @param FileUpload File to be checked
	 */
	public static $validateFileCallback;

	/**
	 * Interface registrator instance
	 * @var Registrator
	 */
	public static $interfaceRegistrator;

	/**
	 * Root of mfu directory in public folder (used for serving js, css, ...)
	 * @var string
	 */
	public static $baseWWWRoot = null;

	/** @var Nette\Http\IRequest */
	private static $request;


	/**
	 * Initialize MFU uploads handling and cache cleanups
	 * For form control initialization {@see self::__contruct()}.
	 */
	public static function init(Model\IQueues $queuesModel, Nette\Http\IRequest $request, UI\Registrator $registrator)
	{
		self::$queuesModel = $queuesModel;
		self::$request = $request;
		self::$interfaceRegistrator = $registrator;

		// Set default check callback
		self::$validateFileCallback = [__CLASS__, "validateFile"];
		self::$baseWWWRoot = self::$request->url->baseUrl . "MultipleFileUpload/";
	}


	/* ##########  HANDLING UPLOADS  ########### */

	/**
	 * Sets life time of files in queue (shortcut for self::getQueuesModel()->setLifeTime)
	 * @param int $lifeTime Time in seconds
	 */
	static function setLifeTime($lifeTime)
	{
		self::getQueuesModel()
			->setLifeTime((int) $lifeTime);
	}


	protected static function _doSetLifetime()
	{
		// Auto config of lifeTime
		$maxInputTime = (int) ini_get("max_input_time");
		// default if no max input time defined (-1)
		if ($maxInputTime < 0) {
			$lifeTime = 3600;
		} else {
			$lifeTime = $maxInputTime + 5;
		}

		self::setLifeTime($lifeTime);
	}


	/**
	 * Handles uploading files
	 */
	public static function handleUploads()
	{
		if (self::$handleUploadsCalled === true) {
			return;
		} else {
			self::$handleUploadsCalled = true;
		}
		
		$request = self::$request;
		// Workaround for: http://forum.nettephp.com/cs/3680-httprequest-getheaders-a-content-type
		$contentType = $request->getHeader("content-type");
		if (!$contentType and isset($_SERVER["CONTENT_TYPE"])) {
			$contentType = $_SERVER["CONTENT_TYPE"];
		}

		if ($request->getMethod() !== "POST") {
			return;
		}

		self::getQueuesModel()->initialize();

		foreach (self::getUIRegistrator()->getInterfaces(self::$request) AS $interface) {
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
	 * @param FileUpload $file
	 * @return bool
	 */
	public static function validateFile(FileUpload $file)
	{
		return $file->isOk();
	}


	/**
	 * Cleans cache
	 */
	public static function cleanCache()
	{
		if (false == self::getUIRegistrator()->getProductionMode() or rand(1, 100) < 5) {
			self::getQueuesModel()->cleanup();
		}
	}


	/**
	 * @return IQueues
	 * @throws InvalidStateException
	 */
	public static function getQueuesModel()
	{
		if (!self::$queuesModel instanceof IQueues) {
			throw new InvalidStateException("Queues model was not set or is not instance of Model\IQueues!");
		}
		return self::$queuesModel;
	}


	/**
	 * Sets new queues model
	 * @param IQueues $model
	 */
	public static function setQueuesModel(IQueues $model) {
		self::$queuesModel = $model;
		self::_doSetLifetime();
	}



	/**
	 * @return Registrator
	 */
	public static function getUIRegistrator()
	{
		if (!self::$interfaceRegistrator instanceof Registrator) {
			throw new InvalidStateException("Interface registrator is not instance of MultipleFileUpload\\UI\\Registrator!");
		}
		return self::$interfaceRegistrator;
	}


	public static function getHead()
	{
		$out = "";
		foreach ($this->interfaces AS $interface) {
			$out .= $interface->renderHeadSection();
		}
		return $out;
	}


	/*	 * ****************************************************************************
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
	public function __construct($label = NULL, $maxSelectedFiles = 25, Nette\Http\IRequest $request, UI\Registrator $registrator)
	{
		parent::__construct($label);
		if (!self::$handleUploadsCalled) {
			throw new InvalidStateException("MultipleFileUpload::handleUpload() has not been called. Call `MultipleFileUpload::register();` from your bootstrap before you call Applicaton::run();");
		};
		
		$this->maxFiles = $maxSelectedFiles;
		$this->control = Html::el("div"); // TODO: support for prototype
		$this->maxFileSize = self::parseIniSize(ini_get('upload_max_filesize'));
		$this->simUploadThreads = 5;
		// Set default check callback
		self::$request = $request;
		self::$interfaceRegistrator = $registrator;
		self::$validateFileCallback = [__CLASS__, "validateFile"];
		self::$baseWWWRoot = self::$request->url->baseUrl . "MultipleFileUpload/";
	}


	/**
	 * Generates control
	 * @return Html
	 */
	public function getControl()
	{
		$this->setOption('rendered', TRUE);

		// Create control
		$control = Html::el('div class=MultipleFileUpload')->id($this->getHtmlId());

		// <section token field>
		$tokenField = Html::el('input type=hidden')->name($this->getHtmlName() . '[token]')->value($this->getToken());
		$control->addHtml($tokenField);
		// </section token field>

		$fallbacks = array();
		$interfaces = self::getUIRegistrator()->getInterfaces(self::$request);
		$num = count($interfaces);
		$cnt = 1;
		foreach ($interfaces AS $interface) {
			$html = $interface->render($this);
			// remove wrapping <script> tags
			$init = Strings::replace($interface->renderInitJavaScript($this), '/\s*<\/?script>\s*/');
			$desctruct = $interface->renderDestructJavaScript($this);
			$id = $this->getHtmlId() . "-MFUInterface-" . Strings::webalize($interface->reflection->name);

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

			$control->addHtml($container);
		}

			$latte = new Latte\Engine();
			$template = new Bridges\ApplicationLatte\Template($latte);
			$template->setFile(__DIR__ . DIRECTORY_SEPARATOR . "RegisterJS.latte");
			$template->id = $this->getHtmlId();
			$template->fallbacks = $fallbacks;
			$control->addHtml($template->__toString(TRUE));
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

		// If handleUploads() already called -
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
	public function loadHttpData()
	{
		$name = $this->getHtmlName() . '[token]';
		$data = $this->getForm()->getHttpData();

		// Get queue token for received files
		//  -> js & non-js as well (for js only the token is received)
		if (!empty($data)) {
			$token = Forms\Helpers::extractHttpData($data, $name, Forms\Form::DATA_LINE);
			if ($token) {
				$this->token = $token;
			} else {
				throw new InvalidStateException("Token has not been received! Without token MultipleFileUploader can't identify which files has been received.");
			}
		}
	}


	/**
	 * Sets value
	 * @param mixed $value
	 */
	public function setValue($value)
	{
		if ($value === null) {
			// deleted automatically in destructor
		} else {
			throw new NotSupportedException('Value of MultiFileUpload component cannot be directly set.');
		}
	}


	/**
	 * Gets value
	 * @return array
	 */
	public function getValue()
	{
		$data = $this->getQueue()->getFiles();

		// Get only first <N> allowed files
		// TODO: Implement as validation rule?
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
	public function getToken($need = true)
	{
		// Load token from request
		if (!$this->token) {
			$this->loadHttpData();
		}

		// If upload does not start, generate queueID
		if (!$this->token and ! $this->form->isSubmitted()) {
			$this->token = uniqid(rand());
		}

		if (!$this->token AND $need) {
			throw new InvalidStateException("Can't get a token!");
		}

		return $this->token;
	}


	/**
	 * Gets queue model
	 * @return IQueue
	 */
	public function getQueue()
	{
		return self::getQueuesModel()->getQueue($this->getToken());
	}


	/**
	 * Destructors: makes fast cleanup
	 */
	public function __destruct()
	{
		if ($this->getForm()->isSubmitted()) {
			// comment out if you want to keep files after form submission as well, for cases of server errors so F5 can be used to refresh
			$this->getQueue()->delete();
		}
	}


	/*	 * ****************************************************************************
	 * ***************************  Validators  **************************************
	 * ***************************************************************************** */

	/**
	 * Filled validator: has been any file uploaded?
	 * @param Forms\IControl
	 * @return bool
	 */
	public static function validateFilled(Forms\IControl $control)
	{
		$files = $control->getValue();
		return (count($files) > 0);
	}


	/**
	 * FileSize validator: is file size in limit?
	 * @param  Forms\Controls\UploadControl
	 * @param  int  file size limit
	 * @return bool
	 */
	public static function validateFileSize(Forms\Controls\UploadControl $control, $limit)
	{
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
	public static function validateMimeType(Forms\Controls\UploadControl $control, $mimeType)
	{
		throw new NotSupportedException("Can't validate mime type! This is MULTIPLE file upload control.");
	}


	/*	 * ******************* Helpers ******************** */

	/**
	 * Parses ini size
	 * @param string $value
	 * @return int
	 */
	public static function parseIniSize($value)
	{
		$units = array('k' => 1024, 'm' => 1048576, 'g' => 1073741824);

		$unit = strtolower(substr($value, -1));

		if (is_numeric($unit) || !isset($units[$unit]))
			return $value;

		return ((int) $value) * $units[$unit];
	}


}

/**
 * Extension method for FormContainer

function FormContainer_addMultipleFileUpload(Forms\Container $_this, $name, $label = NULL, $maxFiles = 25)
{
	return $_this[$name] = new MultipleFileUpload($label, $maxFiles);
}


Container::extensionMethod("\Nette\Forms\Container::addMultipleFileUpload", "MultipleFileUpload\FormContainer_addMultipleFileUpload");
 */
