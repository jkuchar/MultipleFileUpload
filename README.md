MultipleFileUpload
==================

[![Code Climate](https://codeclimate.com/github/jkuchar/MultipleFileUpload/badges/gpa.svg)](https://codeclimate.com/github/jkuchar/MultipleFileUpload)


MultipleFileUpload (shorter MFU) is an add-on that makes **uploading thousands of huge files piece of cake**. Just 4 simple steps and you will not need to deal with chunking, autentization of uploads or browser compatibility.

Thanks to multi-backend design you can change your file storage just by changing line in documentation. Currently allows you to pick up one of these storages **SQLite3** (default), **SQLite** and [Dibi](http://dibiphp.com/) (**MySQL**, **PostgreSQL**, MSSql, ...).

This add-on also allows you to change UI library just by changing one config property. Currently are supported **PlUpload**, **Uploadify**, **SwfUpload** and standard **HTML4 form fields**. You can add more interfaces - than the best supported for client's browser will be automatically chosen.


- Demo page: http://multiplefileupload.projekty.mujserver.net/
- Composer package: https://packagist.org/packages/jkuchar/multiplefileupload

Installation
------------
1. Install [composer](https://getcomposer.org/download/) if you don't have it yet
2. run `composer require jkuchar/multiplefileupload:1.*`
3. Copy files from libs/jkuchar/multiplefileupload/public to www/MultipleFileUpload and [include them into your template](https://github.com/jkuchar/MultipleFileUpload-example/blob/a80f234740d32dac038e105e9bc6742f52adc841/app/templates/%40layout.latte#L33).
4. [Register addon](https://github.com/jkuchar/MultipleFileUpload-example/blob/edb0a960dea344b4b1790cfc9b30f7ecdfbd9d1c/app/bootstrap.php#L31) and you are done!

For more information see [example project with this addon](https://github.com/jkuchar/MultipleFileUpload-example).


Usage
-----
```php
$f = new Form($this,$name);

$f->addMultipleFileUpload("exampleUploadField1","Upload field 1", /*max num. of files*/ 20)
  ->addRule('MultipleFileUpload\MultipleFileUpload::validateFilled',"You must upload at least one file")
  ->addRule('MultipleFileUpload\MultipleFileUpload::validateFileSize',"Files you've selected are too big.", 1024); //kB
```

Challenge
---------

Uploadify and SwfUpload are a little unmaintained. If you are interested you can get commit right to this repo. First create pull request and if it is ok, drop me a line that you want to have commit rights.


Full documentation
------------------
- http://addons.nette.org/jkuchar/multiplefileupload
