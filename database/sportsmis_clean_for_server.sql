-- MariaDB dump 10.19  Distrib 10.4.25-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sportsmis
-- ------------------------------------------------------
-- Server version	10.4.25-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `albums`
--

DROP TABLE IF EXISTS `albums`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `albums` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `event_type` enum('General','Meetings','Ceremony','Game') NOT NULL DEFAULT 'Game',
  `day` int(11) NOT NULL DEFAULT 1,
  `game_id` int(11) DEFAULT NULL,
  `match_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cover_photo_id` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`),
  KEY `match_id` (`match_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `albums_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE SET NULL,
  CONSTRAINT `albums_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `albums_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `albums`
--

LOCK TABLES `albums` WRITE;
/*!40000 ALTER TABLE `albums` DISABLE KEYS */;
/*!40000 ALTER TABLE `albums` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text NOT NULL,
  `ip_address` varchar(50) DEFAULT '127.0.0.1',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'SYSTEM_DEPLOYMENT_PREPARED','Cleared test athletes and media repository; prepared database for live server deployment.','127.0.0.1','2026-10-09 01:44:22'),(2,1,'ATHLETE_ROSTER_IMPORTED','Imported official athlete roster from EZ.xlsx: 370 athletes registered across 10 HPCL units.','127.0.0.1','2026-10-09 03:48:39');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facilities`
--

DROP TABLE IF EXISTS `facilities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `facilities` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `location` varchar(150) NOT NULL,
  `court_number` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=787 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facilities`
--

