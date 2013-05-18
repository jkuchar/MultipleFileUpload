MultipleFileUpload
##################

.[perex]
MultipleFileUpload (zkrácenì MFU) je doplnìk pro Nette Framework, kterı vašim uivatelùm umoní pohodlnì, jednoduše a interaktivnì odesílat hromady souborù. Komfort nabízí nejen uivateli, ale i programátorovi. Uivatel mùe na pár kliknutí odeslat celé fotoalbum a programátorovi staèí na implementaci tohoto doplòku pouhé 2 øádky kódu. Navíc tento doplnìk podporuje AJAX pro maximální rychlost a uivatelské pohodlí! I na uivatele se staršími prohlíeèi se myslelo. Pokud prohlíeè nesplòuje minimální poadavky, uivatel mùe odeslat soubory standardním zpùsobem.




|*Autor doplòku     | "Honza Kuchaø":http://forum.nette.org/cs/profile.php?id=1018
|*Autor driver(ù)   | "Honza Kuchaø":http://forum.nette.org/cs/profile.php?id=1018 (sqlite a log driver), "Martin Sadovı":http://forum.nette.org/cs/profile.php?id=1876 (dibi driver)
|*Autor interface(ù)| "Honza Kuchaø":http://forum.nette.org/cs/profile.php?id=1018 (HTML4SingleUpload, Plupload, Uploadify), "Roman Vykuka":http://forum.nette.org/cs/profile.php?id=2221 (SWFUpload)
|*Inspirováno       | http://forum.nette.org/cs/viewtopic.php?pid=15098#p15098
|*Licence           | New BSD License
|*Diskuse           | http://forum.nette.org/cs/2386-addon-multiplefileupload-multiplefileupload-form-control
|*Demo              | http://multiplefileupload.projekty.mujserver.net/
|*Stav              | Stabilní. Nicménì pár problémù k vyøešení to ještì je - viz kapitola "Známé problémy":[#toc-zname-problemy]
|*Nette             | viz "závislosti":[#toc-zavislosti]


Instalace
*********

1. Zkopírujte následující soubory:
  - app/controls/MultipleFileUpload/*
  - document_root/css/MultipleFileUpload/*
  - document_root/images/MultipleFileUpload/*
  - document_root/js/MultipleFileUpload/*
  - document_root/swf/MultipleFileUpload/*
  - document_root/xap/MultipleFileUpload/*

2. Do bootstrapu za registraci "RobotLoaderu":http://doc.nette.org/cs/nette-loaders#toc-nette-loaders-robotloader a pøed `$application->run();` pøidejte následující øádku.
  /---code php
  MultipleFileUpload::register();
  \---

3. Do vašeho layoutu pøípadnì do stránek, kde chcete pouívat MFU, pøidejte následující øádek pøed tag `</head>`
  JavaScriptové knihovny, které jsou spoleèné (pravdìpodobnì je budete pouívat ve své aplikaci) - viz `@layout.phml` v GITu.
  /---code html
  {!=MultipleFi­leUpload::get­Head()}
  \---
  Tímto krokem naètete a zaregistrujete *Multiple File Upload*.

.[note]
Pokud pouíváte Nette 0.9.2 nebo niší, ètìte zde: http://forum.nette.org/cs/2566-httpuploadedfile-bug-pri-safe-mode-nema-opravneni-pri-presunu-souboru . V Nette 0.9.3-RC je u tato "chyba" opravena. http://github.com/dg/nette/commit/f36e5f07e0df3580ebae8a3b26562f8ae53e84a1

.[note]
Pokud na vašem serveru bìí PHP pod (Fast)CGI, je doporuèená verze Nette 0.9.4. V této verzi je u opravené získávání hlavièek. Viz http://github.com/nette/nette/commit/d6b7f0c31ae3e3fcedf0e0a914bae84a260119db.

.[note]
**Nette 2.0** zatím není oficiálnì podporováno, nicménì díky skvìlé, aktivní komunitì mùete ji dnes vyuívat originální "MultipleFileUpload na Nette 2.0 a PHP 5.3":http://projekty.mujserver.net/nette/MultipleFileUpload_pro_PHP5.3/.

A potom u mùete zaèít **Multiple File Uploader pouívat**.





Pouití
*******
/---code php
$f = new AppForm($this,$name);

$f->addMultipleFileUpload("pokus1","Testík",20)
    ->addRule("MultipleFileUpload::validateFilled","Musíte odeslat alespoò jeden soubor!")
    ->addRule("MultipleFileUpload::validateFileSize","Soubory jsou dohromady moc veliké!",1024); // 1 KB
\---
.[note]
Do formuláøe je potøeba opravdu zadávat pøímo callback, protoe ZATÍM není podporována validace na stranì klienta!

Jako hodnotu MFU ve formuláøi dostanu pole ve kterém je kadı soubor validní (prošel HttpUploadedFile::isOK()) a je instancí HttpUploadedFile. Všechny soubory co zùstanou v tempech po odeslání formuláøe budou smazány.

Takto vypadá pole co dostanete ke zpracování (`$hodnoty = $form->getValues();`):
/---code php
array(1) {
      0 => object(HttpUploadedFile) (5) {
         "name" private => string(15) "application.ini"
         "type" private => string(24) "application/octet-stream"
         "size" private => int(164)
         "tmpName" private => string(107) "xxx"
         "error" private => int(0)
      }
}
\---

Vzhled prvku mùete upravovat v šablonách `MultipleFileUpload-withJS.phtml` a `MultipleFileUpload-withoutJS.phtml`. Popøípadì zde mùete i mìnit nastavení uploadify.






Závislosti
**********

- Nette
  - minimální verze Nette 0.9.1, 0.9.2 (nutnost pøepsat "HttpUploadedFile::move()":http://forum.nette.org/cs/2566-httpuploadedfile-bug-pri-safe-mode-nema-opravneni-pri-presunu-souboru)
  - **doporuèená verze Nette 0.9.3** (nebo vyšší)
  - Nette 1.0-dev - http://forum.nette.org/cs/profile.php?id=2529.
  - Nette 2.0 zatím není oficiálnì podporováno, **nicménì díky skvìlé, aktivní komunitì mùete ji dnes "vyuívat originální MultipleFileUpload":http://projekty.mujserver.net/nette/MultipleFileUpload_pro_PHP5.3/ na Nette 2.0 a PHP 5.3**. (hlavní díky v souèasné chvíli patøí Matúši Matulovi, kterı se stará o verzi pro Nette 2.0)
  - nyní jsou zdrojáky na GIThubu, doufám, e se brzy objeví fork pro Nette 2.0

- JavaScript: Závisí na pouitém interface
- Napøíklad interface Uploadify:
  - [jQuery | http://jquery.com/], [Uploadify | http://www.uploadify.com/], [swfobject.js | http://code.google.com/p/swfobject/], [livequery | http://plugins.jquery.com/project/livequery/], [upravenı ajax-form driver | https://github.com/jkuchar/MultipleFileUpload/blob/master/document_root/js/nette-ajax-form.js]
  - originální `jquery.uploadify.js` nefunguje s Internet Explorerem, protoe má v id objektu pomlèku. IE to interpretuje jako mínus. Proto jsem vydal upravenou verzi, kterou najdete na GITu https://github.com/jkuchar/MultipleFileUpload/tree/master/document_root/js.
  - originální `swfobject.js` nefunguje v Internet Exploreru, protoe Internet Explorer má bug s flashem  ve formuláøi. Upravenou verzi tohoto souboru najdete na GITu https://github.com/jkuchar/MultipleFileUpload/tree/master/document_root/js.







Známé problémy
**************

Uploadify
=========
- pokud není k dispozici Flash Player, nenastane automatickı fallback a ani není k dispozici ruèní fallback na klasické nahrávání souborù

HTML4SingleUpload
=================
- nabídne se i pokud formuláø funguje ajaxovì (soubory se samozøejmì neodešlou). Nìjaké nápady jak toto vyøešit?









Drivery
*******

Driver má za úkol skladovat informace o pøenesenıch souborech a samotné soubory. V distribuci Multiple File Uploadu se driverù nachází hned nìkolik.

|-----------------------------------------------------------------
| Driver              | Autor                      | Licence  | Umístìní | Thread-safe   | Popis | Instalace | Stav
|-----------------------------------------------------------------
| **SQLite v. 2**     | "Honza Kuchaø":http://forum.nette.org/cs/profile.php?id=1018 | New BSD | distribuce | ano | Ukládá informace o pøenesenıch souborech do databáze SQLite. (vyuívá php_sqlite; nevyaduje dibi) | Staèí povolit zápis (chmod(0777)) ve sloce app/controls/drivers/Sqlite/database.sdb. | **Doporuèenı, Stabilní**
| **Dibi**            | "Martin Sadovı":http://forum.nette.org/cs/profile.php?id=1876 (prvotní implementace) + "Honza Kuchaø":http://forum.nette.org/cs/profile.php?id=1018 (uèesáno; pøidán workaround pro http://forum.dibiphp.com/cs/1003-pgsql-a-znak-x00-oriznuti-zbytku-vstupu?pid=3840#p3840) | New BSD  | distribuce | ano | Ukládá informace o pøenesenıch souborech do jakékoli databáze, kterou podporuje Dibi. (vyaduje Dibi; tabulku musíte v databázi ruènì vytvoøit; v distribuci pøiloeny dumpy databází mysql (Martin) a postgres (Honza)) | Zprovozníte dibi, vytvoøíte tabulku files v databázi. (viz dumpy databází) | **Experimentální** (nejspíš si ho budete muset poupravit právì pro vaši databázi, ale bez úprav by to mìlo fungovat pod MySQL a PgSQL)
| **Log**             | "Honza Kuchaø":http://forum.nette.org/cs/profile.php?id=1018 | New BSD | distribuce | ano | Nikam nic neukládá. Slouí pro vıvojáøe k zjištìní poøadí volanıch metod v driveru. Pro bìného uivatele nemá ádnı vıznam. | Nakonfigurovat "Logger":http://addons.nette.org/cs/logger. | **Stabilní**


A jak to v praxi zaregistrovat? Je to jednoduché. Podívejte se do souboru bootstrap.php v distribuci. Najdete tam zhruba toto:

/---code php
// Optional step: register driver
//
// As default driver is used Sqlite driver
// @see http://addons.nettephp.com/cs/multiplefileupload#toc-drivery
//
// When you want to use other driver use something like this:


Dibi::connect(array(
	"driver"   => "postgre",
	"host"     => "127.0.0.1",
	"dbname"   => "MFU",
	"schema"   => "public",
	"user"     => "postgres",
	"pass"     => "toor",
	"charset"  => "UTF-8"
));
//MultipleFileUpload::$queuesModel = new MFUQueuesDibi(); // do revize 69
MultipleFileUpload::setQueuesModel(new MFUQueuesDibi()); // od revize 69 (vèetnì)
\---code







Interfaces
**********
Zaèal bych tím co to vlastnì je. Je to jakısi balíèek s uivatelskım rozhraním a jeho pozadím na stranì serveru, které èeká na data od klientského rozhraní. Èást na stranì serveru má za úkol pøedat pøijatá data ve specifikované formì modelu. Nejlépe pochopíte, pokud si zobrazíte ji funkèní interfacy v distribuci.

V souèasné chvíli máme v distribuci tyto interfacy:

|-----------------------------------------------------------------
| Interface               | Autor                                                        | Licence | Vyadován JavaScript?  | Popis
|-----------------------------------------------------------------
| **HTML4SingleUpload**   | "Honza Kuchaø":http://forum.nette.org/cs/profile.php?id=1018 | NewBSD  | **Ne**                 | Implementuje standardní HTML4 odesílací políèka
| **Plupload**            | "Honza Kuchaø":http://forum.nette.org/cs/profile.php?id=1018 | NewBSD  | Ano                    | Implementuje "plupload":http://www.plupload.com/
| **SWFUpload**           | "Roman Vykuka":http://forum.nette.org/cs/profile.php?id=2221 | NewBSD  | Ano                    | Implementuje "SwfUpload":http://swfupload.org/; více informací o tomto interface na "fóru":http://forum.nette.org/cs/2386-addon-multiplefileupload-multiplefileupload-form-control?p=4#p37697.
| **Uploadify**           | "Honza Kuchaø":http://forum.nette.org/cs/profile.php?id=1018 | NewBSD  | Ano                     | Implementuje "Uploadify":http://www.uploadify.com/


Teï tedy èistì praktická a logická otázka, jak ten interface zaregistrovat a pouívat? Registrace v do MFU se dìlá pomocí pøedání názvu tøídy interface. V MFU jsou tøídy interfacù pojmenování jako MFUUI a následuje název interface. Tedy napøíklad `MFUUIUploadify`. Poté u nám zbıvá jen interface zaregistrovat. Jak na to nám ukáe pøíklad z bootstrapu.

/---code php

MultipleFileUpload::getUIRegistrator()
	->clear()
	->register("MFUUIHTML4SingleUpload")
	->register("MFUUIUploadify");

\---

Jak vidíte nejdøíve jsme získali od objektu MFU UIRegistrator. To je tøída, která v sobì uchovává informace o zaregistrovanıch interfacech. Tedy nejdøíve jsme vymazali ji zaregistrované vıchozí hodnoty (HTML4SingleUpload, Uploadify). A nyní zaregistrujeme nìjakı svùj interface. Poøadí mùeme libovolnì mìnit, jediné co musíme dodret je, aby první registrovanı interface nevyadoval JavaScript, protoe ten jako jedinı se vygeneruje do stránky, pokud ho prohlíeè nepodporuje.

Pokud máte svoje klientské øešení a chcete ho pouívat s MFU, ètìte èlánek "Jak na vlastní interface?":[multiplefileupload/jak-na-vlastni-interface].








Podpora v prohlíeèích
**********************

Liší se interface od interface. Tedy postupnì.

HTML4SingleUpload
=================

Nevím jak textové prohlíeèe, ale jinak by mìl bıt podporován všude.

Plupload
========

- Všude, kde funguje Javascript.
- Co jsem zkoušel, jdou odesílat opravdu gigantické soubory, nejvíce jsem zkoušel 6GB. Ale pozor, potom pouije "knihovnu na zjišování velikostí souborù":[bigfiletools], pokud ji potøebujete vìdìt. `HttpUploadeFile` vám bude vracet nesmysly, u takto velikıch souborù.

SWFUpload
=========

*(Honza: Prosím autora o doplnìní. Já jsem kompatibilitu netestoval. Nicménì bude to dost podobné jako uploadify)*

Uploadify
=========


|-----------------------------------------------------------------
|   OS   | Prohlíeè          | Verze             | Kvalita podpory            | Komentáø
|-----------------------------------------------------------------
|  Win7  | Google Chrome      |    2              | <b> ***** </b> .<> | Bez vıhrad
|       ^|                   ^| 4.1.249.1025 beta | <b> ***** </b> .<> | Bez vıhrad
|       ^| Opera              |   10.0            | <b> ***** </b> .<> | Bez vıhrad
|       ^|                   ^|   9.65            | <b> ***** </b> .<> | Bez vıhrad
|       ^| Firefox            |   3.5             | <b> ****  </b> .<> | O nìco pomalejší ne Google Chrome a Opera. Moná je to ale zpùsobeno nìjakım nainstalovanım doplòkem.
|       ^|                   ^|   3.6             | <b> ****  </b> .<> | O nìco pomalejší ne Google Chrome a Opera. Moná je to ale zpùsobeno nìjakım nainstalovanım doplòkem.
|       ^| Internet Explorer  | všechny           |  -             .<> | Mezi soubory z nìjakıch dùvodù chvíli èeká. Potøeba ošetøit nìkolik chyb v IE/Flash, aby MFU vùbec fungovalo. (viz "Závislosti":[#toc-zavislosti])
|       ^|                   ^|    8              | <b> ***   </b> .<> | Soubory odesílá klidnì v deseti vláknech. (ale kadé vlákno vdy chvíli èeká) Tzn. funguje to v celku správnì.
| WinXP  |                   ^|    6              | <b> *     </b> .<> | Soubory odesílá pouze v jednom vláknì. Tzn. taky to nìjak funguje.

Pokud není váš prohlíeè v pøedcházející tabulce, tak prosím napište na fórum, zada ve vašem prohlíeèi MFU funguje, èi nikoli. A pokud ano, tak jak.






Demo
****
http://multiplefileupload.projekty.mujserver.net/

<iframe width="700" height="1000" frameborder="3" src="http://multiplefileupload.projekty.mujserver.net/"></iframe>





GIT
***
https://github.com/jkuchar/MultipleFileUpload/

.[note]
Na GITu, tedy na oficiální vìtvi, je k dispozici pouze verze pro Nette 0.9. Pro verzi pro Nette 2.0 pokraèujte do sekce "Závislosti":[#toc-zavislosti], kde se dozvíte vše potøebné.



{{extras}}
