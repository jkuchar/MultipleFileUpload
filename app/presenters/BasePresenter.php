<?php

/**
 * My Application
 *
 * @copyright  Copyright (c) 2009 John Doe
 * @package    MyApplication
 */



/**
 * Base class for all application presenters.
 *
 * @author     John Doe
 * @package    MyApplication
 */
abstract class BasePresenter extends Presenter
{
	public $oldLayoutMode = FALSE;

        public function startup() {
            parent::startup();
            $this->invalidateControl("flashes");
        }

        public function flashMessage($message,$type="info") {
            $this->invalidateControl("flashes");
            parent::flashMessage($message, $type);
        }

}
