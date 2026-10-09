-- MariaDB dump 10.19  Distrib 10.4.25-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: sportsmis_empty_teams
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
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
INSERT INTO `games` VALUES (1,'Badminton','badminton','Team Event','pools_knockout','fas fa-feather-alt',NULL,'2026-09-24 21:09:31',4,5,2,3,8),(2,'Table Tennis','table-tennis','Team Event','seeded_knockout','fas fa-table-tennis',NULL,'2026-09-24 21:09:31',4,5,2,3,8),(778,'Bridge','bridge','Team Event','swiss_league','fas fa-clone',NULL,'2026-09-28 11:35:34',4,4,0,0,4),(779,'Carrom','carrom','Team Event','pools_knockout','fas fa-bullseye',NULL,'2026-09-28 11:35:34',4,4,0,4,8),(780,'Chess','chess','Team Event','swiss_league','fas fa-chess',NULL,'2026-09-28 11:35:34',4,4,0,0,4),(781,'Swimming','swimming','Team Event','timed_heats','fas fa-swimmer',NULL,'2026-09-28 11:35:34',2,3,2,3,6),(782,'Tennis','tennis','Team Event','seeded_knockout','fas fa-baseball-ball',NULL,'2026-09-28 11:35:34',2,3,1,2,5),(783,'Badminton - Open Category','badminton-open','Open Category','knockout','fas fa-feather-alt',NULL,'2026-09-28 11:35:34',0,1,0,1,2),(784,'Table Tennis - Open Category','table-tennis-open','Open Category','knockout','fas fa-table-tennis',NULL,'2026-09-28 11:35:34',0,1,0,1,2);
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
) ENGINE=InnoDB AUTO_INCREMENT=778 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_events`
--

LOCK TABLES `master_events` WRITE;
/*!40000 ALTER TABLE `master_events` DISABLE KEYS */;
INSERT INTO `master_events` VALUES (1,'Team Managers Meeting (TMM)','meeting','2026-03-01','09:00:00','10:00:00','Conference Hall A','Fixture draw confirmation & briefing');
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
  `round` varchar(50) NOT NULL,
  `pool_name` varchar(10) DEFAULT NULL,
  `team1_id` int(11) DEFAULT NULL,
  `team2_id` int(11) DEFAULT NULL,
  `winner_id` int(11) DEFAULT NULL,
  `facility_id` int(11) DEFAULT NULL,
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `matches`
--

LOCK TABLES `matches` WRITE;
/*!40000 ALTER TABLE `matches` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `players`
--

LOCK TABLES `players` WRITE;
/*!40000 ALTER TABLE `players` DISABLE KEYS */;
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
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
  PRIMARY KEY (`id`),
  UNIQUE KEY `short_code` (`short_code`)
) ENGINE=InnoDB AUTO_INCREMENT=790 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units`
--

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
INSERT INTO `units` VALUES (778,'Vizag refinery','VR','#0d6efd',NULL,'2026-09-30 09:19:08'),(779,'North Central Zone','NC','#fd0d0d',NULL,'2026-09-30 09:19:30'),(780,'Marathon-Priyadarshini','MP','#fd0ddd',NULL,'2026-09-30 09:19:47'),(781,'Petroleum House','PH','#910dfd',NULL,'2026-09-30 09:20:02'),(782,'South Zone','SZ','#610dfd',NULL,'2026-09-30 09:20:19'),(783,'West Zone','WZ','#0db5fd',NULL,'2026-09-30 09:20:41'),(784,'North Zone','NZ','#0df9fd',NULL,'2026-09-30 09:20:56'),(785,'Mumbai Refinery','MR','#0dfdcd',NULL,'2026-09-30 09:21:13'),(786,'North West Zone','NW','#0dfda1',NULL,'2026-09-30 09:21:33'),(787,'South Central Zone','SC','#0dfd3d',NULL,'2026-09-30 09:22:00'),(788,'East Zone','EZ','#d5fd0d',NULL,'2026-09-30 09:22:19'),(789,'Hindustan Bhawan','HB','#fd8d0d',NULL,'2026-09-30 09:22:36');
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
) ENGINE=InnoDB AUTO_INCREMENT=780 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$0FY9HA3ApE/3VO9AdkAWj.o97kBfa1L8G6whfqHau7jyCZP5OH4gq','HPCL Admin','admin',NULL,'2026-09-24 21:09:26'),(2,'volunteer1','$2y$10$iCyZwtherSTzWhcvuXZQLOopdpsxEMn6xSUsZ/90XNdRjVIqVbY1S','Scoring Volunteer 1','volunteer',NULL,'2026-09-24 21:09:26'),(3,'nodal1','$2y$10$elCCBbqD1C.xyAESg5K7rOAw0s.9pM7mBXtB6Ch5OqMgC6XQRWXJK','Mumbai Refinery Team Manager','nodal',785,'2026-09-24 21:09:26'),(4,'photo1','$2y$10$VMxmrkwT8cgApge2za6ZOOF3LUA6EiGJUbNzTyeNG0tOUeCIOqn6W','Media Photographer 1','photographer',NULL,'2026-09-24 21:09:26'),(778,'nodal_mr','$2y$10$RWA6KglUcxJzgxfFnvJIP.bWBym1Epg5fS5DqTeZfFo6Pav2kp0Ta','MR Team Manager','nodal',785,'2026-09-28 11:24:50'),(779,'nodal_vr','$2y$10$EiJLoO2Mox.glYza/v5M4OcNzqo7aC42ghfN8WimlzatmkLXeEaKO','VR Team Manager','nodal',778,'2026-09-28 11:24:50');
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

-- Dump completed on 2026-10-08 16:47:07
