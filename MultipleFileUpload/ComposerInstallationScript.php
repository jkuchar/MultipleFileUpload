<?php

// This does not work, cause:
// 
// NOTE: Only scripts defined in the root package's composer.json are executed. If a dependency of the root package specifies its own scripts, Composer does not execute those additional scripts.
// 
// 
// 
// 
// 
//namespace MultipleFileUpload;
//
//use Composer\Script\Event;
//
//// @link http://getcomposer.org/doc/articles/scripts.md
//
///**
// * Install MultipleFileUpload
// */
//class ComposerInstallationScript {
//
//	/**
//	 * Warns user that he has to copy files to public folder
//	 * @param \Composer\Script\Event $event
//	 */
//	public static function warn(Event $event) {
//		$io = $event->getIO();
//		$io->write("[MultipleFileUpload] Don't forget to copy content of \"public\" folder to MultipleFileUpload folder in you site root.");
//		$io->write("[MultipleFileUpload] Addon will not work properly if you do not do that.");
//	}
//
//}