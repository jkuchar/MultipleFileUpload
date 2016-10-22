<?php

namespace MultipleFileUpload;

use Nette;

class Extension extends Nette\DI\CompilerExtension 
{
	
	
	public function loadConfiguration() {
		$builder = $this->getContainerBuilder();
		$this->compiler->parseServices($builder, $this->loadFromFile(__DIR__ . '/config.neon'));
	}
	
	
	public function beforeCompile() {
		parent::beforeCompile();
	}
	
	
	public function afterCompile(Nette\PhpGenerator\ClassType $class) {
		$initialize = $class->methods['initialize'];
		$initialize->addBody('Nette\Forms\Container::extensionMethod("\Nette\Forms\Container::addMultipleFileUpload", function (\Nette\Forms\Container $_this, $name, $label = null, $maxFileSize = 25) { return $_this[$name] = new MultipleFileUpload\MultipleFileUpload($label, $maxFileSize, $this->getByType(?), $this->getByType(?)); });', ['Nette\Http\IRequest', 'MultipleFileUpload\UI\Registrator']);
		$initialize->addBody('MultipleFileUpload\MultipleFileUpload::init($this->getService(?), $this->getByType(?), $this->getByType(?));', ['mfuStorage', 'Nette\Http\IRequest', 'MultipleFileUpload\UI\Registrator']);
		$initialize->addBody('$this->getService(?)->onStartup[] = [?, ?];', ['application', 'MultipleFileUpload\MultipleFileUpload', 'handleUploads']);
		$initialize->addBody('$this->getService(?)->onShutdown[] = [?, ?];', ['application', 'MultipleFileUpload\MultipleFileUpload', 'cleanCache']);
	}

}
