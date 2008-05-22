-- MySQL dump 10.11
--
-- Host: localhost    Database: zfdemo
-- ------------------------------------------------------
-- Server version	5.0.27-community-nt

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `attachments`
--

DROP TABLE IF EXISTS `attachments`;
CREATE TABLE `attachments` (
  `attachment_id` int(10) unsigned NOT NULL auto_increment,
  `modification_time` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'time of last edit',
  `creation_time` timestamp NULL default NULL COMMENT 'time when row was first inserted',
  `user_id` int(10) unsigned NOT NULL COMMENT 'authorization identifier',
  `post_id` int(10) unsigned NOT NULL COMMENT 'uniquely identifies a forum post',
  `filename` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'optional name of attached file',
  PRIMARY KEY  (`attachment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='List of all forum posts attachments';

--
-- Dumping data for table `attachments`
--

LOCK TABLES `attachments` WRITE;
/*!40000 ALTER TABLE `attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `p2`
--

DROP TABLE IF EXISTS `p2`;
CREATE TABLE `p2` (
  `post_id` int(10) unsigned NOT NULL auto_increment,
  `modification_time` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'time of last edit',
  `creation_time` timestamp NULL default NULL COMMENT 'time when row was first inserted',
  `user_id` int(10) unsigned NOT NULL COMMENT 'authorization identifier',
  `topic_id` int(10) unsigned NOT NULL COMMENT 'uniquely identifies forum topic',
  `is_visible` tinyint(3) unsigned NOT NULL default '1' COMMENT 'Is the post visible to anonymous users?',
  `subject` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'subject of this post',
  `content` text collate utf8_unicode_ci NOT NULL COMMENT 'body of the forum post',
  PRIMARY KEY  (`post_id`),
  KEY `user_id` (`user_id`),
  KEY `visible` (`is_visible`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='List of all forum posts';

--
-- Dumping data for table `p2`
--

LOCK TABLES `p2` WRITE;
/*!40000 ALTER TABLE `p2` DISABLE KEYS */;
INSERT INTO `p2` VALUES (1,'2007-02-24 02:47:42','2007-02-24 02:47:42',2,1,1,'','Woohooo!  The very first post :)'),(2,'2007-02-24 02:49:39','2007-02-24 02:49:39',1,1,1,'','I am an admin.'),(3,'2007-03-06 21:34:05','2007-03-06 21:34:05',1,2,1,'Greetings!','Please post suggestions and ideas here :)'),(19,'2007-04-05 22:39:06','2007-04-05 22:39:06',4,2,1,'Countries','We need more posts from members of various countries.'),(18,'2007-04-05 20:58:47','2007-04-04 22:49:00',2,2,1,'need more posts','So quiet I heard a tree fall in a distant forest ;)'),(17,'2007-04-04 22:26:39','2007-04-04 22:26:39',3,1,1,'test subject','hello \r\nworld!');
/*!40000 ALTER TABLE `p2` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
CREATE TABLE `posts` (
  `post_id` int(10) unsigned NOT NULL auto_increment,
  `modification_time` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'time of last edit',
  `creation_time` timestamp NULL default NULL COMMENT 'time when row was first inserted',
  `user_id` int(10) unsigned NOT NULL COMMENT 'authorization identifier',
  `topic_id` int(10) unsigned NOT NULL COMMENT 'uniquely identifies forum topic',
  `is_visible` tinyint(3) unsigned NOT NULL default '1' COMMENT 'Is the post visible to anonymous users?',
  `subject` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'subject of this post',
  `content` text collate utf8_unicode_ci NOT NULL COMMENT 'body of the forum post',
  PRIMARY KEY  (`post_id`),
  KEY `user_id` (`user_id`),
  KEY `visible` (`is_visible`)
) ENGINE=MyISAM AUTO_INCREMENT=20 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='List of all forum posts';

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,'2007-02-24 02:47:42','2007-02-24 02:47:42',2,1,1,'','Woohooo!  The very first post :)'),(2,'2007-02-24 02:49:39','2007-02-24 02:49:39',1,1,1,'','I am an admin.'),(3,'2007-03-06 21:34:05','2007-03-06 21:34:05',1,2,1,'Greetings!','Please post suggestions and ideas here :)'),(19,'2007-04-05 22:39:06','2007-04-05 22:39:06',4,2,1,'Countries','We need more posts from members of various countries.'),(18,'2007-04-05 20:58:47','2007-04-04 22:49:00',2,2,1,'need more posts','So quiet I heard a tree fall in a distant forest ;)'),(17,'2007-04-04 22:26:39','2007-04-04 22:26:39',3,1,1,'test subject','hello \r\nworld!');
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `t2`
--

DROP TABLE IF EXISTS `t2`;
CREATE TABLE `t2` (
  `topic_id` int(10) unsigned NOT NULL auto_increment,
  `modification_time` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'time of last edit',
  `creation_time` timestamp NULL default NULL COMMENT 'time when row was first inserted',
  `user_id` int(10) unsigned NOT NULL COMMENT 'authorization identifier',
  `is_visible` tinyint(3) unsigned NOT NULL default '1' COMMENT 'Is the topic visible to anonymous users?',
  `topic_name` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'uniquely identifies forum topic',
  PRIMARY KEY  (`topic_id`),
  KEY `visible` (`is_visible`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='List of forum topics';

--
-- Dumping data for table `t2`
--

LOCK TABLES `t2` WRITE;
/*!40000 ALTER TABLE `t2` DISABLE KEYS */;
INSERT INTO `t2` VALUES (1,'2007-02-24 02:50:08','2007-02-24 02:50:08',2,1,'Introduce Yourself'),(2,'2007-03-06 21:33:08','2007-03-06 21:33:08',2,1,'Suggestions');
/*!40000 ALTER TABLE `t2` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `topics`
--

DROP TABLE IF EXISTS `topics`;
CREATE TABLE `topics` (
  `topic_id` int(10) unsigned NOT NULL auto_increment,
  `modification_time` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'time of last edit',
  `creation_time` timestamp NULL default NULL COMMENT 'time when row was first inserted',
  `user_id` int(10) unsigned NOT NULL COMMENT 'authorization identifier',
  `is_visible` tinyint(3) unsigned NOT NULL default '1' COMMENT 'Is the topic visible to anonymous users?',
  `topic_name` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'uniquely identifies forum topic',
  PRIMARY KEY  (`topic_id`),
  KEY `visible` (`is_visible`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='List of forum topics';

--
-- Dumping data for table `topics`
--

LOCK TABLES `topics` WRITE;
/*!40000 ALTER TABLE `topics` DISABLE KEYS */;
INSERT INTO `topics` VALUES (1,'2007-02-24 02:50:08','2007-02-24 02:50:08',2,1,'Introduce Yourself'),(2,'2007-03-06 21:33:08','2007-03-06 21:33:08',2,1,'Suggestions');
/*!40000 ALTER TABLE `topics` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `u2`
--

DROP TABLE IF EXISTS `u2`;
CREATE TABLE `u2` (
  `user_id` int(10) unsigned NOT NULL auto_increment COMMENT 'authorization identifier',
  `modification_time` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'time of last edit',
  `creation_time` timestamp NULL default NULL COMMENT 'time when row was first inserted',
  `role` varchar(16) collate utf8_unicode_ci NOT NULL default 'member',
  `locale` varchar(20) collate utf8_unicode_ci default NULL COMMENT 'Zend_Locale string identifier',
  `timezone` varchar(64) collate utf8_unicode_ci default NULL COMMENT 'timezone identifier',
  `username` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'authentication id (plain text)',
  `email` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'email address',
  `password` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'authentication credentials (plain text)',
  `post_count` int(10) unsigned NOT NULL default '0' COMMENT 'number of forum posts from this user',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username` (`username`,`email`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='List of all known users and information about them';

--
-- Dumping data for table `u2`
--

LOCK TABLES `u2` WRITE;
/*!40000 ALTER TABLE `u2` DISABLE KEYS */;
INSERT INTO `u2` VALUES (1,'2007-02-24 02:45:08','2007-02-24 02:45:08','admin','en_US','America/Los_Angeles','admin','admin@zfdev.com','adminpass',1003),(2,'2007-02-24 02:45:08','2007-02-24 02:45:08','member','en_US','America/New_York','gavin','gavin@zend.com','gavinpass',3),(3,'2007-02-24 02:45:08','2007-02-24 02:45:08','moderator','en_US','America/New_York','mod','mod@nowhere.com','modpass',1),(4,'2007-02-24 02:45:08','2007-02-24 02:45:08','member','de_AT','Europe/Vienna','member','member@nowhere.com','memberpass',1);
/*!40000 ALTER TABLE `u2` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `user_id` int(10) unsigned NOT NULL auto_increment COMMENT 'authorization identifier',
  `modification_time` timestamp NOT NULL default CURRENT_TIMESTAMP COMMENT 'time of last edit',
  `creation_time` timestamp NULL default NULL COMMENT 'time when row was first inserted',
  `role` varchar(16) collate utf8_unicode_ci NOT NULL default 'member',
  `locale` varchar(20) collate utf8_unicode_ci default NULL COMMENT 'Zend_Locale string identifier',
  `timezone` varchar(64) collate utf8_unicode_ci default NULL COMMENT 'timezone identifier',
  `username` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'authentication id (plain text)',
  `email` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'email address',
  `password` varchar(128) collate utf8_unicode_ci NOT NULL COMMENT 'authentication credentials (plain text)',
  `post_count` int(10) unsigned NOT NULL default '0' COMMENT 'number of forum posts from this user',
  PRIMARY KEY  (`user_id`),
  UNIQUE KEY `username` (`username`,`email`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='List of all known users and information about them';

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'2007-02-24 02:45:08','2007-02-24 02:45:08','admin','en_US','America/Los_Angeles','admin','admin@zfdev.com','adminpass',1003),(2,'2007-02-24 02:45:08','2007-02-24 02:45:08','member','en_US','America/New_York','gavin','gavin@zend.com','gavinpass',3),(3,'2007-02-24 02:45:08','2007-02-24 02:45:08','moderator','en_US','America/New_York','mod','mod@nowhere.com','modpass',1),(4,'2007-02-24 02:45:08','2007-02-24 02:45:08','member','de_AT','Europe/Vienna','member','member@nowhere.com','memberpass',1);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2007-04-09 23:43:12