LOCK TABLES `facilities` WRITE;
/*!40000 ALTER TABLE `facilities` DISABLE KEYS */;
INSERT INTO `facilities` VALUES (1,'Badminton Court 1','court','Balewadi Indoor Stadium, Pune','Court 1'),(778,'Table Tennis','table','Table Tennis',''),(779,'Carrom','board','Carrom',''),(780,'Chess','board','Chess',''),(781,'Bridge','table','Bridge',''),(782,'Lawn Tennis','table','Lawn Tennis',''),(783,'Swimming','pool','Pool Deck',''),(784,'Main Secretariat','lane','Main Secretariat',''),(785,'Badminton Court 2','court','Indoor Sports Complex','Court 2'),(786,'Table Tennis Table 2','table','Indoor Sports Complex','Table 2');
/*!40000 ALTER TABLE `facilities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `games`
--

DROP TABLE IF EXISTS `games`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `games` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(50) NOT NULL,
  `category` varchar(50) NOT NULL DEFAULT 'Team Event',
  `format` varchar(50) NOT NULL,
  `score_format_id` int(11) DEFAULT NULL,
  `icon` varchar(50) DEFAULT 'fas fa-trophy',
  `rules_summary` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `men_min` int(11) NOT NULL DEFAULT 0,
  `men_max` int(11) NOT NULL DEFAULT 0,
  `women_min` int(11) NOT NULL DEFAULT 0,
  `women_max` int(11) NOT NULL DEFAULT 0,
  `total_max` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=785 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `games`
--

LOCK TABLES `games` WRITE;
/*!40000 ALTER TABLE `games` DISABLE KEYS */;
INSERT INTO `games` VALUES (1,'Badminton','badminton','Team Event','pools_knockout',NULL,'fas fa-feather-alt','Team Event: Best of 3 games to 21 points. Top 2 teams qualify for Semi-Finals.','2026-09-24 21:09:31',4,5,2,3,8),(2,'Table Tennis','table-tennis','Team Event','pools_knockout',1,'fas fa-table-tennis','Men\'s Team: Best of 5 games (First 2 Singles, 3rd Doubles, then Reverse Singles). Women\'s Team: Best of 3 games (First 2 Singles, 3rd Doubles if required). Top 2 teams from each pool qualify for Semi-Finals.','2026-09-24 21:09:31',4,5,2,3,8),(778,'Bridge','bridge','Team Event','pools_knockout',NULL,'fas fa-clone',NULL,'2026-09-28 11:35:34',4,4,0,0,4),(779,'Carrom','carrom','Team Event','pools_knockout',NULL,'fas fa-bullseye',NULL,'2026-09-28 11:35:34',4,4,0,4,8),(780,'Chess','chess','Team Event','swiss_league',NULL,'fas fa-chess',NULL,'2026-09-28 11:35:34',4,4,0,0,4),(781,'Swimming','swimming','Team Event','timed_heats',NULL,'fas fa-swimmer',NULL,'2026-09-28 11:35:34',2,3,2,3,6),(782,'Tennis','tennis','Team Event','pools_knockout',NULL,'fas fa-baseball-ball',NULL,'2026-09-28 11:35:34',2,3,1,2,5),(783,'Badminton - Open Category','badminton-open','Open Category','knockout',NULL,'fas fa-feather-alt',NULL,'2026-09-28 11:35:34',0,1,0,1,2),(784,'Table Tennis - Open Category','table-tennis-open','Open Category','knockout',NULL,'fas fa-table-tennis','Men\'s Above 30 / Open Category: Best of 5 games. Knockout format.','2026-09-28 11:35:34',0,1,0,1,2);
/*!40000 ALTER TABLE `games` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_events`
--

DROP TABLE IF EXISTS `master_events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_name` varchar(150) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(150) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=782 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_events`
--

LOCK TABLES `master_events` WRITE;
/*!40000 ALTER TABLE `master_events` DISABLE KEYS */;
INSERT INTO `master_events` VALUES (1,'Team Managers Meeting (TMM)','meeting','2026-03-01','09:00:00','10:00:00','Conference Hall A','Fixture draw confirmation & briefing'),(778,'Reporting & Warm-up','protocol','2026-10-09','09:00:00','09:30:00','Pool Area','All Swimmers: Official reporting, lane verification, and warm-up session.'),(779,'Intermission / Buffer','buffer','2026-10-09','11:45:00','12:15:00','Pool Area','Pool Open for Recovery: Mid-day break and warm-down ahead of Men\'s Finals.'),(780,'LUNCH BREAK','break','2026-10-09','13:00:00','14:00:00','Dining Hall','All Participants: Tournament Lunch Break for athletes, coaches, and officials.'),(781,'Medal Ceremony & Presentation','ceremony','2026-10-09','14:00:00','14:30:00','Podium','All Winners: Swimming Medal Ceremony & Presentation (Gold, Silver, Bronze).');
/*!40000 ALTER TABLE `master_events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `matches`
--

DROP TABLE IF EXISTS `matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `game_id` int(11) NOT NULL,
  `round` varchar(100) NOT NULL,
  `pool_name` varchar(10) DEFAULT NULL,
  `team1_id` int(11) DEFAULT NULL,
  `team2_id` int(11) DEFAULT NULL,
  `winner_id` int(11) DEFAULT NULL,
  `facility_id` int(11) DEFAULT NULL,
  `score_format_id` int(11) DEFAULT NULL,
  `match_date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('scheduled','in_progress','completed') DEFAULT 'scheduled',
  `scores_json` text DEFAULT NULL,
  `volunteer_id` int(11) DEFAULT NULL,
  `is_published` tinyint(1) DEFAULT 1,
  `lock_device_id` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`),
  KEY `team1_id` (`team1_id`),
  KEY `team2_id` (`team2_id`),
  KEY `winner_id` (`winner_id`),
  KEY `facility_id` (`facility_id`),
  KEY `volunteer_id` (`volunteer_id`),
  CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`team1_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`team2_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `matches_ibfk_4` FOREIGN KEY (`winner_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `matches_ibfk_5` FOREIGN KEY (`facility_id`) REFERENCES `facilities` (`id`) ON DELETE SET NULL,
  CONSTRAINT `matches_ibfk_6` FOREIGN KEY (`volunteer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=656 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `matches`
--

LOCK TABLES `matches` WRITE;
/*!40000 ALTER TABLE `matches` DISABLE KEYS */;
INSERT INTO `matches` VALUES (417,778,'Group A - Round 1','A',857,812,NULL,781,NULL,'2026-10-09','10:00:00','11:15:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(418,778,'Group A - Round 1','A',866,839,NULL,781,NULL,'2026-10-09','10:00:00','11:15:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(419,778,'Group B - Round 1','B',848,794,NULL,781,NULL,'2026-10-09','10:00:00','11:15:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(420,778,'Group B - Round 1','B',821,893,NULL,781,NULL,'2026-10-09','10:00:00','11:15:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(421,778,'Group A - Round 2','A',875,857,NULL,781,NULL,'2026-10-09','11:15:00','12:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(422,778,'Group A - Round 2','A',812,866,NULL,781,NULL,'2026-10-09','11:15:00','12:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(423,778,'Group B - Round 2','B',803,848,NULL,781,NULL,'2026-10-09','11:15:00','12:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(424,778,'Group B - Round 2','B',794,821,NULL,781,NULL,'2026-10-09','11:15:00','12:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(425,778,'Group A - Round 3','A',839,875,NULL,781,NULL,'2026-10-09','13:30:00','14:45:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(426,778,'Group A - Round 3','A',857,866,NULL,781,NULL,'2026-10-09','13:30:00','14:45:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(427,778,'Group B - Round 3','B',893,803,NULL,781,NULL,'2026-10-09','13:30:00','14:45:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(428,778,'Group B - Round 3','B',848,821,NULL,781,NULL,'2026-10-09','13:30:00','14:45:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(429,778,'Group A - Round 4','A',812,839,NULL,781,NULL,'2026-10-09','14:45:00','16:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(430,778,'Group A - Round 4','A',866,875,NULL,781,NULL,'2026-10-09','14:45:00','16:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(431,778,'Group B - Round 4','B',794,893,NULL,781,NULL,'2026-10-09','14:45:00','16:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(432,778,'Group B - Round 4','B',821,803,NULL,781,NULL,'2026-10-09','14:45:00','16:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(433,778,'Group A - Round 5','A',839,857,NULL,781,NULL,'2026-10-09','16:00:00','17:15:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(434,778,'Group A - Round 5','A',875,812,NULL,781,NULL,'2026-10-09','16:00:00','17:15:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(435,778,'Group B - Round 5','B',893,848,NULL,781,NULL,'2026-10-09','16:00:00','17:15:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(436,778,'Group B - Round 5','B',803,794,NULL,781,NULL,'2026-10-09','16:00:00','17:15:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:12:48'),(459,782,'Group A - RR 1','A',798,834,NULL,782,NULL,'2026-10-09','10:00:00','11:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:24:21'),(460,782,'Group B - RR 1','B',870,852,NULL,782,NULL,'2026-10-09','10:00:00','11:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:24:21'),(461,782,'Group C - RR 1','C',879,816,NULL,782,NULL,'2026-10-09','10:00:00','11:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:24:21'),(462,782,'Group D - RR 1','D',825,807,NULL,782,NULL,'2026-10-09','10:00:00','11:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:24:21'),(463,782,'Group A - RR 2','A',834,861,NULL,782,NULL,'2026-10-09','11:00:00','12:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:24:21'),(464,782,'Group B - RR 2','B',852,843,NULL,782,NULL,'2026-10-09','11:00:00','12:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:24:21'),(465,782,'Group C - RR 2','C',816,897,NULL,782,NULL,'2026-10-09','11:00:00','12:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:24:21'),(466,782,'Group D - RR 2','D',807,888,NULL,782,NULL,'2026-10-09','11:00:00','12:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:24:21'),(467,782,'Group A - RR 3','A',861,798,NULL,782,NULL,'2026-10-09','12:00:00','13:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:24:21'),(468,782,'Group B - RR 3','B',843,870,NULL,782,NULL,'2026-10-09','12:00:00','13:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:24:21'),(469,782,'Group C - RR 3','C',897,879,NULL,782,NULL,'2026-10-09','12:00:00','13:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:24:21'),(470,782,'Group D - RR 3','D',888,825,NULL,782,NULL,'2026-10-09','12:00:00','13:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:24:21'),(471,782,'Quarter-Final 1 (Q1)','Knockout',798,807,NULL,782,NULL,'2026-10-09','14:00:00','15:30:00','scheduled',NULL,NULL,0,NULL,'2026-10-08 22:24:21'),(472,782,'Quarter-Final 2 (Q2)','Knockout',861,825,NULL,782,NULL,'2026-10-09','14:00:00','15:30:00','scheduled',NULL,NULL,0,NULL,'2026-10-08 22:24:21'),(473,782,'Quarter-Final 3 (Q3)','Knockout',870,816,NULL,782,NULL,'2026-10-09','15:30:00','17:00:00','scheduled',NULL,NULL,0,NULL,'2026-10-08 22:24:21'),(474,782,'Quarter-Final 4 (Q4)','Knockout',852,879,NULL,782,NULL,'2026-10-09','15:30:00','17:00:00','scheduled',NULL,NULL,0,NULL,'2026-10-08 22:24:21'),(475,782,'Semi-Final 1 (SF1)','Knockout',798,852,NULL,782,NULL,'2026-10-10','10:00:00','11:30:00','scheduled',NULL,NULL,0,NULL,'2026-10-08 22:24:21'),(476,782,'Semi-Final 2 (SF2)','Knockout',861,870,NULL,782,NULL,'2026-10-10','10:00:00','11:30:00','scheduled',NULL,NULL,0,NULL,'2026-10-08 22:24:21'),(477,782,'Championship Final','Knockout',798,861,NULL,782,NULL,'2026-10-10','11:30:00','13:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:24:21'),(478,779,'Men\'s Group A - RR 1','A',822,795,NULL,779,NULL,'2026-10-09','10:00:00','11:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:28:02'),(479,779,'Men\'s Group B - RR 1','B',858,804,NULL,779,NULL,'2026-10-09','10:00:00','11:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:28:02'),(480,779,'Men\'s Group C - RR 1','C',840,831,NULL,779,NULL,'2026-10-09','10:00:00','11:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:28:02'),(481,779,'Men\'s Group D - RR 1','D',813,894,NULL,779,NULL,'2026-10-09','10:00:00','11:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:28:02'),(482,779,'Men\'s Group A - RR 2','A',795,885,NULL,779,NULL,'2026-10-09','11:30:00','13:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:28:02'),(483,779,'Men\'s Group B - RR 2','B',804,849,NULL,779,NULL,'2026-10-09','11:30:00','13:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:28:02'),(484,779,'Men\'s Group C - RR 2','C',831,867,NULL,779,NULL,'2026-10-09','11:30:00','13:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:28:02'),(485,779,'Men\'s Group D - RR 2','D',894,876,NULL,779,NULL,'2026-10-09','11:30:00','13:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:28:02'),(486,779,'Men\'s Group A - RR 3','A',885,822,NULL,779,NULL,'2026-10-09','14:00:00','15:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:28:02'),(487,779,'Men\'s Group B - RR 3','B',849,858,NULL,779,NULL,'2026-10-09','14:00:00','15:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:28:02'),(488,779,'Men\'s Group C - RR 3','C',867,840,NULL,779,NULL,'2026-10-09','14:00:00','15:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:28:02'),(489,779,'Men\'s Group D - RR 3','D',876,813,NULL,779,NULL,'2026-10-09','14:00:00','15:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:28:02'),(490,779,'Men\'s QF 1','Knockout',822,894,NULL,779,NULL,'2026-10-09','15:30:00','17:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:28:02'),(491,779,'Men\'s QF 2','Knockout',795,813,NULL,779,NULL,'2026-10-09','15:30:00','17:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:28:02'),(492,779,'Men\'s QF 3','Knockout',858,831,NULL,779,NULL,'2026-10-09','15:30:00','17:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:28:02'),(493,779,'Men\'s QF 4','Knockout',804,840,NULL,779,NULL,'2026-10-09','15:30:00','17:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:28:02'),(494,779,'Men\'s SF 1','Knockout',822,840,NULL,779,NULL,'2026-10-10','10:00:00','11:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:28:02'),(495,779,'Men\'s SF 2','Knockout',813,858,NULL,779,NULL,'2026-10-10','10:00:00','11:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:28:02'),(496,779,'Men\'s Final','Knockout',822,813,NULL,779,NULL,'2026-10-10','11:30:00','13:00:00','scheduled',NULL,NULL,0,NULL,'2026-10-08 22:28:02'),(497,780,'Round 1 - Board A','Swiss',796,850,NULL,780,NULL,'2026-10-09','10:00:00','11:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(498,780,'Round 1 - Board B','Swiss',805,859,NULL,780,NULL,'2026-10-09','10:00:00','11:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(499,780,'Round 1 - Board C','Swiss',814,868,NULL,780,NULL,'2026-10-09','10:00:00','11:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(500,780,'Round 1 - Board D','Swiss',823,877,NULL,780,NULL,'2026-10-09','10:00:00','11:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(501,780,'Round 1 - Board E','Swiss',832,886,NULL,780,NULL,'2026-10-09','10:00:00','11:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(502,780,'Round 1 - Board F','Swiss',841,895,NULL,780,NULL,'2026-10-09','10:00:00','11:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(503,780,'Round 2 - Board A','Swiss',796,859,NULL,780,NULL,'2026-10-09','11:00:00','12:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(504,780,'Round 2 - Board B','Swiss',850,805,NULL,780,NULL,'2026-10-09','11:00:00','12:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(505,780,'Round 2 - Board C','Swiss',814,877,NULL,780,NULL,'2026-10-09','11:00:00','12:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(506,780,'Round 2 - Board D','Swiss',868,823,NULL,780,NULL,'2026-10-09','11:00:00','12:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(507,780,'Round 2 - Board E','Swiss',832,895,NULL,780,NULL,'2026-10-09','11:00:00','12:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(508,780,'Round 2 - Board F','Swiss',886,841,NULL,780,NULL,'2026-10-09','11:00:00','12:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(509,780,'Round 3 - Board A','Swiss',796,805,NULL,780,NULL,'2026-10-09','12:00:00','13:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(510,780,'Round 3 - Board B','Swiss',859,850,NULL,780,NULL,'2026-10-09','12:00:00','13:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(511,780,'Round 3 - Board C','Swiss',814,823,NULL,780,NULL,'2026-10-09','12:00:00','13:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(512,780,'Round 3 - Board D','Swiss',877,868,NULL,780,NULL,'2026-10-09','12:00:00','13:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(513,780,'Round 3 - Board E','Swiss',832,841,NULL,780,NULL,'2026-10-09','12:00:00','13:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(514,780,'Round 3 - Board F','Swiss',895,886,NULL,780,NULL,'2026-10-09','12:00:00','13:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(515,780,'Round 4 - Board A','Swiss',796,868,NULL,780,NULL,'2026-10-09','14:00:00','15:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(516,780,'Round 4 - Board B','Swiss',805,877,NULL,780,NULL,'2026-10-09','14:00:00','15:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(517,780,'Round 4 - Board C','Swiss',814,886,NULL,780,NULL,'2026-10-09','14:00:00','15:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(518,780,'Round 4 - Board D','Swiss',823,895,NULL,780,NULL,'2026-10-09','14:00:00','15:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(519,780,'Round 4 - Board E','Swiss',832,850,NULL,780,NULL,'2026-10-09','14:00:00','15:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(520,780,'Round 4 - Board F','Swiss',841,859,NULL,780,NULL,'2026-10-09','14:00:00','15:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(521,780,'Round 5 - Board A','Swiss',796,877,NULL,780,NULL,'2026-10-09','15:00:00','16:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(522,780,'Round 5 - Board B','Swiss',805,868,NULL,780,NULL,'2026-10-09','15:00:00','16:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(523,780,'Round 5 - Board C','Swiss',814,895,NULL,780,NULL,'2026-10-09','15:00:00','16:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(524,780,'Round 5 - Board D','Swiss',823,886,NULL,780,NULL,'2026-10-09','15:00:00','16:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(525,780,'Round 5 - Board E','Swiss',832,859,NULL,780,NULL,'2026-10-09','15:00:00','16:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(526,780,'Round 5 - Board F','Swiss',841,850,NULL,780,NULL,'2026-10-09','15:00:00','16:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(527,780,'Round 6 - Board A','Swiss',796,886,NULL,780,NULL,'2026-10-10','10:00:00','11:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(528,780,'Round 6 - Board B','Swiss',805,895,NULL,780,NULL,'2026-10-10','10:00:00','11:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(529,780,'Round 6 - Board C','Swiss',814,850,NULL,780,NULL,'2026-10-10','10:00:00','11:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(530,780,'Round 6 - Board D','Swiss',823,859,NULL,780,NULL,'2026-10-10','10:00:00','11:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(531,780,'Round 6 - Board E','Swiss',832,868,NULL,780,NULL,'2026-10-10','10:00:00','11:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(532,780,'Round 6 - Board F','Swiss',841,877,NULL,780,NULL,'2026-10-10','10:00:00','11:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(533,780,'Round 7 (Final) - Board A','Swiss',796,895,NULL,780,NULL,'2026-10-10','11:00:00','12:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(534,780,'Round 7 (Final) - Board B','Swiss',805,886,NULL,780,NULL,'2026-10-10','11:00:00','12:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(535,780,'Round 7 (Final) - Board C','Swiss',814,859,NULL,780,NULL,'2026-10-10','11:00:00','12:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(536,780,'Round 7 (Final) - Board D','Swiss',823,850,NULL,780,NULL,'2026-10-10','11:00:00','12:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(537,780,'Round 7 (Final) - Board E','Swiss',832,877,NULL,780,NULL,'2026-10-10','11:00:00','12:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(538,780,'Round 7 (Final) - Board F','Swiss',841,868,NULL,780,NULL,'2026-10-10','11:00:00','12:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:31:04'),(588,2,'Men\'s Gr A (5 v 6)','A',847,829,NULL,778,1,'2026-10-10','10:00:00','11:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(589,2,'Men\'s Gr A (3 v 5)','A',838,847,NULL,778,1,'2026-10-10','10:00:00','11:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(590,2,'Men\'s Gr B (5 v 6)','B',892,865,NULL,778,1,'2026-10-10','10:00:00','11:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(591,2,'Men\'s Gr B (3 v 5)','B',883,892,NULL,778,1,'2026-10-10','10:00:00','11:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(592,2,'Men\'s Gr A (2 v 4)','A',856,874,NULL,778,1,'2026-10-10','11:30:00','12:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(593,2,'Men\'s Gr A (1 v 5)','A',811,847,NULL,778,1,'2026-10-10','11:30:00','12:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(594,2,'Men\'s Gr B (2 v 4)','B',793,802,NULL,778,1,'2026-10-10','11:30:00','12:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(595,2,'Men\'s Gr B (1 v 5)','B',820,892,NULL,778,1,'2026-10-10','11:30:00','12:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(596,2,'Men\'s Gr A (3 v 6)','A',838,829,NULL,778,1,'2026-10-10','12:30:00','13:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(597,2,'Men\'s Gr B (3 v 6)','B',883,865,NULL,778,1,'2026-10-10','12:30:00','13:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(598,2,'Women\'s League (5 v 6)','Women',793,874,NULL,778,2,'2026-10-10','12:30:00','13:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(599,2,'Women\'s League (3 v 4)','Women',811,838,NULL,778,2,'2026-10-10','12:30:00','13:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(600,2,'Men\'s Above 30 - Match 1','Open',811,820,NULL,778,1,'2026-10-10','12:30:00','13:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(601,2,'Men\'s Gr A (4 v 5)','A',874,847,NULL,778,1,'2026-10-10','13:30:00','14:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(602,2,'Men\'s Gr B (4 v 5)','B',802,892,NULL,778,1,'2026-10-10','13:30:00','14:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(603,2,'Women\'s League (4 v 5)','Women',838,793,NULL,778,2,'2026-10-10','13:30:00','14:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(604,2,'Women\'s League (2 v 6)','Women',892,874,NULL,778,2,'2026-10-10','13:30:00','14:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(605,2,'Men\'s Above 30 - Match 2','Open',856,793,NULL,778,1,'2026-10-10','13:30:00','14:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(606,2,'Men\'s Gr A (2 v 6)','A',856,829,NULL,778,1,'2026-10-10','14:30:00','15:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(607,2,'Men\'s Gr A (3 v 4)','A',838,874,NULL,778,1,'2026-10-10','14:30:00','15:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(608,2,'Men\'s Gr B (2 v 6)','B',793,865,NULL,778,1,'2026-10-10','14:30:00','15:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(609,2,'Men\'s Gr B (3 v 4)','B',883,802,NULL,778,1,'2026-10-10','14:30:00','15:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(610,2,'Men\'s Gr A (2 v 5)','A',856,847,NULL,778,1,'2026-10-10','15:30:00','16:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(611,2,'Men\'s Gr B (2 v 5)','B',793,892,NULL,778,1,'2026-10-10','15:30:00','16:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(612,2,'Women\'s League (1 v 6)','Women',820,874,NULL,778,2,'2026-10-10','15:30:00','16:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(613,2,'Women\'s League (3 v 5)','Women',811,793,NULL,778,2,'2026-10-10','15:30:00','16:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(614,2,'Men\'s Above 30 - Match 3','Open',838,883,NULL,778,1,'2026-10-10','15:30:00','16:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(615,2,'Men\'s Above 30 - Match 4','Open',874,802,NULL,778,1,'2026-10-10','16:30:00','17:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(616,2,'Men\'s Gr A (1 v 6)','A',811,829,NULL,778,1,'2026-10-10','17:00:00','18:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(617,2,'Men\'s Gr B (1 v 6)','B',820,865,NULL,778,1,'2026-10-10','17:00:00','18:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(618,2,'Women\'s League (3 v 6)','Women',811,874,NULL,778,2,'2026-10-10','17:00:00','18:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(619,2,'Women\'s League (2 v 5)','Women',892,793,NULL,778,2,'2026-10-10','17:00:00','18:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(620,2,'Men\'s Gr A (4 v 6)','A',874,829,NULL,778,1,'2026-10-10','18:00:00','19:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(621,2,'Men\'s Gr A (1 v 3)','A',811,838,NULL,778,1,'2026-10-10','18:00:00','19:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(622,2,'Men\'s Gr B (4 v 5)','B',802,892,NULL,778,1,'2026-10-10','18:00:00','19:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(623,2,'Men\'s Gr B (1 v 3)','B',820,883,NULL,778,1,'2026-10-10','18:00:00','19:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(624,2,'Men\'s Gr A (3 v 6)','A',838,829,NULL,778,1,'2026-10-10','19:00:00','20:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(625,2,'Men\'s Gr A (1 v 4)','A',811,874,NULL,778,1,'2026-10-10','19:00:00','20:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(626,2,'Men\'s Gr B (3 v 6)','B',883,865,NULL,778,1,'2026-10-10','19:00:00','20:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(627,2,'Men\'s Gr B (1 v 4)','B',820,802,NULL,778,1,'2026-10-10','19:00:00','20:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(628,2,'Men\'s Above 30 - Match 5','Open',847,892,NULL,778,1,'2026-10-10','20:00:00','21:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(629,2,'Men\'s Gr A (2 v 3)','A',856,838,NULL,778,1,'2026-10-11','09:30:00','10:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(630,2,'Men\'s Gr B (2 v 3)','B',793,883,NULL,778,1,'2026-10-11','09:30:00','10:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(631,2,'Women\'s League (1 v 5)','Women',820,793,NULL,778,2,'2026-10-11','09:30:00','10:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(632,2,'Women\'s League (2 v 4)','Women',892,838,NULL,778,2,'2026-10-11','09:30:00','10:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(633,2,'Men\'s Above 30 - Semifinal 1','Open',811,856,NULL,778,1,'2026-10-11','09:30:00','10:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(634,2,'Men\'s Gr A (1 v 2)','A',811,856,NULL,778,1,'2026-10-11','10:30:00','11:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(635,2,'Men\'s Gr B (1 v 2)','B',820,793,NULL,778,1,'2026-10-11','10:30:00','11:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(636,2,'Women\'s League (1 v 4)','Women',820,838,NULL,778,2,'2026-10-11','10:30:00','11:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(637,2,'Women\'s League (2 v 3)','Women',892,811,NULL,778,2,'2026-10-11','10:30:00','11:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(638,2,'Men\'s Above 30 - Semifinal 2','Open',820,793,NULL,778,1,'2026-10-11','10:30:00','11:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(639,2,'Women\'s League (1 v 3)','Women',820,811,NULL,778,2,'2026-10-11','11:30:00','13:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(640,2,'Men\'s Above 30 - Final','Open',811,820,NULL,778,1,'2026-10-11','11:30:00','13:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(641,2,'Women\'s League (1 v 2)','Women',820,892,NULL,778,2,'2026-10-11','13:00:00','15:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(642,2,'Men\'s Semi-Final 1','Knockout',811,793,NULL,778,1,'2026-10-11','13:00:00','15:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(643,2,'Men\'s Semi-Final 2','Knockout',820,856,NULL,778,1,'2026-10-11','13:00:00','15:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(644,2,'Men\'s Championship Final','Knockout',811,820,NULL,778,1,'2026-10-11','15:00:00','17:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(645,778,'Semi-Final 1 (SF1)','Knockout',857,803,NULL,781,NULL,'2026-10-10','10:00:00','11:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(646,778,'Semi-Final 2 (SF2)','Knockout',848,839,NULL,781,NULL,'2026-10-10','10:00:00','11:30:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(647,778,'Championship Final','Knockout',857,848,NULL,781,NULL,'2026-10-10','11:30:00','13:00:00','scheduled',NULL,NULL,1,NULL,'2026-10-08 22:45:33'),(648,781,'50m Freestyle (Men) - Heat 1 (8 Swimmers)','Heats',NULL,NULL,NULL,783,NULL,'2026-10-09','09:30:00','09:50:00','scheduled','{\"type\":\"swimming\",\"event_name\":\"50m Freestyle (Men)\",\"heat_no\":\"50m Freestyle (Men) - Heat 1 (8 Swimmers)\",\"stage\":\"Heats\",\"participants\":\"Swimmers #1 to #8\",\"summary\":\"Scheduled: Swimmers #1 to #8 \\u2022 Heats\",\"lanes\":[{\"lane\":1,\"swimmer\":\"BHOWMICK KOUSHIK\",\"zone\":\"EZ\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":2,\"swimmer\":\"BHUTIA TASHI WANGYAL\",\"zone\":\"EZ\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":3,\"swimmer\":\"ROUT NIHAR RANJAN\",\"zone\":\"EZ\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":4,\"swimmer\":\"PATEL GIRIRAJ KISHORE\",\"zone\":\"HB\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":5,\"swimmer\":\"PRABHAKARARAO KOPPISETTY\",\"zone\":\"HB\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":6,\"swimmer\":\"SUNEET BATTY\",\"zone\":\"HB\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":7,\"swimmer\":\"HITESH PANIHAR\",\"zone\":\"MF\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":8,\"swimmer\":\"SURAJ CHOTULAL SHAHU\",\"zone\":\"MF\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"}]}',NULL,1,NULL,'2026-10-09 04:20:18'),(649,781,'50m Freestyle (Men) - Heat 2 (8 Swimmers)','Heats',NULL,NULL,NULL,783,NULL,'2026-10-09','09:50:00','10:10:00','scheduled','{\"type\":\"swimming\",\"event_name\":\"50m Freestyle (Men)\",\"heat_no\":\"50m Freestyle (Men) - Heat 2 (8 Swimmers)\",\"stage\":\"Heats\",\"participants\":\"Swimmers #9 to #16\",\"summary\":\"Scheduled: Swimmers #9 to #16 \\u2022 Heats\",\"lanes\":[{\"lane\":1,\"swimmer\":\"ADSULE VISHWAS APPASAHEB\",\"zone\":\"MR\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":2,\"swimmer\":\"PATHAK DIVYANG\",\"zone\":\"MR\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":3,\"swimmer\":\"Sunil Singh Yadav\",\"zone\":\"MR\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":4,\"swimmer\":\"THAKUR DEEPAK VASANT\",\"zone\":\"MR\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":5,\"swimmer\":\"CHANDNANI MAHESH K\",\"zone\":\"NWZ\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":6,\"swimmer\":\"KUSHAGRA VASHISHTH\",\"zone\":\"NWZ\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":7,\"swimmer\":\"MEENA MAHENDRA PRASAD\",\"zone\":\"NWZ\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":8,\"swimmer\":\"SAH BAIRISTER\",\"zone\":\"NZ\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"}]}',NULL,1,NULL,'2026-10-09 04:20:18'),(650,781,'50m Freestyle (Women) - Direct Final','Final',NULL,NULL,NULL,783,NULL,'2026-10-09','10:15:00','10:35:00','scheduled','{\"type\":\"swimming\",\"event_name\":\"50m Freestyle (Women)\",\"heat_no\":\"50m Freestyle (Women) - Direct Final\",\"stage\":\"Final\",\"participants\":\"All 4 Swimmers\",\"summary\":\"Scheduled: All 4 Swimmers \\u2022 Final\",\"lanes\":[{\"lane\":1,\"swimmer\":\"KAMAT NAMRATA\",\"zone\":\"MF\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":2,\"swimmer\":\"NIDHI AGARWAL\",\"zone\":\"MF\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":3,\"swimmer\":\"PANDEY KANIKA\",\"zone\":\"MF\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":4,\"swimmer\":\"AISHWARYA DEVANAND LAKHE\",\"zone\":\"MR\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"}]}',NULL,1,NULL,'2026-10-09 04:20:18'),(651,781,'50m Breaststroke (Men) - Heat 1 (5 Swimmers)','Heats',NULL,NULL,NULL,783,NULL,'2026-10-09','10:45:00','11:05:00','scheduled','{\"type\":\"swimming\",\"event_name\":\"50m Breaststroke (Men)\",\"heat_no\":\"50m Breaststroke (Men) - Heat 1 (5 Swimmers)\",\"stage\":\"Heats\",\"participants\":\"Swimmers #1 to #5\",\"summary\":\"Scheduled: Swimmers #1 to #5 \\u2022 Heats\",\"lanes\":[{\"lane\":1,\"swimmer\":\"BHOWMICK KOUSHIK\",\"zone\":\"EZ\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":2,\"swimmer\":\"BHUTIA TASHI WANGYAL\",\"zone\":\"EZ\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":3,\"swimmer\":\"ROUT NIHAR RANJAN\",\"zone\":\"EZ\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":4,\"swimmer\":\"PATEL GIRIRAJ KISHORE\",\"zone\":\"HB\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":5,\"swimmer\":\"PRABHAKARARAO KOPPISETTY\",\"zone\":\"HB\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"}]}',NULL,1,NULL,'2026-10-09 04:20:18'),(652,781,'50m Breaststroke (Men) - Heat 2 (5 Swimmers)','Heats',NULL,NULL,NULL,783,NULL,'2026-10-09','11:05:00','11:25:00','scheduled','{\"type\":\"swimming\",\"event_name\":\"50m Breaststroke (Men)\",\"heat_no\":\"50m Breaststroke (Men) - Heat 2 (5 Swimmers)\",\"stage\":\"Heats\",\"participants\":\"Swimmers #6 to #10\",\"summary\":\"Scheduled: Swimmers #6 to #10 \\u2022 Heats\",\"lanes\":[{\"lane\":1,\"swimmer\":\"SUNEET BATTY\",\"zone\":\"HB\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":2,\"swimmer\":\"HITESH PANIHAR\",\"zone\":\"MF\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":3,\"swimmer\":\"SURAJ CHOTULAL SHAHU\",\"zone\":\"MF\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":4,\"swimmer\":\"ADSULE VISHWAS APPASAHEB\",\"zone\":\"MR\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":5,\"swimmer\":\"PATHAK DIVYANG\",\"zone\":\"MR\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"}]}',NULL,1,NULL,'2026-10-09 04:20:18'),(653,781,'50m Breaststroke (Women) - Direct Final','Final',NULL,NULL,NULL,783,NULL,'2026-10-09','11:30:00','11:45:00','scheduled','{\"type\":\"swimming\",\"event_name\":\"50m Breaststroke (Women)\",\"heat_no\":\"50m Breaststroke (Women) - Direct Final\",\"stage\":\"Final\",\"participants\":\"All 2 Swimmers\",\"summary\":\"Scheduled: All 2 Swimmers \\u2022 Final\",\"lanes\":[{\"lane\":1,\"swimmer\":\"TISHA MEENA\",\"zone\":\"MR\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"},{\"lane\":2,\"swimmer\":\"PRIYANKA\",\"zone\":\"SCZ\",\"time\":\"\",\"position\":\"\",\"status\":\"NORMAL\"}]}',NULL,1,NULL,'2026-10-09 04:20:18'),(654,781,'50m Freestyle (Men) - FINAL','Final',NULL,NULL,NULL,783,NULL,'2026-10-09','12:15:00','12:35:00','scheduled','{\"type\":\"swimming\",\"event_name\":\"50m Freestyle (Men)\",\"heat_no\":\"50m Freestyle (Men) - FINAL\",\"stage\":\"Final\",\"participants\":\"Top Qualifiers\",\"summary\":\"Scheduled: Top Qualifiers \\u2022 Final\",\"lanes\":[]}',NULL,1,NULL,'2026-10-09 04:20:18'),(655,781,'50m Breaststroke (Men) - FINAL','Final',NULL,NULL,NULL,783,NULL,'2026-10-09','12:40:00','13:00:00','scheduled','{\"type\":\"swimming\",\"event_name\":\"50m Breaststroke (Men)\",\"heat_no\":\"50m Breaststroke (Men) - FINAL\",\"stage\":\"Final\",\"participants\":\"Top Qualifiers\",\"summary\":\"Scheduled: Top Qualifiers \\u2022 Final\",\"lanes\":[]}',NULL,1,NULL,'2026-10-09 04:20:18');
/*!40000 ALTER TABLE `matches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `photos`
--

DROP TABLE IF EXISTS `photos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `photos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `album_id` int(11) DEFAULT NULL,
  `event_type` enum('General','Meetings','Ceremony','Game') NOT NULL DEFAULT 'Game',
  `title` varchar(255) DEFAULT NULL,
  `day` int(11) NOT NULL,
  `game_id` int(11) DEFAULT NULL,
  `time_slot` varchar(50) DEFAULT NULL,
  `match_id` int(11) DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `compressed_path` varchar(255) DEFAULT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `game_id` (`game_id`),
  KEY `match_id` (`match_id`),
  KEY `uploaded_by` (`uploaded_by`),
  KEY `fk_photos_album` (`album_id`),
  CONSTRAINT `fk_photos_album` FOREIGN KEY (`album_id`) REFERENCES `albums` (`id`) ON DELETE SET NULL,
  CONSTRAINT `photos_ibfk_1` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE,
  CONSTRAINT `photos_ibfk_2` FOREIGN KEY (`match_id`) REFERENCES `matches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `photos_ibfk_3` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `photos`
--

LOCK TABLES `photos` WRITE;
/*!40000 ALTER TABLE `photos` DISABLE KEYS */;
/*!40000 ALTER TABLE `photos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `players`
--

DROP TABLE IF EXISTS `players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `designation` varchar(100) DEFAULT 'Executive',
  `gender` enum('Men','Women') NOT NULL DEFAULT 'Men',
  `photo_path` varchar(255) DEFAULT NULL,
  `is_u30` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `team_id` (`team_id`),
  KEY `unit_id` (`unit_id`),
  CONSTRAINT `players_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE CASCADE,
  CONSTRAINT `players_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=371 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `players`
--

LOCK TABLES `players` WRITE;
/*!40000 ALTER TABLE `players` DISABLE KEYS */;
INSERT INTO `players` VALUES (1,883,788,'MAHAPATRA PROMOD KUMAR','Player','Men',NULL,0),(2,883,788,'KUMAR ANAND','Player','Men',NULL,0),(3,883,788,'RAJAUR ANIL KUMAR','Player','Men',NULL,0),(4,883,788,'SALIL DAS','Player','Men',NULL,0),(5,883,788,'CHAKRABORTY SHOVAN','Player','Men',NULL,0),(6,890,788,'RAHUL KARMAKAR','Player','Men',NULL,0),(7,888,788,'BORO MANAS','Player','Men',NULL,0),(8,888,788,'MISRI SAINYUM','Player','Men',NULL,0),(9,888,788,'SINGH ARUN','Player','Men',NULL,0),(10,885,788,'PATTANAIK RABI RANJAN','Player','Men',NULL,0),(11,885,788,'MEENA SUNIL KUMAR','Player','Men',NULL,0),(12,885,788,'NEWAR GYAN','Player','Men',NULL,0),(13,885,788,'NAREN DAS','Player','Men',NULL,0),(14,885,788,'KEDIA ANKITA KUMARI','Player','Women',NULL,0),(15,885,788,'PRITIKA SONI','Player','Women',NULL,0),(16,882,788,'KAMAN MRINMOY','Player','Men',NULL,0),(17,882,788,'SIJOU SWRJEE','Player','Men',NULL,0),(18,882,788,'KOTHA RAJEEV RATHAN KUMAR','Player','Men',NULL,0),(19,882,788,'Pujyam Mishra','Player','Men',NULL,0),(20,882,788,'ANURAG KUMAR','Player','Men',NULL,0),(21,889,788,'RAVADA BALA PAVAN KALYAN','Player','Men',NULL,0),(22,882,788,'PRARTHANA MISRI','Player','Women',NULL,0),(23,882,788,'PRIYANKA SINGH','Player','Women',NULL,0),(24,882,788,'KUMARI SWATI','Player','Women',NULL,0),(25,887,788,'BHUTIA TASHI WANGYAL','Player','Men',NULL,0),(26,887,788,'ROUT NIHAR RANJAN','Player','Men',NULL,0),(27,887,788,'BHOWMICK KOUSHIK','Player','Men',NULL,0),(28,886,788,'KOMA YATENDRA KUMAR','Player','Men',NULL,0),(29,886,788,'ACHARYA DEEPAM','Player','Men',NULL,0),(30,886,788,'ADRISH KAR','Player','Men',NULL,0),(31,886,788,'DAS BISWANATH','Player','Men',NULL,0),(32,882,788,'DASAR NARZARI','Team Manager','Men',NULL,0),(33,893,789,'DEWANGAN BHUPENDRA','Captain','Men',NULL,0),(34,893,789,'EESHAAN MITTAL','Player','Men',NULL,0),(35,893,789,'PRIYANK PRAKASH','Player','Men',NULL,0),(36,893,789,'RAY NEELAM SIDDHARTHA','Player','Women',NULL,0),(37,891,789,'SACHIN YADAV','Captain','Men',NULL,0),(38,891,789,'DIWAKAR KUMAR','Player','Men',NULL,0),(39,891,789,'SURENDER SINGH KUMAR','Player','Men',NULL,0),(40,891,789,'BANOTH SHIVAKUMAR','Player','Men',NULL,0),(41,891,789,'ALAGU PRASANTH GNANASEKARAN','Player','Men',NULL,0),(42,891,789,'JITENDRA SUMAN','Open Event','Men',NULL,0),(43,895,789,'KAMBOJ SAKET','Captain','Men',NULL,0),(44,895,789,'ASHUTOSH KUMAR','Player','Men',NULL,0),(45,895,789,'MISHRA CHANDRA PRAKASH','Player','Men',NULL,0),(46,895,789,'GEHANI JAIKUMAR VASUDEV','Player','Men',NULL,0),(47,895,789,'S.Balachandar','Senior Leadership','Men',NULL,0),(48,894,789,'TAMORE HARESHWAR','Captain','Men',NULL,0),(49,894,789,'PATANKAR SATISH GANPAT','Player','Men',NULL,0),(50,894,789,'MANGADE UMESH BABU','Player','Men',NULL,0),(51,894,789,'VIVEK BHASKAR THAKARE','Player','Men',NULL,0),(52,894,789,'NAGARE KALPANA','Captain','Women',NULL,0),(53,894,789,'TEJASWINI KISHOR KENI','Player','Women',NULL,0),(54,894,789,'CHOUDHARY SHUBHADA','Player','Women',NULL,0),(55,894,789,'VINAYA UNDALE','Player','Women',NULL,0),(56,897,789,'SARANGI PITABAS','Senior Leadership','Men',NULL,0),(57,897,789,'ARUN JANARDHANAN','Player','Men',NULL,0),(58,897,789,'THAKUR ASHISH SINGH','Captain','Men',NULL,0),(59,897,789,'ANURAG SINGH','Player','Men',NULL,0),(60,892,789,'N.THIRUNAVUKKARASU','Captain','Men',NULL,0),(61,892,789,'CHAWLA GURDEEP SINGH','Player','Men',NULL,0),(62,892,789,'ANIRBAN BISWAS','Player','Men',NULL,0),(63,892,789,'AKEN ANILKUMAR RAMDAS','Player','Men',NULL,0),(64,892,789,'ASHUTOSH SANDIP JADHAV','Player','Men',NULL,0),(65,892,789,'B SRINIVASA GOPALA KRISHNA','Senior Leadership','Men',NULL,0),(66,892,789,'DEBASHISH BASAK','Senior Leadership','Men',NULL,0),(67,892,789,'BASAK MANDIRA','Captain','Women',NULL,0),(68,892,789,'NIKAM VINITA VINAYAK','Player','Women',NULL,0),(69,892,789,'SHREYA GUPTA','Player','Women',NULL,0),(70,896,789,'PATEL GIRIRAJ KISHORE','Captain','Men',NULL,0),(71,896,789,'PRABHAKARARAO KOPPISETTY','Player','Men',NULL,0),(72,896,789,'SUNEET BATTY','Player','Men',NULL,0),(73,891,789,'VIJAYANAND M RANE','Team Manager','Men',NULL,0),(74,810,780,'BHIL MUKESH KUMAR','Player','Men',NULL,0),(75,810,780,'SAHA RAHUL','Player','Men',NULL,0),(76,810,780,'M KURIAKOSE JACOB','Player','Men',NULL,0),(77,810,780,'YADAV MUKESH','Player','Men',NULL,0),(78,810,780,'D GOPINATH','Player','Men',NULL,0),(79,810,780,'SRINIVAS REDDY ALETI','Player','Men',NULL,0),(80,810,780,'CH SRINIVAS','Player','Men',NULL,0),(81,810,780,'BAFILA RAJINI DEEPAK','Player','Women',NULL,0),(82,810,780,'BARA ARPITA KANAK','Player','Women',NULL,0),(83,810,780,'BHAVIKA VERMA','Player','Women',NULL,0),(84,817,780,'YASH SINGANIA','Player','Men',NULL,0),(85,817,780,'POORNIMA','Player','Women',NULL,0),(86,812,780,'RAMAN S S','Player','Men',NULL,0),(87,812,780,'DEBJYOTI CHATTERJEE','Player','Men',NULL,0),(88,812,780,'SAAYALI RAJENDRA LIMJE','Player','Women',NULL,0),(89,812,780,'SHIKHARE ASHA SHARAD','Player','Women',NULL,0),(90,813,780,'DASHARATH VAIKOLI','Player','Men',NULL,0),(91,813,780,'MUKUNDE PRAKASH S','Player','Men',NULL,0),(92,813,780,'MAHADIK SANJAY YASHWANT','Player','Men',NULL,0),(93,813,780,'RAUL TUSHAR R','Player','Men',NULL,0),(94,813,780,'GANU NEHA DINESH','Player','Women',NULL,0),(95,813,780,'DHURI RASIKA SUSHIL','Player','Women',NULL,0),(96,813,780,'TAPKE VANDANA AMRUT','Player','Women',NULL,0),(97,813,780,'SAWANT SHOBHA RAJAN','Player','Women',NULL,0),(98,814,780,'AGRAWAL ANSHUL MADHUSUDAN','Player','Men',NULL,0),(99,814,780,'R. NARAYANAN','Player','Men',NULL,0),(100,814,780,'GUPTA ASHISH','Player','Men',NULL,0),(101,814,780,'ALAPATI CHAITANYA','Player','Men',NULL,0),(102,816,780,'BANSAL PRABHJOT VISHAV VAS','Player','Men',NULL,0),(103,816,780,'VANKUDOTH VIJAYKUMAR','Player','Men',NULL,0),(104,811,780,'KOSTA ARUN','Player','Men',NULL,0),(105,811,780,'PARMAR GAJANAN','Player','Men',NULL,0),(106,811,780,'PAREKH PANKAJ PRAVINCHANDRA','Player','Men',NULL,0),(107,811,780,'RAO SRINIVASA MARNI','Player','Men',NULL,0),(108,811,780,'SINGH SHASHI KANT','Player','Men',NULL,0),(109,811,780,'JASRAI MADHU','Player','Women',NULL,0),(110,811,780,'TARUNA HEMANT KUMAR RATHOD','Player','Women',NULL,0),(111,811,780,'BANDEKAR PRAGATI PRASAD','Player','Women',NULL,0),(112,818,780,'DHAKER ROHIT','Player','Men',NULL,0),(113,818,780,'Shilpa J Ramya','Player','Women',NULL,0),(114,815,780,'HITESH PANIHAR','Player','Men',NULL,0),(115,815,780,'SURAJ CHOTULAL SHAHU','Player','Men',NULL,0),(116,815,780,'PANDEY KANIKA','Player','Women',NULL,0),(117,815,780,'KAMAT NAMRATA','Player','Women',NULL,0),(118,815,780,'NIDHI AGARWAL','Player','Women',NULL,0),(119,810,780,'Tanaji Adate','Team Manager','Men',NULL,0),(120,846,784,'GYAMBA SONAM','Player','Women',NULL,0),(121,846,784,'KIRTDEEP KAUR','Player','Women',NULL,0),(122,846,784,'CHAUHAN PRADEEP','Player','Men',NULL,0),(123,846,784,'AKKAL VINOD KUMAR','Player','Men',NULL,0),(124,846,784,'SHAIK ALTHAF','Player','Men',NULL,0),(125,846,784,'DASH KAMAL KRUSHNA','Player','Men',NULL,0),(126,846,784,'BOGALAGANI GANESH','Player','Men',NULL,0),(127,848,784,'SHARMA NEERAJ','Player','Men',NULL,0),(128,848,784,'SUNIL KUMAR','Player','Men',NULL,0),(129,848,784,'GOYAL ROHIT','Player','Men',NULL,0),(130,848,784,'TARACHAND','Player','Men',NULL,0),(131,849,784,'SHORAJ SINGH','Player','Men',NULL,0),(132,849,784,'RAMESH CHAND','Player','Men',NULL,0),(133,849,784,'KHURANA DEEPAK','Player','Men',NULL,0),(134,849,784,'P.V.K.N. APPA RAO','Player','Men',NULL,0),(135,850,784,'PANDEY SANJAY KUMAR','Player','Men',NULL,0),(136,850,784,'SACHIN CHAKRAVORTY','Player','Men',NULL,0),(137,850,784,'VINAY KUMAR','Player','Men',NULL,0),(138,850,784,'Kumar Varun','Player','Men',NULL,0),(139,852,784,'NAIN SHRAVAN KUMAR','Player','Men',NULL,0),(140,852,784,'PANGTEY DINESH','Player','Men',NULL,0),(141,852,784,'SINGH SHAKTI','Player','Men',NULL,0),(142,854,784,'RISHABH JAIN','Player','Men',NULL,0),(143,851,784,'SAH BAIRISTER','Player','Men',NULL,0),(144,851,784,'SHAH SHAHID LATEEF','Player','Men',NULL,0),(145,847,784,'RAUT AJAY GAJANAN','Player','Men',NULL,0),(146,847,784,'KUMAR NAVEEN','Player','Men',NULL,0),(147,847,784,'NEHRA ABHISHEK','Player','Men',NULL,0),(148,847,784,'BANSAL MANI','Player','Men',NULL,0),(149,847,784,'HARSHIL ABHIJEET','Player','Men',NULL,0),(150,864,786,'RAMESH PARMAR','Team Manager','Men',NULL,0),(151,870,786,'RAJENDRAN V','Player','Men',NULL,0),(152,870,786,'B SATHEESH KUMAR','Player','Men',NULL,0),(153,864,786,'MEENA CHETAN PRAKASH','Player','Men',NULL,0),(154,864,786,'MOHIT RAMEJA','Player','Men',NULL,0),(155,864,786,'KHUSHAL MEENA','Player','Men',NULL,0),(156,864,786,'PAWAR PRASAD MANOHAR','Captain','Men',NULL,0),(157,864,786,'MEENA HEMENDRA KUMAR','Player','Men',NULL,0),(158,864,786,'YADAV SWATI','Player','Women',NULL,0),(159,864,786,'ANITA SHARMA','Player','Women',NULL,0),(160,864,786,'BHUVANESHWARI','Player','Women',NULL,0),(161,866,786,'MADHUKAR MANOJ KUMAR','Player','Men',NULL,0),(162,866,786,'Sanjay Kumar Gupta','Player','Men',NULL,0),(163,866,786,'NILESH MOHAN JAGTAP','Player','Men',NULL,0),(164,866,786,'KAPIL DEV','Player','Men',NULL,0),(165,867,786,'MEHTA DEVENDRAKUMAR P','Captain','Men',NULL,0),(166,867,786,'MEHTA VIMAL AMRUTLAL','Player','Men',NULL,0),(167,867,786,'MOHITE DHARMESH RAMRAO','Player','Men',NULL,0),(168,867,786,'PATEL SANJAYKUMAR GOPALBHAI','Player','Men',NULL,0),(169,867,786,'NIRMALA MARSHAL GONSALVES','Player','Women',NULL,0),(170,867,786,'SHIVA NIGAM','Captain','Women',NULL,0),(171,868,786,'BHATT SAURABH DIPAKKUMAR','Player','Men',NULL,0),(172,868,786,'SINGH BHUPINDER','Player','Men',NULL,0),(173,868,786,'BODDETI TULASI RAM','Captain','Men',NULL,0),(174,868,786,'GIRI ANJANI KUMAR','Player','Men',NULL,0),(175,870,786,'BISHNOI KRISHAN KUMAR','Player','Men',NULL,0),(176,870,786,'KHANDELWAL VAIBHAV','Player','Men',NULL,0),(177,870,786,'RAJ KUMAR','Captain','Men',NULL,0),(178,865,786,'KUMAR ASHOK','Player','Men',NULL,0),(179,865,786,'NIRANJAN ABHISHEK SINGH','Captain','Men',NULL,0),(180,865,786,'DINKAR VAIBHAV','Player','Men',NULL,0),(181,865,786,'PARPIYANI PIYUSH VINODBHAI','Player','Men',NULL,0),(182,865,786,'AGARWAL MANISH','Player','Men',NULL,0),(183,872,786,'CHOUHAN NAVEEN','Player','Men',NULL,0),(184,871,786,'RAJ SUNNY','Player','Men',NULL,0),(185,869,786,'CHANDNANI MAHESH K','Player','Men',NULL,0),(186,869,786,'KUSHAGRA VASHISHTH','Player','Men',NULL,0),(187,869,786,'MEENA MAHENDRA PRASAD','Captain','Men',NULL,0),(188,855,785,'MD RAHISH ALAM','Player','Men',NULL,0),(189,855,785,'VIVEK SINGH','Player','Men',NULL,0),(190,858,785,'PADTE DINESH RAMCHANDRA','Player','Men',NULL,0),(191,860,785,'THAKUR DEEPAK VASANT','Player','Men',NULL,0),(192,862,785,'ROJIN ROBINSON','Player','Men',NULL,0),(193,863,785,'VINAY GAUTAM','Player','Men',NULL,0),(194,857,785,'MENDONSA MICHEAL PASCAL','Player','Men',NULL,0),(195,857,785,'SHETTIGAR ASHOK PADMANABHA','Player','Men',NULL,0),(196,857,785,'MUNDA ARUN SINGH','Player','Men',NULL,0),(197,858,785,'ABHYANKAR ATUL YESHWANT','Player','Men',NULL,0),(198,858,785,'DALVI JITENDRA LAXMAN','Player','Men',NULL,0),(199,858,785,'PRAJAPATI KIRITKUMAR NAROTAM','Player','Men',NULL,0),(200,859,785,'KHABIA RAMESHLAL ZUMBARLAL','Player','Men',NULL,0),(201,859,785,'VISHNU','Player','Men',NULL,0),(202,859,785,'SWARAJ VIBHOOSHAN PAI','Player','Men',NULL,0),(203,856,785,'PANGE MILIND MOHAN','Player','Men',NULL,0),(204,855,785,'DANGI MAHESH','Player','Men',NULL,0),(205,861,785,'PRANAY RAHUL SHARMA','Player','Men',NULL,0),(206,859,785,'NAYAK SHUBHAM','Player','Men',NULL,0),(207,860,785,'ADSULE VISHWAS APPASAHEB','Player','Men',NULL,0),(208,860,785,'PATHAK DIVYANG','Player','Men',NULL,0),(209,856,785,'KUMAR MANISH','Player','Men',NULL,0),(210,855,785,'DEEKSHA SRIVAS','Player','Women',NULL,0),(211,855,785,'KOCHE SANSKRUTI','Player','Women',NULL,0),(212,855,785,'PRIYA CHAUHAN','Player','Women',NULL,0),(213,855,785,'DIVYA','Player','Women',NULL,0),(214,860,785,'AISHWARYA DEVANAND LAKHE','Player','Women',NULL,0),(215,860,785,'TISHA MEENA','Player','Women',NULL,0),(216,856,785,'KUMAR SUJIT','Player','Men',NULL,0),(217,855,785,'MORATHOTI GOPI KRISHNA','Player','Men',NULL,0),(218,855,785,'WALA  SUKETU DHIRAJ','Player','Men',NULL,0),(219,857,785,'SUNKARA RAMA CHANDRA RAO','Player','Men',NULL,0),(220,856,785,'JAISWAL ABHISHEK','Player','Men',NULL,0),(221,856,785,'BHARDWAJ PRIYANK','Player','Men',NULL,0),(222,861,785,'VERMA DEEPAK KUMAR','Player','Men',NULL,0),(223,861,785,'DAS ANKIT','Player','Men',NULL,0),(224,861,785,'RAI NEERAJ KISHORE','Player','Men',NULL,0),(225,861,785,'K. Thirumurugan','Player','Men',NULL,0),(226,860,785,'Sunil Singh Yadav','Player','Men',NULL,0),(227,873,787,'CHIRUMAMILLA G V S R K PRASAD','Player','Men',NULL,0),(228,873,787,'KONDAGORRI KRANTI KUMAR','Player','Men',NULL,0),(229,873,787,'ABHINAV VUDDAGIRI','Player','Men',NULL,0),(230,873,787,'MOOKERJEE INDRAJIT','Player','Men',NULL,0),(231,873,787,'BANDARU CHAITANYA VARAHA SAI RAM','Player','Men',NULL,0),(232,873,787,'PARAVASTU VINUTHA','Player','Women',NULL,0),(233,873,787,'KIRAN KUMARI','Player','Women',NULL,0),(234,875,787,'AJAY A','Player','Men',NULL,0),(235,875,787,'PAIDIPAMULA VEERENDRA BABU','Player','Men',NULL,0),(236,875,787,'GOTE VINOD BHAURAOJI','Player','Men',NULL,0),(237,875,787,'DAS AMITAVA','Player','Men',NULL,0),(238,876,787,'P V K BHASKAR','Player','Men',NULL,0),(239,876,787,'VENUGOPAL P','Player','Men',NULL,0),(240,876,787,'LIONEL KENNETH JOHN','Player','Men',NULL,0),(241,876,787,'SANTHOSH KUMAR S SHETTY','Player','Men',NULL,0),(242,876,787,'V HAMSAVENI','Player','Women',NULL,0),(243,876,787,'GUPTA SAUMYA','Player','Women',NULL,0),(244,876,787,'PUVVALA SAI SUDHAMAYEE','Player','Women',NULL,0),(245,876,787,'VIDYA VIJAY SHINDE','Player','Women',NULL,0),(246,877,787,'SRIMANTHULA VENKATA SAI DHEERENDRA','Player','Men',NULL,0),(247,877,787,'SNEHA BISHT','Player','Women',NULL,0),(248,877,787,'GOLAP DAS','Player','Men',NULL,0),(249,877,787,'GUPTA RAHUL','Player','Men',NULL,0),(250,879,787,'BHUPATI MURALI KRISHNA','Player','Men',NULL,0),(251,879,787,'SINGH NITIN CHANDRA','Player','Men',NULL,0),(252,879,787,'SINGH SUKHWINDER','Player','Men',NULL,0),(253,879,787,'SHALU PANDEY','Player','Women',NULL,0),(254,880,787,'NIMISHAKAVI VENKATA RAMA CHITRA','Player','Women',NULL,0),(255,880,787,'ABHISHEK SAGAR','Player','Men',NULL,0),(256,881,787,'POLISETTY SAI MOHAN','Player','Men',NULL,0),(257,881,787,'PERABATHULA SATYA MANIKYAM','Player','Women',NULL,0),(258,878,787,'BATNA RAJASEKHAR','Player','Men',NULL,0),(259,878,787,'MADEM LAXMI NAGA SRINIVAS','Player','Men',NULL,0),(260,878,787,'ANAND RUPAK','Player','Men',NULL,0),(261,878,787,'PRIYANKA','Player','Women',NULL,0),(262,874,787,'KUMAR NAGMANI','Player','Men',NULL,0),(263,874,787,'DHULKHED PRASAD SHRIPATI','Player','Men',NULL,0),(264,874,787,'ASTHANA VATSAL','Player','Men',NULL,0),(265,874,787,'GULATI ANKUR','Player','Men',NULL,0),(266,874,787,'SWAMY MAHADEV H S','Player','Men',NULL,0),(267,873,787,'ANIL KUMAR PALAKALURI','Team Manager','Men',NULL,0),(268,828,782,'GOKULNATH M','Player','Men',NULL,0),(269,828,782,'PRASHANTH PITTA','Player','Men',NULL,0),(270,828,782,'D G KIRAN','Player','Men',NULL,0),(271,828,782,'P S KATHIRVEL','Player','Men',NULL,0),(272,828,782,'KISHAN KUMAR CHALLA','Player','Men',NULL,0),(273,835,782,'SHRIDHAR DWIVEDI','Player','Men',NULL,0),(274,834,782,'R.SELLA PRABU','Player','Men',NULL,0),(275,834,782,'G GOUTHAM','Player','Men',NULL,0),(276,834,782,'KIRAN KUMAR VARANASI','Player','Men',NULL,0),(277,832,782,'C SARAVANA PERUMAL','Player','Men',NULL,0),(278,832,782,'H EASWARA IYER','Player','Men',NULL,0),(279,832,782,'GIRDONIA SACHIN KUMAR','Player','Men',NULL,0),(280,832,782,'SANAL KUMAR MA','Player','Men',NULL,0),(281,831,782,'K WILLIAMS','Player','Men',NULL,0),(282,831,782,'G SEKARBABU','Player','Men',NULL,0),(283,831,782,'SHARMA SHARAD','Player','Men',NULL,0),(284,831,782,'PONNAPATI RATHNAKAR BABU','Player','Men',NULL,0),(285,829,782,'R SUBHASH CHANDRA','Player','Men',NULL,0),(286,829,782,'B PRABHU','Player','Men',NULL,0),(287,829,782,'BSV HARSHAVARDHANA HEMANTH','Player','Men',NULL,0),(288,829,782,'S SHANKAR','Player','Men',NULL,0),(289,829,782,'D ANILKUMAR','Player','Men',NULL,0),(290,828,782,'ANISHA THOTTEMPUDI','Player','Women',NULL,0),(291,828,782,'P NEENA','Player','Women',NULL,0),(292,835,782,'K R RANJANI','Player','Women',NULL,0),(293,833,782,'ADHARI SOMAIAH','Player','Men',NULL,0),(294,833,782,'BIJEESH PULIYASSERY','Player','Men',NULL,0),(295,828,782,'U SAMBASIVAM','Team Manager','Men',NULL,0),(296,792,778,'V B ANEESH','Player','Men',NULL,0),(297,792,778,'K S DEEPAK','Player','Men',NULL,0),(298,792,778,'SAI NISCHAL DEV','Player','Men',NULL,0),(299,792,778,'SVS DURGA PRASAD','Player','Men',NULL,0),(300,792,778,'K.SUNEEL','Player','Men',NULL,0),(301,792,778,'TEJASWINI DEVI BHIMIREDDY','Player','Women',NULL,0),(302,792,778,'P MEERA','Player','Women',NULL,0),(303,794,778,'K N SATYANARAYANA','Player','Men',NULL,0),(304,794,778,'CH NAGA CHAITANYA','Player','Men',NULL,0),(305,794,778,'PUTREVU RAMAKRISHNA MURTY','Player','Men',NULL,0),(306,794,778,'Ch SOMA SEKHARA BABU','Player','Men',NULL,0),(307,795,778,'M LINGA SWAMY','Player','Men',NULL,0),(308,795,778,'BORA GANAPATHI RAO','Player','Men',NULL,0),(309,795,778,'G APPALA RAJU','Player','Men',NULL,0),(310,795,778,'I RAMA RAO','Player','Men',NULL,0),(311,795,778,'RETHUVARNA N K','Player','Women',NULL,0),(312,795,778,'ANUSREE T P','Player','Women',NULL,0),(313,796,778,'BEESETTI VENKATA SAI AKHIL','Player','Men',NULL,0),(314,796,778,'KONERU CHARAN KUMAR','Player','Men',NULL,0),(315,796,778,'M MURUGHESH','Player','Men',NULL,0),(316,796,778,'M SANTHOSH KRISHNA','Player','Men',NULL,0),(317,797,778,'PUNIT DESWAL','Player','Men',NULL,0),(318,797,778,'HEMANTH KUMAR','Player','Men',NULL,0),(319,797,778,'S KHARE PRASANNA','Player','Men',NULL,0),(320,793,778,'ANUP TOPPO','Player','Men',NULL,0),(321,793,778,'EESWAR CHAITANYA BATTULA','Player','Men',NULL,0),(322,793,778,'SATISH KUMAR KARRI','Player','Men',NULL,0),(323,793,778,'MOHAN KUMAR PACHILA','Player','Men',NULL,0),(324,793,778,'M RAFI ALAM','Player','Men',NULL,0),(325,793,778,'D BALA TRIPURA SUNDARI DEVI','Player','Women',NULL,0),(326,793,778,'NAVODITA KANDARI','Player','Women',NULL,0),(327,798,778,'PONNAGANTI RAVI','Player','Men',NULL,0),(328,798,778,'K RISHIKESWAR','Player','Men',NULL,0),(329,798,778,'SANJEEV RAJAK','Player','Men',NULL,0),(330,798,778,'KIRAN KUMAR GANTA','Player','Men',NULL,0),(331,799,778,'GANDU AKHIL','Player','Men',NULL,0),(332,799,778,'MUNMUN KUMARI','Player','Women',NULL,0),(333,800,778,'SALAPU VARDHAN','Player','Men',NULL,0),(334,792,778,'P VENKATAPATHI RAJU','Team Manager','Men',NULL,0),(335,837,783,'SRIVASTAVA SHIKHAR','Player','Men',NULL,0),(336,837,783,'GUPTA SAMIR','Player','Men',NULL,0),(337,837,783,'PAL GAURAV','Player','Men',NULL,0),(338,837,783,'DHANISHTH RAMESH PAWAR','Player','Men',NULL,0),(339,837,783,'CHAITANYA NALLA','Player','Men',NULL,0),(340,844,783,'SEJAL SINGH','Player','Women',NULL,0),(341,844,783,'KUMAR MANGLAM','Player','Men',NULL,0),(342,838,783,'SAHU MAHENDRA KUMAR','Player','Men',NULL,0),(343,838,783,'MEENA HARMUKH','Player','Men',NULL,0),(344,838,783,'SAXENA UMESH CHANDRA','Player','Men',NULL,0),(345,838,783,'RAO SHAILENDRA','Player','Men',NULL,0),(346,838,783,'SHINDE PRATIDNYA RAVINDRA','Player','Women',NULL,0),(347,838,783,'Mehak','Player','Women',NULL,0),(348,845,783,'NIBIR BORA','Player','Men',NULL,0),(349,840,783,'MORAVAKAR MANOHAR ANANT','Player','Men',NULL,0),(350,840,783,'VICKY AGRAWAL','Player','Men',NULL,0),(351,840,783,'RANE MANISH PANDURANG','Player','Men',NULL,0),(352,840,783,'UDAWANT ASHISH MUKUNDRAO','Player','Men',NULL,0),(353,840,783,'SUVARNA SHOBHA SHAILESH','Player','Women',NULL,0),(354,840,783,'PRERNA BHARTI','Player','Women',NULL,0),(355,840,783,'TWINA NADKARNI','Player','Women',NULL,0),(356,843,783,'RAJPAL SUNNY','Player','Men',NULL,0),(357,843,783,'MANAV PURI','Player','Men',NULL,0),(358,843,783,'BHIMANENI SRIKANTH','Player','Men',NULL,0),(359,842,783,'RAJ PUNEET','Player','Men',NULL,0),(360,842,783,'DIXIT RAHUL','Player','Men',NULL,0),(361,842,783,'NIGAM AMEET','Player','Men',NULL,0),(362,841,783,'ANKIT','Player','Men',NULL,0),(363,841,783,'REHAN FAHMID','Player','Men',NULL,0),(364,841,783,'SONAWANE GULAB BALKRUSHNA','Player','Men',NULL,0),(365,841,783,'CHIMMALAGI MARULASWAMI','Player','Men',NULL,0),(366,839,783,'NARAYANE KISHORKUMAR SUDAM','Player','Men',NULL,0),(367,839,783,'BHARUKA VINEET','Player','Men',NULL,0),(368,839,783,'MAZGAONKAR PRADNYAN MAHADEV','Player','Men',NULL,0),(369,839,783,'KUDTARKAR DINESH S','Player','Men',NULL,0),(370,837,783,'NITIN T JADHAV','Team Manager','Men',NULL,0);
/*!40000 ALTER TABLE `players` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `points_scheme`
--

DROP TABLE IF EXISTS `points_scheme`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `points_scheme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position` int(11) NOT NULL,
  `points` int(11) NOT NULL,
  `label` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `position` (`position`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `points_scheme`
--

LOCK TABLES `points_scheme` WRITE;
/*!40000 ALTER TABLE `points_scheme` DISABLE KEYS */;
INSERT INTO `points_scheme` VALUES (1,1,5,'Winner'),(2,2,3,'Runner-up'),(3,3,1,'Second Runner-up');
/*!40000 ALTER TABLE `points_scheme` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `score_formats`
--

DROP TABLE IF EXISTS `score_formats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `score_formats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `code` varchar(50) NOT NULL,
  `game_id` int(11) DEFAULT NULL,
  `type` enum('sets','periods','points','custom') NOT NULL DEFAULT 'sets',
  `columns_json` text NOT NULL,
  `total_columns` int(11) NOT NULL DEFAULT 3,
  `win_rule` varchar(50) NOT NULL DEFAULT 'most_games',
  `target_wins` int(11) NOT NULL DEFAULT 2,
  `points_to_win` int(11) NOT NULL DEFAULT 21,
  `has_serving` tinyint(1) NOT NULL DEFAULT 1,
  `badge_text` varchar(50) NOT NULL DEFAULT 'Best of 3',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `score_formats`
--

LOCK TABLES `score_formats` WRITE;
/*!40000 ALTER TABLE `score_formats` DISABLE KEYS */;
INSERT INTO `score_formats` VALUES (1,'Table Tennis Men (Best of 5)','tt_mens_bo5',2,'sets','[{\"key\":\"g1\",\"label\":\"Game 1\",\"short\":\"G1\",\"max\":40},{\"key\":\"g2\",\"label\":\"Game 2\",\"short\":\"G2\",\"max\":40},{\"key\":\"g3\",\"label\":\"Game 3\",\"short\":\"G3\",\"max\":40},{\"key\":\"g4\",\"label\":\"Game 4\",\"short\":\"G4\",\"max\":40},{\"key\":\"g5\",\"label\":\"Game 5\",\"short\":\"G5\",\"max\":40}]',5,'most_games',3,11,1,'Best of 5','Official 5-game format for Men\'s team event. First team to win 3 games wins.','2026-10-08 23:34:58'),(2,'Table Tennis Women (Best of 3)','tt_womens_bo3',2,'sets','[{\"key\":\"g1\",\"label\":\"Game 1\",\"short\":\"G1\",\"max\":40},{\"key\":\"g2\",\"label\":\"Game 2\",\"short\":\"G2\",\"max\":40},{\"key\":\"g3\",\"label\":\"Game 3\",\"short\":\"G3\",\"max\":40}]',3,'most_games',2,11,1,'Best of 3','Official 3-game format for Women\'s team event. First team to win 2 games wins.','2026-10-08 23:34:58'),(3,'Badminton (Best of 3 to 21 Pts)','badminton_bo3',1,'sets','[{\"key\":\"g1\",\"label\":\"Game 1\",\"short\":\"G1\",\"max\":40},{\"key\":\"g2\",\"label\":\"Game 2\",\"short\":\"G2\",\"max\":40},{\"key\":\"g3\",\"label\":\"Game 3\",\"short\":\"G3\",\"max\":40}]',3,'most_games',2,21,1,'Best of 3','Standard 3-game format to 21 points with deuce up to 30.','2026-10-08 23:34:58'),(4,'Lawn Tennis (Best of 3 Sets)','tennis_bo3',782,'sets','[{\"key\":\"s1\",\"label\":\"Set 1\",\"short\":\"S1\",\"max\":7},{\"key\":\"s2\",\"label\":\"Set 2\",\"short\":\"S2\",\"max\":7},{\"key\":\"s3\",\"label\":\"Set 3\",\"short\":\"S3\",\"max\":7}]',3,'most_games',2,6,1,'Best of 3 Sets','3 sets with advantage game scoring and tiebreaks.','2026-10-08 23:34:58'),(5,'Custom 4 Quarters / Periods','periods_4q',NULL,'periods','[{\"key\":\"q1\",\"label\":\"Quarter 1\",\"short\":\"Q1\",\"max\":99},{\"key\":\"q2\",\"label\":\"Quarter 2\",\"short\":\"Q2\",\"max\":99},{\"key\":\"q3\",\"label\":\"Quarter 3\",\"short\":\"Q3\",\"max\":99},{\"key\":\"q4\",\"label\":\"Quarter 4\",\"short\":\"Q4\",\"max\":99}]',4,'total_score',0,0,0,'4 Quarters','Cumulative 4-quarter / period scoring with total score summation.','2026-10-08 23:34:58');
/*!40000 ALTER TABLE `score_formats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `system_settings`
--

DROP TABLE IF EXISTS `system_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `system_settings` (
  `setting_key` varchar(64) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `system_settings`
--

LOCK TABLES `system_settings` WRITE;
/*!40000 ALTER TABLE `system_settings` DISABLE KEYS */;
INSERT INTO `system_settings` VALUES ('leaderboard_mode','auto','2026-10-09 01:31:41');
/*!40000 ALTER TABLE `system_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `game_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `seed` int(11) DEFAULT NULL,
  `pool` varchar(10) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `game_id` (`game_id`),
  CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teams_ibfk_2` FOREIGN KEY (`game_id`) REFERENCES `games` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=900 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
INSERT INTO `teams` VALUES (792,778,1,'VR Badminton',NULL,'B','2026-09-30 09:19:08'),(793,778,2,'VR Table Tennis',NULL,'B','2026-09-30 09:19:08'),(794,778,778,'VR Bridge',NULL,'B','2026-09-30 09:19:08'),(795,778,779,'VR Carrom',NULL,'A','2026-09-30 09:19:08'),(796,778,780,'VR Chess',NULL,NULL,'2026-09-30 09:19:08'),(797,778,781,'VR Swimming',NULL,'B','2026-09-30 09:19:08'),(798,778,782,'VR Tennis',NULL,'A','2026-09-30 09:19:08'),(799,778,783,'VR Badminton - Open Category',NULL,'B','2026-09-30 09:19:08'),(800,778,784,'VR Table Tennis - Open Category',NULL,'B','2026-09-30 09:19:08'),(801,779,1,'NCZ Badminton',NULL,'B','2026-09-30 09:19:30'),(802,779,2,'NCZ Table Tennis',NULL,'B','2026-09-30 09:19:30'),(803,779,778,'NCZ Bridge',NULL,'B','2026-09-30 09:19:30'),(804,779,779,'NCZ Carrom',NULL,'B','2026-09-30 09:19:30'),(805,779,780,'NCZ Chess',NULL,NULL,'2026-09-30 09:19:30'),(806,779,781,'NCZ Swimming',NULL,'B','2026-09-30 09:19:30'),(807,779,782,'NCZ Tennis',NULL,'D','2026-09-30 09:19:30'),(808,779,783,'NCZ Badminton - Open Category',NULL,'B','2026-09-30 09:19:30'),(809,779,784,'NCZ Table Tennis - Open Category',NULL,'B','2026-09-30 09:19:30'),(810,780,1,'MF Badminton',NULL,'B','2026-09-30 09:19:47'),(811,780,2,'MF Table Tennis',NULL,'A','2026-09-30 09:19:47'),(812,780,778,'MF Bridge',NULL,'A','2026-09-30 09:19:47'),(813,780,779,'MF Carrom',NULL,'D','2026-09-30 09:19:47'),(814,780,780,'MF Chess',NULL,NULL,'2026-09-30 09:19:47'),(815,780,781,'MF Swimming',NULL,'B','2026-09-30 09:19:47'),(816,780,782,'MF Tennis',NULL,'C','2026-09-30 09:19:47'),(817,780,783,'MF Badminton - Open Category',NULL,'B','2026-09-30 09:19:47'),(818,780,784,'MF Table Tennis - Open Category',NULL,'B','2026-09-30 09:19:47'),(819,781,1,'PH Badminton',NULL,'A','2026-09-30 09:20:02'),(820,781,2,'PH Table Tennis',NULL,'B','2026-09-30 09:20:02'),(821,781,778,'PH Bridge',NULL,'B','2026-09-30 09:20:02'),(822,781,779,'PH Carrom',NULL,'A','2026-09-30 09:20:02'),(823,781,780,'PH Chess',NULL,NULL,'2026-09-30 09:20:02'),(824,781,781,'PH Swimming',NULL,'B','2026-09-30 09:20:02'),(825,781,782,'PH Tennis',NULL,'D','2026-09-30 09:20:02'),(826,781,783,'PH Badminton - Open Category',NULL,'A','2026-09-30 09:20:02'),(827,781,784,'PH Table Tennis - Open Category',NULL,'A','2026-09-30 09:20:02'),(828,782,1,'SZ Badminton',NULL,'B','2026-09-30 09:20:19'),(829,782,2,'SZ Table Tennis',NULL,'A','2026-09-30 09:20:19'),(830,782,778,'SZ Bridge',NULL,NULL,'2026-09-30 09:20:19'),(831,782,779,'SZ Carrom',NULL,'C','2026-09-30 09:20:19'),(832,782,780,'SZ Chess',NULL,NULL,'2026-09-30 09:20:19'),(833,782,781,'SZ Swimming',NULL,'B','2026-09-30 09:20:19'),(834,782,782,'SZ Tennis',NULL,'A','2026-09-30 09:20:19'),(835,782,783,'SZ Badminton - Open Category',NULL,'B','2026-09-30 09:20:19'),(836,782,784,'SZ Table Tennis - Open Category',NULL,'B','2026-09-30 09:20:19'),(837,783,1,'WZ Badminton',NULL,'A','2026-09-30 09:20:41'),(838,783,2,'WZ Table Tennis',NULL,'A','2026-09-30 09:20:41'),(839,783,778,'WZ Bridge',NULL,'A','2026-09-30 09:20:41'),(840,783,779,'WZ Carrom',NULL,'C','2026-09-30 09:20:41'),(841,783,780,'WZ Chess',NULL,NULL,'2026-09-30 09:20:41'),(842,783,781,'WZ Swimming',NULL,'B','2026-09-30 09:20:41'),(843,783,782,'WZ Tennis',NULL,'B','2026-09-30 09:20:41'),(844,783,783,'WZ Badminton - Open Category',NULL,'B','2026-09-30 09:20:41'),(845,783,784,'WZ Table Tennis - Open Category',NULL,'B','2026-09-30 09:20:41'),(846,784,1,'NZ Badminton',NULL,'A','2026-09-30 09:20:56'),(847,784,2,'NZ Table Tennis',NULL,'A','2026-09-30 09:20:56'),(848,784,778,'NZ Bridge',NULL,'B','2026-09-30 09:20:56'),(849,784,779,'NZ Carrom',NULL,'B','2026-09-30 09:20:56'),(850,784,780,'NZ Chess',NULL,NULL,'2026-09-30 09:20:56'),(851,784,781,'NZ Swimming',NULL,'B','2026-09-30 09:20:56'),(852,784,782,'NZ Tennis',NULL,'B','2026-09-30 09:20:56'),(853,784,783,'NZ Badminton - Open Category',NULL,'A','2026-09-30 09:20:56'),(854,784,784,'NZ Table Tennis - Open Category',NULL,'A','2026-09-30 09:20:56'),(855,785,1,'MR Badminton',NULL,'B','2026-09-30 09:21:13'),(856,785,2,'MR Table Tennis',NULL,'A','2026-09-30 09:21:13'),(857,785,778,'MR Bridge',NULL,'A','2026-09-30 09:21:13'),(858,785,779,'MR Carrom',NULL,'B','2026-09-30 09:21:13'),(859,785,780,'MR Chess',NULL,NULL,'2026-09-30 09:21:13'),(860,785,781,'MR Swimming',NULL,'B','2026-09-30 09:21:13'),(861,785,782,'MR Tennis',NULL,'A','2026-09-30 09:21:13'),(862,785,783,'MR Badminton - Open Category',NULL,'B','2026-09-30 09:21:13'),(863,785,784,'MR Table Tennis - Open Category',NULL,'B','2026-09-30 09:21:13'),(864,786,1,'NWZ Badminton',NULL,'A','2026-09-30 09:21:33'),(865,786,2,'NWZ Table Tennis',NULL,'B','2026-09-30 09:21:33'),(866,786,778,'NWZ Bridge',NULL,'A','2026-09-30 09:21:33'),(867,786,779,'NWZ Carrom',NULL,'C','2026-09-30 09:21:33'),(868,786,780,'NWZ Chess',NULL,NULL,'2026-09-30 09:21:33'),(869,786,781,'NWZ Swimming',NULL,'B','2026-09-30 09:21:33'),(870,786,782,'NWZ Tennis',NULL,'B','2026-09-30 09:21:33'),(871,786,783,'NWZ Badminton - Open Category',NULL,'A','2026-09-30 09:21:33'),(872,786,784,'NWZ Table Tennis - Open Category',NULL,'A','2026-09-30 09:21:33'),(873,787,1,'SCZ Badminton',NULL,'A','2026-09-30 09:22:00'),(874,787,2,'SCZ Table Tennis',NULL,'A','2026-09-30 09:22:00'),(875,787,778,'SCZ Bridge',NULL,'A','2026-09-30 09:22:00'),(876,787,779,'SCZ Carrom',NULL,'D','2026-09-30 09:22:00'),(877,787,780,'SCZ Chess',NULL,NULL,'2026-09-30 09:22:00'),(878,787,781,'SCZ Swimming',NULL,'B','2026-09-30 09:22:00'),(879,787,782,'SCZ Tennis',NULL,'C','2026-09-30 09:22:00'),(880,787,783,'SCZ Badminton - Open Category',NULL,'A','2026-09-30 09:22:00'),(881,787,784,'SCZ Table Tennis - Open Category',NULL,'A','2026-09-30 09:22:00'),(882,788,1,'EZ Badminton',NULL,'B','2026-09-30 09:22:19'),(883,788,2,'EZ Table Tennis',NULL,'B','2026-09-30 09:22:19'),(884,788,778,'EZ Bridge',NULL,NULL,'2026-09-30 09:22:19'),(885,788,779,'EZ Carrom',NULL,'A','2026-09-30 09:22:19'),(886,788,780,'EZ Chess',NULL,NULL,'2026-09-30 09:22:19'),(887,788,781,'EZ Swimming',NULL,'B','2026-09-30 09:22:19'),(888,788,782,'EZ Tennis',NULL,'D','2026-09-30 09:22:19'),(889,788,783,'EZ Badminton - Open Category',NULL,'A','2026-09-30 09:22:19'),(890,788,784,'EZ Table Tennis - Open Category',NULL,'A','2026-09-30 09:22:19'),(891,789,1,'HB Badminton',NULL,'A','2026-09-30 09:22:36'),(892,789,2,'HB Table Tennis',NULL,'B','2026-09-30 09:22:36'),(893,789,778,'HB Bridge',NULL,'B','2026-09-30 09:22:36'),(894,789,779,'HB Carrom',NULL,'D','2026-09-30 09:22:36'),(895,789,780,'HB Chess',NULL,NULL,'2026-09-30 09:22:36'),(896,789,781,'HB Swimming',NULL,'B','2026-09-30 09:22:36'),(897,789,782,'HB Tennis',NULL,'C','2026-09-30 09:22:36'),(898,789,783,'HB Badminton - Open Category',NULL,'A','2026-09-30 09:22:36'),(899,789,784,'HB Table Tennis - Open Category',NULL,'A','2026-09-30 09:22:36');
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `short_code` varchar(10) NOT NULL,
  `color_code` varchar(20) DEFAULT '#0d6efd',
  `logo_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `manual_rank` int(11) DEFAULT NULL,
  `manual_points` int(11) NOT NULL DEFAULT 0,
  `manual_gold` int(11) NOT NULL DEFAULT 0,
  `manual_silver` int(11) NOT NULL DEFAULT 0,
  `manual_bronze` int(11) NOT NULL DEFAULT 0,
  `manual_notes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `short_code` (`short_code`)
) ENGINE=InnoDB AUTO_INCREMENT=790 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units`
--

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
INSERT INTO `units` VALUES (778,'Vizag refinery','VR','#0d6efd',NULL,'2026-09-30 09:19:08',NULL,0,0,0,0,NULL),(779,'North Central Zone','NCZ','#fd0d0d',NULL,'2026-09-30 09:19:30',NULL,0,0,0,0,NULL),(780,'Marathon','MF','#fd0ddd',NULL,'2026-09-30 09:19:47',NULL,0,0,0,0,NULL),(781,'Petroleum House','PH','#910dfd',NULL,'2026-09-30 09:20:02',NULL,0,0,0,0,NULL),(782,'South Zone','SZ','#610dfd',NULL,'2026-09-30 09:20:19',NULL,0,0,0,0,NULL),(783,'West Zone','WZ','#0db5fd',NULL,'2026-09-30 09:20:41',NULL,0,0,0,0,NULL),(784,'North Zone','NZ','#0df9fd',NULL,'2026-09-30 09:20:56',NULL,0,0,0,0,NULL),(785,'Mumbai Refinery','MR','#0dfdcd',NULL,'2026-09-30 09:21:13',NULL,0,0,0,0,NULL),(786,'North West Zone','NWZ','#0dfda1',NULL,'2026-09-30 09:21:33',NULL,0,0,0,0,NULL),(787,'South Central Zone','SCZ','#0dfd3d',NULL,'2026-09-30 09:22:00',NULL,0,0,0,0,NULL),(788,'East Zone','EZ','#d5fd0d',NULL,'2026-09-30 09:22:19',NULL,0,0,0,0,NULL),(789,'Hindustan Bhawan','HB','#fd8d0d',NULL,'2026-09-30 09:22:36',NULL,0,0,0,0,NULL);
/*!40000 ALTER TABLE `units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','nodal','volunteer','photographer') NOT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `unit_id` (`unit_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=798 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$0FY9HA3ApE/3VO9AdkAWj.o97kBfa1L8G6whfqHau7jyCZP5OH4gq','HPCL Admin','admin',NULL,'2026-09-24 21:09:26'),(2,'volunteer1','$2y$10$iCyZwtherSTzWhcvuXZQLOopdpsxEMn6xSUsZ/90XNdRjVIqVbY1S','Scoring Volunteer 1','volunteer',NULL,'2026-09-24 21:09:26'),(3,'nodal1','$2y$10$elCCBbqD1C.xyAESg5K7rOAw0s.9pM7mBXtB6Ch5OqMgC6XQRWXJK','Mumbai Refinery Team Manager','nodal',785,'2026-09-24 21:09:26'),(4,'photo1','$2y$10$VMxmrkwT8cgApge2za6ZOOF3LUA6EiGJUbNzTyeNG0tOUeCIOqn6W','Media Photographer 1','photographer',NULL,'2026-09-24 21:09:26'),(778,'nodal_mr','$2y$10$RWA6KglUcxJzgxfFnvJIP.bWBym1Epg5fS5DqTeZfFo6Pav2kp0Ta','MR Team Manager','nodal',785,'2026-09-28 11:24:50'),(779,'nodal_vr','$2y$10$EiJLoO2Mox.glYza/v5M4OcNzqo7aC42ghfN8WimlzatmkLXeEaKO','VR Team Manager','nodal',778,'2026-09-28 11:24:50'),(780,'vol_badminton1','$2y$10$igJJaYAnSNQXHrdqoVysr.dR5G/3AlM.9eNUyPnxuV.WeKW7Fassi','Badminton Official Scorer 1','volunteer',NULL,'2026-10-09 04:38:20'),(781,'vol_badminton2','$2y$10$4qLGuINGUXRcQfju70h/1ep.JwmYIF7cUFObEev0dkU4lr.OI0qGO','Badminton Official Scorer 2','volunteer',NULL,'2026-10-09 04:38:20'),(782,'vol_tabletennis1','$2y$10$AjmyYcEAA7FxMMIXxtLfbeOMbtgmzYaFfNnfXabg3pOVOpE/ky8N6','Table Tennis Official Scorer 1','volunteer',NULL,'2026-10-09 04:38:20'),(783,'vol_tabletennis2','$2y$10$QCS1B7d0imEzcpeBdgpNQ.JrvV5n4PS8xblKAO2anCCSjgnt2KtsC','Table Tennis Official Scorer 2','volunteer',NULL,'2026-10-09 04:38:20'),(784,'vol_bridge1','$2y$10$QKlzSXOPuwFJFMBKttfzw.i1LQ6xDA1fLE84e1oEsUzldu0N3O3M2','Bridge Official Scorer 1','volunteer',NULL,'2026-10-09 04:38:20'),(785,'vol_bridge2','$2y$10$64RzTdiXf/0O5CP4Dql.zeHKhVcyJgxD9JwfjI8LFc0AbLkxO.7wy','Bridge Official Scorer 2','volunteer',NULL,'2026-10-09 04:38:21'),(786,'vol_carrom1','$2y$10$mPVurUBoHzeGJAeLC8Nr8efS4VdL4wi40smBVSXR3kWQ.1nPgYQ/K','Carrom Official Scorer 1','volunteer',NULL,'2026-10-09 04:38:21'),(787,'vol_carrom2','$2y$10$57Usdf2MpCXiuKH.lQ1bnuS5/wJa6yo/vIulI0s0i2uoYQr6IGEPu','Carrom Official Scorer 2','volunteer',NULL,'2026-10-09 04:38:21'),(788,'vol_chess1','$2y$10$q7ToMHRfykS0SCTxKl8kiuFHFKgbmIfrGVXeQp.hWMxSyv.xyCz6.','Chess Official Scorer 1','volunteer',NULL,'2026-10-09 04:38:21'),(789,'vol_chess2','$2y$10$pSOHLhgHHlprADFT1sWAJ.YEnwah.azICqmyi8eqlEoiQvdVsddnO','Chess Official Scorer 2','volunteer',NULL,'2026-10-09 04:38:21'),(790,'vol_swimming1','$2y$10$kK00OfIUMfoJL5Az1xsjM.e2O3LJvF6ecyPv8auECplcygPKTvdoy','Swimming Official Scorer 1','volunteer',NULL,'2026-10-09 04:38:21'),(791,'vol_swimming2','$2y$10$8OZjOLCJxGZsIfxsBDxIx.S7Yu1/9aVFf/ku55790ol.NvCezswfO','Swimming Official Scorer 2','volunteer',NULL,'2026-10-09 04:38:21'),(792,'vol_tennis1','$2y$10$ElVukRExmac9NniCIsizjOc35GsjS0HQyvxgsZCq9RVdgBosn0rOm','Tennis Official Scorer 1','volunteer',NULL,'2026-10-09 04:38:21'),(793,'vol_tennis2','$2y$10$5jqzlZhSkTkdRB5sVZemO.C2omYTstyj6EVUm1XEqfSzQL/B0YvLG','Tennis Official Scorer 2','volunteer',NULL,'2026-10-09 04:38:21'),(794,'vol_badminton_open1','$2y$10$/AzoPgNkHOTeAK/iH4kKT.UIRqeeX9XA/HIc6ZF4u5WxYqMCF90aO','Badminton Open Scorer 1','volunteer',NULL,'2026-10-09 04:38:21'),(795,'vol_badminton_open2','$2y$10$Sxn0S.hRp81J0rrhUsEFMuZj2jYLN6s8czJdozgulJ1QJT0EDVmNu','Badminton Open Scorer 2','volunteer',NULL,'2026-10-09 04:38:21'),(796,'vol_tt_open1','$2y$10$XFTjr0RviHBEq1C/3jYB1u1xExBi.Sr.Vhg2zwbc9l6vr5901CXZW','Table Tennis Open Scorer 1','volunteer',NULL,'2026-10-09 04:38:21'),(797,'vol_tt_open2','$2y$10$S8/8gLJqdbqD.321fYJEhuSUe58ytbUVJz/0E.NqWDSL72iiuV5W6','Table Tennis Open Scorer 2','volunteer',NULL,'2026-10-09 04:38:21');
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

-- Dump completed on 2026-10-09 10:09:37
