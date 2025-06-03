-- MySQL dump 10.13  Distrib 8.0.42, for Linux (x86_64)
--
-- Host: localhost    Database: taravel_docker
-- ------------------------------------------------------
-- Server version	8.0.42-0ubuntu0.22.04.1

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `accounts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `account_num` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_name` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `initial_balance` decimal(10,2) NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts`
--

LOCK TABLES `accounts` WRITE;
/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
INSERT INTO `accounts` VALUES (1,'1243242','Onchain',10100.00,'Citybank','2025-03-25 11:58:37.000000','2025-03-25 13:44:26.000000',NULL);
/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `announcements`
--

DROP TABLE IF EXISTS `announcements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `announcements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `company_id` int NOT NULL,
  `department_id` int DEFAULT NULL,
  `summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `announcements_company_id` (`company_id`),
  KEY `announcements_department_id` (`department_id`),
  CONSTRAINT `announcements_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `announcements_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `announcements`
--

LOCK TABLES `announcements` WRITE;
/*!40000 ALTER TABLE `announcements` DISABLE KEYS */;
INSERT INTO `announcements` VALUES (1,'Announcement testing','2025-03-25','2025-03-31',2,2,'Brief Summary of Announcement','Everyone should be maintaining this description','2025-03-25 11:18:20.000000','2025-03-25 11:18:20.000000',NULL),(2,'Test','2025-04-27','2025-04-29',2,3,'test','Test','2025-04-27 14:41:06.000000','2025-04-27 14:41:06.000000',NULL),(3,'cdghdgd','2025-05-20','2025-05-21',3,6,'rdcghdhgd','bgncbgnhc m','2025-05-20 11:03:03.000000','2025-05-20 11:03:03.000000',NULL);
/*!40000 ALTER TABLE `announcements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `arrangement_types`
--

DROP TABLE IF EXISTS `arrangement_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `arrangement_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `arrangement_types`
--

LOCK TABLES `arrangement_types` WRITE;
/*!40000 ALTER TABLE `arrangement_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `arrangement_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendances`
--

DROP TABLE IF EXISTS `attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendances` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `mode` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` date NOT NULL,
  `clock_in` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `clock_in_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `clock_out` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `clock_out_ip` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `clock_in_out` tinyint(1) NOT NULL,
  `depart_early` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '00:00',
  `late_time` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '00:00',
  `overtime` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '00:00',
  `total_work` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '00:00',
  `total_rest` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '00:00',
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'present',
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendances_company_id` (`company_id`),
  KEY `attendances_employee_id` (`employee_id`),
  CONSTRAINT `attendances_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `attendances_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendances`
--

LOCK TABLES `attendances` WRITE;
/*!40000 ALTER TABLE `attendances` DISABLE KEYS */;
INSERT INTO `attendances` VALUES (1,1,3,NULL,'2025-03-25','16:48','::1','','',1,'00:00','04:19','00:00','00:00','00:00','present','2025-03-25 10:48:42.000000','2025-03-25 10:48:42.000000',NULL),(2,1,3,NULL,'2025-04-27','12:36','103.156.167.11','','',1,'00:00','00:00','00:00','00:00','00:00','present','2025-04-27 12:36:57.000000','2025-04-27 12:36:57.000000',NULL),(3,1,3,NULL,'2025-05-03','16:28','103.73.225.0','16:29','',0,'02:01','03:58','00:00','00:01','00:00','present','2025-05-03 16:28:04.000000','2025-05-03 16:29:12.000000',NULL),(4,1,3,NULL,'2025-05-03','16:30','103.73.225.0','16:33','',0,'01:57','00:00','00:00','00:03','00:01','present','2025-05-03 16:30:18.000000','2025-05-03 16:33:42.000000',NULL),(5,1,3,NULL,'2025-05-03','16:34','103.73.225.0','16:41','',0,'01:49','00:00','00:00','00:07','00:01','present','2025-05-03 16:34:30.000000','2025-05-03 16:41:09.000000',NULL),(6,1,3,NULL,'2025-05-03','16:41','103.73.225.0','16:41','',0,'01:49','00:00','00:00','00:00','00:00','present','2025-05-03 16:41:22.000000','2025-05-03 16:41:35.000000',NULL),(7,1,3,NULL,'2025-05-03','16:42','103.73.225.0','16:46','',0,'01:44','00:00','00:00','00:04','00:01','present','2025-05-03 16:42:01.000000','2025-05-03 16:46:00.000000',NULL),(8,1,3,NULL,'2025-05-03','16:47','103.73.225.0','16:49','',0,'01:41','00:00','00:00','00:02','00:01','present','2025-05-03 16:47:04.000000','2025-05-03 16:49:03.000000',NULL),(9,1,3,NULL,'2025-05-03','16:49','103.73.225.0','16:50','',0,'01:40','00:00','00:00','00:01','00:00','present','2025-05-03 16:49:26.000000','2025-05-03 16:50:48.000000',NULL),(10,1,3,NULL,'2025-05-03','16:54','103.73.225.0','16:56','',0,'01:34','00:00','00:00','00:02','00:04','present','2025-05-03 16:54:11.000000','2025-05-03 16:56:50.000000',NULL),(11,1,3,NULL,'2025-05-03','16:58','103.73.225.0','17:01','',0,'01:29','00:00','00:00','00:03','00:02','present','2025-05-03 16:58:21.000000','2025-05-03 17:01:24.000000',NULL),(12,1,3,NULL,'2025-05-03','17:02','103.73.225.0','17:09','',0,'01:21','00:00','00:00','00:07','00:01','present','2025-05-03 17:02:29.000000','2025-05-03 17:09:21.000000',NULL),(13,1,3,NULL,'2025-05-03','17:11','103.73.225.0','17:20','',0,'01:10','00:00','00:00','00:09','00:02','present','2025-05-03 17:11:24.000000','2025-05-03 17:20:24.000000',NULL),(14,1,3,NULL,'2025-05-03','17:20','103.73.225.0','17:22','',0,'01:08','00:00','00:00','00:02','00:00','present','2025-05-03 17:20:56.000000','2025-05-03 17:22:04.000000',NULL),(15,1,3,NULL,'2025-05-03','17:22','103.73.225.0','17:26','',0,'01:04','00:00','00:00','00:04','00:00','present','2025-05-03 17:22:52.000000','2025-05-03 17:26:26.000000',NULL),(16,1,3,NULL,'2025-05-03','17:28','103.73.225.0','17:29','',0,'01:01','00:00','00:00','00:01','00:02','present','2025-05-03 17:28:21.000000','2025-05-03 17:29:02.000000',NULL),(17,1,3,NULL,'2025-05-03','17:30','103.73.225.0','17:34','',0,'00:56','00:00','00:00','00:04','00:01','present','2025-05-03 17:30:56.000000','2025-05-03 17:34:48.000000',NULL),(18,1,3,NULL,'2025-05-03','17:36','103.73.225.0','17:36','',0,'00:54','00:00','00:00','00:00','00:02','present','2025-05-03 17:36:08.000000','2025-05-03 17:36:38.000000',NULL),(19,1,3,NULL,'2025-05-03','17:36','103.73.225.0','17:37','',0,'00:53','00:00','00:00','00:01','00:00','present','2025-05-03 17:36:49.000000','2025-05-03 17:37:04.000000',NULL),(20,1,3,NULL,'2025-05-03','17:37','103.73.225.0','17:39','',0,'00:51','00:00','00:00','00:02','00:00','present','2025-05-03 17:37:11.000000','2025-05-03 17:39:52.000000',NULL),(21,1,3,NULL,'2025-05-03','17:40','103.73.225.0','17:40','',0,'00:50','00:00','00:00','00:00','00:01','present','2025-05-03 17:40:02.000000','2025-05-03 17:40:15.000000',NULL),(22,1,3,NULL,'2025-05-03','17:40','103.73.225.0','17:42','',0,'00:48','00:00','00:00','00:02','00:00','present','2025-05-03 17:40:20.000000','2025-05-03 17:42:27.000000',NULL),(23,1,3,NULL,'2025-05-03','17:42','103.73.225.0','17:42','',0,'00:48','00:00','00:00','00:00','00:00','present','2025-05-03 17:42:31.000000','2025-05-03 17:42:46.000000',NULL),(24,1,3,NULL,'2025-05-03','17:42','103.73.225.0','17:45','',0,'00:45','00:00','00:00','00:03','00:00','present','2025-05-03 17:42:52.000000','2025-05-03 17:45:32.000000',NULL),(25,1,3,NULL,'2025-05-03','17:45','103.73.225.0','17:46','',0,'00:44','00:00','00:00','00:01','00:00','present','2025-05-03 17:45:46.000000','2025-05-03 17:46:00.000000',NULL),(26,1,3,NULL,'2025-05-03','17:46','103.73.225.0','17:46','',0,'00:44','00:00','00:00','00:00','00:00','present','2025-05-03 17:46:06.000000','2025-05-03 17:46:10.000000',NULL),(27,1,3,NULL,'2025-05-03','17:57','103.73.225.0','17:57','',0,'00:33','00:00','00:00','00:00','00:11','present','2025-05-03 17:57:20.000000','2025-05-03 17:57:32.000000',NULL),(28,1,3,NULL,'2025-05-03','18:40','103.73.225.0','18:43','',0,'00:00','00:00','00:03','00:03','00:43','present','2025-05-03 18:40:13.000000','2025-05-03 18:43:33.000000',NULL),(29,3,8,NULL,'2025-05-03','20:11','103.73.225.0','20:11','',0,'00:00','09:41','00:00','00:00','00:00','present','2025-05-03 20:11:17.000000','2025-05-03 20:11:49.000000',NULL),(30,3,8,NULL,'2025-05-03','20:12','103.73.225.0','20:19','',0,'00:00','00:00','00:07','00:07','00:01','present','2025-05-03 20:12:02.000000','2025-05-03 20:19:48.000000',NULL),(31,3,8,NULL,'2025-05-03','20:20','103.73.225.0','21:00','',0,'00:00','00:00','00:40','00:40','00:01','present','2025-05-03 20:20:38.000000','2025-05-03 21:00:42.000000',NULL),(32,3,9,NULL,'2025-05-04','07:37','103.73.225.0','07:38','',0,'00:00','00:00','00:00','00:01','00:00','present','2025-05-04 07:37:29.000000','2025-05-04 07:38:10.000000',NULL),(33,3,9,NULL,'2025-05-04','08:00','103.73.225.0','08:00','',0,'10:30','00:00','00:00','00:00','00:22','present','2025-05-04 08:00:13.000000','2025-05-04 08:00:56.000000',NULL),(34,3,9,NULL,'2025-05-04','08:02','103.73.225.0','08:03','',0,'00:00','00:00','00:00','00:01','00:02','present','2025-05-04 08:02:57.000000','2025-05-04 08:03:00.000000',NULL),(35,3,9,NULL,'2025-05-04','08:59','103.73.225.0','20:30','',0,'00:00','00:00','00:00','11:31','00:56','present','2025-05-04 08:59:59.000000','2025-05-05 08:53:29.000000',NULL),(36,3,8,NULL,'2025-05-04','12:08','103.23.255.99','16:40','',0,'01:50','01:38','00:00','04:32','00:00','present','2025-05-04 12:08:47.000000','2025-05-04 16:40:51.000000',NULL),(37,3,10,NULL,'2025-05-04','12:49','103.23.255.99','16:40','',0,'01:50','02:19','00:00','03:51','00:00','present','2025-05-04 12:49:32.000000','2025-05-04 16:40:30.000000',NULL),(38,3,13,NULL,'2025-05-04','15:11','180.148.210.41','15:13','',0,'03:17','04:41','00:00','00:02','00:00','present','2025-05-04 15:11:25.000000','2025-05-04 15:13:14.000000',NULL),(39,3,13,NULL,'2025-05-04','15:17','180.148.210.41','15:23','',0,'03:07','00:00','00:00','00:06','00:04','present','2025-05-04 15:17:51.000000','2025-05-04 15:23:26.000000',NULL),(40,3,13,NULL,'2025-05-04','15:36','180.148.210.41','16:39','',0,'01:51','00:00','00:00','01:03','00:13','present','2025-05-04 15:36:31.000000','2025-05-04 16:39:37.000000',NULL),(41,3,11,NULL,'2025-05-04','15:56','103.140.83.67','16:39','',0,'01:51','05:26','00:00','00:43','00:00','present','2025-05-04 15:56:27.000000','2025-05-04 16:39:37.000000',NULL),(42,3,8,NULL,'2025-05-04','16:40','103.23.255.99','20:09','',0,'00:00','00:00','01:39','03:29','00:00','present','2025-05-04 16:40:55.000000','2025-05-04 20:09:52.000000',NULL),(43,3,12,NULL,'2025-05-04','16:45','103.159.186.88','20:34','',0,'00:00','06:15','02:04','03:49','00:00','present','2025-05-04 16:45:57.000000','2025-05-04 20:34:26.000000',NULL),(44,3,14,NULL,'2025-05-04','16:46','103.23.255.99','20:31','',0,'00:00','00:00','00:00','03:45','00:00','present','2025-05-04 16:46:23.000000','2025-05-05 10:40:31.000000',NULL),(45,3,13,NULL,'2025-05-04','16:54','180.148.210.41','17:04','',0,'01:26','00:00','00:00','00:10','00:15','present','2025-05-04 16:54:55.000000','2025-05-04 17:04:13.000000',NULL),(46,3,11,NULL,'2025-05-04','16:54','103.140.83.67','18:12','',0,'00:18','00:00','00:00','01:18','00:15','present','2025-05-04 16:54:58.000000','2025-05-04 18:12:32.000000',NULL),(47,3,13,NULL,'2025-05-04','17:20','180.148.210.41','18:25','',0,'00:05','00:00','00:00','01:05','00:16','present','2025-05-04 17:20:54.000000','2025-05-04 18:25:47.000000',NULL),(48,3,9,NULL,'2025-05-05','06:57','103.73.225.0','08:13','',0,'00:00','00:00','00:00','01:16','00:00','present','2025-05-05 06:57:16.000000','2025-05-05 08:13:36.000000',NULL),(49,3,9,NULL,'2025-05-05','08:13','103.73.225.0','08:50','',0,'00:00','00:00','00:00','00:37','00:00','present','2025-05-05 08:13:48.000000','2025-05-05 08:53:06.000000',NULL),(50,1,3,NULL,'2025-05-05','08:23','103.73.225.0','','',1,'00:00','08:18','00:00','00:00','00:00','present','2025-05-05 08:23:49.000000','2025-05-05 08:43:07.000000','2025-05-05 08:43:07'),(51,1,3,NULL,'2025-05-05','08:43','103.73.225.0','','',1,'00:00','08:38','00:00','00:00','00:00','present','2025-05-05 08:43:34.000000','2025-05-05 08:50:30.000000','2025-05-05 08:50:30'),(52,1,3,NULL,'2025-05-05','08:51','103.73.225.0','08:51','',0,'00:00','08:46','00:00','00:00','00:00','present','2025-05-05 08:51:21.000000','2025-05-05 08:52:18.000000','2025-05-05 08:52:18'),(53,3,9,NULL,'2025-05-05','09:28','103.73.225.0','10:20','',0,'00:00','00:00','00:00','00:52','00:38','present','2025-05-05 09:28:27.000000','2025-05-05 10:36:15.000000',NULL),(54,3,13,NULL,'2025-05-05','10:30','180.148.210.41','09:58','',0,'08:32','00:00','00:00','00:32','00:00','present','2025-05-05 09:35:37.000000','2025-05-05 09:58:04.000000',NULL),(55,3,11,NULL,'2025-05-05','10:30','103.140.83.67','12:50','',0,'05:40','00:00','00:00','02:20','00:00','present','2025-05-05 09:54:49.000000','2025-05-05 12:50:54.000000',NULL),(56,3,13,NULL,'2025-05-05','10:00','180.148.210.41','10:54','',0,'07:36','00:00','00:00','00:54','00:02','present','2025-05-05 10:00:02.000000','2025-05-05 10:54:25.000000',NULL),(57,3,9,NULL,'2025-05-05','10:41','103.23.255.99','13:02','',0,'00:00','00:00','00:00','02:21','00:21','present','2025-05-05 10:41:03.000000','2025-05-05 13:02:02.000000',NULL),(58,3,13,NULL,'2025-05-05','10:54','180.148.210.41','11:54','',0,'06:36','00:00','00:00','01:00','00:00','present','2025-05-05 10:54:29.000000','2025-05-05 11:54:33.000000',NULL),(59,3,12,NULL,'2025-05-05','11:37','103.23.255.99','','',1,'00:00','01:07','00:00','00:00','00:00','present','2025-05-05 11:37:26.000000','2025-05-05 11:37:26.000000',NULL),(60,3,8,NULL,'2025-05-05','12:18','103.23.255.99','20:54','',0,'00:00','01:48','02:24','08:36','00:00','present','2025-05-05 12:18:37.000000','2025-05-05 20:54:31.000000',NULL),(61,3,10,NULL,'2025-05-05','12:38','103.23.255.99','20:56','',0,'00:00','02:08','02:26','08:18','00:00','present','2025-05-05 12:38:21.000000','2025-05-05 20:56:31.000000',NULL),(62,3,14,NULL,'2025-05-05','12:52','103.23.255.99','','',1,'00:00','02:22','00:00','00:00','00:00','present','2025-05-05 12:52:14.000000','2025-05-05 12:52:14.000000',NULL),(63,3,13,NULL,'2025-05-05','12:57','180.148.210.41','16:34','',0,'01:56','00:00','00:00','03:37','01:03','present','2025-05-05 12:57:12.000000','2025-05-05 16:34:35.000000',NULL),(64,3,11,NULL,'2025-05-05','13:35','103.140.83.67','18:16','',0,'00:14','00:00','00:00','04:41','00:45','present','2025-05-05 13:35:04.000000','2025-05-05 18:16:22.000000',NULL),(65,3,13,NULL,'2025-05-05','16:57','180.148.210.41','19:04','',0,'00:00','00:00','00:34','02:07','00:23','present','2025-05-05 16:57:05.000000','2025-05-05 19:04:11.000000',NULL),(66,3,9,'office','2025-05-20','07:13','103.23.255.99','07:37','',0,'00:00','00:00','00:00','00:24','00:00','present','2025-05-20 07:13:08.000000','2025-05-20 07:37:26.000000',NULL),(67,3,9,'remote','2025-05-20','07:38','103.23.255.99','07:38','',0,'00:00','00:00','00:00','00:00','00:01','present','2025-05-20 07:38:08.000000','2025-05-20 07:38:13.000000',NULL),(68,3,9,'office','2025-05-20','07:38','103.23.255.99','','',1,'00:00','00:00','00:00','00:00','00:00','present','2025-05-20 07:38:20.000000','2025-05-20 07:38:20.000000',NULL),(69,3,13,'remote','2025-05-20','10:30','180.148.210.41','10:00','',0,'08:30','00:00','00:00','00:30','00:00','present','2025-05-20 09:59:49.000000','2025-05-20 10:00:08.000000',NULL),(70,3,13,'remote','2025-05-20','10:00','180.148.210.41','','',1,'00:00','00:00','00:00','00:00','00:00','present','2025-05-20 10:00:21.000000','2025-05-20 10:00:21.000000',NULL),(71,3,11,'remote','2025-05-20','10:30','202.126.123.30','10:05','',0,'08:25','00:00','00:00','00:25','00:00','present','2025-05-20 10:04:50.000000','2025-05-20 10:05:57.000000',NULL),(72,3,11,'remote','2025-05-20','10:06','202.126.123.30','','',1,'00:00','00:00','00:00','00:00','00:01','present','2025-05-20 10:06:05.000000','2025-05-20 10:06:05.000000',NULL),(73,3,8,'remote','2025-05-20','10:30','103.195.0.233','11:01','',0,'07:29','00:00','00:00','00:31','00:00','present','2025-05-20 10:17:28.000000','2025-05-20 11:01:14.000000',NULL),(74,3,10,'office','2025-05-20','11:36','37.111.206.13','','',1,'00:00','01:06','00:00','00:00','00:00','present','2025-05-20 11:36:59.000000','2025-05-20 11:36:59.000000',NULL);
/*!40000 ALTER TABLE `attendances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `award_types`
--

DROP TABLE IF EXISTS `award_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `award_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `award_types`
--

LOCK TABLES `award_types` WRITE;
/*!40000 ALTER TABLE `award_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `award_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `awards`
--

DROP TABLE IF EXISTS `awards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `awards` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `department_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `award_type_id` int NOT NULL,
  `date` date NOT NULL,
  `gift` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cash` decimal(65,2) NOT NULL,
  `photo` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `awards_company_id` (`company_id`),
  KEY `awards_department_id` (`department_id`),
  KEY `awards_employee_id` (`employee_id`),
  KEY `award_award_type_id` (`award_type_id`),
  CONSTRAINT `award_award_type_id` FOREIGN KEY (`award_type_id`) REFERENCES `award_types` (`id`),
  CONSTRAINT `awards_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `awards_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `awards_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `awards`
--

LOCK TABLES `awards` WRITE;
/*!40000 ALTER TABLE `awards` DISABLE KEYS */;
/*!40000 ALTER TABLE `awards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bonus_allowances`
--

DROP TABLE IF EXISTS `bonus_allowances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bonus_allowances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `type` enum('fixed','percentage') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bonus_allowances_employee_id_index` (`employee_id`),
  CONSTRAINT `bonus_allowances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bonus_allowances`
--

LOCK TABLES `bonus_allowances` WRITE;
/*!40000 ALTER TABLE `bonus_allowances` DISABLE KEYS */;
INSERT INTO `bonus_allowances` VALUES (1,3,12.00,'percentage','asfasf','2025-05-20 07:32:22','2025-05-20 07:32:22'),(2,6,12.00,'percentage','asfasf','2025-05-20 07:32:22','2025-05-20 07:32:22'),(3,8,12.00,'percentage','asfasf','2025-05-20 07:32:22','2025-05-20 07:32:22'),(4,9,12.00,'percentage','asfasf','2025-05-20 07:32:22','2025-05-20 07:32:22'),(5,10,12.00,'percentage','asfasf','2025-05-20 07:32:22','2025-05-20 07:32:22'),(6,11,12.00,'percentage','asfasf','2025-05-20 07:32:22','2025-05-20 07:32:22'),(7,12,12.00,'percentage','asfasf','2025-05-20 07:32:22','2025-05-20 07:32:22'),(8,13,12.00,'percentage','asfasf','2025-05-20 07:32:22','2025-05-20 07:32:22'),(9,14,12.00,'percentage','asfasf','2025-05-20 07:32:22','2025-05-20 07:32:22');
/*!40000 ALTER TABLE `bonus_allowances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clients`
--

DROP TABLE IF EXISTS `clients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `clients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `firstname` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_users_id` bigint unsigned NOT NULL,
  `code` int NOT NULL,
  `email` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `clients_role_users_id` (`role_users_id`),
  CONSTRAINT `clients_role_users_id` FOREIGN KEY (`role_users_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clients`
--

LOCK TABLES `clients` WRITE;
/*!40000 ALTER TABLE `clients` DISABLE KEYS */;
INSERT INTO `clients` VALUES (4,'MD. jesus','Hoque','MD. jesus Hoque',3,1,'melosryz1@gmail.com','Bangladesh','Dhaka','01708020605','Khilkhet','2025-03-25 10:52:13.000000','2025-05-05 20:01:39.000000','2025-05-05 20:01:39'),(15,'Onchain Software','& Research limited','Onchain Software & Research limited',3,2,'onchainsoftwareresearch@gmail.com','Bangaldesh','Dhaka','','','2025-05-04 23:51:51.000000','2025-05-04 23:51:51.000000',NULL),(16,'Banglachain','Foundation','Banglachain Foundation',3,3,'Banglachain@banglachain.com','','','','','2025-05-04 23:52:39.000000','2025-05-04 23:52:39.000000',NULL);
/*!40000 ALTER TABLE `clients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES (1,'Melodic','melosryz@gmail.com','01708020605','Bangladesh','2025-03-25 10:40:15.000000','2025-03-25 10:40:15.000000',NULL),(2,'Uday Inc','uday@gmail.com','01717326286','USA','2025-03-25 11:14:49.000000','2025-05-03 19:57:42.000000','2025-05-03 19:57:42'),(3,'Onchain Software & Research Limited','onchainsoftwareresearch@gmail.com','09643112277','Bangaldesh','2025-05-03 19:58:48.000000','2025-05-03 19:58:48.000000',NULL);
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `complaints`
--

DROP TABLE IF EXISTS `complaints`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `complaints` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `employee_from` int NOT NULL,
  `employee_against` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `time` time DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `complaint_company_id` (`company_id`),
  KEY `complaint_employee_from` (`employee_from`),
  KEY `complaint_employee_against` (`employee_against`),
  CONSTRAINT `complaint_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `complaint_employee_against` FOREIGN KEY (`employee_against`) REFERENCES `employees` (`id`),
  CONSTRAINT `complaint_employee_from` FOREIGN KEY (`employee_from`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `complaints`
--

LOCK TABLES `complaints` WRITE;
/*!40000 ALTER TABLE `complaints` DISABLE KEYS */;
/*!40000 ALTER TABLE `complaints` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `currencies`
--

DROP TABLE IF EXISTS `currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `currencies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currencies`
--

LOCK TABLES `currencies` WRITE;
/*!40000 ALTER TABLE `currencies` DISABLE KEYS */;
INSERT INTO `currencies` VALUES (1,'USD','US Dollar','$',NULL,'2025-03-25 12:00:14.000000','2025-03-25 12:00:14'),(2,'Taka','Taka','BDT','2025-03-25 12:00:03.000000','2025-03-25 12:00:03.000000',NULL);
/*!40000 ALTER TABLE `currencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `department` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int NOT NULL,
  `department_head` int DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `department_company_id` (`company_id`),
  KEY `department_department_head` (`department_head`),
  CONSTRAINT `department_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `department_department_head` FOREIGN KEY (`department_head`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
INSERT INTO `departments` VALUES (1,'Programming',1,NULL,'2025-03-25 10:43:11.000000','2025-03-25 10:43:11.000000',NULL),(2,'Product',2,NULL,'2025-03-25 11:15:12.000000','2025-03-25 11:15:12.000000',NULL),(3,'Marketing',2,NULL,'2025-03-25 11:15:22.000000','2025-03-25 11:15:22.000000',NULL),(4,'Product',3,NULL,'2025-05-03 20:07:35.000000','2025-05-03 20:07:35.000000',NULL),(5,'Operations',3,NULL,'2025-05-03 20:07:46.000000','2025-05-03 20:07:46.000000',NULL),(6,'Software',3,NULL,'2025-05-03 20:08:11.000000','2025-05-03 20:08:11.000000',NULL),(7,'Research',3,NULL,'2025-05-03 20:08:21.000000','2025-05-03 20:08:21.000000',NULL),(8,'Management',3,NULL,'2025-05-03 20:26:01.000000','2025-05-03 20:26:01.000000',NULL);
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deposit_categories`
--

DROP TABLE IF EXISTS `deposit_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deposit_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deposit_categories`
--

LOCK TABLES `deposit_categories` WRITE;
/*!40000 ALTER TABLE `deposit_categories` DISABLE KEYS */;
INSERT INTO `deposit_categories` VALUES (1,'Deposit Category','2025-03-25 13:42:37.000000','2025-03-25 13:42:37.000000',NULL);
/*!40000 ALTER TABLE `deposit_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deposits`
--

DROP TABLE IF EXISTS `deposits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deposits` (
  `id` int NOT NULL AUTO_INCREMENT,
  `account_id` int NOT NULL,
  `deposit_category_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method_id` int NOT NULL,
  `date` date NOT NULL,
  `deposit_ref` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attachment` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deposit_account_id` (`account_id`),
  KEY `deposit_category_id` (`deposit_category_id`),
  KEY `deposit_payment_method_id` (`payment_method_id`),
  CONSTRAINT `deposit_account_id` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  CONSTRAINT `deposit_category_id` FOREIGN KEY (`deposit_category_id`) REFERENCES `deposit_categories` (`id`),
  CONSTRAINT `deposit_payment_method_id` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deposits`
--

LOCK TABLES `deposits` WRITE;
/*!40000 ALTER TABLE `deposits` DISABLE KEYS */;
INSERT INTO `deposits` VALUES (1,1,1,12000.00,1,'2025-03-25','Testing','',NULL,'2025-03-25 13:43:53.000000','2025-03-25 13:43:53.000000',NULL);
/*!40000 ALTER TABLE `deposits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `designations`
--

DROP TABLE IF EXISTS `designations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `department_id` int NOT NULL,
  `designation` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `designation_company_id` (`company_id`),
  KEY `designation_departement_id` (`department_id`),
  CONSTRAINT `designation_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `designation_departement_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `designations`
--

LOCK TABLES `designations` WRITE;
/*!40000 ALTER TABLE `designations` DISABLE KEYS */;
INSERT INTO `designations` VALUES (1,1,1,'Main Programmer','2025-03-25 10:43:36.000000','2025-03-25 10:43:36.000000',NULL),(2,2,2,'VP,Products','2025-03-25 11:15:48.000000','2025-05-03 20:09:01.000000','2025-05-03 20:09:01'),(3,2,3,'VP, Marketing','2025-03-25 11:16:05.000000','2025-05-03 20:08:56.000000','2025-05-03 20:08:56'),(4,3,4,'VP, Products','2025-05-03 20:08:49.000000','2025-05-03 20:08:49.000000',NULL),(5,3,5,'Assistant Manager','2025-05-03 20:24:05.000000','2025-05-03 20:24:05.000000',NULL),(6,3,6,'Fullstack Intern','2025-05-03 20:27:09.000000','2025-05-03 20:27:09.000000',NULL),(7,3,6,'Developer','2025-05-03 20:27:44.000000','2025-05-03 20:27:44.000000',NULL),(8,3,7,'Researcher, Blockchain','2025-05-03 20:30:21.000000','2025-05-03 20:30:21.000000',NULL),(9,3,8,'President','2025-05-03 20:46:10.000000','2025-05-03 20:46:10.000000',NULL);
/*!40000 ALTER TABLE `designations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_accounts`
--

DROP TABLE IF EXISTS `employee_accounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_accounts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `bank_name` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_branch` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_no` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_accounts_employee_id` (`employee_id`),
  CONSTRAINT `employee_accounts_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_accounts`
--

LOCK TABLES `employee_accounts` WRITE;
/*!40000 ALTER TABLE `employee_accounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_documents`
--

DROP TABLE IF EXISTS `employee_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attachment` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `documents_employee_id` (`employee_id`),
  CONSTRAINT `documents_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_documents`
--

LOCK TABLES `employee_documents` WRITE;
/*!40000 ALTER TABLE `employee_documents` DISABLE KEYS */;
INSERT INTO `employee_documents` VALUES (1,3,'azaira','erwerwer','1746447415.pdf','2025-05-05 18:16:55.000000','2025-05-05 18:16:55.000000',NULL);
/*!40000 ALTER TABLE `employee_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_experiences`
--

DROP TABLE IF EXISTS `employee_experiences`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_experiences` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `employment_type` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_experience_employee_id` (`employee_id`),
  CONSTRAINT `employee_experience_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_experiences`
--

LOCK TABLES `employee_experiences` WRITE;
/*!40000 ALTER TABLE `employee_experiences` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_experiences` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_project`
--

DROP TABLE IF EXISTS `employee_project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_project` (
  `employee_id` int NOT NULL,
  `project_id` int NOT NULL,
  KEY `employee_project_employee_id` (`employee_id`),
  KEY `employee_project_project_id` (`project_id`),
  CONSTRAINT `employee_project_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `employee_project_project_id` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_project`
--

LOCK TABLES `employee_project` WRITE;
/*!40000 ALTER TABLE `employee_project` DISABLE KEYS */;
INSERT INTO `employee_project` VALUES (3,1),(12,2),(10,2),(9,2),(8,2),(8,3),(9,3),(10,3),(8,4),(10,4),(10,5),(8,5),(8,6),(12,6);
/*!40000 ALTER TABLE `employee_project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_task`
--

DROP TABLE IF EXISTS `employee_task`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_task` (
  `employee_id` int NOT NULL,
  `task_id` int NOT NULL,
  KEY `employee_task_employee_id` (`employee_id`),
  KEY `employee_task_task_id` (`task_id`),
  CONSTRAINT `employee_task_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `employee_task_task_id` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_task`
--

LOCK TABLES `employee_task` WRITE;
/*!40000 ALTER TABLE `employee_task` DISABLE KEYS */;
INSERT INTO `employee_task` VALUES (3,1),(6,2),(12,3),(10,3),(8,3),(9,4),(11,4),(13,4),(12,5),(12,6),(12,7),(12,8),(8,8),(9,9),(9,10);
/*!40000 ALTER TABLE `employee_task` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_training`
--

DROP TABLE IF EXISTS `employee_training`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_training` (
  `employee_id` int NOT NULL,
  `training_id` int NOT NULL,
  KEY `employee_training_employee_id` (`employee_id`),
  KEY `employee_training_training_id` (`training_id`),
  CONSTRAINT `employee_training_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `employee_training_training_id` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_training`
--

LOCK TABLES `employee_training` WRITE;
/*!40000 ALTER TABLE `employee_training` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_training` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_users_id` bigint unsigned NOT NULL,
  `firstname` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zipcode` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `resume` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'no_avatar.png',
  `document` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `company_id` int NOT NULL,
  `department_id` int NOT NULL,
  `designation_id` int NOT NULL,
  `office_shift_id` int NOT NULL,
  `remaining_leave` tinyint(1) DEFAULT '0',
  `total_leave` tinyint(1) DEFAULT '0',
  `hourly_rate` decimal(10,2) DEFAULT '0.00',
  `basic_salary` decimal(10,2) DEFAULT '0.00',
  `employment_type` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'full_time',
  `mode` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expected_hours` decimal(5,2) DEFAULT NULL,
  `leaving_date` date DEFAULT NULL,
  `marital_status` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'single',
  `facebook` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skype` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employees_role_users_id` (`role_users_id`),
  KEY `employees_company_id` (`company_id`),
  KEY `employees_department_id` (`department_id`),
  KEY `employees_designation_id` (`designation_id`),
  KEY `employees_office_shift_id` (`office_shift_id`),
  CONSTRAINT `employees_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `employees_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `employees_designation_id` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`),
  CONSTRAINT `employees_office_shift_id` FOREIGN KEY (`office_shift_id`) REFERENCES `office_shifts` (`id`),
  CONSTRAINT `employees_role_users_id` FOREIGN KEY (`role_users_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (3,2,'MD. Nizamul','Hoque','MD. Nizamul Hoque','melosryz@gmail.com','01708020605','Bangladesh',NULL,NULL,NULL,NULL,'male',NULL,'no_avatar.png',NULL,'1994-09-21','2025-03-01',1,1,1,1,9,30,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-03-25 10:48:24.000000','2025-05-05 18:29:55.000000',NULL),(6,2,'Uday','Uday','Uday Uday','uday1@gmail.com','01717326286','Bangaldesh',NULL,NULL,NULL,NULL,'male',NULL,'no_avatar.png',NULL,'0000-00-00','0000-00-00',2,2,2,2,0,0,0.00,0.00,'full_time',NULL,NULL,NULL,'single',NULL,NULL,NULL,NULL,NULL,'2025-03-25 11:31:11.000000','2025-03-25 11:31:11.000000',NULL),(8,2,'Thajid Ibna Rouf','Uday','Thajid Ibna Rouf Uday','uday@onchain.com.bd','01717326286','Bangaldesh',NULL,NULL,NULL,NULL,'male',NULL,'no_avatar.png',NULL,'1994-02-15','2025-02-05',3,4,4,3,21,21,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-05-03 20:10:57.000000','2025-05-05 18:28:24.000000',NULL),(9,2,'MD NIZAMUL','HOQUE','MD NIZAMUL HOQUE','melo.nizam16@gmail.com','+8801629903608','Bangaldesh','','','','','male',NULL,'no_avatar.png',NULL,'1993-09-18','2025-02-05',3,6,7,4,19,21,0.00,25000.00,'',NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'2025-05-03 20:39:24.000000','2025-05-20 07:34:09.000000',NULL),(10,2,'Mohaiminul Bashar','Raj','Mohaiminul Bashar Raj','mohaiminul252@gmail.com','+8801312935376','Bangaldesh',NULL,NULL,NULL,NULL,'male',NULL,'no_avatar.png',NULL,'1993-07-22','2025-02-05',3,8,9,3,0,0,0.00,0.00,'full_time',NULL,NULL,NULL,'single',NULL,NULL,NULL,NULL,NULL,'2025-05-03 20:48:12.000000','2025-05-03 20:48:12.000000',NULL),(11,2,'Sraboni','Akter','Sraboni Akter','dev.intern.onchain@gmail.com','01319479694','Bangaldesh',NULL,NULL,NULL,NULL,'female',NULL,'no_avatar.png',NULL,'1999-10-15','2025-04-28',3,6,6,3,0,0,0.00,0.00,'full_time',NULL,NULL,NULL,'single',NULL,NULL,NULL,NULL,NULL,'2025-05-03 20:57:03.000000','2025-05-03 20:57:03.000000',NULL),(12,2,'Sabrina Shahrin','Rashid Hia','Sabrina Shahrin Rashid Hia','asstmgr.onchain@gmail.com','01623828068','Bangaldesh','Dhaka','Mohakhali','Dhaka-1212','','female',NULL,'no_avatar.png',NULL,'1998-01-26','2025-05-04',3,5,5,3,0,0,0.00,0.00,'full_time',NULL,NULL,NULL,'single',NULL,NULL,NULL,NULL,NULL,'2025-05-04 14:12:58.000000','2025-05-05 16:44:43.000000',NULL),(13,2,'SM','Shamim','SM Shamim','dev.intern.onchain2@gmail.com','01611052723','Bangaldesh',NULL,NULL,NULL,NULL,'male',NULL,'no_avatar.png',NULL,'1999-04-04','2025-04-28',3,6,6,3,0,0,0.00,0.00,'full_time',NULL,NULL,NULL,'single',NULL,NULL,NULL,NULL,NULL,'2025-05-04 15:05:20.000000','2025-05-04 15:05:20.000000',NULL),(14,2,'Alamgir','Kabir','Alamgir Kabir','alamgirmohammad_ece@icloud.com','01331733003','Bangaldesh',NULL,NULL,NULL,NULL,'male',NULL,'no_avatar.png',NULL,'1994-02-02','2025-02-05',3,7,8,3,0,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-05-04 15:14:18.000000','2025-05-05 20:12:42.000000',NULL);
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int NOT NULL,
  `department_id` int NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event_company_id` (`company_id`),
  KEY `event_department_id` (`department_id`),
  CONSTRAINT `event_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `event_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
INSERT INTO `events` VALUES (1,'Test Event',2,2,'2025-03-25','08:19:00','Testing event','pending','2025-03-25 11:21:33.000000','2025-03-25 11:21:33.000000',NULL),(2,'Event test 2',2,3,'2025-03-25','05:25:00','Test event 2','approved','2025-03-25 11:22:13.000000','2025-03-25 11:22:13.000000',NULL);
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expense_categories`
--

DROP TABLE IF EXISTS `expense_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expense_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expense_categories`
--

LOCK TABLES `expense_categories` WRITE;
/*!40000 ALTER TABLE `expense_categories` DISABLE KEYS */;
INSERT INTO `expense_categories` VALUES (1,'Testing Category','2025-03-25 13:41:11.000000','2025-03-25 13:41:11.000000',NULL);
/*!40000 ALTER TABLE `expense_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `account_id` int NOT NULL,
  `expense_category_id` int NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method_id` int NOT NULL,
  `date` date NOT NULL,
  `expense_ref` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attachment` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_account_id` (`account_id`),
  KEY `expenses_category_id` (`expense_category_id`),
  KEY `expenses_payment_method_id` (`payment_method_id`),
  CONSTRAINT `expenses_account_id` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  CONSTRAINT `expenses_category_id` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`id`),
  CONSTRAINT `expenses_payment_method_id` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
INSERT INTO `expenses` VALUES (1,1,1,2000.00,1,'2025-03-18','teting','',NULL,'2025-03-25 13:44:26.000000','2025-03-25 13:44:26.000000',NULL);
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `holidays`
--

DROP TABLE IF EXISTS `holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `holidays` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `holidays_company_id` (`company_id`),
  CONSTRAINT `holidays_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holidays`
--

LOCK TABLES `holidays` WRITE;
/*!40000 ALTER TABLE `holidays` DISABLE KEYS */;
INSERT INTO `holidays` VALUES (1,'Eid Vacation',2,'2025-03-29','2025-04-03','Vacation','2025-03-25 11:23:08.000000','2025-03-25 11:23:08.000000',NULL);
/*!40000 ALTER TABLE `holidays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_vacancies`
--

DROP TABLE IF EXISTS `job_vacancies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_vacancies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `company_id` int NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_vacancies_company_id_foreign` (`company_id`),
  KEY `job_vacancies_created_by_foreign` (`created_by`),
  CONSTRAINT `job_vacancies_company_id_foreign` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE,
  CONSTRAINT `job_vacancies_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_vacancies`
--

LOCK TABLES `job_vacancies` WRITE;
/*!40000 ALTER TABLE `job_vacancies` DISABLE KEYS */;
INSERT INTO `job_vacancies` VALUES (2,'Junior Embedded/Robotics Engineer (L1)','Responsible for testing embedded hardware, writing basic code for sensors and microcontrollers, and assisting in research documentation and hardware-software integration.','https://docs.google.com/forms/d/e/1FAIpQLSdqguUsH3owwVdxu0-oSqOlxWRu0JA2quGBeTDis5-wkmkkyQ/viewform?usp=dialog',1,3,1,'2025-05-20 10:36:26','2025-05-20 10:37:16',NULL),(3,'Assistant Manager – Operations & Communications','Oversees daily operations, manages internal coordination and reporting, and leads digital communication efforts including content creation and social media management.','https://docs.google.com/forms/d/e/1FAIpQLSdqguUsH3owwVdxu0-oSqOlxWRu0JA2quGBeTDis5-wkmkkyQ/viewform?usp=dialog',1,3,1,'2025-05-20 10:38:11','2025-05-20 10:38:11',NULL),(4,'Fullstack Developer – Level 3 (Senior)','Leads the development of scalable web applications, designs APIs, mentors junior developers, and ensures secure, high-performance deployment of fullstack systems.','https://docs.google.com/forms/d/e/1FAIpQLSdqguUsH3owwVdxu0-oSqOlxWRu0JA2quGBeTDis5-wkmkkyQ/viewform?usp=dialog',1,3,1,'2025-05-20 10:38:58','2025-05-20 10:38:58',NULL),(5,'Fullstack Developer – Level 1 (Junior)','Supports frontend and backend development, assists in API integration and bug fixes, and learns core development practices in Web2 and Web3 environments.','https://docs.google.com/forms/d/e/1FAIpQLSdqguUsH3owwVdxu0-oSqOlxWRu0JA2quGBeTDis5-wkmkkyQ/viewform?usp=dialog',1,3,1,'2025-05-20 10:39:32','2025-05-20 10:39:32',NULL),(6,'Junior UI/UX Developer','Designs user interfaces for web and mobile apps, creates prototypes and wireframes, and collaborates with developers to ensure responsive and intuitive user experiences.','https://docs.google.com/forms/d/e/1FAIpQLSdqguUsH3owwVdxu0-oSqOlxWRu0JA2quGBeTDis5-wkmkkyQ/viewform?usp=dialog',1,3,1,'2025-05-20 10:39:59','2025-05-20 10:39:59',NULL),(7,'Blockchain Developer – Level 3 (Senior)','Develops secure smart contracts and DApps, architects blockchain systems, and drives technical leadership in implementing decentralized solutions.','https://docs.google.com/forms/d/e/1FAIpQLSdqguUsH3owwVdxu0-oSqOlxWRu0JA2quGBeTDis5-wkmkkyQ/viewform?usp=dialog',1,3,1,'2025-05-20 10:40:32','2025-05-20 10:40:32',NULL),(8,'Junior Research Associate','Conducts literature reviews and technical research, supports data collection and experiments, and contributes to writing and publishing research papers in emerging technologies.','https://docs.google.com/forms/d/e/1FAIpQLSdqguUsH3owwVdxu0-oSqOlxWRu0JA2quGBeTDis5-wkmkkyQ/viewform?usp=dialog',1,3,1,'2025-05-20 10:41:06','2025-05-20 10:41:06',NULL),(9,'Senior Robotics and Embedded Engineer (Level 3)','Responsible for leading embedded system and robotics development, including firmware programming, PCB design, 3D modeling, and IoT integration, while managing hardware testing, documentation, and mentoring junior engineers.','https://docs.google.com/forms/d/e/1FAIpQLSeL0lK6GCZHHHAkTcyGTZxo9f5to-Z0QK7ms87SSr724bX-vw/viewform?usp=dialog',1,3,1,'2025-05-20 10:41:31','2025-05-20 10:41:31',NULL),(10,'Personal Secretary to Vice President, International Affairs','Responsible for providing high-level administrative and executive support to the VP of International Affairs, including managing schedules, handling correspondence, coordinating meetings and travel, assisting with financial oversight and compliance, and ensuring smooth internal and external communication in a fast-paced, hybrid work environment.','https://docs.google.com/forms/d/e/1FAIpQLSe3Rg1Yq8NQM7XkcFLVQVJTaZfndiraufCNVwgXcIIZr-tzDA/viewform?usp=dialog',1,3,1,'2025-05-20 10:41:54','2025-05-20 10:41:54',NULL);
/*!40000 ALTER TABLE `job_vacancies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_types`
--

DROP TABLE IF EXISTS `leave_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_types`
--

LOCK TABLES `leave_types` WRITE;
/*!40000 ALTER TABLE `leave_types` DISABLE KEYS */;
INSERT INTO `leave_types` VALUES (1,'Casual Leave','2025-05-05 18:23:56.000000','2025-05-05 18:23:56.000000',NULL),(2,'Sick Leave','2025-05-05 18:24:05.000000','2025-05-05 18:26:10.000000',NULL);
/*!40000 ALTER TABLE `leave_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leaves`
--

DROP TABLE IF EXISTS `leaves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leaves` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `company_id` int NOT NULL,
  `department_id` int NOT NULL,
  `leave_type_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attachment` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `half_day` tinyint(1) DEFAULT NULL,
  `status` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_employee_id` (`employee_id`),
  KEY `leave_company_id` (`company_id`),
  KEY `leave_department_id` (`department_id`),
  KEY `leave_leave_type_id` (`leave_type_id`),
  CONSTRAINT `leave_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `leave_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  CONSTRAINT `leave_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `leave_leave_type_id` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leaves`
--

LOCK TABLES `leaves` WRITE;
/*!40000 ALTER TABLE `leaves` DISABLE KEYS */;
INSERT INTO `leaves` VALUES (1,9,3,6,1,'2025-05-21','2025-05-22','2','bbb','no_image.png',0,'approved','2025-05-05 18:31:03.000000','2025-05-05 18:32:19.000000',NULL);
/*!40000 ALTER TABLE `leaves` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2022_02_18_161351_create_accounts_table',1),(2,'2022_02_18_161351_create_announcements_table',1),(3,'2022_02_18_161351_create_arrangement_types_table',1),(4,'2022_02_18_161351_create_attendances_table',1),(5,'2022_02_18_161351_create_award_types_table',1),(6,'2022_02_18_161351_create_awards_table',1),(7,'2022_02_18_161351_create_clients_table',1),(8,'2022_02_18_161351_create_companies_table',1),(9,'2022_02_18_161351_create_complaints_table',1),(10,'2022_02_18_161351_create_currencies_table',1),(11,'2022_02_18_161351_create_departments_table',1),(12,'2022_02_18_161351_create_deposit_categories_table',1),(13,'2022_02_18_161351_create_deposits_table',1),(14,'2022_02_18_161351_create_designations_table',1),(15,'2022_02_18_161351_create_employee_accounts_table',1),(16,'2022_02_18_161351_create_employee_documents_table',1),(17,'2022_02_18_161351_create_employee_experiences_table',1),(18,'2022_02_18_161351_create_employee_project_table',1),(19,'2022_02_18_161351_create_employee_task_table',1),(20,'2022_02_18_161351_create_employee_training_table',1),(21,'2022_02_18_161351_create_employees_table',1),(22,'2022_02_18_161351_create_events_table',1),(23,'2022_02_18_161351_create_expense_categories_table',1),(24,'2022_02_18_161351_create_expenses_table',1),(25,'2022_02_18_161351_create_holidays_table',1),(26,'2022_02_18_161351_create_leave_types_table',1),(27,'2022_02_18_161351_create_leaves_table',1),(28,'2022_02_18_161351_create_model_has_permissions_table',1),(29,'2022_02_18_161351_create_model_has_roles_table',1),(30,'2022_02_18_161351_create_office_shifts_table',1),(31,'2022_02_18_161351_create_password_resets_table',1),(32,'2022_02_18_161351_create_payment_methods_table',1),(33,'2022_02_18_161351_create_permissions_table',1),(34,'2022_02_18_161351_create_policies_table',1),(35,'2022_02_18_161351_create_project_discussions_table',1),(36,'2022_02_18_161351_create_project_documents_table',1),(37,'2022_02_18_161351_create_project_issues_table',1),(38,'2022_02_18_161351_create_projects_table',1),(39,'2022_02_18_161351_create_role_has_permissions_table',1),(40,'2022_02_18_161351_create_roles_table',1),(41,'2022_02_18_161351_create_settings_table',1),(42,'2022_02_18_161351_create_task_discussions_table',1),(43,'2022_02_18_161351_create_task_documents_table',1),(44,'2022_02_18_161351_create_tasks_table',1),(45,'2022_02_18_161351_create_trainers_table',1),(46,'2022_02_18_161351_create_training_skills_table',1),(47,'2022_02_18_161351_create_trainings_table',1),(48,'2022_02_18_161351_create_travel_table',1),(49,'2022_02_18_161351_create_users_table',1),(50,'2022_02_18_161355_add_foreign_keys_to_announcements_table',1),(51,'2022_02_18_161355_add_foreign_keys_to_attendances_table',1),(52,'2022_02_18_161355_add_foreign_keys_to_awards_table',1),(53,'2022_02_18_161355_add_foreign_keys_to_clients_table',1),(54,'2022_02_18_161355_add_foreign_keys_to_complaints_table',1),(55,'2022_02_18_161355_add_foreign_keys_to_departments_table',1),(56,'2022_02_18_161355_add_foreign_keys_to_deposits_table',1),(57,'2022_02_18_161355_add_foreign_keys_to_designations_table',1),(58,'2022_02_18_161355_add_foreign_keys_to_employee_accounts_table',1),(59,'2022_02_18_161355_add_foreign_keys_to_employee_documents_table',1),(60,'2022_02_18_161355_add_foreign_keys_to_employee_experiences_table',1),(61,'2022_02_18_161355_add_foreign_keys_to_employee_project_table',1),(62,'2022_02_18_161355_add_foreign_keys_to_employee_task_table',1),(63,'2022_02_18_161355_add_foreign_keys_to_employee_training_table',1),(64,'2022_02_18_161355_add_foreign_keys_to_employees_table',1),(65,'2022_02_18_161355_add_foreign_keys_to_events_table',1),(66,'2022_02_18_161355_add_foreign_keys_to_expenses_table',1),(67,'2022_02_18_161355_add_foreign_keys_to_holidays_table',1),(68,'2022_02_18_161355_add_foreign_keys_to_leaves_table',1),(69,'2022_02_18_161355_add_foreign_keys_to_model_has_permissions_table',1),(70,'2022_02_18_161355_add_foreign_keys_to_model_has_roles_table',1),(71,'2022_02_18_161355_add_foreign_keys_to_office_shifts_table',1),(72,'2022_02_18_161355_add_foreign_keys_to_policies_table',1),(73,'2022_02_18_161355_add_foreign_keys_to_project_discussions_table',1),(74,'2022_02_18_161355_add_foreign_keys_to_project_documents_table',1),(75,'2022_02_18_161355_add_foreign_keys_to_project_issues_table',1),(76,'2022_02_18_161355_add_foreign_keys_to_projects_table',1),(77,'2022_02_18_161355_add_foreign_keys_to_role_has_permissions_table',1),(78,'2022_02_18_161355_add_foreign_keys_to_settings_table',1),(79,'2022_02_18_161355_add_foreign_keys_to_task_discussions_table',1),(80,'2022_02_18_161355_add_foreign_keys_to_task_documents_table',1),(81,'2022_02_18_161355_add_foreign_keys_to_tasks_table',1),(82,'2022_02_18_161355_add_foreign_keys_to_trainers_table',1),(83,'2022_02_18_161355_add_foreign_keys_to_trainings_table',1),(84,'2022_02_18_161355_add_foreign_keys_to_travel_table',1),(85,'2022_02_18_161355_add_foreign_keys_to_users_table',1),(86,'2025_04_16_191417_create_posts_table',2),(87,'2024_05_18_000000_add_is_flexible_to_office_shifts_table',3),(88,'2024_05_06_000001_add_kpi_fields_to_employees_table',4),(89,'2024_05_06_000002_add_kpi_fields_to_tasks_table',4),(90,'2024_05_19_000000_add_expected_hours_to_office_shifts_table',4),(91,'2024_05_19_000001_add_mode_to_attendances_table',4),(92,'2024_05_20_000000_add_weekend_days_to_office_shifts_table',4),(93,'2024_06_10_000000_add_half_day_to_office_shifts_table',4),(94,'2024_06_10_000000_create_bonus_allowances_table',4),(95,'2024_06_11_000000_create_job_vacancies_table',4),(96,'2025_05_14_181304_create_notifications_table',4),(97,'2024_06_12_000000_create_salary_disbursements_table',5);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(1,'App\\Models\\User',2),(2,'App\\Models\\User',3),(3,'App\\Models\\User',4),(1,'App\\Models\\User',5),(2,'App\\Models\\User',6),(1,'App\\Models\\User',7),(2,'App\\Models\\User',8),(2,'App\\Models\\User',9),(2,'App\\Models\\User',10),(2,'App\\Models\\User',11),(2,'App\\Models\\User',12),(2,'App\\Models\\User',13),(2,'App\\Models\\User',14),(3,'App\\Models\\User',15),(3,'App\\Models\\User',16);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES ('16e06f0c-1758-42c2-a287-5b33e63afc9a','App\\Notifications\\NewAnnouncement','App\\Models\\User',11,'{\"announcement_id\":3,\"title\":\"cdghdgd\",\"summary\":\"rdcghdhgd\",\"message\":\"New announcement: cdghdgd\",\"url\":\"https:\\/\\/onchain.com.bd\\/core\\/announcements\"}',NULL,'2025-05-20 11:03:03','2025-05-20 11:03:03'),('55262d18-4110-4a95-84d5-10378089f157','App\\Notifications\\TaskAssigned','App\\Models\\User',9,'{\"task_id\":9,\"title\":\"testst\",\"assigned_by\":\"Admin\",\"message\":\"You have been assigned a new task: testst\",\"url\":\"https:\\/\\/onchain.com.bd\\/tasks\\/9\"}','2025-05-20 08:05:04','2025-05-20 08:04:58','2025-05-20 08:05:04'),('8c1c2deb-3b71-41e9-9ae0-49e0aef7ee70','App\\Notifications\\AttendanceAnomaly','App\\Models\\User',8,'{\"employee_name\":\"Thajid Ibna Rouf Uday\",\"date\":\"2025-05-20\",\"anomaly_type\":\"Early Departure\",\"message\":\"Attendance anomaly detected: Early Departure on 2025-05-20\",\"url\":\"https:\\/\\/onchain.com.bd\\/attendance\"}',NULL,'2025-05-20 11:01:14','2025-05-20 11:01:14'),('8f45591c-cec8-451b-b43c-920d3c1273f1','App\\Notifications\\TaskAssigned','App\\Models\\User',9,'{\"task_id\":10,\"title\":\"Check Onchain site\",\"assigned_by\":\"Admin\",\"message\":\"You have been assigned a new task: Check Onchain site\",\"url\":\"https:\\/\\/onchain.com.bd\\/tasks\\/10\"}','2025-05-20 08:12:23','2025-05-20 08:12:14','2025-05-20 08:12:23'),('aa469819-be58-4658-9b34-4198302aa775','App\\Notifications\\AttendanceAnomaly','App\\Models\\User',11,'{\"employee_name\":\"Sraboni Akter\",\"date\":\"2025-05-20\",\"anomaly_type\":\"Early Departure\",\"message\":\"Attendance anomaly detected: Early Departure on 2025-05-20\",\"url\":\"https:\\/\\/onchain.com.bd\\/attendance\"}',NULL,'2025-05-20 10:05:57','2025-05-20 10:05:57'),('b4e99778-a2d8-4704-9458-15ce0b21325e','App\\Notifications\\AttendanceAnomaly','App\\Models\\User',10,'{\"employee_name\":\"Mohaiminul Bashar Raj\",\"date\":\"2025-05-20\",\"anomaly_type\":\"Late Arrival\",\"message\":\"Attendance anomaly detected: Late Arrival on 2025-05-20\",\"url\":\"https:\\/\\/onchain.com.bd\\/attendance\"}',NULL,'2025-05-20 11:36:59','2025-05-20 11:36:59'),('cab7f73c-54f4-48df-bc0b-f4646a370c7a','App\\Notifications\\AttendanceAnomaly','App\\Models\\User',13,'{\"employee_name\":\"SM Shamim\",\"date\":\"2025-05-20\",\"anomaly_type\":\"Early Departure\",\"message\":\"Attendance anomaly detected: Early Departure on 2025-05-20\",\"url\":\"https:\\/\\/onchain.com.bd\\/attendance\"}','2025-05-20 10:01:38','2025-05-20 10:00:08','2025-05-20 10:01:38'),('d42362c3-7d87-4a95-9f17-5c8f055188bb','App\\Notifications\\NewAnnouncement','App\\Models\\User',13,'{\"announcement_id\":3,\"title\":\"cdghdgd\",\"summary\":\"rdcghdhgd\",\"message\":\"New announcement: cdghdgd\",\"url\":\"https:\\/\\/onchain.com.bd\\/core\\/announcements\"}',NULL,'2025-05-20 11:03:03','2025-05-20 11:03:03'),('f28f0541-8099-4f11-89c3-639a8bd8b413','App\\Notifications\\NewAnnouncement','App\\Models\\User',9,'{\"announcement_id\":3,\"title\":\"cdghdgd\",\"summary\":\"rdcghdhgd\",\"message\":\"New announcement: cdghdgd\",\"url\":\"https:\\/\\/onchain.com.bd\\/core\\/announcements\"}','2025-05-20 11:03:22','2025-05-20 11:03:03','2025-05-20 11:03:22');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `office_shifts`
--

DROP TABLE IF EXISTS `office_shifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `office_shifts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `is_flexible` tinyint(1) NOT NULL DEFAULT '0',
  `expected_hours` decimal(5,2) DEFAULT NULL,
  `weekend_days` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `half_day_of_week` tinyint DEFAULT NULL,
  `half_day_expected_hours` decimal(5,2) DEFAULT NULL,
  `company_id` int NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `monday_in` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monday_out` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tuesday_in` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tuesday_out` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wednesday_in` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wednesday_out` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thursday_in` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thursday_out` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `friday_in` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `friday_out` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `saturday_in` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `saturday_out` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sunday_in` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sunday_out` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `office_shift_company_id` (`company_id`),
  CONSTRAINT `office_shift_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `office_shifts`
--

LOCK TABLES `office_shifts` WRITE;
/*!40000 ALTER TABLE `office_shifts` DISABLE KEYS */;
INSERT INTO `office_shifts` VALUES (1,0,NULL,NULL,NULL,NULL,1,'Melodic','00:05AM','08:29AM','12:29PM','06:30AM','12:00PM','06:30AM','12:30PM','18:30PM',NULL,NULL,'12:30PM','18:30PM',NULL,NULL,'2025-03-25 10:47:36.000000','2025-03-25 10:47:36.000000',NULL),(2,0,NULL,NULL,NULL,NULL,2,'Onchain Shift','10:00AM','08:00AM',NULL,NULL,'10:00AM','08:00AM',NULL,NULL,'10:00AM','08:00AM',NULL,NULL,'10:00AM','08:00AM','2025-03-25 11:20:43.000000','2025-03-25 11:20:43.000000',NULL),(3,0,NULL,NULL,NULL,NULL,3,'Onchain HQ','10:30AM','18:30PM','10:30AM','18:30PM','10:30AM','18:30PM','10:30AM','18:30PM',NULL,NULL,'10:30AM','18:30PM','10:30AM','18:30PM','2025-05-03 20:05:09.000000','2025-05-03 20:33:59.000000',NULL),(4,1,8.00,'5',0,0.00,3,'test',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-05-04 07:36:11.000000','2025-05-20 07:35:45.000000',NULL);
/*!40000 ALTER TABLE `office_shifts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_resets` (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `password_resets_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_methods`
--

DROP TABLE IF EXISTS `payment_methods`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_methods` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_methods`
--

LOCK TABLES `payment_methods` WRITE;
/*!40000 ALTER TABLE `payment_methods` DISABLE KEYS */;
INSERT INTO `payment_methods` VALUES (1,'Payment methods','2025-03-25 13:42:55.000000','2025-03-25 13:42:55.000000',NULL);
/*!40000 ALTER TABLE `payment_methods` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=116 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'employee_view','web',NULL,NULL),(2,'employee_add','web',NULL,NULL),(3,'employee_edit','web',NULL,NULL),(4,'employee_delete','web',NULL,NULL),(5,'user_view','web',NULL,NULL),(6,'user_add','web',NULL,NULL),(7,'user_edit','web',NULL,NULL),(8,'user_delete','web',NULL,NULL),(9,'company_view','web',NULL,NULL),(10,'company_add','web',NULL,NULL),(11,'company_edit','web',NULL,NULL),(12,'company_delete','web',NULL,NULL),(13,'department_view','web',NULL,NULL),(14,'department_add','web',NULL,NULL),(15,'department_edit','web',NULL,NULL),(16,'department_delete','web',NULL,NULL),(17,'designation_view','web',NULL,NULL),(18,'designation_add','web',NULL,NULL),(19,'designation_edit','web',NULL,NULL),(20,'designation_delete','web',NULL,NULL),(21,'policy_view','web',NULL,NULL),(22,'policy_add','web',NULL,NULL),(23,'policy_edit','web',NULL,NULL),(24,'policy_delete','web',NULL,NULL),(25,'announcement_view','web',NULL,NULL),(26,'announcement_add','web',NULL,NULL),(27,'announcement_edit','web',NULL,NULL),(28,'announcement_delete','web',NULL,NULL),(29,'office_shift_view','web',NULL,NULL),(30,'office_shift_add','web',NULL,NULL),(31,'office_shift_edit','web',NULL,NULL),(32,'office_shift_delete','web',NULL,NULL),(33,'event_view','web',NULL,NULL),(34,'event_add','web',NULL,NULL),(35,'event_edit','web',NULL,NULL),(36,'event_delete','web',NULL,NULL),(37,'holiday_view','web',NULL,NULL),(38,'holiday_add','web',NULL,NULL),(39,'holiday_edit','web',NULL,NULL),(40,'holiday_delete','web',NULL,NULL),(41,'award_view','web',NULL,NULL),(42,'award_add','web',NULL,NULL),(43,'award_edit','web',NULL,NULL),(44,'award_delete','web',NULL,NULL),(45,'award_type','web',NULL,NULL),(46,'complaint_view','web',NULL,NULL),(47,'complaint_add','web',NULL,NULL),(48,'complaint_edit','web',NULL,NULL),(49,'complaint_delete','web',NULL,NULL),(50,'travel_view','web',NULL,NULL),(51,'travel_add','web',NULL,NULL),(52,'travel_edit','web',NULL,NULL),(53,'travel_delete','web',NULL,NULL),(54,'arrangement_type','web',NULL,NULL),(55,'attendance_view','web',NULL,NULL),(56,'attendance_add','web',NULL,NULL),(57,'attendance_edit','web',NULL,NULL),(58,'attendance_delete','web',NULL,NULL),(59,'account_view','web',NULL,NULL),(60,'account_add','web',NULL,NULL),(61,'account_edit','web',NULL,NULL),(62,'account_delete','web',NULL,NULL),(63,'deposit_view','web',NULL,NULL),(64,'deposit_add','web',NULL,NULL),(65,'deposit_edit','web',NULL,NULL),(66,'deposit_delete','web',NULL,NULL),(67,'expense_view','web',NULL,NULL),(68,'expense_add','web',NULL,NULL),(69,'expense_edit','web',NULL,NULL),(70,'expense_delete','web',NULL,NULL),(71,'client_view','web',NULL,NULL),(72,'client_add','web',NULL,NULL),(73,'client_edit','web',NULL,NULL),(74,'client_delete','web',NULL,NULL),(75,'deposit_category','web',NULL,NULL),(76,'payment_method','web',NULL,NULL),(77,'expense_category','web',NULL,NULL),(78,'project_view','web',NULL,NULL),(79,'project_add','web',NULL,NULL),(80,'project_edit','web',NULL,NULL),(81,'project_delete','web',NULL,NULL),(82,'task_view','web',NULL,NULL),(83,'task_add','web',NULL,NULL),(84,'task_edit','web',NULL,NULL),(85,'task_delete','web',NULL,NULL),(86,'leave_view','web',NULL,NULL),(87,'leave_add','web',NULL,NULL),(88,'leave_edit','web',NULL,NULL),(89,'leave_delete','web',NULL,NULL),(90,'training_view','web',NULL,NULL),(91,'training_add','web',NULL,NULL),(92,'training_edit','web',NULL,NULL),(93,'training_delete','web',NULL,NULL),(94,'trainer','web',NULL,NULL),(95,'training_skills','web',NULL,NULL),(96,'settings','web',NULL,NULL),(97,'currency','web',NULL,NULL),(98,'backup','web',NULL,NULL),(99,'group_permission','web',NULL,NULL),(100,'attendance_report','web',NULL,NULL),(101,'employee_report','web',NULL,NULL),(102,'project_report','web',NULL,NULL),(103,'task_report','web',NULL,NULL),(104,'expense_report','web',NULL,NULL),(105,'deposit_report','web',NULL,NULL),(106,'employee_details','web',NULL,NULL),(107,'leave_type','web',NULL,NULL),(108,'project_details','web',NULL,NULL),(109,'task_details','web',NULL,NULL),(110,'module_settings','web',NULL,NULL),(111,'kanban_task','web',NULL,NULL),(112,'kpi_report','web',NULL,NULL),(113,'salary_disbursement_report','web',NULL,NULL),(114,'leave_absence_report','web',NULL,NULL),(115,'task_view_own','web',NULL,NULL);
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `policies`
--

DROP TABLE IF EXISTS `policies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `policies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `policy_company_id` (`company_id`),
  CONSTRAINT `policy_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `policies`
--

LOCK TABLES `policies` WRITE;
/*!40000 ALTER TABLE `policies` DISABLE KEYS */;
INSERT INTO `policies` VALUES (1,2,'Office Time','Every Employee should come to office within 10.30am morning. After 10.30am entry shall count as late entry.','2025-03-25 11:17:18.000000','2025-03-25 11:17:18.000000',NULL);
/*!40000 ALTER TABLE `policies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `posts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `posts_slug_unique` (`slug`),
  KEY `posts_user_id_foreign` (`user_id`),
  CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `posts`
--

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;
INSERT INTO `posts` VALUES (1,1,'set','eerwer','erwer','2025-04-17 09:59:34','2025-04-17 09:59:34',NULL),(2,1,'hgnhgk','gdcgdhgd','cxfgdhg','2025-04-26 21:53:35','2025-04-26 21:53:35',NULL);
/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_discussions`
--

DROP TABLE IF EXISTS `project_discussions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_discussions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `user_id` int NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_discussions_project_id` (`project_id`),
  KEY `project_discussions_user_id` (`user_id`),
  CONSTRAINT `project_discussions_project_id` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  CONSTRAINT `project_discussions_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_discussions`
--

LOCK TABLES `project_discussions` WRITE;
/*!40000 ALTER TABLE `project_discussions` DISABLE KEYS */;
INSERT INTO `project_discussions` VALUES (1,1,1,'vfsdgsdg','2025-03-25 11:02:10.000000','2025-03-25 11:02:10.000000',NULL);
/*!40000 ALTER TABLE `project_discussions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_documents`
--

DROP TABLE IF EXISTS `project_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attachment` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_documents_projet_id` (`project_id`),
  CONSTRAINT `project_documents_projet_id` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_documents`
--

LOCK TABLES `project_documents` WRITE;
/*!40000 ALTER TABLE `project_documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `project_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `project_issues`
--

DROP TABLE IF EXISTS `project_issues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_issues` (
  `id` int NOT NULL AUTO_INCREMENT,
  `project_id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `project_issues_project_id` (`project_id`),
  CONSTRAINT `project_issues_project_id` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `project_issues`
--

LOCK TABLES `project_issues` WRITE;
/*!40000 ALTER TABLE `project_issues` DISABLE KEYS */;
INSERT INTO `project_issues` VALUES (1,1,'sdgsdg','sdgsdgsdgdsg','invalid',NULL,'pending','2025-03-25 11:02:23.000000','2025-03-25 11:02:23.000000',NULL);
/*!40000 ALTER TABLE `project_issues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `projects`
--

DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` int NOT NULL,
  `priority` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `company_id` int NOT NULL,
  `project_progress` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `projects_client_id` (`client_id`),
  KEY `projects_company_id` (`company_id`),
  CONSTRAINT `projects_client_id` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  CONSTRAINT `projects_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `projects`
--

LOCK TABLES `projects` WRITE;
/*!40000 ALTER TABLE `projects` DISABLE KEYS */;
INSERT INTO `projects` VALUES (1,'Voting',4,'medium','2025-01-25','2025-04-30','Create voting system','ksldfjhsdlikf dsfjhsdoifhoi sdflohisdoifkbnhslkd sdlkfghoisduf',1,'12','progress',NULL,'2025-03-25 10:54:23.000000','2025-03-25 11:03:22.000000',NULL),(2,'Onchain Website',15,'urgent','2025-05-05','2025-05-29','The website will serve as the digital presence for the company,','Onchain Software and Research Limited is a forward-thinking blockchain-based research and development company, specializing in integrating cutting-edge technologies such as AI, blockchain, and cloud computing to create scalable, user-centric solutions. The website will serve as the digital presence for the company, providing detailed information on the company’s services, vision, mission, and groundbreaking work in the technology sector.\nObjectives:\nTo create an engaging, visually appealing, and professional website that reflects Onchain’s innovation-driven approach.\nTo provide easy-to-navigate sections for both visitors and potential clients.\nTo showcase services in blockchain, AI, and cloud computing, particularly focusing on the government, enterprise, and NGO sectors.\nTo build trust and reliability through clear, transparent messaging.',3,'0','progress',NULL,'2025-05-04 23:54:55.000000','2025-05-04 23:54:55.000000',NULL),(3,'Hub: Gamified Problem Solving Platfor',16,'high','2025-05-08','2025-08-31','Gamified Problem-Solving Platform: A Blockchain-Powered Ecosystem for Real-World Impact','This concept note presents an innovative platform that bridges the gap between global problem solvers and real-world challenges through gamification and blockchain technology. The platform creates a meritocratic ecosystem where individuals can apply their expertise to meaningful problems while earning tangible rewards, ultimately transforming how we approach complex social, environmental, and technological issues in Bangladesh and beyond.',3,'0','not_started',NULL,'2025-05-04 23:58:53.000000','2025-05-04 23:58:53.000000',NULL),(4,'Plant Network',16,'high','2025-04-15','2025-08-30','The Plant Network is a digital ecosystem designed to identify, monitor, and track the environmental impact of significant and mature plants-especially those in public spaces such as roadsides, parks, and forests','The Plant Network is a digital ecosystem designed to identify, monitor, and track the environmental impact of significant and mature plants-especially those in public spaces such as roadsides, parks, and forests. The system is first deployed as a Web2 solution for rapid adoption and scalability, with a planned transition to Web3 using BanglaChain for enhanced transparency, automation, and accountability.',3,'0','progress',NULL,'2025-05-05 00:58:01.000000','2025-05-05 00:58:01.000000',NULL),(5,'BanglaChain Foundation',16,'high','2025-04-15','2025-05-31','Banglachain foundation Registration and Launch','প্রাণ-প্রকৃতির সুরক্ষা নিশ্চিত করে মানব সভ্যতার অগ্রগতিই  বাংলাচেইন ফাউন্ডেশন এর  মূল লক্ষ্য ও উদ্দেশ্য। জলবায়ু পরিবর্তন ও পরিবেশ বিপর্যয় এই শতকে আমাদের সামনে সবচেয়ে বড় সংকট হিসেবে হাজির হয়েছে৷ আধুনিক মানুষ হিসেবে প্রাণ-প্রকৃতির সাথে আমাদের বৈরিতাপূর্ণ সম্পর্কই এই সংকটের মূল উৎস৷ যান্ত্রিক উৎপাদনের যুগে প্রকৃতি হয়েছে মানুষের অধীনস্ত, যেখানে প্রযুক্তিগত উৎকর্ষ আর প্রগতির ধারণা হয়ে উঠেছে প্রকৃতিকে নিয়ন্ত্রণ ও শোষণ করারই নামান্তর। তারই প্রতিক্রিয়ায় প্রকৃতিও হয়ে উঠছে বিরূপ৷ মহামারি, বন্যা, খরাসহ নানা বিপর্যয় মানবসভ্যতাকে ফেলে দিয়েছে হুমকির মুখে। ফলে এই সংকট মোকাবিলায় মানুষ ও প্রকৃতির সহাবস্থানে সভ্যতার এক নতুন গতিপথ আমাদের আবিষ্কার করতে হবে। বুদ্ধিদীপ্ত উদ্ভাবন ও দূরদর্শী পরিকল্পনার মাধ্যমেই তা সম্ভব বলে আমরা বিশ্বাস করি। এ কারণেই মানুষ ও প্রকৃতির সম্পর্ক পুনর্বিবেচনায় জ্ঞানজগতের পুনর্বিন্যাস জরুরি বলে আমরা মনে করি। বহুশাস্ত্রীয় মিথোষ্ক্রিয়ায় ব্যক্তি ও গোষ্ঠীগত উদ্যোগ এবং বিবিধ প্রতিষ্ঠানের অংশীদারিত্বপূর্ণ প্রচেষ্টার মধ্য দিয়ে একটি টেকসই বাস্তুসংস্থান তৈরির লক্ষ্যেই তাই আমাদের পথচলা৷',3,'0','progress',NULL,'2025-05-05 01:00:27.000000','2025-05-05 01:00:27.000000',NULL),(6,'Onchain D2D Operations',15,'high','2025-05-01','2025-05-31','Day to Day operations','',3,'0','not_started',NULL,'2025-05-05 01:10:21.000000','2025-05-05 01:10:21.000000',NULL);
/*!40000 ALTER TABLE `projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),(45,1),(46,1),(47,1),(48,1),(49,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(64,1),(65,1),(66,1),(67,1),(68,1),(69,1),(70,1),(71,1),(72,1),(73,1),(74,1),(75,1),(76,1),(77,1),(78,1),(79,1),(80,1),(81,1),(82,1),(83,1),(84,1),(85,1),(86,1),(87,1),(88,1),(89,1),(90,1),(91,1),(92,1),(93,1),(94,1),(95,1),(96,1),(97,1),(98,1),(99,1),(100,1),(101,1),(102,1),(103,1),(104,1),(105,1),(106,1),(107,1),(108,1),(109,1),(110,1),(111,1),(112,1),(113,1),(114,1),(25,2),(55,2),(56,2),(57,2),(58,2),(82,2),(83,2),(84,2),(85,2),(109,2),(115,2),(71,3),(78,3),(82,3);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `guard_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Super Admin','Super Admin','web',NULL,'2025-05-20 08:01:22.000000',NULL),(2,'Employee','Employee Access','web',NULL,'2025-05-20 11:05:19.000000',NULL),(3,'Client','Client Access','web',NULL,NULL,NULL),(4,'Team Member','All users','web','2025-05-20 07:22:06.000000','2025-05-20 07:25:37.000000','2025-05-20 07:25:37'),(5,'Team Member','asdasdf','web','2025-05-20 07:41:36.000000','2025-05-20 07:43:36.000000',NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `salary_disbursements`
--

DROP TABLE IF EXISTS `salary_disbursements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `salary_disbursements` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `month` varchar(7) COLLATE utf8mb4_unicode_ci NOT NULL,
  `basic_salary` decimal(10,2) NOT NULL,
  `adjustments` decimal(10,2) NOT NULL DEFAULT '0.00',
  `leave_deductions` decimal(10,2) NOT NULL DEFAULT '0.00',
  `bonus_allowance` decimal(10,2) NOT NULL DEFAULT '0.00',
  `gross_salary` decimal(10,2) NOT NULL,
  `net_payable` decimal(10,2) NOT NULL,
  `paid` tinyint(1) NOT NULL DEFAULT '0',
  `payment_date` date DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `salary_disbursements_employee_id_month_index` (`employee_id`,`month`),
  CONSTRAINT `salary_disbursements_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `salary_disbursements`
--

LOCK TABLES `salary_disbursements` WRITE;
/*!40000 ALTER TABLE `salary_disbursements` DISABLE KEYS */;
/*!40000 ALTER TABLE `salary_disbursements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `currency_id` int DEFAULT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `CompanyName` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `CompanyPhone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `CompanyAdress` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `footer` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `developed_by` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_language` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en',
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `settings_currency_id` (`currency_id`),
  CONSTRAINT `settings_currency_id` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,1,'admin@example.com','Onchain','6315996770','Baridhara DOHS','Onchain','melodic','logo-default.png','en',NULL,'2025-04-26 21:57:22.000000',NULL);
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_discussions`
--

DROP TABLE IF EXISTS `task_discussions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_discussions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `user_id` int NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `task_discussions_task_id` (`task_id`),
  KEY `task_discussions_user_id` (`user_id`),
  CONSTRAINT `task_discussions_task_id` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`),
  CONSTRAINT `task_discussions_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_discussions`
--

LOCK TABLES `task_discussions` WRITE;
/*!40000 ALTER TABLE `task_discussions` DISABLE KEYS */;
/*!40000 ALTER TABLE `task_discussions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `task_documents`
--

DROP TABLE IF EXISTS `task_documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `task_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `task_id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attachment` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tasks_doocuments_task_id` (`task_id`),
  CONSTRAINT `tasks_doocuments_task_id` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `task_documents`
--

LOCK TABLES `task_documents` WRITE;
/*!40000 ALTER TABLE `task_documents` DISABLE KEYS */;
INSERT INTO `task_documents` VALUES (1,3,'Requirements','https://docs.google.com/document/d/1mr8aW0mpfMxEmIzJTwYILy7RoFSkZlp4oj8WwwiY7PI/edit?tab=t.0','1746385464.png','2025-05-05 01:04:24.000000','2025-05-05 01:04:24.000000',NULL),(2,10,'jbjk','','1747707263.png','2025-05-20 08:14:23.000000','2025-05-20 08:14:23.000000',NULL);
/*!40000 ALTER TABLE `task_documents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tasks`
--

DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `project_id` int NOT NULL,
  `company_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `estimated_hour` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `task_progress` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `summary` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `quality_score` decimal(5,2) DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Tasks_project_id` (`project_id`),
  KEY `Tasks_company_id` (`company_id`),
  CONSTRAINT `Tasks_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `Tasks_project_id` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tasks`
--

LOCK TABLES `tasks` WRITE;
/*!40000 ALTER TABLE `tasks` DISABLE KEYS */;
INSERT INTO `tasks` VALUES (1,'login',1,1,'2025-03-02','2025-03-10',NULL,'100','Create voting system','sdgfswdgsdgsdgsdgsdg sdfgsdfgsd','completed','medium',NULL,NULL,NULL,'2025-03-25 10:57:19.000000','2025-03-25 11:02:44.000000',NULL),(2,'Testing task',1,2,'2025-03-20','2025-03-23',NULL,'47','Testing summary','','completed','high',NULL,NULL,NULL,'2025-03-25 13:45:13.000000','2025-03-25 13:45:55.000000',NULL),(3,'Onchain Website UI',2,3,'2025-05-05','2025-05-15',NULL,'0','https://docs.google.com/document/d/1mr8aW0mpfMxEmIzJTwYILy7RoFSkZlp4oj8WwwiY7PI/edit?usp=sharing','Onchain Software and Research Limited is a forward-thinking blockchain-based research and development company, specializing in integrating cutting-edge technologies such as AI, blockchain, and cloud computing to create scalable, user-centric solutions. The website will serve as the digital presence for the company, providing detailed information on the company’s services, vision, mission, and groundbreaking work in the technology sector.\nObjectives:\nTo create an engaging, visually appealing, and professional website that reflects Onchain’s innovation-driven approach.\nTo provide easy-to-navigate sections for both visitors and potential clients.\nTo showcase services in blockchain, AI, and cloud computing, particularly focusing on the government, enterprise, and NGO sectors.\nTo build trust and reliability through clear, transparent messaging.','progress','urgent',NULL,NULL,NULL,'2025-05-04 23:49:39.000000','2025-05-05 01:01:27.000000',NULL),(4,'Developing Onchain Webpage',2,3,'2025-05-16','2025-05-28',NULL,'0','Developing Webpage','Update after completing the UI design','not_started','urgent',NULL,NULL,NULL,'2025-05-05 01:06:49.000000','2025-05-05 01:06:49.000000',NULL),(5,'ID card design and send to printing',6,3,'2025-05-05','2025-05-08',NULL,'0','Design and send for print','1. Ask President for .ai design file. 2. Design according to information for Assistant Manager, Fullstack Developer - Intern (2). 3. Review and confirm the information. 4. send it for printing.','not_started','medium',NULL,NULL,NULL,'2025-05-05 01:13:07.000000','2025-05-05 01:13:07.000000',NULL),(6,'new member onboarding design',6,3,'2025-05-05','2025-05-08',NULL,'0','new member onboarding social media post','1. New member onboarding post for social media design. 2. Approve. 3. Post on social media \nnew members are: 1. Assistant manager, 2. Fullstack Developer inter 3. Fullstack Developer inter \nInformation: https://docs.google.com/spreadsheets/d/1KO7Kw5DL4gPz9RE5pMv8FIZyzD9034HwgtIDGHUAh0A/edit?usp=sharing','progress','medium',NULL,NULL,NULL,'2025-05-05 01:15:53.000000','2025-05-05 16:38:42.000000',NULL),(7,'Introducing President, VP and Management post',6,3,'2025-05-05','2025-05-08',NULL,'0','Design and post to introduce team','Design and post to Introducing President, VP and Management','not_started','medium',NULL,NULL,NULL,'2025-05-05 01:17:31.000000','2025-05-05 01:17:31.000000',NULL),(8,'Developed SOP',6,3,'2025-05-05','2025-05-15',NULL,'0','Developed SOP for hiring and fixed tasks that we should maintaining each position','Developed SOP for hiring and fixed tasks that we should maintaining each position.','not_started','low',NULL,NULL,NULL,'2025-05-05 01:21:23.000000','2025-05-05 01:21:23.000000',NULL),(9,'testst',2,3,'2025-05-20','2025-05-25',NULL,'26','sdfsdfsdfsdf','dfsdfsdfsd','not_started','medium',NULL,NULL,NULL,'2025-05-20 08:04:58.000000','2025-05-20 08:04:58.000000',NULL),(10,'Check Onchain site',2,3,'2025-05-20','2025-05-21',NULL,'28','Onchain site has to go through a down time. now I need to check if all functions working properly','1. Check if Attendance list for employees showing other employees data\n2. For employees, Do they see more options in the sidebar.','not_started','urgent',NULL,NULL,NULL,'2025-05-20 08:12:14.000000','2025-05-20 08:12:14.000000',NULL);
/*!40000 ALTER TABLE `tasks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trainers`
--

DROP TABLE IF EXISTS `trainers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trainers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int NOT NULL,
  `phone` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trainers_company_id` (`company_id`),
  CONSTRAINT `trainers_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trainers`
--

LOCK TABLES `trainers` WRITE;
/*!40000 ALTER TABLE `trainers` DISABLE KEYS */;
/*!40000 ALTER TABLE `trainers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `training_skills`
--

DROP TABLE IF EXISTS `training_skills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `training_skills` (
  `id` int NOT NULL AUTO_INCREMENT,
  `training_skill` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `training_skills`
--

LOCK TABLES `training_skills` WRITE;
/*!40000 ALTER TABLE `training_skills` DISABLE KEYS */;
/*!40000 ALTER TABLE `training_skills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `trainings`
--

DROP TABLE IF EXISTS `trainings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `trainings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `trainer_id` int NOT NULL,
  `company_id` int NOT NULL,
  `training_skill_id` int NOT NULL,
  `training_cost` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trainings_trainer_id` (`trainer_id`),
  KEY `trainings_company_id` (`company_id`),
  KEY `trainings_training_skill_id` (`training_skill_id`),
  CONSTRAINT `trainings_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `trainings_trainer_id` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`),
  CONSTRAINT `trainings_training_skill_id` FOREIGN KEY (`training_skill_id`) REFERENCES `training_skills` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `trainings`
--

LOCK TABLES `trainings` WRITE;
/*!40000 ALTER TABLE `trainings` DISABLE KEYS */;
/*!40000 ALTER TABLE `trainings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `travel`
--

DROP TABLE IF EXISTS `travel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `travel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `visit_purpose` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `visit_place` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `travel_mode` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `arrangement_type_id` int NOT NULL,
  `expected_budget` decimal(65,2) NOT NULL DEFAULT '0.00',
  `actual_budget` decimal(65,2) NOT NULL DEFAULT '0.00',
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `travels_company_id` (`company_id`),
  KEY `travels_employee_id` (`employee_id`),
  KEY `travels_arrangement_type_id` (`arrangement_type_id`),
  CONSTRAINT `travels_arrangement_type_id` FOREIGN KEY (`arrangement_type_id`) REFERENCES `arrangement_types` (`id`),
  CONSTRAINT `travels_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  CONSTRAINT `travels_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `travel`
--

LOCK TABLES `travel` WRITE;
/*!40000 ALTER TABLE `travel` DISABLE KEYS */;
/*!40000 ALTER TABLE `travel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `avatar` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `role_users_id` bigint unsigned NOT NULL,
  `password` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `users_role_users_id` (`role_users_id`),
  CONSTRAINT `users_role_users_id` FOREIGN KEY (`role_users_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'William Castillo','admin@example.com',NULL,'no_avatar.png',1,1,'$2y$10$IFj6SwqC0Sxrsiv4YkCt.OJv1UV4mZrWuyLoRG7qt47mseP9mJ58u',NULL,NULL,NULL,NULL),(2,'melodic1','admin2@example.com',NULL,'no_avatar.png',0,1,'$2y$10$BbhuOwogNBFb5VNHigVYZu49lpt1HDMi/.ahjFQDeq04Hn3RpNpqC',NULL,'2025-03-25 10:42:06.000000','2025-03-25 10:42:20.000000',NULL),(3,'MD. Nizamul Hoque','melosryz@gmail.com',NULL,'no_avatar.png',1,2,'$2y$10$k2MAZifugtNmkB0dqjldLOkuhT9eYTaKx9rRpqQm3BlQNmvlfSr.y',NULL,'2025-03-25 10:48:24.000000','2025-05-05 18:29:55.000000',NULL),(4,'MD. jesus Hoque','melosryz1@gmail.com',NULL,'no_avatar.png',0,3,'$2y$10$Lv7JtqRgayM4C7cyCV3qNuCWsj0ZyH0sKnnZy8Vl5BaCQBmD5Gkwu',NULL,'2025-03-25 10:52:13.000000','2025-05-05 20:01:39.000000',NULL),(5,'Uday','uday@gmail.com',NULL,'no_avatar.png',0,1,'$2y$10$Bb0YT6UN4gZHFJsaOsLWpOi8ob0RU.9E64iJfqexKC3TLlvIiVzOi',NULL,'2025-03-25 11:29:57.000000','2025-04-27 12:09:41.000000',NULL),(6,'Uday Uday','uday1@gmail.com',NULL,'no_avatar.png',0,2,'$2y$10$jnilANDuF/HjOUWV6oVpweUWY4.8Ojr.9X6xfVjCzVqoyrsZWT65q',NULL,'2025-03-25 11:31:11.000000','2025-04-27 12:09:34.000000',NULL),(7,'Uday Thajid Ibna Rouf','ibnauday@gmail.com',NULL,'1745734269.jpg',1,1,'$2y$10$/0qN1Qhdhcxu5nBeVf/WfefCiLSuD5WbBXFHGJAEsqcvCFGYmL4da',NULL,'2025-04-27 12:11:09.000000','2025-04-27 12:11:09.000000',NULL),(8,'Thajid Ibna Rouf Uday','uday@onchain.com.bd',NULL,'no_avatar.png',1,2,'$2y$10$00aKMjcUlDRPVnZJCuSt4O0DtpAPYvuTXHi.TbWHOJM0QggOwD1fG',NULL,'2025-05-03 20:10:57.000000','2025-05-05 18:28:24.000000',NULL),(9,'MD NIZAMUL HOQUE','melo.nizam16@gmail.com',NULL,'no_avatar.png',1,2,'$2y$10$vCTdzaMis.RsPzkJ7K53oOQJ6SFeIYW9vT5tEQpu/EipYcVLPa/Fu',NULL,'2025-05-03 20:39:24.000000','2025-05-20 08:03:12.000000',NULL),(10,'Mohaiminul Bashar Raj','mohaiminul252@gmail.com',NULL,'no_avatar.png',1,2,'$2y$10$GO2X6a1irOTnLmgF6LcCwuA35m8w3Uu/jH6O9mfBj/KHXyGsfUTCa',NULL,'2025-05-03 20:48:12.000000','2025-05-03 20:48:12.000000',NULL),(11,'Sraboni Akter','dev.intern.onchain@gmail.com',NULL,'no_avatar.png',1,2,'$2y$10$rW2VAi9tOQoAu1P0bjnRPuuGJWHjMHdU8GyQHbl6O5Diuw7UuadEK',NULL,'2025-05-03 20:57:03.000000','2025-05-03 20:57:03.000000',NULL),(12,'Sabrina Shahrin Rashid Hia','asstmgr.onchain@gmail.com',NULL,'no_avatar.png',1,2,'$2y$10$N4NnMpcb.wIg.4isg3DiX.X05UacrWFgmiMFLYUb5tgOqcy6ziqwO',NULL,'2025-05-04 14:12:58.000000','2025-05-05 16:44:43.000000',NULL),(13,'SM Shamim','dev.intern.onchain2@gmail.com',NULL,'no_avatar.png',1,2,'$2y$10$.w7/mOQW.znxAgJZSbHlFeS6Ec0mvSbppIXhajyw7ABUTGgXv5CxO',NULL,'2025-05-04 15:05:20.000000','2025-05-04 15:05:20.000000',NULL),(14,'Alamgir Kabir','alamgirmohammad_ece@icloud.com',NULL,'no_avatar.png',1,2,'$2y$10$uzpQkUYbN5lZBgjyt1UMyOywoDW0jySKJQ3EwCMXdyR/64obBWuee',NULL,'2025-05-04 15:14:18.000000','2025-05-05 20:13:37.000000',NULL),(15,'Onchain Software & Research limited','onchainsoftwareresearch@gmail.com',NULL,'no_avatar.png',1,3,'$2y$10$Nn7swhUVphkxRZytTENNCes23RJwlU3ANZyTzbyIGFUclWf8uXHqW',NULL,'2025-05-04 23:51:51.000000','2025-05-04 23:51:51.000000',NULL),(16,'Banglachain Foundation','Banglachain@banglachain.com',NULL,'no_avatar.png',1,3,'$2y$10$8cgdGYHe1EoqcK1RhWV83.IBNExErchirVydwc4v15/K1JHzyDgue',NULL,'2025-05-04 23:52:39.000000','2025-05-04 23:52:39.000000',NULL);
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

-- Dump completed on 2025-05-20  6:01:35
