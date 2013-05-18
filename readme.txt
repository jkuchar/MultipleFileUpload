Tato dokumentace je napsána v Texy. Správně naformátovanou byste ji měli najít zde: http://addons.nette.org/cs/multiplefileupload?rev=50


MultipleFileUpload
##################

.[perex]
MultipleFileUpload (zkr�cen� MFU) je dopln�k pro Nette Framework, kter� va�im u�ivatel�m umo�n� pohodln�, jednodu�e a interaktivn� odes�lat hromady soubor�. Komfort nab�z� nejen u�ivateli, ale i program�torovi. U�ivatel m��e na p�r kliknut� odeslat cel� fotoalbum a program�torovi sta�� na implementaci tohoto dopl�ku pouh� 2 ��dky k�du. Nav�c tento dopln�k podporuje AJAX pro maxim�ln� rychlost a u�ivatelsk� pohodl�! I na u�ivatele se star��mi prohl��e�i se myslelo. Pokud prohl��e� nespl�uje minim�ln� po�adavky, u�ivatel m��e odeslat soubory standardn�m zp�sobem.




|*Autor dopl�ku     | "Honza Kucha�":http://forum.nette.org/cs/profile.php?id=1018
|*Autor driver(�)   | "Honza Kucha�":http://forum.nette.org/cs/profile.php?id=1018 (sqlite a log driver), "Martin Sadov�":http://forum.nette.org/cs/profile.php?id=1876 (dibi driver)
|*Autor interface(�)| "Honza Kucha�":http://forum.nette.org/cs/profile.php?id=1018 (HTML4SingleUpload, Plupload, Uploadify), "Roman Vykuka":http://forum.nette.org/cs/profile.php?id=2221 (SWFUpload)
|*Inspirov�no       | http://forum.nette.org/cs/viewtopic.php?pid=15098#p15098
|*Licence           | New BSD License
|*Diskuse           | http://forum.nette.org/cs/2386-addon-multiplefileupload-multiplefileupload-form-control
|*Demo              | http://multiplefileupload.projekty.mujserver.net/
|*Stav              | Stabiln�. Nicm�n� p�r probl�m� k vy�e�en� to je�t� je - viz kapitola "Zn�m� probl�my":[#toc-zname-problemy]
|*Nette             | viz "z�vislosti":[#toc-zavislosti]


Instalace
*********

1. Zkop�rujte n�sleduj�c� soubory:
  - app/controls/MultipleFileUpload/*
  - document_root/css/MultipleFileUpload/*
  - document_root/images/MultipleFileUpload/*
  - document_root/js/MultipleFileUpload/*
  - document_root/swf/MultipleFileUpload/*
  - document_root/xap/MultipleFileUpload/*

2. Do bootstrapu za registraci "RobotLoaderu":http://doc.nette.org/cs/nette-loaders#toc-nette-loaders-robotloader a p�ed `$application->run();` p�idejte n�sleduj�c� ��dku.
  /---code php
  MultipleFileUpload::register();
  \---

3. Do va�eho layoutu p��padn� do str�nek, kde chcete pou��vat MFU, p�idejte n�sleduj�c� ��dek p�ed tag `</head>`
  JavaScriptov� knihovny, kter� jsou spole�n� (pravd�podobn� je budete pou��vat ve sv� aplikaci) - viz `@layout.phml` v GITu.
  /---code html
  {!=MultipleFi�leUpload::get�Head()}
  \---
  T�mto krokem na�tete a zaregistrujete *Multiple File Upload*.

.[note]
Pokud pou��v�te Nette 0.9.2 nebo ni���, �t�te zde: http://forum.nette.org/cs/2566-httpuploadedfile-bug-pri-safe-mode-nema-opravneni-pri-presunu-souboru . V Nette 0.9.3-RC je u� tato "chyba" opravena. http://github.com/dg/nette/commit/f36e5f07e0df3580ebae8a3b26562f8ae53e84a1

.[note]
Pokud na va�em serveru b��� PHP pod (Fast)CGI, je doporu�en� verze Nette 0.9.4. V t�to verzi je u� opraven� z�sk�v�n� hlavi�ek. Viz http://github.com/nette/nette/commit/d6b7f0c31ae3e3fcedf0e0a914bae84a260119db.

.[note]
**Nette 2.0** zat�m nen� ofici�ln� podporov�no, nicm�n� d�ky skv�l�, aktivn� komunit� m��ete ji� dnes vyu��vat origin�ln� "MultipleFileUpload na Nette 2.0 a PHP 5.3":http://projekty.mujserver.net/nette/MultipleFileUpload_pro_PHP5.3/.

A potom u� m��ete za��t **Multiple File Uploader pou��vat**.





Pou�it�
*******
/---code php
$f = new AppForm($this,$name);

$f->addMultipleFileUpload("pokus1","Test�k",20)
    ->addRule("MultipleFileUpload::validateFilled","Mus�te odeslat alespo� jeden soubor!")
    ->addRule("MultipleFileUpload::validateFileSize","Soubory jsou dohromady moc velik�!",1024); // 1 KB
\---
.[note]
Do formul��e je pot�eba opravdu zad�vat p��mo callback, proto�e ZAT�M nen� podporov�na validace na stran� klienta!

Jako hodnotu MFU ve formul��i dostanu pole ve kter�m je ka�d� soubor validn� (pro�el HttpUploadedFile::isOK()) a je instanc� HttpUploadedFile. V�echny soubory co z�stanou v tempech po odesl�n� formul��e budou smaz�ny.

Takto vypad� pole co dostanete ke zpracov�n� (`$hodnoty = $form->getValues();`):
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

Vzhled prvku m��ete upravovat v �ablon�ch `MultipleFileUpload-withJS.phtml` a `MultipleFileUpload-withoutJS.phtml`. Pop��pad� zde m��ete i m�nit nastaven� uploadify.






Z�vislosti
**********

- Nette
  - minim�ln� verze Nette 0.9.1, 0.9.2 (nutnost p�epsat "HttpUploadedFile::move()":http://forum.nette.org/cs/2566-httpuploadedfile-bug-pri-safe-mode-nema-opravneni-pri-presunu-souboru)
  - **doporu�en� verze Nette 0.9.3** (nebo vy���)
  - Nette 1.0-dev - http://forum.nette.org/cs/profile.php?id=2529.
  - Nette 2.0 zat�m nen� ofici�ln� podporov�no, **nicm�n� d�ky skv�l�, aktivn� komunit� m��ete ji� dnes "vyu��vat origin�ln� MultipleFileUpload":http://projekty.mujserver.net/nette/MultipleFileUpload_pro_PHP5.3/ na Nette 2.0 a PHP 5.3**. (hlavn� d�ky v sou�asn� chv�li pat�� Mat��i Matulovi, kter� se star� o verzi pro Nette 2.0)
  - nyn� jsou zdroj�ky na GIThubu, douf�m, �e se brzy objev� fork pro Nette 2.0

- JavaScript: Z�vis� na pou�it�m interface
- Nap��klad interface Uploadify:
  - [jQuery | http://jquery.com/], [Uploadify | http://www.uploadify.com/], [swfobject.js | http://code.google.com/p/swfobject/], [livequery | http://plugins.jquery.com/project/livequery/], [upraven� ajax-form driver | https://github.com/jkuchar/MultipleFileUpload/blob/master/document_root/js/nette-ajax-form.js]
  - origin�ln� `jquery.uploadify.js` nefunguje s Internet Explorerem, proto�e m� v id objektu poml�ku. IE to interpretuje jako m�nus. Proto jsem vydal upravenou verzi, kterou najdete na GITu https://github.com/jkuchar/MultipleFileUpload/tree/master/document_root/js.
  - origin�ln� `swfobject.js` nefunguje v Internet Exploreru, proto�e Internet Explorer m� bug s flashem  ve formul��i. Upravenou verzi tohoto souboru najdete na GITu https://github.com/jkuchar/MultipleFileUpload/tree/master/document_root/js.







Zn�m� probl�my
**************

Uploadify
=========
- pokud nen� k dispozici Flash Player, nenastane automatick� fallback a ani nen� k dispozici ru�n� fallback na klasick� nahr�v�n� soubor�

HTML4SingleUpload
=================
- nab�dne se i pokud formul�� funguje ajaxov� (soubory se samoz�ejm� neode�lou). N�jak� n�pady jak toto vy�e�it?









Drivery
*******

Driver m� za �kol skladovat informace o p�enesen�ch souborech a samotn� soubory. V distribuci Multiple File Uploadu se driver� nach�z� hned n�kolik.

|-----------------------------------------------------------------
| Driver              | Autor                      | Licence  | Um�st�n� | Thread-safe   | Popis | Instalace | Stav
|-----------------------------------------------------------------
| **SQLite v. 2**     | "Honza Kucha�":http://forum.nette.org/cs/profile.php?id=1018 | New BSD | distribuce | ano | Ukl�d� informace o p�enesen�ch souborech do datab�ze SQLite. (vyu��v� php_sqlite; nevy�aduje dibi) | Sta�� povolit z�pis (chmod(0777)) ve slo�ce app/controls/drivers/Sqlite/database.sdb. | **Doporu�en�, Stabiln�**
| **Dibi**            | "Martin Sadov�":http://forum.nette.org/cs/profile.php?id=1876 (prvotn� implementace) + "Honza Kucha�":http://forum.nette.org/cs/profile.php?id=1018 (u�es�no; p�id�n workaround pro http://forum.dibiphp.com/cs/1003-pgsql-a-znak-x00-oriznuti-zbytku-vstupu?pid=3840#p3840) | New BSD  | distribuce | ano | Ukl�d� informace o p�enesen�ch souborech do jak�koli datab�ze, kterou podporuje Dibi. (vy�aduje Dibi; tabulku mus�te v datab�zi ru�n� vytvo�it; v distribuci p�ilo�eny dumpy datab�z� mysql (Martin) a postgres (Honza)) | Zprovozn�te dibi, vytvo��te tabulku files v datab�zi. (viz dumpy datab�z�) | **Experiment�ln�** (nejsp�� si ho budete muset poupravit pr�v� pro va�i datab�zi, ale bez �prav by to m�lo fungovat pod MySQL a PgSQL)
| **Log**             | "Honza Kucha�":http://forum.nette.org/cs/profile.php?id=1018 | New BSD | distribuce | ano | Nikam nic neukl�d�. Slou�� pro v�voj��e k zji�t�n� po�ad� volan�ch metod v driveru. Pro b��n�ho u�ivatele nem� ��dn� v�znam. | Nakonfigurovat "Logger":http://addons.nette.org/cs/logger. | **Stabiln�**


A jak to v praxi zaregistrovat? Je to jednoduch�. Pod�vejte se do souboru bootstrap.php v distribuci. Najdete tam zhruba toto:

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
MultipleFileUpload::setQueuesModel(new MFUQueuesDibi()); // od revize 69 (v�etn�)
\---code







Interfaces
**********
Za�al bych t�m co to vlastn� je. Je to jak�si bal��ek s u�ivatelsk�m rozhran�m a jeho pozad�m na stran� serveru, kter� �ek� na data od klientsk�ho rozhran�. ��st na stran� serveru m� za �kol p�edat p�ijat� data ve specifikovan� form� modelu. Nejl�pe pochop�te, pokud si zobraz�te ji� funk�n� interfacy v distribuci.

V sou�asn� chv�li m�me v distribuci tyto interfacy:

|-----------------------------------------------------------------
| Interface               | Autor                                                        | Licence | Vy�adov�n JavaScript?  | Popis
|-----------------------------------------------------------------
| **HTML4SingleUpload**   | "Honza Kucha�":http://forum.nette.org/cs/profile.php?id=1018 | NewBSD  | **Ne**                 | Implementuje standardn� HTML4 odes�lac� pol��ka
| **Plupload**            | "Honza Kucha�":http://forum.nette.org/cs/profile.php?id=1018 | NewBSD  | Ano                    | Implementuje "plupload":http://www.plupload.com/
| **SWFUpload**           | "Roman Vykuka":http://forum.nette.org/cs/profile.php?id=2221 | NewBSD  | Ano                    | Implementuje "SwfUpload":http://swfupload.org/; v�ce informac� o tomto interface na "f�ru":http://forum.nette.org/cs/2386-addon-multiplefileupload-multiplefileupload-form-control?p=4#p37697.
| **Uploadify**           | "Honza Kucha�":http://forum.nette.org/cs/profile.php?id=1018 | NewBSD  | Ano                     | Implementuje "Uploadify":http://www.uploadify.com/


Te� tedy �ist� praktick� a logick� ot�zka, jak ten interface zaregistrovat a pou��vat? Registrace v do MFU se d�l� pomoc� p�ed�n� n�zvu t��dy interface. V MFU jsou t��dy interfac� pojmenov�n� jako MFUUI a n�sleduje n�zev interface. Tedy nap��klad `MFUUIUploadify`. Pot� u� n�m zb�v� jen interface zaregistrovat. Jak na to n�m uk��e p��klad z bootstrapu.

/---code php

MultipleFileUpload::getUIRegistrator()
	->clear()
	->register("MFUUIHTML4SingleUpload")
	->register("MFUUIUploadify");

\---

Jak vid�te nejd��ve jsme z�skali od objektu MFU UIRegistrator. To je t��da, kter� v sob� uchov�v� informace o zaregistrovan�ch interfacech. Tedy nejd��ve jsme vymazali ji� zaregistrovan� v�choz� hodnoty (HTML4SingleUpload, Uploadify). A nyn� zaregistrujeme n�jak� sv�j interface. Po�ad� m��eme libovoln� m�nit, jedin� co mus�me dodr�et je, aby prvn� registrovan� interface nevy�adoval JavaScript, proto�e ten jako jedin� se vygeneruje do str�nky, pokud ho prohl��e� nepodporuje.

Pokud m�te svoje klientsk� �e�en� a chcete ho pou��vat s MFU, �t�te �l�nek "Jak na vlastn� interface?":[multiplefileupload/jak-na-vlastni-interface].








Podpora v prohl��e��ch
**********************

Li�� se interface od interface. Tedy postupn�.

HTML4SingleUpload
=================

Nev�m jak textov� prohl��e�e, ale jinak by m�l b�t podporov�n v�ude.

Plupload
========

- V�ude, kde funguje Javascript.
- Co jsem zkou�el, jdou odes�lat opravdu gigantick� soubory, nejv�ce jsem zkou�el 6GB. Ale pozor, potom pou�ije "knihovnu na zji��ov�n� velikost� soubor�":[bigfiletools], pokud ji pot�ebujete v�d�t. `HttpUploadeFile` v�m bude vracet nesmysly, u takto velik�ch soubor�.

SWFUpload
=========

*(Honza: Pros�m autora o dopln�n�. J� jsem kompatibilitu netestoval. Nicm�n� bude to dost podobn� jako uploadify)*

Uploadify
=========


|-----------------------------------------------------------------
|   OS   | Prohl��e�          | Verze             | Kvalita podpory            | Koment��
|-----------------------------------------------------------------
|  Win7  | Google Chrome      |    2              | <b> ***** </b> .<> | Bez v�hrad
|       ^|                   ^| 4.1.249.1025 beta | <b> ***** </b> .<> | Bez v�hrad
|       ^| Opera              |   10.0            | <b> ***** </b> .<> | Bez v�hrad
|       ^|                   ^|   9.65            | <b> ***** </b> .<> | Bez v�hrad
|       ^| Firefox            |   3.5             | <b> ****  </b> .<> | O n�co pomalej�� ne� Google Chrome a Opera. Mo�n� je to ale zp�sobeno n�jak�m nainstalovan�m dopl�kem.
|       ^|                   ^|   3.6             | <b> ****  </b> .<> | O n�co pomalej�� ne� Google Chrome a Opera. Mo�n� je to ale zp�sobeno n�jak�m nainstalovan�m dopl�kem.
|       ^| Internet Explorer  | v�echny           |  -             .<> | Mezi soubory z n�jak�ch d�vod� chv�li �ek�. Pot�eba o�et�it n�kolik chyb v IE/Flash, aby MFU v�bec fungovalo. (viz "Z�vislosti":[#toc-zavislosti])
|       ^|                   ^|    8              | <b> ***   </b> .<> | Soubory odes�l� klidn� v deseti vl�knech. (ale ka�d� vl�kno v�dy chv�li �ek�) Tzn. funguje to v celku spr�vn�.
| WinXP  |                   ^|    6              | <b> *     </b> .<> | Soubory odes�l� pouze v jednom vl�kn�. Tzn. taky to n�jak funguje.

Pokud nen� v�� prohl��e� v p�edch�zej�c� tabulce, tak pros�m napi�te na f�rum, zada ve va�em prohl��e�i MFU funguje, �i nikoli. A pokud ano, tak jak.






Demo
****
http://multiplefileupload.projekty.mujserver.net/

<iframe width="700" height="1000" frameborder="3" src="http://multiplefileupload.projekty.mujserver.net/"></iframe>





GIT
***
https://github.com/jkuchar/MultipleFileUpload/

.[note]
Na GITu, tedy na ofici�ln� v�tvi, je k dispozici pouze verze pro Nette 0.9. Pro verzi pro Nette 2.0 pokra�ujte do sekce "Z�vislosti":[#toc-zavislosti], kde se dozv�te v�e pot�ebn�.



{{extras}}
