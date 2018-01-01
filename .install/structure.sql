# ************************************************************
# Sequel Pro SQL dump
# Version 4541
#
# http://www.sequelpro.com/
# https://github.com/sequelpro/sequelpro
#
# Host: 127.0.0.1 (MySQL 5.7.20)
# Database: zlinskybarcamp
# Generation Time: 2018-01-01 11:50:28 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table config
# ------------------------------------------------------------

CREATE TABLE `config` (
  `id` varchar(64) NOT NULL DEFAULT '' COMMENT 'Unique name of config item',
  `value` text NOT NULL COMMENT 'JSON encoded config value',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;



# Dump of table newsletter_subscribe
# ------------------------------------------------------------

CREATE TABLE `newsletter_subscribe` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(200) NOT NULL DEFAULT '' COMMENT 'E-mail to subscribe',
  `consent_date` datetime NOT NULL COMMENT 'Date of user''s consent',
  `consent_desc` varchar(200) NOT NULL DEFAULT '' COMMENT 'Way to get user''s consent',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MEMORY DEFAULT CHARSET=utf8mb4;



# Dump of table user
# ------------------------------------------------------------

CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(200) NOT NULL DEFAULT '',
  `password` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;



# Dump of table user_federated
# ------------------------------------------------------------

CREATE TABLE `user_federated` (
  `id` varchar(100) NOT NULL DEFAULT '',
  `platform` varchar(30) NOT NULL DEFAULT '',
  `user_id` int(11) NOT NULL,
  `identity` text,
  `access_token` text,
  PRIMARY KEY (`platform`,`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;



# Dump of table user_role
# ------------------------------------------------------------

CREATE TABLE `user_role` (
  `user_id` int(11) NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`,`role`),
  KEY `role` (`role`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
