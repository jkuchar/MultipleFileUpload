<?php

/**
 * My Application
 *
 * @copyright  Copyright (c) 2009 Jan Kuchař
 */



/**
 * Homepage presenter.
 *
 * @author     Jan Kuchař
 */
class HomepagePresenter extends BasePresenter
{

        public function createComponentForm($name) {
            $form = new AppForm($this, $name);
            $form->getElementPrototype()->class[] = "ajax";

            /*$form->addText("test","Textové políčko")
              ->addRule(Form::FILLED, "Textové políčko test musí být vyplněno!");*/

            // Uploadů můžete do formuláře samozdřejmě přidat více, ale zatím je docela nepříjemná validace a jedna chybka v JS
            $form->addMultipleFileUpload("upload","Soubory")
                /*->addRule("MultipleFileUpload::validateFilled","Musíte odeslat alespoň jeden soubor!")
                ->addRule("MultipleFileUpload::validateFileSize","Soubory jsou dohromady moc veliké!",100*1024)*/;

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

            $queueId = uniqid();

            // Přesumene uploadované soubory
            foreach($data["upload"] AS $file){
                // $file je instance HttpUploadedFile
		$newFilePath = APP_DIR."/uploadedData/q{".$queueId."}__f{".rand(10,99)."}__".$file->getName();

		// V produkčním módu nepřesunujeme soubory...
		if(!Environment::isProduction()) {
			if($file->move($newFilePath))
			    $this->flashMessage("Soubor ".$file->getName() . " byl úspěšně přesunut!");
			else
			    $this->flashMessage("Při přesouvání souboru ".$file->getName() . " nastala chyba! Pro více informací se podívejte do logů.");
		}
            }
        }

        public function handlePrekresliForm() {
            $this->invalidateControl("form");
        }

}
