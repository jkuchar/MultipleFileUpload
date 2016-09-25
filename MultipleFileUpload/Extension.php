<?php

namespace MultipleFileUpload;

use Nette;

class Extension extends Nette\DI\CompilerExtension {

    public function loadConfiguration() {
    }

    public function beforeCompile() {
        parent::beforeCompile();
    }

    public function afterCompile(Nette\PhpGenerator\ClassType $class) {
        $initialize = $class->methods['initialize'];
        $initialize->addBody('MultipleFileUpload\MultipleFileUpload::init($this->getByType(?), $this->getParameters());', ['Nette\Http\IRequest']);
        $initialize->addBody('MultipleFileUpload\MultipleFileUpload::register($this->getService(?));', ['mfuStorage']);
        $initialize->addBody('$this->getService(?)->onStartup[] = [?, ?];', ['application', 'MultipleFileUpload\MultipleFileUpload', 'handleUploads']);
        $initialize->addBody('$this->getService(?)->onShutdown[] = [?, ?];', ['application', 'MultipleFileUpload\MultipleFileUpload', 'cleanCache']);
    }

}
