<?php

/**
 * My Application
 *
 * @copyright  Copyright (c) 2009 John Doe
 * @package    MyApplication
 */



/**
 * Homepage presenter.
 *
 * @author     John Doe
 * @package    MyApplication
 */
class HomepagePresenter extends BasePresenter
{

        public function actionDefault(){

        }

        public function createComponentForm($name) {
            $form = new AppForm($this, $name);
            $form->getElementPrototype()->class[] = "ajax";
            $form->addText("test","Textové políčko")
              ->addRule(Form::FILLED, "Textové políčko test musí být vyplněno!");
            $form->addMultipleFileUpload("upload","Upload test")
                ->addRule("MultipleFileUpload::validateFilled","Musíte odeslat alespoň jeden soubor!")
                ->addRule("MultipleFileUpload::validateFileSize","Soubory jsou dohromady moc veliké!",100*1024); // 1 KB
            $form->addSubmit("odeslat", "Odeslat");
            $form->onSubmit[] = array($this,"onSubmit");

            // Invalidace snippetů
            $form->onInvalidSubmit[] = array($this,"handlePrekresliForm");
            $form->onSubmit[] = array($this,"handlePrekresliForm");
        }

        public function onSubmit(Form $form) {
            $data = $form->getValues();

            // Předáme data do šablony
            $this->template->values = $data;

            // Přesumene uploadované soubory
            foreach($data["upload"] AS $file){
                // $file je instance HttpUploadedFile
                if($file->move(APP_DIR."/uploadData/".$file->getName()))
                    $this->flashMessage("Přesunut soubor ".$file->getName());
            }
        }

        public function handlePrekresliForm() {
            $this->invalidateControl("form");
        }

	public function renderDefault()
	{
		
	}

}
