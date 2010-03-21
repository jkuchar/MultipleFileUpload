CREATE TABLE `files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queueID` varchar(100) NOT NULL,
  `created` int(11) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) 