-- MySQL dump 10.13  Distrib 8.0.30, for Win64 (x86_64)
--
-- Host: localhost    Database: presensi_guru_baru
-- ------------------------------------------------------
-- Server version	8.0.30

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
-- Table structure for table `attendances`
--

DROP TABLE IF EXISTS `attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `schedule_id` bigint unsigned NOT NULL,
  `teacher_id` bigint unsigned NOT NULL,
  `attendance_date` date NOT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recorded_by` bigint unsigned NOT NULL,
  `recorded_at` datetime NOT NULL,
  `verified_by` bigint unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendances_schedule_id_attendance_date_unique` (`schedule_id`,`attendance_date`),
  KEY `attendances_teacher_id_foreign` (`teacher_id`),
  KEY `attendances_recorded_by_foreign` (`recorded_by`),
  KEY `attendances_verified_by_foreign` (`verified_by`),
  KEY `attendances_attendance_date_index` (`attendance_date`),
  KEY `attendances_status_index` (`status`),
  CONSTRAINT `attendances_recorded_by_foreign` FOREIGN KEY (`recorded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `attendances_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `attendances_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `attendances_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendances`
--

LOCK TABLES `attendances` WRITE;
/*!40000 ALTER TABLE `attendances` DISABLE KEYS */;
INSERT INTO `attendances` VALUES (1,165,1,'2026-11-22','hadir',2,'2026-11-22 12:54:50',NULL,NULL,'2026-11-22 06:51:58','2026-11-22 06:51:58'),(2,166,2,'2026-11-22','terlambat',3,'2026-11-22 13:18:50',NULL,NULL,'2026-11-22 06:51:58','2026-11-22 06:51:58');
/*!40000 ALTER TABLE `attendances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `actor_user_id` bigint unsigned DEFAULT NULL,
  `event_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `target_id` bigint unsigned DEFAULT NULL,
  `action_result` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `before_data` json DEFAULT NULL,
  `after_data` json DEFAULT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci,
  `occurred_at` datetime(6) NOT NULL,
  `previous_hash` char(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `entry_hash` char(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `audit_logs_entry_hash_unique` (`entry_hash`),
  KEY `audit_logs_actor_user_id_foreign` (`actor_user_id`),
  KEY `audit_logs_occurred_at_index` (`occurred_at`),
  KEY `audit_logs_event_type_index` (`event_type`),
  KEY `audit_logs_target_type_target_id_index` (`target_type`,`target_id`),
  CONSTRAINT `audit_logs_actor_user_id_foreign` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,2,'attendance.recorded','Attendance',1,'success',NULL,'{\"status\": \"hadir\", \"schedule_id\": 165, \"attendance_date\": \"2026-11-22\"}',NULL,'2026-11-22 13:51:58.085341',NULL,'4cecbcc879c36e2817f05b200fe2600017f8184cd46ca098351500d91925d6bc'),(2,3,'attendance.recorded','Attendance',2,'success',NULL,'{\"status\": \"terlambat\", \"schedule_id\": 166, \"attendance_date\": \"2026-11-22\"}',NULL,'2026-11-22 13:51:58.107240','4cecbcc879c36e2817f05b200fe2600017f8184cd46ca098351500d91925d6bc','de1a2ff8296336b68c27648fe2a4ae5353b7e2a46958c1ae21c1758eede948d5'),(3,26,'class_report.created','ClassReport',1,'success',NULL,'{\"report_date\": \"2026-11-22\", \"schedule_id\": 169, \"report_status\": \"kelas_diberi_tugas\", \"follow_up_status\": \"belum\"}',NULL,'2026-11-22 13:51:58.132426','de1a2ff8296336b68c27648fe2a4ae5353b7e2a46958c1ae21c1758eede948d5','d99b0836a75ec07cd19f949ed42890e3aa25e82ea6fc6c0cc61dd97afb8777d2'),(4,26,'class_report.follow_up_changed','ClassReport',1,'success','{\"follow_up_status\": \"belum\"}','{\"follow_up_status\": \"proses\"}','Guru pengganti sedang dihubungi','2026-11-22 13:51:58.140141','d99b0836a75ec07cd19f949ed42890e3aa25e82ea6fc6c0cc61dd97afb8777d2','ebacf5fe6ac8b5d71823892fbde3ec9d5e6d70d27f5b6a2faf3d8d219aca5e1d');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-spatie.permission.cache','a:3:{s:5:\"alias\";a:4:{s:1:\"a\";s:2:\"id\";s:1:\"b\";s:4:\"name\";s:1:\"c\";s:10:\"guard_name\";s:1:\"r\";s:5:\"roles\";}s:11:\"permissions\";a:34:{i:0;a:4:{s:1:\"a\";i:1;s:1:\"b\";s:10:\"users.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:1;a:4:{s:1:\"a\";i:2;s:1:\"b\";s:12:\"users.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:2;a:4:{s:1:\"a\";i:3;s:1:\"b\";s:12:\"users.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:3;a:4:{s:1:\"a\";i:4;s:1:\"b\";s:16:\"users.deactivate\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:4;a:4:{s:1:\"a\";i:5;s:1:\"b\";s:20:\"users.reset_password\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:5;a:4:{s:1:\"a\";i:6;s:1:\"b\";s:13:\"teachers.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:6;a:4:{s:1:\"a\";i:7;s:1:\"b\";s:15:\"teachers.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:7;a:4:{s:1:\"a\";i:8;s:1:\"b\";s:15:\"teachers.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:8;a:4:{s:1:\"a\";i:9;s:1:\"b\";s:19:\"teachers.deactivate\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:9;a:4:{s:1:\"a\";i:10;s:1:\"b\";s:12:\"classes.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:10;a:4:{s:1:\"a\";i:11;s:1:\"b\";s:14:\"classes.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:11;a:4:{s:1:\"a\";i:12;s:1:\"b\";s:14:\"classes.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:12;a:4:{s:1:\"a\";i:13;s:1:\"b\";s:13:\"subjects.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:13;a:4:{s:1:\"a\";i:14;s:1:\"b\";s:18:\"classes.deactivate\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:14;a:4:{s:1:\"a\";i:15;s:1:\"b\";s:15:\"subjects.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:15;a:4:{s:1:\"a\";i:16;s:1:\"b\";s:15:\"subjects.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:16;a:4:{s:1:\"a\";i:17;s:1:\"b\";s:19:\"subjects.deactivate\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:17;a:4:{s:1:\"a\";i:18;s:1:\"b\";s:14:\"schedules.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:18;a:4:{s:1:\"a\";i:19;s:1:\"b\";s:16:\"schedules.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:19;a:4:{s:1:\"a\";i:20;s:1:\"b\";s:16:\"schedules.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:20;a:4:{s:1:\"a\";i:21;s:1:\"b\";s:20:\"schedules.deactivate\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:21;a:4:{s:1:\"a\";i:22;s:1:\"b\";s:19:\"attendance.view_own\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:22;a:4:{s:1:\"a\";i:23;s:1:\"b\";s:19:\"attendance.view_all\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:23;a:4:{s:1:\"a\";i:24;s:1:\"b\";s:17:\"attendance.record\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:2;}}i:24;a:4:{s:1:\"a\";i:25;s:1:\"b\";s:24:\"attendance.change_status\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:25;a:4:{s:1:\"a\";i:26;s:1:\"b\";s:17:\"attendance.delete\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:26;a:4:{s:1:\"a\";i:27;s:1:\"b\";s:14:\"dashboard.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:27;a:4:{s:1:\"a\";i:28;s:1:\"b\";s:18:\"class_reports.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:28;a:4:{s:1:\"a\";i:29;s:1:\"b\";s:20:\"class_reports.create\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:3;}}i:29;a:4:{s:1:\"a\";i:30;s:1:\"b\";s:20:\"class_reports.update\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:2:{i:0;i:1;i:1;i:3;}}i:30;a:4:{s:1:\"a\";i:31;s:1:\"b\";s:15:\"audit_logs.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:31;a:4:{s:1:\"a\";i:32;s:1:\"b\";s:17:\"audit_logs.verify\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:32;a:4:{s:1:\"a\";i:33;s:1:\"b\";s:20:\"security_alerts.view\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}i:33;a:4:{s:1:\"a\";i:34;s:1:\"b\";s:22:\"security_alerts.review\";s:1:\"c\";s:3:\"web\";s:1:\"r\";a:1:{i:0;i:1;}}}s:5:\"roles\";a:3:{i:0;a:3:{s:1:\"a\";i:1;s:1:\"b\";s:13:\"administrator\";s:1:\"c\";s:3:\"web\";}i:1;a:3:{s:1:\"a\";i:2;s:1:\"b\";s:4:\"guru\";s:1:\"c\";s:3:\"web\";}i:2;a:3:{s:1:\"a\";i:3;s:1:\"b\";s:10:\"guru_piket\";s:1:\"c\";s:3:\"web\";}}}',1795416718);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_reports`
--

DROP TABLE IF EXISTS `class_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `class_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `schedule_id` bigint unsigned NOT NULL,
  `report_date` date NOT NULL,
  `reported_by` bigint unsigned NOT NULL,
  `report_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `follow_up_status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `class_reports_reported_by_foreign` (`reported_by`),
  KEY `class_reports_schedule_id_report_date_index` (`schedule_id`,`report_date`),
  KEY `class_reports_report_date_index` (`report_date`),
  CONSTRAINT `class_reports_reported_by_foreign` FOREIGN KEY (`reported_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `class_reports_schedule_id_foreign` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_reports`
--

LOCK TABLES `class_reports` WRITE;
/*!40000 ALTER TABLE `class_reports` DISABLE KEYS */;
INSERT INTO `class_reports` VALUES (1,169,'2026-11-22',26,'kelas_diberi_tugas','Siswa diberi tugas oleh guru piket','proses','2026-11-22 06:51:58','2026-11-22 06:51:58');
/*!40000 ALTER TABLE `class_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `academic_year` varchar(9) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `classes_class_name_academic_year_unique` (`class_name`,`academic_year`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
INSERT INTO `classes` VALUES (1,'IV TKJT','2026/2027',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(2,'V TKJT','2026/2027',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(3,'IV AKL','2026/2027',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(4,'V AKL','2026/2027',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(5,'VI AKL','2026/2027',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(6,'UAT 1','2026/2027',1,'2026-11-22 06:51:58','2026-11-22 06:51:58'),(7,'UAT 2','2026/2027',1,'2026-11-22 06:51:58','2026-11-22 06:51:58'),(8,'UAT 3','2026/2027',1,'2026-11-22 06:51:58','2026-11-22 06:51:58'),(9,'UAT 4','2026/2027',1,'2026-11-22 06:51:58','2026-11-22 06:51:58'),(10,'UAT 5','2026/2027',1,'2026-11-22 06:51:58','2026-11-22 06:51:58'),(11,'UAT 6','2026/2027',1,'2026-11-22 06:51:58','2026-11-22 06:51:58');
/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_11_01_125655_create_permission_tables',1),(5,'2026_11_01_140059_add_username_and_is_active_to_users_table',1),(6,'2026_11_01_140126_create_teachers_table',1),(7,'2026_11_01_140150_create_classes_table',1),(8,'2026_11_01_140214_create_subjects_table',1),(9,'2026_11_01_140228_create_schedules_table',1),(10,'2026_11_01_140242_create_attendances_table',1),(11,'2026_11_01_140251_create_class_reports_table',1),(12,'2026_11_01_140300_create_audit_logs_table',1),(13,'2026_11_01_140311_create_security_alerts_table',1),(14,'2026_11_06_091625_add_type_and_activity_name_to_schedules_table',1);
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
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(2,'App\\Models\\User',2),(2,'App\\Models\\User',3),(2,'App\\Models\\User',4),(2,'App\\Models\\User',5),(2,'App\\Models\\User',6),(2,'App\\Models\\User',7),(2,'App\\Models\\User',8),(2,'App\\Models\\User',9),(2,'App\\Models\\User',10),(2,'App\\Models\\User',11),(2,'App\\Models\\User',12),(2,'App\\Models\\User',13),(2,'App\\Models\\User',14),(2,'App\\Models\\User',15),(2,'App\\Models\\User',16),(2,'App\\Models\\User',17),(2,'App\\Models\\User',18),(2,'App\\Models\\User',19),(2,'App\\Models\\User',20),(2,'App\\Models\\User',21),(2,'App\\Models\\User',22),(2,'App\\Models\\User',23),(2,'App\\Models\\User',24),(2,'App\\Models\\User',25),(3,'App\\Models\\User',26),(3,'App\\Models\\User',27);
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'users.view','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(2,'users.create','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(3,'users.update','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(4,'users.deactivate','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(5,'users.reset_password','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(6,'teachers.view','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(7,'teachers.create','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(8,'teachers.update','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(9,'teachers.deactivate','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(10,'classes.view','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(11,'classes.create','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(12,'classes.update','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(13,'subjects.view','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(14,'classes.deactivate','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(15,'subjects.create','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(16,'subjects.update','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(17,'subjects.deactivate','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(18,'schedules.view','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(19,'schedules.create','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(20,'schedules.update','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(21,'schedules.deactivate','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(22,'attendance.view_own','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(23,'attendance.view_all','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(24,'attendance.record','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(25,'attendance.change_status','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(26,'attendance.delete','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(27,'dashboard.view','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(28,'class_reports.view','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(29,'class_reports.create','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(30,'class_reports.update','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(31,'audit_logs.view','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(32,'audit_logs.verify','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(33,'security_alerts.view','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(34,'security_alerts.review','web','2026-11-22 06:51:51','2026-11-22 06:51:51');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
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
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(23,1),(25,1),(26,1),(27,1),(28,1),(30,1),(31,1),(32,1),(33,1),(34,1),(22,2),(24,2),(23,3),(25,3),(27,3),(28,3),(29,3),(30,3);
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
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'administrator','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(2,'guru','web','2026-11-22 06:51:51','2026-11-22 06:51:51'),(3,'guru_piket','web','2026-11-22 06:51:51','2026-11-22 06:51:51');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedules`
--

DROP TABLE IF EXISTS `schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` bigint unsigned DEFAULT NULL,
  `class_id` bigint unsigned NOT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'lesson',
  `activity_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `day_of_week` tinyint unsigned NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `schedules_subject_id_foreign` (`subject_id`),
  KEY `schedules_day_of_week_start_time_index` (`day_of_week`,`start_time`),
  KEY `schedules_teacher_id_day_of_week_index` (`teacher_id`,`day_of_week`),
  KEY `schedules_class_id_day_of_week_index` (`class_id`,`day_of_week`),
  CONSTRAINT `schedules_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `schedules_subject_id_foreign` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `schedules_teacher_id_foreign` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=171 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedules`
--

LOCK TABLES `schedules` WRITE;
/*!40000 ALTER TABLE `schedules` DISABLE KEYS */;
INSERT INTO `schedules` VALUES (1,NULL,1,NULL,'activity','Upacara Bendera',6,'07:30:00','08:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(2,20,1,9,'lesson',NULL,6,'08:10:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(3,20,1,9,'lesson',NULL,6,'10:30:00','11:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(4,15,1,13,'lesson',NULL,6,'11:10:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(5,15,1,13,'lesson',NULL,6,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(6,4,1,20,'lesson',NULL,6,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(7,NULL,1,NULL,'activity','Pengajian Ahad',7,'07:30:00','08:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(8,21,1,12,'lesson',NULL,7,'08:10:00','09:30:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(9,8,1,29,'lesson',NULL,7,'09:30:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(10,8,1,29,'lesson',NULL,7,'10:30:00','11:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(11,22,1,15,'lesson',NULL,7,'11:10:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(12,22,1,15,'lesson',NULL,7,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(13,23,1,6,'lesson',NULL,7,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(14,1,1,21,'lesson',NULL,1,'07:30:00','08:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(15,22,1,15,'lesson',NULL,1,'08:50:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(16,22,1,15,'lesson',NULL,1,'10:30:00','11:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(17,14,1,24,'lesson',NULL,1,'11:10:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(18,14,1,24,'lesson',NULL,1,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(19,11,1,30,'lesson',NULL,1,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(20,19,1,14,'lesson',NULL,2,'07:30:00','08:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(21,14,1,24,'lesson',NULL,2,'08:50:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(22,9,1,7,'lesson',NULL,2,'10:30:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(23,9,1,7,'lesson',NULL,2,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(24,4,1,20,'lesson',NULL,2,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(25,7,1,8,'lesson',NULL,3,'07:30:00','09:30:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(26,20,1,28,'lesson',NULL,3,'09:30:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(27,20,1,28,'lesson',NULL,3,'10:30:00','11:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(28,3,1,25,'lesson',NULL,3,'11:10:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(29,3,1,25,'lesson',NULL,3,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(30,23,1,6,'lesson',NULL,3,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(31,24,1,26,'lesson',NULL,4,'07:30:00','08:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(32,12,1,32,'lesson',NULL,4,'08:50:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(33,2,1,19,'lesson',NULL,4,'10:30:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(34,2,1,19,'lesson',NULL,4,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(35,23,1,6,'lesson',NULL,4,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(36,NULL,2,NULL,'activity','Upacara Bendera',6,'07:30:00','08:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(37,24,2,26,'lesson',NULL,6,'08:10:00','09:30:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(38,15,2,13,'lesson',NULL,6,'09:30:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(39,15,2,13,'lesson',NULL,6,'10:30:00','11:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(40,12,2,32,'lesson',NULL,6,'11:10:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(41,12,2,32,'lesson',NULL,6,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(42,20,2,16,'lesson',NULL,6,'13:50:00','14:25:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(43,20,2,9,'lesson',NULL,6,'14:25:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(44,NULL,2,NULL,'activity','Pengajian Ahad',7,'07:30:00','08:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(45,16,2,6,'lesson',NULL,7,'08:10:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(46,5,2,7,'lesson',NULL,7,'10:30:00','11:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(47,20,2,9,'lesson',NULL,7,'11:10:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(48,5,2,7,'lesson',NULL,7,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(49,22,2,31,'lesson',NULL,7,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(50,2,2,19,'lesson',NULL,1,'07:30:00','09:30:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(51,7,2,8,'lesson',NULL,1,'09:30:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(52,7,2,8,'lesson',NULL,1,'10:30:00','11:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(53,22,2,1,'lesson',NULL,1,'11:10:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(54,22,2,1,'lesson',NULL,1,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(55,8,2,29,'lesson',NULL,1,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(56,1,2,21,'lesson',NULL,2,'07:30:00','08:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(57,3,2,25,'lesson',NULL,2,'08:50:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(58,22,2,1,'lesson',NULL,2,'10:30:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(59,22,2,1,'lesson',NULL,2,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(60,11,2,30,'lesson',NULL,2,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(61,22,2,20,'lesson',NULL,3,'07:30:00','08:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(62,18,2,18,'lesson',NULL,3,'08:50:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(63,16,2,6,'lesson',NULL,3,'10:30:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(64,20,2,23,'lesson',NULL,3,'13:15:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(65,22,2,20,'lesson',NULL,4,'07:30:00','08:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(66,22,2,31,'lesson',NULL,4,'08:50:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(67,20,2,16,'lesson',NULL,4,'10:30:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(68,20,2,23,'lesson',NULL,4,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(69,21,2,12,'lesson',NULL,4,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(70,NULL,3,NULL,'activity','Upacara Bendera',6,'07:30:00','08:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(71,19,3,27,'lesson',NULL,6,'08:10:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(72,19,3,11,'lesson',NULL,6,'10:30:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(73,19,3,11,'lesson',NULL,6,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(74,23,3,6,'lesson',NULL,6,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(75,NULL,3,NULL,'activity','Pengajian Ahad',7,'07:30:00','08:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(76,8,3,29,'lesson',NULL,7,'08:10:00','09:30:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(77,15,3,13,'lesson',NULL,7,'09:30:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(78,15,3,13,'lesson',NULL,7,'10:30:00','11:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(79,17,3,2,'lesson',NULL,7,'11:10:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(80,17,3,2,'lesson',NULL,7,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(81,17,3,2,'lesson',NULL,7,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(82,11,3,30,'lesson',NULL,1,'07:30:00','08:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(83,14,3,24,'lesson',NULL,1,'08:50:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(84,2,3,19,'lesson',NULL,1,'10:30:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(85,2,3,19,'lesson',NULL,1,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(86,23,3,6,'lesson',NULL,1,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(87,24,3,26,'lesson',NULL,2,'07:30:00','08:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(88,1,3,21,'lesson',NULL,2,'08:50:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(89,13,3,7,'lesson',NULL,2,'10:30:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(90,13,3,7,'lesson',NULL,2,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(91,23,3,6,'lesson',NULL,2,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(92,19,3,14,'lesson',NULL,3,'07:30:00','08:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(93,3,3,25,'lesson',NULL,3,'08:50:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(94,7,3,8,'lesson',NULL,3,'10:30:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(95,14,3,24,'lesson',NULL,3,'13:15:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(96,17,3,2,'lesson',NULL,4,'07:30:00','08:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(97,18,3,22,'lesson',NULL,4,'08:50:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(98,18,3,22,'lesson',NULL,4,'10:30:00','11:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(99,21,3,12,'lesson',NULL,4,'11:10:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(100,21,3,12,'lesson',NULL,4,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(101,12,3,32,'lesson',NULL,4,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(102,NULL,4,NULL,'activity','Upacara Bendera',6,'07:30:00','08:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(103,17,4,4,'lesson',NULL,6,'08:10:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(104,17,4,4,'lesson',NULL,6,'10:30:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(105,17,4,4,'lesson',NULL,6,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(106,15,4,13,'lesson',NULL,6,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(107,NULL,4,NULL,'activity','Pengajian Ahad',7,'07:30:00','08:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(108,17,4,17,'lesson',NULL,7,'08:10:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(109,21,4,12,'lesson',NULL,7,'10:30:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(110,21,4,12,'lesson',NULL,7,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(111,4,4,10,'lesson',NULL,7,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(112,10,4,5,'lesson',NULL,1,'07:30:00','09:30:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(113,18,4,18,'lesson',NULL,1,'09:30:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(114,18,4,18,'lesson',NULL,1,'10:30:00','11:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(115,1,4,21,'lesson',NULL,1,'11:10:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(116,1,4,21,'lesson',NULL,1,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(117,16,4,6,'lesson',NULL,1,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(118,7,4,8,'lesson',NULL,2,'07:30:00','09:30:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(119,24,4,26,'lesson',NULL,2,'09:30:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(120,24,4,26,'lesson',NULL,2,'10:30:00','11:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(121,11,4,30,'lesson',NULL,2,'11:10:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(122,11,4,30,'lesson',NULL,2,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(123,3,4,25,'lesson',NULL,2,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(124,16,4,6,'lesson',NULL,3,'07:30:00','09:30:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(125,2,4,19,'lesson',NULL,3,'09:30:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(126,2,4,19,'lesson',NULL,3,'10:30:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(127,9,4,7,'lesson',NULL,3,'13:15:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(128,10,4,3,'lesson',NULL,4,'07:30:00','09:30:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(129,8,4,29,'lesson',NULL,4,'09:30:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(130,8,4,29,'lesson',NULL,4,'10:30:00','11:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(131,12,4,32,'lesson',NULL,4,'11:10:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(132,12,4,32,'lesson',NULL,4,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(133,17,4,4,'lesson',NULL,4,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(134,NULL,5,NULL,'activity','Upacara Bendera',6,'07:30:00','08:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(135,12,5,32,'lesson',NULL,6,'08:10:00','09:30:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(136,18,5,18,'lesson',NULL,6,'09:30:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(137,18,5,18,'lesson',NULL,6,'10:30:00','11:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(138,8,5,29,'lesson',NULL,6,'11:10:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(139,8,5,29,'lesson',NULL,6,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(140,17,5,17,'lesson',NULL,6,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(141,NULL,5,NULL,'activity','Pengajian Ahad',7,'07:30:00','08:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(142,15,5,13,'lesson',NULL,7,'08:10:00','09:30:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(143,3,5,25,'lesson',NULL,7,'09:30:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(144,3,5,25,'lesson',NULL,7,'10:30:00','11:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(145,16,5,6,'lesson',NULL,7,'11:10:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(146,16,5,6,'lesson',NULL,7,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(147,7,5,8,'lesson',NULL,7,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(148,17,5,17,'lesson',NULL,1,'07:30:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(149,17,5,4,'lesson',NULL,1,'10:30:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(150,17,5,4,'lesson',NULL,1,'13:15:00','13:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(151,10,5,5,'lesson',NULL,1,'13:50:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(152,10,5,5,'lesson',NULL,2,'07:30:00','09:30:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(153,21,5,12,'lesson',NULL,2,'09:30:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(154,21,5,12,'lesson',NULL,2,'10:30:00','11:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(155,7,5,8,'lesson',NULL,2,'11:10:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(156,5,5,7,'lesson',NULL,2,'13:15:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(157,11,5,30,'lesson',NULL,3,'07:30:00','08:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(158,6,5,26,'lesson',NULL,3,'08:50:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(159,4,5,10,'lesson',NULL,3,'10:30:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(160,2,5,19,'lesson',NULL,3,'13:15:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(161,16,5,6,'lesson',NULL,4,'07:30:00','09:30:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(162,17,5,4,'lesson',NULL,4,'09:30:00','10:10:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(163,17,5,4,'lesson',NULL,4,'10:30:00','11:50:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(164,1,5,21,'lesson',NULL,4,'13:15:00','15:00:00',0,'2026-11-22 06:51:52','2026-11-22 06:51:58'),(165,1,6,1,'lesson',NULL,7,'12:51:50','13:36:50',1,'2026-11-22 06:51:58','2026-11-22 06:51:58'),(166,2,7,1,'lesson',NULL,7,'13:06:50','13:49:50',1,'2026-11-22 06:51:58','2026-11-22 06:51:58'),(167,3,8,1,'lesson',NULL,7,'13:48:50','14:33:50',1,'2026-11-22 06:51:58','2026-11-22 06:51:58'),(168,4,9,1,'lesson',NULL,7,'13:26:50','14:11:50',1,'2026-11-22 06:51:58','2026-11-22 06:51:58'),(169,5,10,1,'lesson',NULL,7,'13:31:50','14:16:50',1,'2026-11-22 06:51:58','2026-11-22 06:51:58'),(170,1,11,1,'lesson',NULL,7,'14:01:50','14:46:50',1,'2026-11-22 06:51:58','2026-11-22 06:51:58');
/*!40000 ALTER TABLE `schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_alerts`
--

DROP TABLE IF EXISTS `security_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `security_alerts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rule_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `severity` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `related_user_id` bigint unsigned DEFAULT NULL,
  `related_audit_log_id` bigint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `detected_at` datetime NOT NULL,
  `status` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'baru',
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `security_alerts_related_user_id_foreign` (`related_user_id`),
  KEY `security_alerts_related_audit_log_id_foreign` (`related_audit_log_id`),
  KEY `security_alerts_reviewed_by_foreign` (`reviewed_by`),
  KEY `security_alerts_status_index` (`status`),
  KEY `security_alerts_rule_code_index` (`rule_code`),
  KEY `security_alerts_detected_at_index` (`detected_at`),
  CONSTRAINT `security_alerts_related_audit_log_id_foreign` FOREIGN KEY (`related_audit_log_id`) REFERENCES `audit_logs` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `security_alerts_related_user_id_foreign` FOREIGN KEY (`related_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `security_alerts_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_alerts`
--

LOCK TABLES `security_alerts` WRITE;
/*!40000 ALTER TABLE `security_alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `security_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subjects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subject_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `subjects_subject_name_unique` (`subject_name`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
INSERT INTO `subjects` VALUES (1,'Administrasi Sistem Jaringan',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(2,'Akuntansi Dasar',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(3,'Akuntansi Keuangan',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(4,'Akuntansi Perusahaan Dagang',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(5,'Akuntansi Perusahaan dan Manufaktur',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(6,'Amtsilati',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(7,'Bahasa Inggris',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(8,'Bahasa Indonesia',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(9,'Desain Grafis',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(10,'Digital Marketing',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(11,'Ekonomi Bisnis',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(12,'Fiqih',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(13,'Hadits',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(14,'Informatika',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(15,'Jaringan Dasar',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(16,'Keamanan Jaringan',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(17,'Komputer Akuntansi',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(18,'Kreatif dan Inovasi Kewirausahaan',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(19,'Matematika',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(20,'Pemrograman Web',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(21,'Pendidikan Pancasila',1,'2026-11-22 06:51:51','2026-11-22 06:51:51'),(22,'Perbankan Dasar',1,'2026-11-22 06:51:52','2026-11-22 06:51:52'),(23,'Perencanaan dan Pengalamatan Jaringan',1,'2026-11-22 06:51:52','2026-11-22 06:51:52'),(24,'Projek Ilmu Pengetahuan Alam dan Sosial',1,'2026-11-22 06:51:52','2026-11-22 06:51:52'),(25,'Sejarah',1,'2026-11-22 06:51:52','2026-11-22 06:51:52'),(26,'SKI',1,'2026-11-22 06:51:52','2026-11-22 06:51:52'),(27,'Spreadsheet',1,'2026-11-22 06:51:52','2026-11-22 06:51:52'),(28,'Sistem Komputer',1,'2026-11-22 06:51:52','2026-11-22 06:51:52'),(29,'Tahsin Al-Qur’an',1,'2026-11-22 06:51:52','2026-11-22 06:51:52'),(30,'Tasawuf',1,'2026-11-22 06:51:52','2026-11-22 06:51:52'),(31,'Teknologi Jaringan Kabel dan Nirkabel',1,'2026-11-22 06:51:52','2026-11-22 06:51:52'),(32,'Tauhid',1,'2026-11-22 06:51:52','2026-11-22 06:51:52');
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teachers`
--

DROP TABLE IF EXISTS `teachers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `teachers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `teacher_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `teachers_teacher_code_unique` (`teacher_code`),
  UNIQUE KEY `teachers_user_id_unique` (`user_id`),
  CONSTRAINT `teachers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teachers`
--

LOCK TABLES `teachers` WRITE;
/*!40000 ALTER TABLE `teachers` DISABLE KEYS */;
INSERT INTO `teachers` VALUES (1,2,'RS001','Alis Tati Hartati, S.Pd',1,'2026-11-22 06:51:51','2026-11-22 06:51:52'),(2,3,'RS002','Ani Nuriyani, S.Pd',1,'2026-11-22 06:51:51','2026-11-22 06:51:53'),(3,4,'RS003','Anisa Amalia Wahyuni',1,'2026-11-22 06:51:51','2026-11-22 06:51:53'),(4,5,'RS004','Arfida Nuha Piinata',1,'2026-11-22 06:51:51','2026-11-22 06:51:53'),(5,6,'RS005','Devi Fauziah, S.Pd',1,'2026-11-22 06:51:51','2026-11-22 06:51:53'),(6,7,'RS006','Eli Herlina Khoirunnisa, S.Pd.I',1,'2026-11-22 06:51:51','2026-11-22 06:51:53'),(7,8,'RS007','Etih Pandu Windari, M.Pd',1,'2026-11-22 06:51:51','2026-11-22 06:51:54'),(8,9,'RS008','Halimah, S.Ag',1,'2026-11-22 06:51:51','2026-11-22 06:51:54'),(9,10,'RS009','Iin Parlina, S.Pd',1,'2026-11-22 06:51:51','2026-11-22 06:51:54'),(10,11,'RS010','Ismia Hazar Ridwan, S.E',1,'2026-11-22 06:51:51','2026-11-22 06:51:54'),(11,12,'RS011','Laily',1,'2026-11-22 06:51:51','2026-11-22 06:51:54'),(12,13,'RS012','Lisna Mardiana Solihat, S.Ag',1,'2026-11-22 06:51:51','2026-11-22 06:51:55'),(13,14,'RS013','Maya Mahdiyah, M.Pd',1,'2026-11-22 06:51:51','2026-11-22 06:51:55'),(14,15,'RS014','Mina Fakhriyati Masykur, S.Pd',1,'2026-11-22 06:51:51','2026-11-22 06:51:55'),(15,16,'RS015','Nurotul Insani, S.Ag',1,'2026-11-22 06:51:51','2026-11-22 06:51:55'),(16,17,'RS016','Nurfitri',1,'2026-11-22 06:51:51','2026-11-22 06:51:55'),(17,18,'RS017','Rosi Rahmayanti, S.Ak',1,'2026-11-22 06:51:51','2026-11-22 06:51:56'),(18,19,'RS018','Siti Adawiyah, S.Ak',1,'2026-11-22 06:51:51','2026-11-22 06:51:56'),(19,20,'RS019','Tika Rahmawati, S.M',1,'2026-11-22 06:51:51','2026-11-22 06:51:56'),(20,21,'RS020','Vita Fatimah, A.Md',1,'2026-11-22 06:51:51','2026-11-22 06:51:56'),(21,22,'RS021','Widia Nurkamal, S.Ag',1,'2026-11-22 06:51:51','2026-11-22 06:51:57'),(22,23,'RS022','Yusfiah Latifah, S.Kom',1,'2026-11-22 06:51:51','2026-11-22 06:51:57'),(23,24,'RS023','Yeni Fadillah',1,'2026-11-22 06:51:51','2026-11-22 06:51:57'),(24,25,'RS024','Yuli Sri Wulandari, S.Pd.I',1,'2026-11-22 06:51:51','2026-11-22 06:51:57');
/*!40000 ALTER TABLE `teachers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrator','admin','admin@presensiguru.local',NULL,'$2y$12$8pgJ7Hq4VT5t1i7x5NjABOVsybU91kAl6nz8o1yHW7WxgK3hL58ni',1,NULL,'2026-11-22 06:51:52','2026-11-22 06:51:52'),(2,'Alis Tati Hartati, S.Pd','rs001','rs001@presensiguru.local',NULL,'$2y$12$ReaXn4lOLcNN/gDkFZ5UVe2zx6R4nkbkFBOU4AmvxOE0e4lufY0Ze',1,NULL,'2026-11-22 06:51:52','2026-11-22 06:51:52'),(3,'Ani Nuriyani, S.Pd','rs002','rs002@presensiguru.local',NULL,'$2y$12$UwNKK..MyouwUC8emuiCCuv1jUByzpmpn19p2t5IK/KZ0Gaoqonem',1,NULL,'2026-11-22 06:51:53','2026-11-22 06:51:53'),(4,'Anisa Amalia Wahyuni','rs003','rs003@presensiguru.local',NULL,'$2y$12$Te7AZhGpnPJDk..6HkNcweR3HHuLpP3aRPQEe6pxxYG55xAJ6y7aS',1,NULL,'2026-11-22 06:51:53','2026-11-22 06:51:53'),(5,'Arfida Nuha Piinata','rs004','rs004@presensiguru.local',NULL,'$2y$12$/HnoMu.1rv9GsV.NkCPjYO1z7bmi77A3/KxJsQwXGpQ9IwwK5ojBW',1,NULL,'2026-11-22 06:51:53','2026-11-22 06:51:53'),(6,'Devi Fauziah, S.Pd','rs005','rs005@presensiguru.local',NULL,'$2y$12$FHQY2nTvcKQk/Rs5sMD99.vSWCzZV2n0xVOLcBS8tlaRmrDoBCdou',1,NULL,'2026-11-22 06:51:53','2026-11-22 06:51:53'),(7,'Eli Herlina Khoirunnisa, S.Pd.I','rs006','rs006@presensiguru.local',NULL,'$2y$12$JbD8mzikkDqMwrENe20PQ.ghrmdb2V38vJJVxnVQcq7.yed1XM9uS',1,NULL,'2026-11-22 06:51:53','2026-11-22 06:51:53'),(8,'Etih Pandu Windari, M.Pd','rs007','rs007@presensiguru.local',NULL,'$2y$12$ojY1gYXRM8QglL7QE.7c6u9ggQiO8.XzGn6ZkCnkQM2pZ9oatCqhS',1,NULL,'2026-11-22 06:51:54','2026-11-22 06:51:54'),(9,'Halimah, S.Ag','rs008','rs008@presensiguru.local',NULL,'$2y$12$JbPLKpkDtUhTqXTPmlVzhuUfh9ku5K/vLVPqJSp73mj2P9nSd8i7y',1,NULL,'2026-11-22 06:51:54','2026-11-22 06:51:54'),(10,'Iin Parlina, S.Pd','rs009','rs009@presensiguru.local',NULL,'$2y$12$qxDiNsxh67QkV5UfyDiLXOmGbxnZZC6T7AlA2bxId4KPORKIQOdQ2',1,NULL,'2026-11-22 06:51:54','2026-11-22 06:51:54'),(11,'Ismia Hazar Ridwan, S.E','rs010','rs010@presensiguru.local',NULL,'$2y$12$lzeil0CsMCf2/t09Tz0cT.SSunpykjqWNvy.81WW40EFulwNVOCP2',1,NULL,'2026-11-22 06:51:54','2026-11-22 06:51:54'),(12,'Laily','rs011','rs011@presensiguru.local',NULL,'$2y$12$BGXRH7hC9StV7b5fi5//ruFwM8DjT3i.X2b4sLANY/L7A41Be9Ydi',1,NULL,'2026-11-22 06:51:54','2026-11-22 06:51:54'),(13,'Lisna Mardiana Solihat, S.Ag','rs012','rs012@presensiguru.local',NULL,'$2y$12$2jaHcxXTgd6uPF1GybMMcODnnBWsJFRM412vrCC2/4JC0PRTYRRDu',1,NULL,'2026-11-22 06:51:55','2026-11-22 06:51:55'),(14,'Maya Mahdiyah, M.Pd','rs013','rs013@presensiguru.local',NULL,'$2y$12$gKdOVHyG1dq6O3mNKKtWo.R99LFA9osDVBKx6oMQNlcgmTQyfqFO2',1,NULL,'2026-11-22 06:51:55','2026-11-22 06:51:55'),(15,'Mina Fakhriyati Masykur, S.Pd','rs014','rs014@presensiguru.local',NULL,'$2y$12$PkrWaP.4sMzz46XMAissGe9T7WvKer0tbWNA3fadNuAlcICGK4gga',1,NULL,'2026-11-22 06:51:55','2026-11-22 06:51:55'),(16,'Nurotul Insani, S.Ag','rs015','rs015@presensiguru.local',NULL,'$2y$12$ljL4.ZzoFSPEDHDnKbhQZuQAOu8z8nO1Vz.TQfOzTkj.zzo07BGf2',1,NULL,'2026-11-22 06:51:55','2026-11-22 06:51:55'),(17,'Nurfitri','rs016','rs016@presensiguru.local',NULL,'$2y$12$zyE9leb0xKI9QjVDstdiRe/Sj/6PlSdb7HwR9Z0fPAy8Ntz7D83Ga',1,NULL,'2026-11-22 06:51:55','2026-11-22 06:51:55'),(18,'Rosi Rahmayanti, S.Ak','rs017','rs017@presensiguru.local',NULL,'$2y$12$Re5NmNrBsuAmnfQK267hWOj93Xkl2fmeuRu6mK39Gil.8/PUa7Uj6',1,NULL,'2026-11-22 06:51:56','2026-11-22 06:51:56'),(19,'Siti Adawiyah, S.Ak','rs018','rs018@presensiguru.local',NULL,'$2y$12$m6GBjvcceHyJHncAhDpUKOToJjx3Lna/Pw0hjMNVP367IIngQPzPK',1,NULL,'2026-11-22 06:51:56','2026-11-22 06:51:56'),(20,'Tika Rahmawati, S.M','rs019','rs019@presensiguru.local',NULL,'$2y$12$OYdc8i1A6apCgDN.kVk8ueXghs4g.hAZ7vXYotw59JfLOe3EhNoWi',1,NULL,'2026-11-22 06:51:56','2026-11-22 06:51:56'),(21,'Vita Fatimah, A.Md','rs020','rs020@presensiguru.local',NULL,'$2y$12$a2ciPHjc0pSMiPFtVBv26ODxEDL3bHLxUo4H1fisxLz4b4TFPcu5y',1,NULL,'2026-11-22 06:51:56','2026-11-22 06:51:56'),(22,'Widia Nurkamal, S.Ag','rs021','rs021@presensiguru.local',NULL,'$2y$12$BKC0kDlb9S.lwMfrp7esGOgmVVxbJIk9c9WIggS8CaMk5y0sYYK0S',1,NULL,'2026-11-22 06:51:57','2026-11-22 06:51:57'),(23,'Yusfiah Latifah, S.Kom','rs022','rs022@presensiguru.local',NULL,'$2y$12$UgG9x16FiY/U5Zn6z65cPu0UCqIRncaSI64.swmu4ijawWQhKpY0e',1,NULL,'2026-11-22 06:51:57','2026-11-22 06:51:57'),(24,'Yeni Fadillah','rs023','rs023@presensiguru.local',NULL,'$2y$12$mg0D50.hYqSz6XEFKcBUsu6.m6eLyp9QHKeYuIcVil.kyZrgX7DzO',1,NULL,'2026-11-22 06:51:57','2026-11-22 06:51:57'),(25,'Yuli Sri Wulandari, S.Pd.I','rs024','rs024@presensiguru.local',NULL,'$2y$12$xNQpIgFpMjjWeMCINN.srelmNCm8nNhht/B3w0bzea4TBtQxhsIUW',1,NULL,'2026-11-22 06:51:57','2026-11-22 06:51:57'),(26,'Guru Piket 1','piket1','piket1@presensiguru.local',NULL,'$2y$12$WbvM3zIA8RmyFSn/1SMDy.CvU/xwZKqatWodsh26WXhzCbFfj4rxO',1,NULL,'2026-11-22 06:51:57','2026-11-22 06:51:57'),(27,'Guru Piket 2','piket2','piket2@presensiguru.local',NULL,'$2y$12$5Sl5xpNKvV1/RkDursHj/OEfKSCC2X4sdp9fc5.LKkO0eADvE16K2',1,NULL,'2026-11-22 06:51:58','2026-11-22 06:51:58');
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

-- Dump completed on 2026-11-23 14:36:25
