_This is a fork of https://github.com/jkuchar/MultipleFileUpload/_

MultipleFileUpload
==================

Project that allows you to upload more files to server at once using Nette Forms. You can choose from various front-end interfaces and define your own fallbacks between them, best will be used.

Demo page: http://multiplefileupload.projekty.mujserver.net/

Installation
------------
Using Composer
1. Open terminal in your project
2. `composer require jkuchar/multiplefileupload:dev-master`
3. Copy files from libs/jkuchar/multiplefileupload/public to www/MultipleFileUpload
4. You are done!

OR
1. Download this repo as ZIP
2. Copy MultipleFileUpload directory to app/components (or wherever Nette can see it)
3. Copy public directory to web/MultipleFileUpload
4. You are done!

Actually there are few more steps to setup MFU:
5. Initialize MFU in app/bootstrap.php, e.g.
<pre><code>
	/**
   * Initialize MultipleFileUpload component
   */
  private function initMFU()
  {
      \MultipleFileUpload\MultipleFileUpload::register();
      \MultipleFileUpload\MultipleFileUpload::getUIRegistrator()
          ->clear()
          ->register("MultipleFileUpload\UI\HTML4SingleUpload")
          ->register("MultipleFileUpload\UI\Plupload");
//            ->register("MultipleFileUpload\UI\Swfupload");
//            ->register("MultipleFileUpload\UI\Uploadify");

      // SQLite3 driver
      \MultipleFileUpload\MultipleFileUpload::setQueuesModel(new \MultipleFileUpload\Model\SQLite3\Queues());
  }

</code></pre>
5. Use it in form
<pre><code>
protected function createComponentForm() {
    $form = new \MyAppForm;
    $form->addMultipleFileUpload('file_upload', 'Testík', 5)
        ->addRule('MultipleFileUpload\MultipleFileUpload::validateFilled', 'Musíte odoslať aspoň jeden súbor!')
        ->addRule('MultipleFileUpload\MultipleFileUpload::validateFileSize', 'Súbory sú dokopy príliš veľké!', 10*1024*1024); // 10 MB
    $form->addSubmit('send', 'Odoslať');
    $form->onSuccess[] = callback($this, 'success');
    return $form;
}
public function success($form)
{
    foreach ($form->values->file_upload as $file) {
        \Utils\FileUpload::save($file, DATA_DIR);
    }
    $this->flashMessage('Obrázky uložené.');
    $this->refresh();
}
</code></pre>
6. Link assets by adding following call to template (head section)
<pre><code>
	<script src="{$basePath}/MultipleFileUpload/MFUFallbackController.js"></script>
  {=\MultipleFileUpload\MultipleFileUpload::getHead()|noescape}
</code></pre>

For more information see [example][].


Full documentation
------------------
- Czech: http://addons.nette.org/cs/multiplefileupload
- English: http://addons.nette.org/en/multiplefileupload (machine translation)


Ako to funguje
--------------
Najprv sa na pozadi odoslu subory cez interface (Plupload, Uploadify, ..), kt. sa ulozia do zvoleneho storage (defaultne SQLite3).
Nasledne sa pri odoslani samotneho formulara posiela iba token, na zaklade ktoreho sa pri spracovani formulara vytiahnu z uloziska predtym odoslane subory.