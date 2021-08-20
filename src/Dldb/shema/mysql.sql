-- MySQL dump 10.13  Distrib 5.7.34, for Linux (x86_64)
--
-- Host: localhost    Database: dldb_frontend_dev
-- ------------------------------------------------------
-- Server version	5.5.5-10.2.19-MariaDB-1:10.2.19+maria~bionic

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
-- Table structure for table `authority`
--

DROP TABLE IF EXISTS `authority`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authority` (
  `id` int(11) NOT NULL DEFAULT 0,
  `locale` varchar(4) NOT NULL DEFAULT 'de',
  `name` varchar(255) DEFAULT NULL,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `locations_json` text DEFAULT NULL,
  `relation_json` text DEFAULT NULL,
  `contact_json` text DEFAULT NULL,
  `data_json` text DEFAULT NULL,
  PRIMARY KEY (`id`,`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `authority_service`
--

DROP TABLE IF EXISTS `authority_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authority_service` (
  `authority_id` int(11) NOT NULL DEFAULT 0,
  `service_id` int(11) NOT NULL DEFAULT 0,
  `locale` varchar(4) NOT NULL DEFAULT 'de',
  PRIMARY KEY (`authority_id`,`service_id`,`locale`),
  KEY `service_id_index` (`service_id`),
  KEY `authority_id_index` (`authority_id`),
  KEY `locale_index` (`locale`),
  KEY `service_id_locale_index` (`service_id`,`locale`),
  KEY `authority_id_locale_index` (`authority_id`,`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `authority_localtion`
--

DROP TABLE IF EXISTS `authority_location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `authority_location` (
  `authority_id` int(11) NOT NULL DEFAULT 0,
  `location_id` int(11) NOT NULL DEFAULT 0,
  `locale` varchar(4) NOT NULL DEFAULT 'de',
  PRIMARY KEY (`authority_id`,`location_id`,`locale`),
  KEY `location_id_index` (`location_id`),
  KEY `authority_id_index` (`authority_id`),
  KEY `locale_index` (`locale`),
  KEY `location_id_locale_index` (`location_id`,`locale`),
  KEY `authority_id_locale_index` (`authority_id`,`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `contact`
--

DROP TABLE IF EXISTS `contact`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `contact` (
  `object_id` int(11) NOT NULL DEFAULT 0,
  `locale` varchar(4) NOT NULL DEFAULT 'de',
  `name` varchar(255) DEFAULT NULL,
  `contact_json` text DEFAULT NULL,
  `address_json` text DEFAULT NULL,
  `deviating_postal_address_json` text DEFAULT NULL,
  `geo_json` text DEFAULT NULL,
  PRIMARY KEY (`object_id`,`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `location`
--

DROP TABLE IF EXISTS `location`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `location` (
  `id` int(11) NOT NULL DEFAULT 0,
  `locale` varchar(4) NOT NULL DEFAULT 'de',
  `name` varchar(255) NOT NULL DEFAULT '',
  `category_name` varchar(255) NOT NULL DEFAULT '',
  `category_identifier` varchar(255) NOT NULL DEFAULT '',
  `authority_id` int(11) DEFAULT 0,
  `authority_name` varchar(255) NOT NULL DEFAULT '',
  `note` text DEFAULT NULL,
  `category_json` text DEFAULT NULL,
  `urgent_json` text DEFAULT NULL,
  `opening_times_json` text DEFAULT NULL,
  `transit_json` text DEFAULT NULL,
  `deviating_postal_address_json` text DEFAULT NULL,
  `payment_json` text DEFAULT NULL,
  `accessibility_json` text DEFAULT NULL,
  `appointment_json` text DEFAULT NULL,
  `data_json` text DEFAULT NULL,
  PRIMARY KEY (`id`,`locale`),
  KEY `name_index` (`name`),
  KEY `category_identifier_index` (`category_identifier`),
  KEY `authority_id_index` (`authority_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `location_service`
--

DROP TABLE IF EXISTS `location_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `location_service` (
  `location_id` int(11) NOT NULL DEFAULT 0,
  `service_id` int(11) NOT NULL DEFAULT 0,
  `locale` varchar(4) NOT NULL DEFAULT 'de',
  `appointment_slots` tinyint(4) DEFAULT 0,
  `appointment_bookable` tinyint(4) DEFAULT 0,
  `appointment_external` tinyint(4) DEFAULT 0,
  `appointment_multiple` tinyint(4) DEFAULT 0,
  `appointment_link` varchar(255) DEFAULT '',
  `appointment_note` mediumtext DEFAULT '',
  `contact_json` text DEFAULT NULL,
  PRIMARY KEY (`location_id`,`service_id`,`locale`),
  KEY `service_id_index` (`service_id`),
  KEY `location_id_index` (`location_id`),
  KEY `locale_index` (`locale`),
  KEY `service_id_locale_index` (`service_id`,`locale`),
  KEY `location_id_locale_index` (`location_id`,`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `meta`
--

DROP TABLE IF EXISTS `meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `meta` (
  `object_id` int(11) NOT NULL DEFAULT 0,
  `hash` varchar(255) NOT NULL DEFAULT '',
  `locale` varchar(4) NOT NULL DEFAULT 'de',
  `lastupdate` DATETIME NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `keywords` text DEFAULT '',
  `url` varchar(255) DEFAULT '',
  `type` varchar(25) DEFAULT '',
  `titles_json` text DEFAULT '',
  PRIMARY KEY (`object_id`,`locale`,`type`),
  KEY `object_id_index` (`object_id`),
  KEY `type_index` (`type`),
  KEY `hash_index` (`hash`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service`
--

DROP TABLE IF EXISTS `service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service` (
  `id` int(11) NOT NULL DEFAULT 0,
  `locale` varchar(4) NOT NULL DEFAULT 'de',
  `leika` varchar(14) NOT NULL,
  `name` varchar(255) NOT NULL DEFAULT '',
  `description` text NOT NULL DEFAULT '',
  `hint` mediumtext DEFAULT NULL,
  `fees` mediumtext DEFAULT '',
  `residence` varchar(255) DEFAULT '',
  `representation` varchar(255) DEFAULT '',
  `responsibility` mediumtext DEFAULT NULL,
  `responsibility_all` tinyint(2) DEFAULT 0,
  `processing_time` mediumtext DEFAULT '',
  `root_topic_id` int(11) NOT NULL DEFAULT 0,
  `appointment_all_link` varchar(255) DEFAULT '',
  `onlineprocessing_json` text DEFAULT NULL,
  `relation_json` text DEFAULT NULL,
  `authorities_json` text DEFAULT NULL,
  `data_json` text DEFAULT NULL,
  PRIMARY KEY (`id`,`locale`),
  FULLTEXT KEY `search_index` (`description`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `service_information`
--

DROP TABLE IF EXISTS `service_information`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `service_information` (
  `service_id` int(11) NOT NULL DEFAULT 0,
  `locale` varchar(4) NOT NULL DEFAULT 'de',
  `name` mediumtext DEFAULT NULL,
  `description` text NOT NULL,
  `link` mediumtext DEFAULT NULL,
  `type` varchar(255) NOT NULL,
  `sort` tinyint(4) NOT NULL DEFAULT 0,
  `data_json` text DEFAULT NULL,
  PRIMARY KEY (`service_id`,`locale`,`type`,`sort`),
  KEY `service_id_index` (`service_id`),
  KEY `type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `search`
--

DROP TABLE IF EXISTS `search`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `search` (
  `object_id` int(11) NOT NULL DEFAULT 0,
  `locale` varchar(4) NOT NULL DEFAULT 'de',
  `entity_type` varchar(255) DEFAULT NULL,
  `search_type` ENUM('name', 'description', 'keywords', 'titles', 'address'),
  `search_value` text DEFAULT NULL,
  PRIMARY KEY (`object_id`,`locale`,`search_type`),
  FULLTEXT KEY `search_index` (`search_value`),
  KEY `object_id_index` (`object_id`),
  KEY `search_type_index` (`search_type`),
  KEY `entity_type_index` (`entity_type`),
  KEY `locale_index` (`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `setting`
--

DROP TABLE IF EXISTS `setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `setting` (
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` text DEFAULT '',
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `topic`
--

DROP TABLE IF EXISTS `topic`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `topic` (
  `id` int(11) NOT NULL DEFAULT 0,
  `locale` varchar(4) NOT NULL DEFAULT 'de',
  `name` varchar(255) DEFAULT NULL,
  `path` varchar(255) NOT NULL DEFAULT '',
  `navi` int(11) DEFAULT 0,
  `root` int(11) DEFAULT 0,
  `rank` int(11) NOT NULL DEFAULT 0,
  `data_json` text DEFAULT NULL,
  PRIMARY KEY (`id`,`locale`),
  KEY `path_index` (`path`),
  KEY `rank_index` (`rank`),
  KEY `navi_index` (`navi`),
  KEY `root_index` (`root`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `topic_cluster`
--

DROP TABLE IF EXISTS `topic_cluster`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `topic_cluster` (
  `topic_id` int(11) NOT NULL DEFAULT 0,
  `parent_id` int(11) NOT NULL DEFAULT 0,
  `rank` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`topic_id`,`parent_id`,`rank`),
  KEY `topic_id_index` (`topic_id`),
  KEY `parent_id_index` (`parent_id`),
  KEY `rank_index` (`rank`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `topic_links`
--

DROP TABLE IF EXISTS `topic_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `topic_links` (
  `topic_id` int(11) NOT NULL DEFAULT 0,
  `name` varchar(255) DEFAULT NULL,
  `locale` varchar(4) NOT NULL DEFAULT 'de',
  `rank` int(11) NOT NULL DEFAULT 0,
  `url` varchar(255) DEFAULT NULL,
  `highlight` tinyint(4) DEFAULT 0,
  `search` text DEFAULT NULL,
  `meta_json` text DEFAULT NULL,
  `data_json` text DEFAULT NULL,
  PRIMARY KEY (`topic_id`,`locale`,`rank`),
  FULLTEXT KEY `keywords_search_index` (`search`),
  KEY `topic_id_index` (`topic_id`),
  KEY `rank_index` (`rank`),
  KEY `locale_index` (`locale`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `topic_service`
--

DROP TABLE IF EXISTS `topic_service`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `topic_service` (
  `topic_id` int(11) NOT NULL DEFAULT 0,
  `service_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`topic_id`,`service_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2021-05-21 13:39:20
