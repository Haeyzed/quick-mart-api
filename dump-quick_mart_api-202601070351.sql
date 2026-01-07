-- MySQL dump 10.13  Distrib 8.0.19, for Win64 (x86_64)
--
-- Host: localhost    Database: quick_mart_api
-- ------------------------------------------------------
-- Server version	8.0.41

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
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `account_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `initial_balance` double DEFAULT NULL,
  `total_balance` double NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `is_default` tinyint(1) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Bank Account',
  `parent_account_id` bigint unsigned DEFAULT NULL,
  `is_payment` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `accounts_account_no_index` (`account_no`),
  KEY `accounts_parent_account_id_index` (`parent_account_id`),
  KEY `accounts_is_default_index` (`is_default`),
  KEY `accounts_is_active_index` (`is_active`),
  KEY `accounts_type_index` (`type`),
  CONSTRAINT `accounts_parent_account_id_foreign` FOREIGN KEY (`parent_account_id`) REFERENCES `accounts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `accounts`
--

LOCK TABLES `accounts` WRITE;
/*!40000 ALTER TABLE `accounts` DISABLE KEYS */;
INSERT INTO `accounts` VALUES (1,'019912229','Sales Account',0,0,'This is the default account.',1,1,NULL,'Bank Account',NULL,1,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL);
/*!40000 ALTER TABLE `accounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `activity_logs_date_index` (`date`),
  KEY `activity_logs_user_id_index` (`user_id`),
  KEY `activity_logs_reference_no_index` (`reference_no`),
  CONSTRAINT `activity_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `adjustments`
--

DROP TABLE IF EXISTS `adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `adjustments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `total_qty` double NOT NULL,
  `item` int NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `adjustments_reference_no_index` (`reference_no`),
  KEY `adjustments_warehouse_id_index` (`warehouse_id`),
  KEY `adjustments_created_at_index` (`created_at`),
  CONSTRAINT `adjustments_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `adjustments`
--

LOCK TABLES `adjustments` WRITE;
/*!40000 ALTER TABLE `adjustments` DISABLE KEYS */;
/*!40000 ALTER TABLE `adjustments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendances`
--

DROP TABLE IF EXISTS `attendances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `checkin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `checkout` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendances_date_index` (`date`),
  KEY `attendances_employee_id_index` (`employee_id`),
  KEY `attendances_user_id_index` (`user_id`),
  KEY `attendances_status_index` (`status`),
  CONSTRAINT `attendances_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `attendances_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendances`
--

LOCK TABLES `attendances` WRITE;
/*!40000 ALTER TABLE `attendances` DISABLE KEYS */;
/*!40000 ALTER TABLE `attendances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `barcodes`
--

DROP TABLE IF EXISTS `barcodes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `barcodes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `width` decimal(22,4) DEFAULT NULL,
  `height` decimal(22,4) DEFAULT NULL,
  `paper_width` decimal(22,4) DEFAULT NULL,
  `paper_height` decimal(22,4) DEFAULT NULL,
  `top_margin` decimal(22,4) DEFAULT NULL,
  `left_margin` decimal(22,4) DEFAULT NULL,
  `row_distance` decimal(22,4) DEFAULT NULL,
  `col_distance` decimal(22,4) DEFAULT NULL,
  `stickers_in_one_row` int DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_continuous` tinyint(1) NOT NULL DEFAULT '0',
  `stickers_in_one_sheet` int DEFAULT NULL,
  `is_custom` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `barcodes_is_default_index` (`is_default`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `barcodes`
--

LOCK TABLES `barcodes` WRITE;
/*!40000 ALTER TABLE `barcodes` DISABLE KEYS */;
INSERT INTO `barcodes` VALUES (1,'20 Labels per Sheet','Sheet Size: 8.5\" x 11\", Label Size: 4\" x 1\", Labels per sheet: 20',4.0000,1.0000,8.5000,11.0000,0.5000,0.1250,0.0000,0.1875,2,0,0,20,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(2,'30 Labels per sheet','Sheet Size: 8.5\" x 11\", Label Size: 2.625\" x 1\", Labels per sheet: 30',2.6250,1.0000,8.5000,11.0000,0.5000,0.1880,0.0000,0.1250,3,0,0,30,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(3,'32 Labels per sheet','Sheet Size: 8.5\" x 11\", Label Size: 2\" x 1.25\", Labels per sheet: 32',2.0000,1.2500,8.5000,11.0000,0.5000,0.2500,0.0000,0.0000,4,0,0,32,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(4,'40 Labels per sheet','Sheet Size: 8.5\" x 11\", Label Size: 2\" x 1\", Labels per sheet: 40',2.0000,1.0000,8.5000,11.0000,0.5000,0.2500,0.0000,0.0000,4,0,0,40,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(5,'50 Labels per Sheet','Sheet Size: 8.5\" x 11\", Label Size: 1.5\" x 1\", Labels per sheet: 50',1.5000,1.0000,8.5000,11.0000,0.5000,0.5000,0.0000,0.0000,5,0,0,50,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(6,'Continuous Rolls - 31.75mm x 25.4mm','Label Size: 31.75mm x 25.4mm, Gap: 3.18mm',1.2500,1.0000,1.2500,0.0000,0.1250,0.0000,0.1250,0.0000,1,0,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15');
/*!40000 ALTER TABLE `barcodes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `billers`
--

DROP TABLE IF EXISTS `billers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `billers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vat_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `billers_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `billers`
--

LOCK TABLES `billers` WRITE;
/*!40000 ALTER TABLE `billers` DISABLE KEYS */;
INSERT INTO `billers` VALUES (1,'Test Biller',NULL,'Test Company',NULL,'test@gmail.com','12312','Test address','Test City',NULL,NULL,NULL,1,'2026-01-03 02:00:15','2026-01-03 02:00:15');
/*!40000 ALTER TABLE `billers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `blogs`
--

DROP TABLE IF EXISTS `blogs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `blogs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `thumbnail` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `youtube` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `og_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blogs_slug_unique` (`slug`),
  KEY `blogs_user_id_index` (`user_id`),
  KEY `blogs_slug_index` (`slug`),
  CONSTRAINT `blogs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `blogs`
--

LOCK TABLES `blogs` WRITE;
/*!40000 ALTER TABLE `blogs` DISABLE KEYS */;
/*!40000 ALTER TABLE `blogs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `brands`
--

DROP TABLE IF EXISTS `brands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `brands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_description` text COLLATE utf8mb4_unicode_ci,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `brands_slug_index` (`slug`),
  KEY `brands_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=110 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `brands`
--

LOCK TABLES `brands` WRITE;
/*!40000 ALTER TABLE `brands` DISABLE KEYS */;
INSERT INTO `brands` VALUES (1,'Apple','20240114102326.png','https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg',NULL,'Consumer electronics and software company','apple',1,'2026-01-03 02:00:15','2026-01-07 01:44:31',NULL),(2,'Samsung','brands/b4ab426f-8ddc-4b0f-be71-13c2a0177e17.jpg','https://upload.wikimedia.org/wikipedia/commons/2/24/Samsung_Logo.svg',NULL,'Global electronics and technology brand','samsung',1,'2026-01-03 02:00:15','2026-01-07 01:11:24',NULL),(3,'Huawei','20240114102512.png','https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','huawei',1,'2026-01-03 02:00:15','2026-01-07 00:42:27',NULL),(4,'Xiaomi','20240114103640.png',NULL,NULL,NULL,NULL,1,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(5,'Whirlpool','20240114103701.png',NULL,NULL,NULL,NULL,1,'2026-01-03 02:00:15','2026-01-07 00:45:42','2026-01-07 00:45:42'),(6,'Nestle','20240114103717.png',NULL,NULL,NULL,NULL,1,'2026-01-03 02:00:15','2026-01-07 00:46:06','2026-01-07 00:46:06'),(7,'Kraft','20240114103851.png',NULL,NULL,NULL,NULL,1,'2026-01-03 02:00:15','2026-01-07 00:47:15','2026-01-07 00:47:15'),(8,'Kellogs','20240114103906.png',NULL,NULL,NULL,NULL,1,'2026-01-03 02:00:15','2026-01-07 00:47:15','2026-01-07 00:47:15'),(9,'Samsung Phone','brands/28cb8310-ab8b-4338-bc58-818a766a72bf.jpg','http://quick-mart-api.test/storage/brands/28cb8310-ab8b-4338-bc58-818a766a72bf.jpg','Samsung','Samsung','samsung-phone',1,'2026-01-07 00:25:23','2026-01-07 00:32:27','2026-01-07 00:32:27'),(10,'Google',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','google',1,'2026-01-07 00:42:27','2026-01-07 00:46:35','2026-01-07 00:46:35'),(11,'Microsoft',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','microsoft',1,'2026-01-07 00:42:27','2026-01-07 00:47:22','2026-01-07 00:47:22'),(12,'Amazon',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','amazon',1,'2026-01-07 00:42:27','2026-01-07 00:46:18','2026-01-07 00:46:18'),(13,'Nvidia',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','nvidia',1,'2026-01-07 00:42:27','2026-01-07 00:46:06','2026-01-07 00:46:06'),(14,'Facebook',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','facebook',1,'2026-01-07 00:42:27','2026-01-07 00:46:35','2026-01-07 00:46:35'),(15,'Instagram',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','instagram',1,'2026-01-07 00:42:27','2026-01-07 00:47:15','2026-01-07 00:47:15'),(16,'McDonald’s',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','mcdonalds',1,'2026-01-07 00:42:27','2026-01-07 00:47:22','2026-01-07 00:47:22'),(17,'Oracle',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','oracle',1,'2026-01-07 00:42:27','2026-01-07 00:46:06','2026-01-07 00:46:06'),(18,'Visa',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','visa',1,'2026-01-07 00:42:27','2026-01-07 00:45:42','2026-01-07 00:45:42'),(19,'Tencent',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','tencent',1,'2026-01-07 00:42:27','2026-01-07 00:45:24','2026-01-07 00:45:24'),(20,'Mastercard',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','mastercard',1,'2026-01-07 00:42:27','2026-01-07 00:47:22','2026-01-07 00:47:22'),(21,'IBM',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','ibm',1,'2026-01-07 00:42:27','2026-01-07 00:46:35','2026-01-07 00:46:35'),(22,'Coca-Cola',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','coca-cola',1,'2026-01-07 00:42:27','2026-01-07 00:46:35','2026-01-07 00:46:35'),(23,'Walmart',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','walmart',1,'2026-01-07 00:42:27','2026-01-07 00:45:42','2026-01-07 00:45:42'),(24,'Netflix',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','netflix',1,'2026-01-07 00:42:27','2026-01-07 00:46:06','2026-01-07 00:46:06'),(25,'Louis Vuitton',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','louis-vuitton',1,'2026-01-07 00:42:27','2026-01-07 00:47:15','2026-01-07 00:47:15'),(26,'Hermes',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','hermes',1,'2026-01-07 00:42:27','2026-01-07 00:46:35','2026-01-07 00:46:35'),(27,'T-Mobile',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','t-mobile',1,'2026-01-07 00:42:27','2026-01-07 00:45:24','2026-01-07 00:45:24'),(28,'Accenture',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','accenture',1,'2026-01-07 00:42:27','2026-01-07 00:46:18','2026-01-07 00:46:18'),(29,'Costco',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','costco',1,'2026-01-07 00:42:27','2026-01-07 00:46:35','2026-01-07 00:46:35'),(30,'Aramco',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','aramco',1,'2026-01-07 00:42:27','2026-01-07 00:46:18','2026-01-07 00:46:18'),(31,'SAP',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','sap',1,'2026-01-07 00:42:27','2026-01-07 00:46:06','2026-01-07 00:46:06'),(32,'Verizon',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','verizon',1,'2026-01-07 00:42:27','2026-01-07 00:45:24','2026-01-07 00:45:24'),(33,'The Home Depot',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','the-home-depot',1,'2026-01-07 00:42:27','2026-01-07 00:45:24','2026-01-07 00:45:24'),(34,'YouTube',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','youtube',1,'2026-01-07 00:42:27','2026-01-07 00:45:42','2026-01-07 00:45:42'),(35,'AT&T',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','att',1,'2026-01-07 00:42:27','2026-01-07 00:46:18','2026-01-07 00:46:18'),(36,'Tesla',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','tesla',1,'2026-01-07 00:42:27','2026-01-07 00:45:24','2026-01-07 00:45:24'),(37,'Alibaba',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','alibaba',1,'2026-01-07 00:42:27','2026-01-07 00:46:18','2026-01-07 00:46:18'),(38,'Adobe',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','adobe',1,'2026-01-07 00:42:27','2026-01-07 00:46:18','2026-01-07 00:46:18'),(39,'LinkedIn',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','linkedin',1,'2026-01-07 00:42:27','2026-01-07 00:47:15','2026-01-07 00:47:15'),(40,'TikTok',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','tiktok',1,'2026-01-07 00:42:27','2026-01-07 00:45:24','2026-01-07 00:45:24'),(41,'Moutai',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','moutai',1,'2026-01-07 00:42:27','2026-01-07 00:46:06','2026-01-07 00:46:06'),(42,'Starbucks',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','starbucks',1,'2026-01-07 00:42:27','2026-01-07 00:45:24','2026-01-07 00:45:24'),(43,'Salesforce',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','salesforce',1,'2026-01-07 00:42:27','2026-01-07 00:46:06','2026-01-07 00:46:06'),(44,'Cisco',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','cisco',1,'2026-01-07 00:42:27','2026-01-07 00:46:34','2026-01-07 00:46:34'),(45,'American Express',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','american-express',1,'2026-01-07 00:42:27','2026-01-07 00:46:18','2026-01-07 00:46:18'),(46,'Snapdragon',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','snapdragon',1,'2026-01-07 00:42:27','2026-01-07 00:46:06','2026-01-07 00:46:06'),(47,'Marlboro',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','marlboro',1,'2026-01-07 00:42:27','2026-01-07 00:47:15','2026-01-07 00:47:15'),(48,'ServiceNow',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','servicenow',1,'2026-01-07 00:42:27','2026-01-07 00:46:06','2026-01-07 00:46:06'),(49,'Chanel',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','chanel',1,'2026-01-07 00:42:27','2026-01-07 00:46:34','2026-01-07 00:46:34'),(50,'Texas Instruments',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','texas-instruments',1,'2026-01-07 00:42:27','2026-01-07 00:45:24','2026-01-07 00:45:24'),(51,'Intuit',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','intuit',1,'2026-01-07 00:42:27','2026-01-07 00:47:15','2026-01-07 00:47:15'),(52,'TCS',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','tcs',1,'2026-01-07 00:42:27','2026-01-07 00:45:24','2026-01-07 00:45:24'),(53,'ADP',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','adp',1,'2026-01-07 00:42:27','2026-01-07 00:46:18','2026-01-07 00:46:18'),(54,'AMD',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','amd',1,'2026-01-07 00:42:27','2026-01-07 00:46:18','2026-01-07 00:46:18'),(55,'UPS',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','ups',1,'2026-01-07 00:42:27','2026-01-07 00:45:24','2026-01-07 00:45:24'),(56,'J.P. Morgan',NULL,'https://vectorseek.com/top-100-famous-brand-logos-from-the-most-valuable-companies-of-2025/',NULL,'Leading global tech brand','jp-morgan',1,'2026-01-07 00:42:27','2026-01-07 00:47:15','2026-01-07 00:47:15'),(57,'Microsoft',NULL,'https://upload.wikimedia.org/wikipedia/commons/4/44/Microsoft_logo.svg',NULL,'Software, cloud, and technology services provider','microsoft',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(58,'Google',NULL,'https://upload.wikimedia.org/wikipedia/commons/2/2f/Google_2015_logo.svg',NULL,'Search engine and technology company','google',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(59,'Amazon',NULL,'https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg',NULL,'E-commerce and cloud computing company','amazon',1,'2026-01-07 01:11:24','2026-01-07 01:47:30','2026-01-07 01:47:30'),(60,'Meta',NULL,'https://upload.wikimedia.org/wikipedia/commons/0/05/Meta_Platforms_Inc._logo.svg',NULL,'Social media and virtual reality company','meta',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(61,'Netflix',NULL,'https://upload.wikimedia.org/wikipedia/commons/0/08/Netflix_2015_logo.svg',NULL,'Online video streaming platform','netflix',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(62,'Nike',NULL,'https://upload.wikimedia.org/wikipedia/commons/a/a6/Logo_NIKE.svg',NULL,'Sportswear and athletic apparel brand','nike',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(63,'Adidas',NULL,'https://upload.wikimedia.org/wikipedia/commons/2/20/Adidas_Logo.svg',NULL,'Athletic clothing and footwear brand','adidas',0,'2026-01-07 01:11:24','2026-01-07 01:47:30','2026-01-07 01:47:30'),(64,'Puma',NULL,'https://upload.wikimedia.org/wikipedia/commons/f/fd/Puma_logo.svg',NULL,'Sportswear and lifestyle brand','puma',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(65,'Coca-Cola',NULL,'https://upload.wikimedia.org/wikipedia/commons/c/ce/Coca-Cola_logo.svg',NULL,'Global beverage manufacturer','coca-cola',1,'2026-01-07 01:11:24','2026-01-07 01:47:31','2026-01-07 01:47:31'),(66,'Pepsi',NULL,'https://upload.wikimedia.org/wikipedia/commons/5/58/Pepsi_logo.svg',NULL,'Food and beverage corporation','pepsi',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(67,'Toyota',NULL,'https://upload.wikimedia.org/wikipedia/commons/9/9d/Toyota_carlogo.svg',NULL,'Japanese automobile manufacturer','toyota',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(68,'Honda',NULL,'https://upload.wikimedia.org/wikipedia/commons/7/7b/Honda-logo.svg',NULL,'Automobile and motorcycle manufacturer','honda',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(69,'BMW',NULL,'https://upload.wikimedia.org/wikipedia/commons/4/44/BMW.svg',NULL,'Luxury automobile manufacturer','bmw',1,'2026-01-07 01:11:24','2026-01-07 01:47:31','2026-01-07 01:47:31'),(70,'Mercedes-Benz',NULL,'https://upload.wikimedia.org/wikipedia/commons/9/90/Mercedes-Logo.svg',NULL,'Luxury automotive brand','mercedes-benz',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(71,'Tesla',NULL,'https://upload.wikimedia.org/wikipedia/commons/b/bd/Tesla_Motors.svg',NULL,'Electric vehicle and clean energy company','tesla',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(72,'Intel',NULL,'https://upload.wikimedia.org/wikipedia/commons/c/c9/Intel-logo.svg',NULL,'Semiconductor and processor manufacturer','intel',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(73,'AMD',NULL,'https://upload.wikimedia.org/wikipedia/commons/7/7c/AMD_Logo.svg',NULL,'Computer processor and graphics company','amd',1,'2026-01-07 01:11:24','2026-01-07 01:47:30','2026-01-07 01:47:30'),(74,'IBM',NULL,'https://upload.wikimedia.org/wikipedia/commons/5/51/IBM_logo.svg',NULL,'Enterprise technology and consulting company','ibm',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(75,'Oracle',NULL,'https://upload.wikimedia.org/wikipedia/commons/5/50/Oracle_logo.svg',NULL,'Database and enterprise software company','oracle',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(76,'Cisco',NULL,'https://upload.wikimedia.org/wikipedia/commons/6/64/Cisco_logo.svg',NULL,'Networking hardware and software company','cisco',1,'2026-01-07 01:11:24','2026-01-07 01:47:31','2026-01-07 01:47:31'),(77,'HP',NULL,'https://upload.wikimedia.org/wikipedia/commons/a/ad/HP_logo_2012.svg',NULL,'Computer and printer manufacturer','hp',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(78,'Dell',NULL,'https://upload.wikimedia.org/wikipedia/commons/4/48/Dell_Logo.svg',NULL,'Computer technology company','dell',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(79,'Sony',NULL,'https://upload.wikimedia.org/wikipedia/commons/2/29/Sony_logo.svg',NULL,'Electronics, gaming, and entertainment company','sony',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(80,'LG',NULL,'https://upload.wikimedia.org/wikipedia/commons/2/20/LG_symbol.svg',NULL,'Electronics and home appliances brand','lg',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(81,'Panasonic',NULL,'https://upload.wikimedia.org/wikipedia/commons/4/4f/Panasonic_logo.svg',NULL,'Electronics and industrial solutions provider','panasonic',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(82,'Philips',NULL,'https://upload.wikimedia.org/wikipedia/commons/5/5c/Philips_logo.svg',NULL,'Health technology and electronics company','philips',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(83,'Unilever',NULL,'https://upload.wikimedia.org/wikipedia/commons/0/0b/Unilever_logo.svg',NULL,'Consumer goods multinational company','unilever',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(84,'Nestlé',NULL,'https://upload.wikimedia.org/wikipedia/commons/3/3d/Nestl%C3%A9.svg',NULL,'Food and beverage multinational','nestle',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(85,'Visa',NULL,'https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg',NULL,'Global digital payment network','visa',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(86,'Mastercard',NULL,'https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg',NULL,'Payment processing company','mastercard',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(87,'PayPal',NULL,'https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg',NULL,'Online payments and financial services company','paypal',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(88,'Stripe',NULL,'https://upload.wikimedia.org/wikipedia/commons/0/00/Stripe_Logo%2C_revised_2016.svg',NULL,'Online payment processing platform','stripe',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(89,'Uber',NULL,'https://upload.wikimedia.org/wikipedia/commons/c/cc/Uber_logo_2018.svg',NULL,'Ride-hailing and mobility platform','uber',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(90,'Airbnb',NULL,'https://upload.wikimedia.org/wikipedia/commons/6/69/Airbnb_Logo_B%C3%A9lo.svg',NULL,'Online lodging and travel marketplace','airbnb',0,'2026-01-07 01:11:24','2026-01-07 01:47:30','2026-01-07 01:47:30'),(91,'Spotify',NULL,'https://upload.wikimedia.org/wikipedia/commons/2/26/Spotify_logo_with_text.svg',NULL,'Music streaming service','spotify',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(92,'Twitter (X)',NULL,'https://upload.wikimedia.org/wikipedia/commons/6/6f/Logo_of_Twitter.svg',NULL,'Social media and microblogging platform','twitter-x',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(93,'LinkedIn',NULL,'https://upload.wikimedia.org/wikipedia/commons/c/ca/LinkedIn_logo_initials.svg',NULL,'Professional networking platform','linkedin',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(94,'Zoom',NULL,'https://upload.wikimedia.org/wikipedia/commons/7/7b/Zoom_Communications_Logo.svg',NULL,'Video conferencing software','zoom',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(95,'Slack',NULL,'https://upload.wikimedia.org/wikipedia/commons/d/d5/Slack_icon_2019.svg',NULL,'Workplace messaging and collaboration tool','slack',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(96,'Shopify',NULL,'https://upload.wikimedia.org/wikipedia/commons/0/0e/Shopify_logo_2018.svg',NULL,'E-commerce platform for online stores','shopify',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(97,'WordPress',NULL,'https://upload.wikimedia.org/wikipedia/commons/9/98/WordPress_blue_logo.svg',NULL,'Content management system for websites','wordpress',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(98,'Cloudflare',NULL,'https://upload.wikimedia.org/wikipedia/commons/9/94/Cloudflare_Logo.svg',NULL,'Web security and performance company','cloudflare',1,'2026-01-07 01:11:24','2026-01-07 01:47:31','2026-01-07 01:47:31'),(99,'DigitalOcean',NULL,'https://upload.wikimedia.org/wikipedia/commons/f/ff/DigitalOcean_logo.svg',NULL,'Cloud infrastructure provider','digitalocean',1,'2026-01-07 01:11:24','2026-01-07 01:11:24',NULL),(100,'Atlassian',NULL,'https://upload.wikimedia.org/wikipedia/commons/8/88/Atlassian_logo.svg',NULL,'Developer and collaboration software company','atlassian',1,'2026-01-07 01:11:24','2026-01-07 01:47:31','2026-01-07 01:47:31'),(101,'Amazon',NULL,'https://upload.wikimedia.org/wikipedia/commons/a/a9/Amazon_logo.svg',NULL,'E-commerce and cloud computing company','amazon',1,'2026-01-07 01:47:54','2026-01-07 01:47:54',NULL),(102,'Adidas',NULL,'https://upload.wikimedia.org/wikipedia/commons/2/20/Adidas_Logo.svg',NULL,'Athletic clothing and footwear brand','adidas',1,'2026-01-07 01:47:54','2026-01-07 01:47:54',NULL),(103,'Coca-Cola',NULL,'https://upload.wikimedia.org/wikipedia/commons/c/ce/Coca-Cola_logo.svg',NULL,'Global beverage manufacturer','coca-cola',1,'2026-01-07 01:47:54','2026-01-07 01:47:54',NULL),(104,'BMW',NULL,'https://upload.wikimedia.org/wikipedia/commons/4/44/BMW.svg',NULL,'Luxury automobile manufacturer','bmw',1,'2026-01-07 01:47:54','2026-01-07 01:47:54',NULL),(105,'AMD',NULL,'https://upload.wikimedia.org/wikipedia/commons/7/7c/AMD_Logo.svg',NULL,'Computer processor and graphics company','amd',1,'2026-01-07 01:47:54','2026-01-07 01:47:54',NULL),(106,'Cisco',NULL,'https://upload.wikimedia.org/wikipedia/commons/6/64/Cisco_logo.svg',NULL,'Networking hardware and software company','cisco',1,'2026-01-07 01:47:54','2026-01-07 01:47:54',NULL),(107,'Airbnb',NULL,'https://upload.wikimedia.org/wikipedia/commons/6/69/Airbnb_Logo_B%C3%A9lo.svg',NULL,'Online lodging and travel marketplace','airbnb',1,'2026-01-07 01:47:54','2026-01-07 01:47:54',NULL),(108,'Cloudflare',NULL,'https://upload.wikimedia.org/wikipedia/commons/9/94/Cloudflare_Logo.svg',NULL,'Web security and performance company','cloudflare',1,'2026-01-07 01:47:54','2026-01-07 01:47:54',NULL),(109,'Atlassian',NULL,'https://upload.wikimedia.org/wikipedia/commons/8/88/Atlassian_logo.svg',NULL,'Developer and collaboration software company','atlassian',1,'2026-01-07 01:47:54','2026-01-07 01:47:54',NULL);
/*!40000 ALTER TABLE `brands` ENABLE KEYS */;
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
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
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
  PRIMARY KEY (`key`)
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
-- Table structure for table `cash_registers`
--

DROP TABLE IF EXISTS `cash_registers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cash_registers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `cash_in_hand` double NOT NULL,
  `closing_balance` double DEFAULT NULL,
  `actual_cash` double DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `status` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cash_registers_user_id_index` (`user_id`),
  KEY `cash_registers_warehouse_id_index` (`warehouse_id`),
  KEY `cash_registers_status_index` (`status`),
  KEY `cash_registers_created_at_index` (`created_at`),
  CONSTRAINT `cash_registers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `cash_registers_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cash_registers`
--

LOCK TABLES `cash_registers` WRITE;
/*!40000 ALTER TABLE `cash_registers` DISABLE KEYS */;
/*!40000 ALTER TABLE `cash_registers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `page_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_description` text COLLATE utf8mb4_unicode_ci,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `featured` tinyint NOT NULL DEFAULT '1',
  `is_active` tinyint(1) DEFAULT NULL,
  `woocommerce_category_id` int DEFAULT NULL,
  `is_sync_disable` tinyint DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `categories_parent_id_index` (`parent_id`),
  KEY `categories_slug_index` (`slug`),
  KEY `categories_is_active_index` (`is_active`),
  CONSTRAINT `categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,'Smartphone & Gadgets',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(2,'Phone Accessories',NULL,NULL,1,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(3,'iPhone',NULL,NULL,1,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(4,'Samsung',NULL,NULL,1,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(5,'Phone Cases',NULL,NULL,1,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(6,'Laptops & Computers',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(7,'Keyboards',NULL,NULL,6,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(8,'Laptop Bags',NULL,NULL,6,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(9,'Mouses',NULL,NULL,6,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(10,'Webcams',NULL,NULL,6,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(11,'Monitors',NULL,NULL,6,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(12,'Smartwatches',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(13,'Sport Watches',NULL,NULL,12,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(14,'Kids Watches',NULL,NULL,12,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(15,'Women Watches',NULL,NULL,12,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(16,'Men Watches',NULL,NULL,12,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(23,'TVs, Audio & Video',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(24,'Television Accessories',NULL,NULL,23,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(25,'HD, DVD Players',NULL,NULL,23,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(26,'TV-DVD Combos',NULL,NULL,23,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(27,'Projectors',NULL,NULL,23,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(28,'Projection Screen',NULL,NULL,23,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(29,'Fruits & Vegetables',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(30,'Dairy & Egg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(31,'Meat & Fish',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(33,'Candy & Chocolates',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(39,'Clothing',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,1,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(40,'Electronics',NULL,NULL,1,'Shop Electronics | Best Deals','High-end electronics and gadgets','electronics',NULL,0,1,123,0,'2026-01-03 02:30:15','2026-01-03 02:30:15',NULL);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `challans`
--

DROP TABLE IF EXISTS `challans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `challans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `courier_id` bigint unsigned NOT NULL,
  `packing_slip_list` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount_list` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `cash_list` longtext COLLATE utf8mb4_unicode_ci,
  `online_payment_list` longtext COLLATE utf8mb4_unicode_ci,
  `cheque_list` longtext COLLATE utf8mb4_unicode_ci,
  `delivery_charge_list` longtext COLLATE utf8mb4_unicode_ci,
  `status_list` longtext COLLATE utf8mb4_unicode_ci,
  `closing_date` date DEFAULT NULL,
  `created_by_id` bigint unsigned NOT NULL,
  `closed_by_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `challans_closed_by_id_foreign` (`closed_by_id`),
  KEY `challans_reference_no_index` (`reference_no`),
  KEY `challans_courier_id_index` (`courier_id`),
  KEY `challans_status_index` (`status`),
  KEY `challans_created_by_id_index` (`created_by_id`),
  KEY `challans_closing_date_index` (`closing_date`),
  CONSTRAINT `challans_closed_by_id_foreign` FOREIGN KEY (`closed_by_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `challans_courier_id_foreign` FOREIGN KEY (`courier_id`) REFERENCES `couriers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `challans_created_by_id_foreign` FOREIGN KEY (`created_by_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `challans`
--

LOCK TABLES `challans` WRITE;
/*!40000 ALTER TABLE `challans` DISABLE KEYS */;
/*!40000 ALTER TABLE `challans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `collections`
--

DROP TABLE IF EXISTS `collections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `collections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `page_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `short_description` text COLLATE utf8mb4_unicode_ci,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `products` longtext COLLATE utf8mb4_unicode_ci,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `collections_slug_index` (`slug`),
  KEY `collections_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `collections`
--

LOCK TABLES `collections` WRITE;
/*!40000 ALTER TABLE `collections` DISABLE KEYS */;
/*!40000 ALTER TABLE `collections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `coupons`
--

DROP TABLE IF EXISTS `coupons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `coupons` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double NOT NULL,
  `minimum_amount` double DEFAULT NULL,
  `quantity` int NOT NULL,
  `used` int NOT NULL,
  `expired_date` date NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `coupons_code_unique` (`code`),
  KEY `coupons_user_id_foreign` (`user_id`),
  KEY `coupons_is_active_index` (`is_active`),
  KEY `coupons_expired_date_index` (`expired_date`),
  KEY `coupons_type_index` (`type`),
  CONSTRAINT `coupons_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `coupons`
--

LOCK TABLES `coupons` WRITE;
/*!40000 ALTER TABLE `coupons` DISABLE KEYS */;
/*!40000 ALTER TABLE `coupons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `couriers`
--

DROP TABLE IF EXISTS `couriers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `couriers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `couriers_name_index` (`name`),
  KEY `couriers_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `couriers`
--

LOCK TABLES `couriers` WRITE;
/*!40000 ALTER TABLE `couriers` DISABLE KEYS */;
/*!40000 ALTER TABLE `couriers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `currencies`
--

DROP TABLE IF EXISTS `currencies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `currencies` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `exchange_rate` double NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `currencies_code_index` (`code`),
  KEY `currencies_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `currencies`
--

LOCK TABLES `currencies` WRITE;
/*!40000 ALTER TABLE `currencies` DISABLE KEYS */;
INSERT INTO `currencies` VALUES (1,'US Dollar','USD',NULL,1,1,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL);
/*!40000 ALTER TABLE `currencies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `custom_fields`
--

DROP TABLE IF EXISTS `custom_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `custom_fields` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `belongs_to` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `default_value` text COLLATE utf8mb4_unicode_ci,
  `option_value` text COLLATE utf8mb4_unicode_ci,
  `grid_value` int NOT NULL,
  `is_table` tinyint(1) NOT NULL,
  `is_invoice` tinyint(1) NOT NULL,
  `is_required` tinyint(1) NOT NULL,
  `is_admin` tinyint(1) NOT NULL,
  `is_disable` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `custom_fields_belongs_to_index` (`belongs_to`),
  KEY `custom_fields_is_table_index` (`is_table`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `custom_fields`
--

LOCK TABLES `custom_fields` WRITE;
/*!40000 ALTER TABLE `custom_fields` DISABLE KEYS */;
/*!40000 ALTER TABLE `custom_fields` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_addresses`
--

DROP TABLE IF EXISTS `customer_addresses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_addresses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default` tinyint(1) NOT NULL DEFAULT '0',
  `customer_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_addresses_customer_id_index` (`customer_id`),
  KEY `customer_addresses_default_index` (`default`),
  CONSTRAINT `customer_addresses_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_addresses`
--

LOCK TABLES `customer_addresses` WRITE;
/*!40000 ALTER TABLE `customer_addresses` DISABLE KEYS */;
/*!40000 ALTER TABLE `customer_addresses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customer_groups`
--

DROP TABLE IF EXISTS `customer_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customer_groups` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `percentage` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_groups_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customer_groups`
--

LOCK TABLES `customer_groups` WRITE;
/*!40000 ALTER TABLE `customer_groups` DISABLE KEYS */;
INSERT INTO `customer_groups` VALUES (1,'General','0',1,'2026-01-03 02:00:15','2026-01-03 02:00:15');
/*!40000 ALTER TABLE `customer_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_group_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('regular','walkin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'regular',
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wa_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_no` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opening_balance` double NOT NULL DEFAULT '0',
  `credit_limit` double DEFAULT NULL,
  `points` double DEFAULT NULL,
  `deposit` double DEFAULT NULL,
  `pay_term_no` int DEFAULT NULL,
  `pay_term_period` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expense` double DEFAULT NULL,
  `wishlist` longtext COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT NULL,
  `ecom` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dsf` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'df',
  `arabic_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `admin` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `franchise_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Same as Customer',
  `customer_assigned_to` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Advocate',
  `assigned` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Advocate',
  `aaaaaaaa` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'aa',
  `district` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customers_customer_group_id_index` (`customer_group_id`),
  KEY `customers_user_id_index` (`user_id`),
  KEY `customers_email_index` (`email`),
  KEY `customers_phone_number_index` (`phone_number`),
  KEY `customers_type_index` (`type`),
  KEY `customers_is_active_index` (`is_active`),
  CONSTRAINT `customers_customer_group_id_foreign` FOREIGN KEY (`customer_group_id`) REFERENCES `customer_groups` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `customers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES (1,1,NULL,'John Doe','Test Company','john@gmail.com','regular','231312',NULL,NULL,'Test address','Test City',NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,'df',NULL,NULL,NULL,'Same as Customer','Advocate','Advocate','aa',NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL);
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deliveries`
--

DROP TABLE IF EXISTS `deliveries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deliveries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sale_id` bigint unsigned NOT NULL,
  `packing_slip_ids` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `courier_id` bigint unsigned DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `delivered_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recieved_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deliveries_reference_no_index` (`reference_no`),
  KEY `deliveries_sale_id_index` (`sale_id`),
  KEY `deliveries_user_id_index` (`user_id`),
  KEY `deliveries_courier_id_index` (`courier_id`),
  KEY `deliveries_status_index` (`status`),
  CONSTRAINT `deliveries_courier_id_foreign` FOREIGN KEY (`courier_id`) REFERENCES `couriers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `deliveries_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `deliveries_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deliveries`
--

LOCK TABLES `deliveries` WRITE;
/*!40000 ALTER TABLE `deliveries` DISABLE KEYS */;
/*!40000 ALTER TABLE `deliveries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `departments`
--

DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `departments_name_index` (`name`),
  KEY `departments_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `departments`
--

LOCK TABLES `departments` WRITE;
/*!40000 ALTER TABLE `departments` DISABLE KEYS */;
/*!40000 ALTER TABLE `departments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `deposits`
--

DROP TABLE IF EXISTS `deposits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `deposits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `amount` double NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `deposits_customer_id_index` (`customer_id`),
  KEY `deposits_user_id_index` (`user_id`),
  KEY `deposits_created_at_index` (`created_at`),
  CONSTRAINT `deposits_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `deposits_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `deposits`
--

LOCK TABLES `deposits` WRITE;
/*!40000 ALTER TABLE `deposits` DISABLE KEYS */;
/*!40000 ALTER TABLE `deposits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `designations`
--

DROP TABLE IF EXISTS `designations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `designations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `designations_name_index` (`name`),
  KEY `designations_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `designations`
--

LOCK TABLES `designations` WRITE;
/*!40000 ALTER TABLE `designations` DISABLE KEYS */;
/*!40000 ALTER TABLE `designations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discount_plan_customers`
--

DROP TABLE IF EXISTS `discount_plan_customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `discount_plan_customers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `discount_plan_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `discount_plan_customers_discount_plan_id_customer_id_unique` (`discount_plan_id`,`customer_id`),
  KEY `discount_plan_customers_discount_plan_id_index` (`discount_plan_id`),
  KEY `discount_plan_customers_customer_id_index` (`customer_id`),
  CONSTRAINT `discount_plan_customers_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `discount_plan_customers_discount_plan_id_foreign` FOREIGN KEY (`discount_plan_id`) REFERENCES `discount_plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discount_plan_customers`
--

LOCK TABLES `discount_plan_customers` WRITE;
/*!40000 ALTER TABLE `discount_plan_customers` DISABLE KEYS */;
/*!40000 ALTER TABLE `discount_plan_customers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discount_plan_discounts`
--

DROP TABLE IF EXISTS `discount_plan_discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `discount_plan_discounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `discount_id` bigint unsigned NOT NULL,
  `discount_plan_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `discount_plan_discounts_discount_id_discount_plan_id_unique` (`discount_id`,`discount_plan_id`),
  KEY `discount_plan_discounts_discount_id_index` (`discount_id`),
  KEY `discount_plan_discounts_discount_plan_id_index` (`discount_plan_id`),
  CONSTRAINT `discount_plan_discounts_discount_id_foreign` FOREIGN KEY (`discount_id`) REFERENCES `discounts` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `discount_plan_discounts_discount_plan_id_foreign` FOREIGN KEY (`discount_plan_id`) REFERENCES `discount_plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discount_plan_discounts`
--

LOCK TABLES `discount_plan_discounts` WRITE;
/*!40000 ALTER TABLE `discount_plan_discounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `discount_plan_discounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discount_plans`
--

DROP TABLE IF EXISTS `discount_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `discount_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('generic','limited') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'limited',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `discount_plans_name_index` (`name`),
  KEY `discount_plans_is_active_index` (`is_active`),
  KEY `discount_plans_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discount_plans`
--

LOCK TABLES `discount_plans` WRITE;
/*!40000 ALTER TABLE `discount_plans` DISABLE KEYS */;
/*!40000 ALTER TABLE `discount_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `discounts`
--

DROP TABLE IF EXISTS `discounts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `discounts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `applicable_for` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_list` longtext COLLATE utf8mb4_unicode_ci,
  `valid_from` date NOT NULL,
  `valid_till` date NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` double NOT NULL,
  `minimum_qty` double DEFAULT NULL,
  `maximum_qty` double DEFAULT NULL,
  `days` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `discounts_applicable_for_index` (`applicable_for`),
  KEY `discounts_valid_from_index` (`valid_from`),
  KEY `discounts_valid_till_index` (`valid_till`),
  KEY `discounts_type_index` (`type`),
  KEY `discounts_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `discounts`
--

LOCK TABLES `discounts` WRITE;
/*!40000 ALTER TABLE `discounts` DISABLE KEYS */;
/*!40000 ALTER TABLE `discounts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `domains`
--

DROP TABLE IF EXISTS `domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `domains` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tenant_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `domains_domain_unique` (`domain`),
  KEY `domains_tenant_id_foreign` (`tenant_id`),
  CONSTRAINT `domains_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `domains`
--

LOCK TABLES `domains` WRITE;
/*!40000 ALTER TABLE `domains` DISABLE KEYS */;
/*!40000 ALTER TABLE `domains` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `dso_alerts`
--

DROP TABLE IF EXISTS `dso_alerts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dso_alerts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_info` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `number_of_products` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `dso_alerts_created_at_index` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `dso_alerts`
--

LOCK TABLES `dso_alerts` WRITE;
/*!40000 ALTER TABLE `dso_alerts` DISABLE KEYS */;
/*!40000 ALTER TABLE `dso_alerts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ecommerce_settings`
--

DROP TABLE IF EXISTS `ecommerce_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ecommerce_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `theme` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `theme_font` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Inter',
  `theme_color` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '#fa9928',
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `favicon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `home_page` bigint DEFAULT NULL,
  `online_order` int NOT NULL DEFAULT '1',
  `is_rtl` int NOT NULL DEFAULT '0',
  `search` int NOT NULL DEFAULT '0',
  `warehouse_id` bigint unsigned NOT NULL,
  `biller_id` bigint unsigned NOT NULL,
  `contact_form_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `free_shipping_from` double DEFAULT NULL,
  `flat_rate_shipping` double DEFAULT NULL,
  `checkout_pages` longtext COLLATE utf8mb4_unicode_ci,
  `gift_card` tinyint NOT NULL DEFAULT '0',
  `custom_css` longtext COLLATE utf8mb4_unicode_ci,
  `custom_js` longtext COLLATE utf8mb4_unicode_ci,
  `chat_code` text COLLATE utf8mb4_unicode_ci,
  `analytics_code` text COLLATE utf8mb4_unicode_ci,
  `fb_pixel_code` text COLLATE utf8mb4_unicode_ci,
  `tktk_pixel_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sell_without_stock` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `ecommerce_settings_warehouse_id_index` (`warehouse_id`),
  KEY `ecommerce_settings_biller_id_index` (`biller_id`),
  CONSTRAINT `ecommerce_settings_biller_id_foreign` FOREIGN KEY (`biller_id`) REFERENCES `billers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `ecommerce_settings_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ecommerce_settings`
--

LOCK TABLES `ecommerce_settings` WRITE;
/*!40000 ALTER TABLE `ecommerce_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `ecommerce_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `department_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `staff_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL,
  `is_sale_agent` tinyint(1) NOT NULL DEFAULT '0',
  `sale_commission_percent` decimal(8,2) DEFAULT NULL,
  `sales_target` longtext COLLATE utf8mb4_unicode_ci,
  `designation_id` bigint unsigned DEFAULT NULL,
  `shift_id` bigint unsigned DEFAULT NULL,
  `basic_salary` decimal(12,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employees_email_index` (`email`),
  KEY `employees_phone_number_index` (`phone_number`),
  KEY `employees_department_id_index` (`department_id`),
  KEY `employees_user_id_index` (`user_id`),
  KEY `employees_is_active_index` (`is_active`),
  KEY `employees_is_sale_agent_index` (`is_sale_agent`),
  KEY `employees_designation_id_foreign` (`designation_id`),
  KEY `employees_shift_id_foreign` (`shift_id`),
  CONSTRAINT `employees_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `employees_designation_id_foreign` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `employees_shift_id_foreign` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expense_categories`
--

DROP TABLE IF EXISTS `expense_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expense_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expense_categories_code_index` (`code`),
  KEY `expense_categories_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expense_categories`
--

LOCK TABLES `expense_categories` WRITE;
/*!40000 ALTER TABLE `expense_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `expense_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `expenses`
--

DROP TABLE IF EXISTS `expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expense_category_id` bigint unsigned NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cash_register_id` bigint unsigned DEFAULT NULL,
  `amount` double NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `boutique_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `expenses_reference_no_index` (`reference_no`),
  KEY `expenses_expense_category_id_index` (`expense_category_id`),
  KEY `expenses_warehouse_id_index` (`warehouse_id`),
  KEY `expenses_account_id_index` (`account_id`),
  KEY `expenses_user_id_index` (`user_id`),
  KEY `expenses_created_at_index` (`created_at`),
  CONSTRAINT `expenses_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `expenses_expense_category_id_foreign` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `expenses_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `expenses_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `expenses`
--

LOCK TABLES `expenses` WRITE;
/*!40000 ALTER TABLE `expenses` DISABLE KEYS */;
/*!40000 ALTER TABLE `expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `external_services`
--

DROP TABLE IF EXISTS `external_services`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `external_services` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `details` longtext COLLATE utf8mb4_unicode_ci,
  `module_status` longtext COLLATE utf8mb4_unicode_ci,
  `active` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `external_services_type_index` (`type`),
  KEY `external_services_active_index` (`active`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `external_services`
--

LOCK TABLES `external_services` WRITE;
/*!40000 ALTER TABLE `external_services` DISABLE KEYS */;
INSERT INTO `external_services` VALUES (1,'PayPal','payment','Client ID,Client Secret;abcd1234,wxyz5678','{\"ecommerce\":true,\"pos\":true}',1,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(2,'Stripe','payment','Public Key,Private Key;efgh1234,stuv5678','{\"ecommerce\":true,\"pos\":true}',1,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(3,'Razorpay','payment','Key,Secret;rzp_test_Y4MCcpHfZNU6rR,3Hr7SDqaZ0G5waN0jsLgsiLx','{\"ecommerce\":true,\"pos\":true}',1,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(4,'Paystack','payment','public_Key,Secret_Key;pk_test_e8d220b7463d64569f0053e78534f38e6b10cf4a,sk_test_6d62cb976e1e0ab43f1e48b2934b0dfc7f32a1fe','{\"ecommerce\":true,\"pos\":true}',1,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(5,'Mollie','payment','api_key;test_dHar4XY7LxsDOtmnkVtjNVWXLSlXsM','{\"ecommerce\":true,\"pos\":true}',1,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(6,'Xendit','payment','secret_key,callback_token;xnd_development_aKJVKYbc4lHkEjcCLzWLrBsKs6jF6nbM6WaCMfnJerP3JW57CLis553XNRdDU,YPZxND92Mt8tdXntTYIEkRX802onZ5OcdKBUzycebuqYvN4n','{\"ecommerce\":true,\"pos\":true}',1,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(7,'bkash','payment','Mode,app_key,app_secret,username,password;sandbox,0vWQuCRGiUX7EPVjQDr0EUAYtc,jcUNPBgbcqEDedNKdvE4G1cAK7D3hCjmJccNPZZBq96QIxxwAMEx,01770618567,D7DaC<*E*eG','{\"ecommerce\":true,\"pos\":true}',1,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(8,'sslcommerz','payment','appkey,appsecret;12341234,asdfa23423','{\"ecommerce\":true,\"pos\":true}',1,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(9,'Mpesa','payment','consumer_Key,consumer_Secret;fhfgkj,dtrddhd','{\"ecommerce\":true,\"pos\":true}',1,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(10,'Pesapal','payment','Mode,Consumer Key,Consumer Secret;sandbox,qkio1BGGYAXTu2JOfm7XSXNruoZsrqEW,osGQ364R49cXKeOYSpaOnT++rHs=','{\"ecommerce\":true,\"pos\":true}',1,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(11,'Moneipoint','payment','Mode,client_id,client_secret,terminal_serial;sandbox,api-client-3956952-7e1279e2-95d2-45e1-825a-3a28e0a35168,ZtH02Q%jQ$Imcf%W^B%q,C42P008D01909830','{\"ecommerce\":true,\"pos\":true}',1,'2026-01-03 02:00:15','2026-01-03 02:00:15');
/*!40000 ALTER TABLE `external_services` ENABLE KEYS */;
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
-- Table structure for table `faq_categories`
--

DROP TABLE IF EXISTS `faq_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faq_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `faq_categories_order_index` (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faq_categories`
--

LOCK TABLES `faq_categories` WRITE;
/*!40000 ALTER TABLE `faq_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `faq_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `faqs`
--

DROP TABLE IF EXISTS `faqs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `faqs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `order` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `faqs_category_id_index` (`category_id`),
  KEY `faqs_order_index` (`order`),
  CONSTRAINT `faqs_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `faq_categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `faqs`
--

LOCK TABLES `faqs` WRITE;
/*!40000 ALTER TABLE `faqs` DISABLE KEYS */;
/*!40000 ALTER TABLE `faqs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `general_settings`
--

DROP TABLE IF EXISTS `general_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `general_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `site_title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `site_logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `favicon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_rtl` tinyint(1) DEFAULT NULL,
  `currency` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `package_id` int DEFAULT NULL,
  `subscription_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `staff_access` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `without_stock` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'no',
  `date_format` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `developed_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_format` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decimal` int NOT NULL DEFAULT '2',
  `state` int DEFAULT NULL,
  `theme` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `modules` text COLLATE utf8mb4_unicode_ci,
  `currency_position` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `expiry_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'days',
  `expiry_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `expiry_alert_days` int unsigned NOT NULL DEFAULT '0' COMMENT 'Number of days before expiry to show alert',
  `is_zatca` tinyint(1) DEFAULT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_registration_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_packing_slip` tinyint(1) NOT NULL DEFAULT '0',
  `app_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `show_products_details_in_sales_table` tinyint(1) NOT NULL DEFAULT '0',
  `show_products_details_in_purchase_table` tinyint(1) NOT NULL DEFAULT '0',
  `default_margin_value` decimal(8,2) NOT NULL DEFAULT '25.00',
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `font_css` text COLLATE utf8mb4_unicode_ci,
  `auth_css` longtext COLLATE utf8mb4_unicode_ci,
  `pos_css` longtext COLLATE utf8mb4_unicode_ci,
  `custom_css` longtext COLLATE utf8mb4_unicode_ci,
  `disable_signup` int NOT NULL DEFAULT '0',
  `disable_forgot_password` int NOT NULL DEFAULT '0',
  `margin_type` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `general_settings`
--

LOCK TABLES `general_settings` WRITE;
/*!40000 ALTER TABLE `general_settings` DISABLE KEYS */;
INSERT INTO `general_settings` VALUES (1,'Quick Mart','https://www.canva.com/design/DAG9U1OeHtU/F4Pb4zdJ8qku1AqRyUKpwQ/view?utm_content=DAG9U1OeHtU&utm_campaign=designshare&utm_medium=link2&utm_source=uniquelinks&utlId=h90bd9f5f4e',NULL,0,'1',0,'monthly','own','no','d/m/Y','Softmax Technologies','standard',2,1,'default.css',NULL,'prefix','1970-01-01','days','0',0,NULL,NULL,NULL,0,NULL,NULL,0,0,25.00,NULL,NULL,NULL,NULL,NULL,0,0,0,'2026-01-03 02:00:15','2026-01-03 02:00:15');
/*!40000 ALTER TABLE `general_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gift_card_recharges`
--

DROP TABLE IF EXISTS `gift_card_recharges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gift_card_recharges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `gift_card_id` bigint unsigned NOT NULL,
  `amount` double NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gift_card_recharges_gift_card_id_index` (`gift_card_id`),
  KEY `gift_card_recharges_user_id_index` (`user_id`),
  KEY `gift_card_recharges_created_at_index` (`created_at`),
  CONSTRAINT `gift_card_recharges_gift_card_id_foreign` FOREIGN KEY (`gift_card_id`) REFERENCES `gift_cards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `gift_card_recharges_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gift_card_recharges`
--

LOCK TABLES `gift_card_recharges` WRITE;
/*!40000 ALTER TABLE `gift_card_recharges` DISABLE KEYS */;
INSERT INTO `gift_card_recharges` VALUES (1,1,250,2,'2026-01-03 02:56:53','2026-01-03 02:56:53');
/*!40000 ALTER TABLE `gift_card_recharges` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `gift_cards`
--

DROP TABLE IF EXISTS `gift_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `gift_cards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `card_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` double NOT NULL,
  `expense` double NOT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `expired_date` date DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `gift_cards_user_id_foreign` (`user_id`),
  KEY `gift_cards_created_by_foreign` (`created_by`),
  KEY `gift_cards_card_no_index` (`card_no`),
  KEY `gift_cards_customer_id_index` (`customer_id`),
  KEY `gift_cards_is_active_index` (`is_active`),
  KEY `gift_cards_expired_date_index` (`expired_date`),
  CONSTRAINT `gift_cards_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `gift_cards_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `gift_cards_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gift_cards`
--

LOCK TABLES `gift_cards` WRITE;
/*!40000 ALTER TABLE `gift_cards` DISABLE KEYS */;
INSERT INTO `gift_cards` VALUES (1,'1234567890123456',350,0,NULL,1,'2024-12-31',2,1,'2026-01-03 02:55:43','2026-01-03 02:56:53',NULL);
/*!40000 ALTER TABLE `gift_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `holidays`
--

DROP TABLE IF EXISTS `holidays`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `holidays` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `recurring` tinyint(1) NOT NULL DEFAULT '0',
  `region` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `is_approved` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `holidays_user_id_index` (`user_id`),
  KEY `holidays_from_date_index` (`from_date`),
  KEY `holidays_to_date_index` (`to_date`),
  KEY `holidays_is_approved_index` (`is_approved`),
  CONSTRAINT `holidays_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `holidays`
--

LOCK TABLES `holidays` WRITE;
/*!40000 ALTER TABLE `holidays` DISABLE KEYS */;
INSERT INTO `holidays` VALUES (1,2,'2024-01-01','2024-01-05',0,'US','Annual leave',1,'2026-01-03 02:31:22','2026-01-03 02:31:33',NULL),(2,1,'2024-01-01','2024-01-05',0,'US','Annual leave',1,'2026-01-03 02:32:23','2026-01-03 02:32:31',NULL);
/*!40000 ALTER TABLE `holidays` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `hrm_settings`
--

DROP TABLE IF EXISTS `hrm_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `hrm_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `checkin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `checkout` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `hrm_settings`
--

LOCK TABLES `hrm_settings` WRITE;
/*!40000 ALTER TABLE `hrm_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `hrm_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `income_categories`
--

DROP TABLE IF EXISTS `income_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `income_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `income_categories_code_index` (`code`),
  KEY `income_categories_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `income_categories`
--

LOCK TABLES `income_categories` WRITE;
/*!40000 ALTER TABLE `income_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `income_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `incomes`
--

DROP TABLE IF EXISTS `incomes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `incomes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `income_category_id` bigint unsigned NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `cash_register_id` bigint unsigned DEFAULT NULL,
  `amount` double NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `boutique_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `incomes_reference_no_index` (`reference_no`),
  KEY `incomes_income_category_id_index` (`income_category_id`),
  KEY `incomes_warehouse_id_index` (`warehouse_id`),
  KEY `incomes_account_id_index` (`account_id`),
  KEY `incomes_user_id_index` (`user_id`),
  KEY `incomes_created_at_index` (`created_at`),
  CONSTRAINT `incomes_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `incomes_income_category_id_foreign` FOREIGN KEY (`income_category_id`) REFERENCES `income_categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `incomes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `incomes_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `incomes`
--

LOCK TABLES `incomes` WRITE;
/*!40000 ALTER TABLE `incomes` DISABLE KEYS */;
/*!40000 ALTER TABLE `incomes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `installment_plans`
--

DROP TABLE IF EXISTS `installment_plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `installment_plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_type` enum('sale','purchase') COLLATE utf8mb4_unicode_ci NOT NULL,
  `reference_id` bigint unsigned NOT NULL,
  `price` decimal(15,2) NOT NULL,
  `additional_amount` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total_amount` decimal(15,2) NOT NULL,
  `down_payment` decimal(15,2) NOT NULL DEFAULT '0.00',
  `months` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `installment_plans_reference_type_reference_id_unique` (`reference_type`,`reference_id`),
  KEY `installment_plans_reference_type_index` (`reference_type`),
  KEY `installment_plans_reference_id_index` (`reference_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `installment_plans`
--

LOCK TABLES `installment_plans` WRITE;
/*!40000 ALTER TABLE `installment_plans` DISABLE KEYS */;
/*!40000 ALTER TABLE `installment_plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `installments`
--

DROP TABLE IF EXISTS `installments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `installments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `installment_plan_id` bigint unsigned NOT NULL,
  `status` enum('pending','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `payment_date` timestamp NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `installments_installment_plan_id_index` (`installment_plan_id`),
  KEY `installments_status_index` (`status`),
  KEY `installments_payment_date_index` (`payment_date`),
  CONSTRAINT `installments_installment_plan_id_foreign` FOREIGN KEY (`installment_plan_id`) REFERENCES `installment_plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `installments`
--

LOCK TABLES `installments` WRITE;
/*!40000 ALTER TABLE `installments` DISABLE KEYS */;
/*!40000 ALTER TABLE `installments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_schemas`
--

DROP TABLE IF EXISTS `invoice_schemas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_schemas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `prefix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number_of_digit` int unsigned DEFAULT NULL,
  `start_number` bigint unsigned DEFAULT NULL,
  `last_invoice_number` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_schemas_prefix_index` (`prefix`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_schemas`
--

LOCK TABLES `invoice_schemas` WRITE;
/*!40000 ALTER TABLE `invoice_schemas` DISABLE KEYS */;
/*!40000 ALTER TABLE `invoice_schemas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invoice_settings`
--

DROP TABLE IF EXISTS `invoice_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `invoice_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `template_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `invoice_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `prefix` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `number_of_digit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `numbering_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_number` bigint unsigned DEFAULT NULL,
  `last_invoice_number` bigint unsigned DEFAULT NULL,
  `header_text` text COLLATE utf8mb4_unicode_ci,
  `header_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `footer_text` text COLLATE utf8mb4_unicode_ci,
  `footer_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preview_invoice` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `primary_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `secondary_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text_color` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_height` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo_width` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0=not defoult, 1= defoult',
  `status` tinyint(1) NOT NULL DEFAULT '0',
  `invoice_date_format` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Y-M-d h:m:s',
  `show_column` longtext COLLATE utf8mb4_unicode_ci,
  `extra` longtext COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoice_settings_created_by_foreign` (`created_by`),
  KEY `invoice_settings_updated_by_foreign` (`updated_by`),
  KEY `invoice_settings_template_name_index` (`template_name`),
  KEY `invoice_settings_is_default_index` (`is_default`),
  KEY `invoice_settings_status_index` (`status`),
  CONSTRAINT `invoice_settings_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `invoice_settings_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invoice_settings`
--

LOCK TABLES `invoice_settings` WRITE;
/*!40000 ALTER TABLE `invoice_settings` DISABLE KEYS */;
INSERT INTO `invoice_settings` VALUES (1,'A4 Size Normal Invoice',NULL,NULL,NULL,'salepro','4','datewise',1000,NULL,'SalePro',NULL,'Thank you for shopping with us','Thank you for shopping with us',NULL,'a4','#ff0000',NULL,'#000000',NULL,'200','200',1,0,'d.m.y h:m A','{\"is_default\":0,\"status\":0,\"show_barcode\":1,\"show_qr_code\":1,\"show_customer_details\":1,\"show_shipping_details\":1,\"show_payment_info\":1,\"show_discount\":1,\"show_tax_info\":1,\"show_description\":1,\"show_in_words\":1,\"active_primary_color\":0,\"active_text_color\":0,\"show_warehouse_info\":1,\"show_bill_to_info\":1,\"show_footer_text\":1,\"show_biller_info\":1,\"show_payment_note\":1,\"show_paid_info\":1,\"show_ref_number\":1,\"show_customer_name\":1,\"active_date_format\":0,\"active_generat_settings\":0,\"active_logo_height_width\":0,\"hide_total_due\":0}',NULL,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(2,'58mm Thermal Invoice',NULL,NULL,NULL,'salepro','4','datewise',1000,NULL,'SalePro',NULL,'Thank you for shopping with us','Thank you for shopping with us',NULL,'58mm','#ff0000',NULL,'#000000',NULL,'200','200',0,0,'d.m.y h:m A','{\"is_default\":0,\"status\":0,\"show_barcode\":1,\"show_qr_code\":1,\"show_customer_details\":1,\"show_shipping_details\":1,\"show_payment_info\":1,\"show_discount\":1,\"show_tax_info\":1,\"show_description\":1,\"show_in_words\":1,\"active_primary_color\":0,\"active_text_color\":0,\"show_warehouse_info\":1,\"show_bill_to_info\":1,\"show_footer_text\":1,\"show_biller_info\":1,\"show_payment_note\":1,\"show_paid_info\":1,\"show_ref_number\":1,\"show_customer_name\":1,\"active_date_format\":0,\"active_generat_settings\":0,\"active_logo_height_width\":0,\"hide_total_due\":0}',NULL,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(3,'80mm Thermal Invoice',NULL,NULL,NULL,'salepro','4','datewise',1000,NULL,'SalePro',NULL,'Thank you for shopping with us','Thank you for shopping with us',NULL,'80mm','#ff0000',NULL,'#000000',NULL,'200','200',0,0,'d.m.y h:m A','{\"is_default\":0,\"status\":0,\"show_barcode\":1,\"show_qr_code\":1,\"show_customer_details\":1,\"show_shipping_details\":1,\"show_payment_info\":1,\"show_discount\":1,\"show_tax_info\":1,\"show_description\":1,\"show_in_words\":1,\"active_primary_color\":0,\"active_text_color\":0,\"show_warehouse_info\":1,\"show_bill_to_info\":1,\"show_footer_text\":1,\"show_biller_info\":1,\"show_payment_note\":1,\"show_paid_info\":1,\"show_ref_number\":1,\"show_customer_name\":1,\"active_date_format\":0,\"active_generat_settings\":0,\"active_logo_height_width\":0,\"hide_total_due\":0}',NULL,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15');
/*!40000 ALTER TABLE `invoice_settings` ENABLE KEYS */;
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
-- Table structure for table `languages`
--

DROP TABLE IF EXISTS `languages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `languages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `language` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `languages_language_unique` (`language`),
  KEY `languages_is_default_index` (`is_default`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `languages`
--

LOCK TABLES `languages` WRITE;
/*!40000 ALTER TABLE `languages` DISABLE KEYS */;
INSERT INTO `languages` VALUES (1,'en','English',1,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(2,'bn','Bangla',0,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(3,'ar','Arabic',0,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(4,'al','Albania',0,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(5,'az','Azerbaijan',0,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(6,'bg','Bulgaria',0,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(7,'de','Germany',0,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(8,'es','Spanish',0,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(9,'fr','French',0,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(10,'id','Indonesian',0,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(11,'tr','Turkish',0,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(12,'vi','Vietnamese',0,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(13,'pt','Portuguese',0,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(14,'ms','Malay',0,'2026-01-03 02:00:15','2026-01-03 02:00:15'),(15,'sr','Serbian',0,'2026-01-03 02:00:15','2026-01-03 02:00:15');
/*!40000 ALTER TABLE `languages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_types`
--

DROP TABLE IF EXISTS `leave_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_types` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `annual_quota` int NOT NULL DEFAULT '0',
  `encashable` tinyint(1) NOT NULL DEFAULT '0',
  `carry_forward_limit` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leave_types_name_index` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_types`
--

LOCK TABLES `leave_types` WRITE;
/*!40000 ALTER TABLE `leave_types` DISABLE KEYS */;
/*!40000 ALTER TABLE `leave_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leaves`
--

DROP TABLE IF EXISTS `leaves`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leaves` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `leave_types` bigint unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days` int NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pending',
  `approver_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `leaves_approver_id_foreign` (`approver_id`),
  KEY `leaves_employee_id_index` (`employee_id`),
  KEY `leaves_leave_types_index` (`leave_types`),
  KEY `leaves_status_index` (`status`),
  KEY `leaves_start_date_index` (`start_date`),
  KEY `leaves_end_date_index` (`end_date`),
  CONSTRAINT `leaves_approver_id_foreign` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `leaves_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `leaves_leave_types_foreign` FOREIGN KEY (`leave_types`) REFERENCES `leave_types` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leaves`
--

LOCK TABLES `leaves` WRITE;
/*!40000 ALTER TABLE `leaves` DISABLE KEYS */;
/*!40000 ALTER TABLE `leaves` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `links`
--

DROP TABLE IF EXISTS `links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `links` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `widget_title` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `links_type_index` (`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `links`
--

LOCK TABLES `links` WRITE;
/*!40000 ALTER TABLE `links` DISABLE KEYS */;
/*!40000 ALTER TABLE `links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mail_settings`
--

DROP TABLE IF EXISTS `mail_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mail_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `driver` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `host` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `port` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `encryption` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mail_settings`
--

LOCK TABLES `mail_settings` WRITE;
/*!40000 ALTER TABLE `mail_settings` DISABLE KEYS */;
INSERT INTO `mail_settings` VALUES (1,'smtp','sandbox.smtp.mailtrap.io','2525','noreply@example.com','Quick Mart','8d661dcb3a4c86','2d709401322e9a','tls','2026-01-03 02:00:15','2026-01-03 02:00:15');
/*!40000 ALTER TABLE `mail_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menu_items`
--

DROP TABLE IF EXISTS `menu_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menu_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `menu_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menu_items_menu_id_index` (`menu_id`),
  KEY `menu_items_type_index` (`type`),
  CONSTRAINT `menu_items_menu_id_foreign` FOREIGN KEY (`menu_id`) REFERENCES `menus` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menu_items`
--

LOCK TABLES `menu_items` WRITE;
/*!40000 ALTER TABLE `menu_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `menu_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `menus`
--

DROP TABLE IF EXISTS `menus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `menus` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `menus_location_index` (`location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `menus`
--

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;
/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=130 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2019_09_15_000010_create_tenants_table',1),(5,'2019_09_15_000020_create_domains_table',1),(6,'2025_12_25_004705_create_personal_access_tokens_table',1),(7,'2025_12_25_005350_create_permission_tables',1),(8,'2025_12_25_174346_create_general_settings_table',1),(9,'2025_12_25_174353_create_currencies_table',1),(10,'2025_12_25_174359_create_brands_table',1),(11,'2025_12_25_174411_create_categories_table',1),(12,'2025_12_25_174624_create_custom_fields_table',1),(13,'2025_12_25_174630_create_barcodes_table',1),(14,'2025_12_25_174701_create_units_table',1),(15,'2025_12_25_174708_create_taxes_table',1),(16,'2025_12_25_174714_create_warehouses_table',1),(17,'2025_12_25_174721_create_billers_table',1),(18,'2025_12_25_174727_create_customer_groups_table',1),(19,'2025_12_25_174855_create_customers_table',1),(20,'2025_12_25_174955_create_suppliers_table',1),(21,'2025_12_25_175008_create_accounts_table',1),(22,'2025_12_25_175018_create_expense_categories_table',1),(23,'2025_12_25_175027_create_income_categories_table',1),(24,'2025_12_25_175141_create_variants_table',1),(25,'2025_12_25_175148_create_products_table',1),(26,'2025_12_25_175237_create_product_variants_table',1),(27,'2025_12_25_175244_create_product_warehouse_table',1),(28,'2025_12_25_175251_create_product_batches_table',1),(29,'2025_12_25_175342_create_sales_table',1),(30,'2025_12_25_175356_create_product_sales_table',1),(31,'2025_12_25_175720_create_purchases_table',1),(32,'2025_12_25_175727_create_product_purchases_table',1),(33,'2025_12_25_175734_create_payments_table',1),(34,'2025_12_25_175742_create_return_sales_table',1),(35,'2025_12_25_175750_create_return_purchases_table',1),(36,'2025_12_25_175804_create_product_return_sales_table',1),(37,'2025_12_25_175813_create_purchase_product_return_table',1),(38,'2025_12_25_180103_create_expenses_table',1),(39,'2025_12_25_180110_create_incomes_table',1),(40,'2025_12_25_180117_create_quotations_table',1),(41,'2025_12_25_180124_create_gift_cards_table',1),(42,'2025_12_25_180131_create_coupons_table',1),(43,'2025_12_25_180244_create_product_quotation_table',1),(44,'2025_12_25_180437_create_transfers_table',1),(45,'2025_12_25_180446_create_employees_table',1),(46,'2025_12_25_180453_create_cash_registers_table',1),(47,'2025_12_25_180500_create_reward_point_settings_table',1),(48,'2025_12_25_180511_create_reward_points_table',1),(49,'2025_12_25_180534_create_departments_table',1),(50,'2025_12_25_180542_create_designations_table',1),(51,'2025_12_25_180548_create_shifts_table',1),(52,'2025_12_25_180555_create_product_transfer_table',1),(53,'2025_12_25_181313_create_activity_logs_table',1),(54,'2025_12_25_181331_create_installment_plans_table',1),(55,'2025_12_25_181338_create_installments_table',1),(56,'2025_12_25_181350_create_attendances_table',1),(57,'2025_12_25_181403_create_holidays_table',1),(58,'2025_12_25_181409_create_couriers_table',1),(59,'2025_12_25_181416_create_adjustments_table',1),(60,'2025_12_25_181518_create_product_adjustments_table',1),(61,'2025_12_25_182122_create_customer_addresses_table',1),(62,'2025_12_25_182129_create_deliveries_table',1),(63,'2025_12_25_182136_create_deposits_table',1),(64,'2025_12_25_182147_create_money_transfers_table',1),(65,'2025_12_25_182155_create_payrolls_table',1),(66,'2025_12_25_182203_create_productions_table',1),(67,'2025_12_25_182212_create_product_productions_table',1),(68,'2025_12_25_182526_create_payment_with_cheque_table',1),(69,'2025_12_25_182538_create_payment_with_credit_card_table',1),(70,'2025_12_25_182557_create_payment_with_gift_card_table',1),(71,'2025_12_25_182605_create_payment_with_paypal_table',1),(72,'2025_12_25_182613_create_packing_slips_table',1),(73,'2025_12_25_182625_create_packing_slip_products_table',1),(74,'2025_12_25_182633_create_challans_table',1),(75,'2025_12_25_183013_create_product_reviews_table',1),(76,'2025_12_25_183020_create_gift_card_recharges_table',1),(77,'2025_12_25_183028_create_overtimes_table',1),(78,'2025_12_25_183036_create_leave_types_table',1),(79,'2025_12_25_183044_create_leaves_table',1),(80,'2025_12_25_183051_create_newsletter_table',1),(81,'2025_12_25_183058_create_notifications_table',1),(82,'2025_12_25_183341_create_blogs_table',1),(83,'2025_12_25_183347_create_collections_table',1),(84,'2025_12_25_183354_create_faq_categories_table',1),(85,'2025_12_25_183400_create_faqs_table',1),(86,'2025_12_25_183408_create_pages_table',1),(87,'2025_12_25_183414_create_menus_table',1),(88,'2025_12_25_183420_create_menu_items_table',1),(89,'2025_12_25_183438_create_page_widgets_table',1),(90,'2025_12_25_183445_create_widgets_table',1),(91,'2025_12_25_183451_create_sliders_table',1),(92,'2025_12_25_183459_create_social_links_table',1),(93,'2025_12_25_183753_create_dso_alerts_table',1),(94,'2025_12_25_183801_create_ecommerce_settings_table',1),(95,'2025_12_25_183808_create_pos_setting_table',1),(96,'2025_12_25_183816_create_printers_table',1),(97,'2025_12_25_183823_create_invoice_settings_table',1),(98,'2025_12_25_183830_create_invoice_schemas_table',1),(99,'2025_12_25_183837_create_languages_table',1),(100,'2025_12_25_183850_create_links_table',1),(101,'2025_12_25_183857_create_mail_settings_table',1),(102,'2025_12_25_183905_create_mobile_tokens_table',1),(103,'2025_12_25_184606_create_discount_plans_table',1),(104,'2025_12_25_184613_create_discount_plan_customers_table',1),(105,'2025_12_25_184621_create_discount_plan_discounts_table',1),(106,'2025_12_25_184629_create_discounts_table',1),(107,'2025_12_25_184636_create_external_services_table',1),(108,'2025_12_25_184643_create_hrm_settings_table',1),(109,'2025_12_25_184828_create_tables_table',1),(110,'2025_12_25_184937_add_product_batch_foreign_key_to_product_warehouse_table',1),(111,'2025_12_25_185014_add_table_foreign_key_to_sales_table',1),(112,'2025_12_25_185025_add_cash_register_foreign_key_to_sales_table',1),(113,'2025_12_25_185031_add_coupon_foreign_key_to_sales_table',1),(114,'2025_12_25_185145_add_product_batch_foreign_key_to_product_sales_table',1),(115,'2025_12_25_185154_add_product_batch_foreign_key_to_product_purchases_table',1),(116,'2025_12_25_185322_add_product_batch_foreign_key_to_product_transfer_table',1),(117,'2025_12_25_185330_add_product_batch_foreign_key_to_purchase_product_return_table',1),(118,'2025_12_25_185336_add_product_batch_foreign_key_to_product_returns_table',1),(119,'2025_12_25_185439_add_product_batch_foreign_key_to_product_quotation_table',1),(120,'2025_12_25_192821_add_employee_foreign_keys_table',1),(121,'2025_12_25_200335_add_discount_plan_discounts_foreign_keys_table',1),(122,'2025_12_25_212743_create_product_supplier_table',1),(123,'2025_12_25_212757_create_woocommerce_settings_table',1),(124,'2025_12_25_212832_create_woocommerce_sync_logs_table',1),(125,'2025_12_25_220200_create_whatsapp_settings_table',1),(126,'2025_12_25_220208_create_sms_templates_table',1),(127,'2025_12_25_220216_create_stock_counts_table',1),(128,'2025_12_25_220231_create_translations_table',1),(129,'2026_01_03_070000_add_fields_to_users_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mobile_tokens`
--

DROP TABLE IF EXISTS `mobile_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mobile_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `last_active` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `mobile_tokens_token_unique` (`token`),
  KEY `mobile_tokens_is_active_index` (`is_active`),
  KEY `mobile_tokens_last_active_index` (`last_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mobile_tokens`
--

LOCK TABLES `mobile_tokens` WRITE;
/*!40000 ALTER TABLE `mobile_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `mobile_tokens` ENABLE KEYS */;
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
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `money_transfers`
--

DROP TABLE IF EXISTS `money_transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `money_transfers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `from_account_id` bigint unsigned NOT NULL,
  `to_account_id` bigint unsigned NOT NULL,
  `amount` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `money_transfers_reference_no_index` (`reference_no`),
  KEY `money_transfers_from_account_id_index` (`from_account_id`),
  KEY `money_transfers_to_account_id_index` (`to_account_id`),
  KEY `money_transfers_created_at_index` (`created_at`),
  CONSTRAINT `money_transfers_from_account_id_foreign` FOREIGN KEY (`from_account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `money_transfers_to_account_id_foreign` FOREIGN KEY (`to_account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `money_transfers`
--

LOCK TABLES `money_transfers` WRITE;
/*!40000 ALTER TABLE `money_transfers` DISABLE KEYS */;
/*!40000 ALTER TABLE `money_transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletter`
--

DROP TABLE IF EXISTS `newsletter`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `newsletter` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `newsletter_email_unique` (`email`),
  KEY `newsletter_email_index` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletter`
--

LOCK TABLES `newsletter` WRITE;
/*!40000 ALTER TABLE `newsletter` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletter` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` varchar(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
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
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `overtimes`
--

DROP TABLE IF EXISTS `overtimes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `overtimes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `date` date NOT NULL,
  `hours` decimal(5,2) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `status` enum('pending','approved','rejected') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `overtimes_employee_id_index` (`employee_id`),
  KEY `overtimes_date_index` (`date`),
  KEY `overtimes_status_index` (`status`),
  CONSTRAINT `overtimes_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `overtimes`
--

LOCK TABLES `overtimes` WRITE;
/*!40000 ALTER TABLE `overtimes` DISABLE KEYS */;
/*!40000 ALTER TABLE `overtimes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packing_slip_products`
--

DROP TABLE IF EXISTS `packing_slip_products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `packing_slip_products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `packing_slip_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `variant_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `packing_slip_products_packing_slip_id_index` (`packing_slip_id`),
  KEY `packing_slip_products_product_id_index` (`product_id`),
  KEY `packing_slip_products_variant_id_index` (`variant_id`),
  CONSTRAINT `packing_slip_products_packing_slip_id_foreign` FOREIGN KEY (`packing_slip_id`) REFERENCES `packing_slips` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `packing_slip_products_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `packing_slip_products_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packing_slip_products`
--

LOCK TABLES `packing_slip_products` WRITE;
/*!40000 ALTER TABLE `packing_slip_products` DISABLE KEYS */;
/*!40000 ALTER TABLE `packing_slip_products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `packing_slips`
--

DROP TABLE IF EXISTS `packing_slips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `packing_slips` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sale_id` bigint unsigned NOT NULL,
  `delivery_id` bigint unsigned DEFAULT NULL,
  `amount` double NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `packing_slips_reference_no_index` (`reference_no`),
  KEY `packing_slips_sale_id_index` (`sale_id`),
  KEY `packing_slips_delivery_id_index` (`delivery_id`),
  KEY `packing_slips_status_index` (`status`),
  CONSTRAINT `packing_slips_delivery_id_foreign` FOREIGN KEY (`delivery_id`) REFERENCES `deliveries` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `packing_slips_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `packing_slips`
--

LOCK TABLES `packing_slips` WRITE;
/*!40000 ALTER TABLE `packing_slips` DISABLE KEYS */;
/*!40000 ALTER TABLE `packing_slips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `page_widgets`
--

DROP TABLE IF EXISTS `page_widgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `page_widgets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `page_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_category_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_category_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_category_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_category_slider_loop` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_category_slider_autoplay` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_category_limit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tab_product_collection_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tab_product_collection_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tab_product_collection_slider_loop` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tab_product_collection_slider_autoplay` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tab_product_collection_limit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_collection_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_collection_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_collection_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_collection_slider_loop` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_collection_slider_autoplay` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_collection_limit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_slider_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_slider_loop` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_slider_autoplay` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_slider_ids` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_slider_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_slider_loop` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_slider_autoplay` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_slider_ids` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `three_c_banner_link1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `three_c_banner_image1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `three_c_banner_link2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `three_c_banner_image2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `three_c_banner_link3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `three_c_banner_image3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_c_banner_link1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_c_banner_image1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_c_banner_link2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `two_c_banner_image2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `one_c_banner_link1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `one_c_banner_image1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text_content` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slider_images` text COLLATE utf8mb4_unicode_ci,
  `slider_links` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `page_widgets_page_id_index` (`page_id`),
  KEY `page_widgets_order_index` (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `page_widgets`
--

LOCK TABLES `page_widgets` WRITE;
/*!40000 ALTER TABLE `page_widgets` DISABLE KEYS */;
/*!40000 ALTER TABLE `page_widgets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pages`
--

DROP TABLE IF EXISTS `pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `page_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `og_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `og_description` text COLLATE utf8mb4_unicode_ci,
  `template` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Default',
  `status` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pages_slug_unique` (`slug`),
  KEY `pages_slug_index` (`slug`),
  KEY `pages_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pages`
--

LOCK TABLES `pages` WRITE;
/*!40000 ALTER TABLE `pages` DISABLE KEYS */;
/*!40000 ALTER TABLE `pages` ENABLE KEYS */;
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
-- Table structure for table `payment_with_cheque`
--

DROP TABLE IF EXISTS `payment_with_cheque`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_with_cheque` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint unsigned NOT NULL,
  `cheque_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_with_cheque_payment_id_index` (`payment_id`),
  CONSTRAINT `payment_with_cheque_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_with_cheque`
--

LOCK TABLES `payment_with_cheque` WRITE;
/*!40000 ALTER TABLE `payment_with_cheque` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_with_cheque` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_with_credit_card`
--

DROP TABLE IF EXISTS `payment_with_credit_card`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_with_credit_card` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `customer_stripe_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `charge_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_with_credit_card_payment_id_index` (`payment_id`),
  KEY `payment_with_credit_card_customer_id_index` (`customer_id`),
  KEY `payment_with_credit_card_charge_id_index` (`charge_id`),
  CONSTRAINT `payment_with_credit_card_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `payment_with_credit_card_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_with_credit_card`
--

LOCK TABLES `payment_with_credit_card` WRITE;
/*!40000 ALTER TABLE `payment_with_credit_card` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_with_credit_card` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_with_gift_card`
--

DROP TABLE IF EXISTS `payment_with_gift_card`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_with_gift_card` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint unsigned NOT NULL,
  `gift_card_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_with_gift_card_payment_id_index` (`payment_id`),
  KEY `payment_with_gift_card_gift_card_id_index` (`gift_card_id`),
  CONSTRAINT `payment_with_gift_card_gift_card_id_foreign` FOREIGN KEY (`gift_card_id`) REFERENCES `gift_cards` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `payment_with_gift_card_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_with_gift_card`
--

LOCK TABLES `payment_with_gift_card` WRITE;
/*!40000 ALTER TABLE `payment_with_gift_card` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_with_gift_card` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payment_with_paypal`
--

DROP TABLE IF EXISTS `payment_with_paypal`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payment_with_paypal` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_id` bigint unsigned NOT NULL,
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payment_with_paypal_payment_id_index` (`payment_id`),
  KEY `payment_with_paypal_transaction_id_index` (`transaction_id`),
  CONSTRAINT `payment_with_paypal_payment_id_foreign` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payment_with_paypal`
--

LOCK TABLES `payment_with_paypal` WRITE;
/*!40000 ALTER TABLE `payment_with_paypal` DISABLE KEYS */;
/*!40000 ALTER TABLE `payment_with_paypal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `purchase_id` bigint unsigned DEFAULT NULL,
  `sale_id` bigint unsigned DEFAULT NULL,
  `cash_register_id` bigint unsigned DEFAULT NULL,
  `account_id` bigint unsigned NOT NULL,
  `payment_receiver` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount` double NOT NULL,
  `currency_id` bigint unsigned DEFAULT NULL,
  `installment_id` bigint unsigned DEFAULT NULL,
  `exchange_rate` decimal(8,2) NOT NULL DEFAULT '1.00',
  `payment_at` timestamp NULL DEFAULT NULL,
  `used_points` double DEFAULT NULL,
  `change` double DEFAULT NULL,
  `paying_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payment_note` text COLLATE utf8mb4_unicode_ci,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payments_currency_id_foreign` (`currency_id`),
  KEY `payments_payment_reference_index` (`payment_reference`),
  KEY `payments_user_id_index` (`user_id`),
  KEY `payments_purchase_id_index` (`purchase_id`),
  KEY `payments_sale_id_index` (`sale_id`),
  KEY `payments_account_id_index` (`account_id`),
  KEY `payments_payment_at_index` (`payment_at`),
  CONSTRAINT `payments_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `payments_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `payments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payrolls`
--

DROP TABLE IF EXISTS `payrolls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `payrolls` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `amount` double NOT NULL,
  `paying_method` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `amount_array` longtext COLLATE utf8mb4_unicode_ci,
  `month` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `payrolls_reference_no_index` (`reference_no`),
  KEY `payrolls_employee_id_index` (`employee_id`),
  KEY `payrolls_account_id_index` (`account_id`),
  KEY `payrolls_user_id_index` (`user_id`),
  KEY `payrolls_status_index` (`status`),
  KEY `payrolls_month_index` (`month`),
  CONSTRAINT `payrolls_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `payrolls_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `payrolls_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payrolls`
--

LOCK TABLES `payrolls` WRITE;
/*!40000 ALTER TABLE `payrolls` DISABLE KEYS */;
/*!40000 ALTER TABLE `payrolls` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=182 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (4,'products-edit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(5,'products-delete','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(6,'products-add','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(7,'products-index','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(8,'purchases-index','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(9,'purchases-add','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(10,'purchases-edit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(11,'purchases-delete','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(12,'sales-index','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(13,'sales-add','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(14,'sales-edit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(15,'sales-delete','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(16,'quotes-index','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(17,'quotes-add','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(18,'quotes-edit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(19,'quotes-delete','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(20,'transfers-index','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(21,'transfers-add','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(22,'transfers-edit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(23,'transfers-delete','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(24,'returns-index','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(25,'returns-add','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(26,'returns-edit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(27,'returns-delete','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(28,'customers-index','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(29,'customers-add','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(30,'customers-edit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(31,'customers-delete','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(32,'suppliers-index','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(33,'suppliers-add','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(34,'suppliers-edit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(35,'suppliers-delete','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(36,'product-report','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(37,'purchase-report','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(38,'sale-report','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(39,'customer-report','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(40,'due-report','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(41,'users-index','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(42,'users-add','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(43,'users-edit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(44,'users-delete','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(45,'profit-loss','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(46,'best-seller','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(47,'daily-sale','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(48,'monthly-sale','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(49,'daily-purchase','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(50,'monthly-purchase','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(51,'payment-report','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(52,'warehouse-stock-report','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(53,'product-qty-alert','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(54,'supplier-report','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(55,'expenses-index','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(56,'expenses-add','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(57,'expenses-edit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(58,'expenses-delete','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(59,'general_setting','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(60,'mail_setting','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(61,'pos_setting','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(62,'hrm_setting','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(63,'purchase-return-index','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(64,'purchase-return-add','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(65,'purchase-return-edit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(66,'purchase-return-delete','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(67,'account-index','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(68,'balance-sheet','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(69,'account-statement','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(70,'department','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(71,'attendance','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(72,'payroll','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(73,'employees-index','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(74,'employees-add','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(75,'employees-edit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(76,'employees-delete','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(77,'user-report','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(78,'stock_count','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(79,'adjustment','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(80,'sms_setting','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(81,'create_sms','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(82,'print_barcode','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(83,'empty_database','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(84,'customer_group','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(85,'unit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(86,'tax','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(87,'gift_card','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(88,'coupon','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(89,'holiday','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(90,'warehouse-report','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(91,'warehouse','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(92,'brand','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(93,'billers-index','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(94,'billers-add','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(95,'billers-edit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(96,'billers-delete','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(97,'money-transfer','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(98,'category','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(99,'delivery','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(100,'send_notification','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(101,'today_sale','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(102,'today_profit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(103,'currency','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(104,'backup_database','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(105,'reward_point_setting','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(106,'revenue_profit_summary','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(107,'cash_flow','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(108,'monthly_summary','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(109,'yearly_report','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(110,'discount_plan','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(111,'discount','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(112,'product-expiry-report','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(113,'purchase-payment-index','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(114,'purchase-payment-add','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(115,'purchase-payment-edit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(116,'purchase-payment-delete','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(117,'sale-payment-index','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(118,'sale-payment-add','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(119,'sale-payment-edit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(120,'sale-payment-delete','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(121,'all_notification','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(122,'sale-report-chart','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(123,'dso-report','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(124,'product_history','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(125,'supplier-due-report','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(126,'custom_field','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(127,'incomes-index','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(128,'incomes-add','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(129,'incomes-edit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(130,'incomes-delete','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(131,'packing_slip_challan','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(132,'biller-report','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(133,'payment_gateway_setting','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(134,'barcode_setting','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(135,'language_setting','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(136,'addons','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(137,'account-selection','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(138,'invoice_setting','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(139,'invoice_create_edit_delete','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(140,'handle_discount','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(145,'products-import','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(146,'purchases-import','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(147,'sales-import','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(148,'customers-import','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(149,'billers-import','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(150,'suppliers-import','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(151,'categories-add','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(152,'categories-import','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(153,'categories-index','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(154,'categories-edit','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(155,'categories-delete','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(156,'role_permission','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(157,'cart-product-update','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(158,'transfers-import','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(159,'change_sale_date','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(160,'sidebar_product','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(161,'sidebar_purchase','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(162,'sidebar_sale','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(163,'sidebar_quotation','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(164,'sidebar_transfer','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(165,'sidebar_expense','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(166,'sidebar_income','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(167,'sidebar_accounting','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(168,'sidebar_hrm','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(169,'sidebar_people','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(170,'sidebar_reports','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(171,'sidebar_settings','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(172,'sale_export','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(173,'product_export','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(174,'purchase_export','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(175,'designations','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(176,'shift','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(177,'overtime','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(178,'leave-type','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(179,'leave','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(180,'hrm-panel','web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(181,'sale-agents','web','2026-01-03 02:00:15','2026-01-03 02:00:15');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\User',2,'auth-token','c447a2009decac540d74ea3059e54136e5891904334efec2fb58e1e4d519b5d2','[\"*\"]','2026-01-03 03:00:07',NULL,'2026-01-03 02:24:36','2026-01-03 03:00:07'),(2,'App\\Models\\User',2,'auth-token','4c8636d1f30c1b8a1ef26127ae64d83ca83fe809b0a464afb659d82b8198fc36','[\"*\"]','2026-01-06 18:11:18',NULL,'2026-01-06 16:47:29','2026-01-06 18:11:18'),(3,'App\\Models\\User',2,'auth-token','c53c7aac92b94dede933f170a8bfb8f87e238d31cb2173eb351e089f9f1ec6b8','[\"*\"]','2026-01-06 18:14:00',NULL,'2026-01-06 18:12:49','2026-01-06 18:14:00'),(4,'App\\Models\\User',2,'auth-token','0f2ad7e889ac367d943c04da3a61482a23e1e97330136ba226cdcb7d70d6a764','[\"*\"]','2026-01-06 18:40:33',NULL,'2026-01-06 18:20:05','2026-01-06 18:40:33'),(5,'App\\Models\\User',2,'auth-token','7f33c7dba6ff420a76bc33c21db43688155c3161cd2dda078061bd97e09d726d','[\"*\"]','2026-01-06 19:03:54',NULL,'2026-01-06 18:43:37','2026-01-06 19:03:54'),(6,'App\\Models\\User',2,'auth-token','a4d07dffd470156d195674d037f33f4b1dfd289ec6cc6935cdcbbfd798da7e9b','[\"*\"]','2026-01-06 19:09:48',NULL,'2026-01-06 19:09:29','2026-01-06 19:09:48'),(7,'App\\Models\\User',2,'auth-token','5040382c84a10d84ff199e3e5b630bad032af9cf4ba7af25b6694dfa259fe7ac','[\"*\"]','2026-01-06 23:23:09',NULL,'2026-01-06 23:20:03','2026-01-06 23:23:09'),(8,'App\\Models\\User',2,'auth-token','e5732e215b9624dfe290960acd38a71a965306ccace949ed7c1b5b1a1cae1c25','[\"*\"]','2026-01-06 23:30:00',NULL,'2026-01-06 23:29:24','2026-01-06 23:30:00'),(9,'App\\Models\\User',2,'auth-token','bac37c64a4ce6d42c07ffc235cf71f3e25a973ee9c1c2b6c10453d7a995ebe26','[\"*\"]','2026-01-06 23:47:37',NULL,'2026-01-06 23:36:04','2026-01-06 23:47:37'),(10,'App\\Models\\User',2,'auth-token','307a5fa06dbc5ccb678f6f50cd40d86541df6fc7a8a8dfd8e7c6b90969053afd','[\"*\"]','2026-01-07 00:25:24',NULL,'2026-01-07 00:00:28','2026-01-07 00:25:24'),(11,'App\\Models\\User',2,'auth-token','94e7342df8b552193c202cfd16a6d4366bc0770f56d6fd7d6944928df0dcd127','[\"*\"]','2026-01-07 00:39:38',NULL,'2026-01-07 00:28:26','2026-01-07 00:39:38'),(12,'App\\Models\\User',2,'auth-token','d40791c88de1bf701b565d87e5d89b1eeebc5fea08d8c6714296e2cbce6b8ee9','[\"*\"]','2026-01-07 00:58:00',NULL,'2026-01-07 00:42:09','2026-01-07 00:58:00'),(13,'App\\Models\\User',2,'auth-token','e447f366c5e57171f7b742b683143d48bd9cd1e30c46e6a888f34872c04f3069','[\"*\"]','2026-01-07 01:11:41',NULL,'2026-01-07 01:10:48','2026-01-07 01:11:41'),(14,'App\\Models\\User',2,'auth-token','b43cc8ca510d9e728addfaae812bb3da8e0d8db94872dde5eb7bf94f6bbc33e4','[\"*\"]','2026-01-07 01:48:08',NULL,'2026-01-07 01:33:52','2026-01-07 01:48:08');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pos_setting`
--

DROP TABLE IF EXISTS `pos_setting`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_setting` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `biller_id` bigint unsigned NOT NULL,
  `product_number` int NOT NULL,
  `keybord_active` tinyint(1) NOT NULL,
  `is_table` tinyint(1) NOT NULL DEFAULT '0',
  `send_sms` tinyint(1) NOT NULL DEFAULT '0',
  `stripe_public_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stripe_secret_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_live_api_username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_live_api_password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paypal_live_api_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `payment_options` text COLLATE utf8mb4_unicode_ci,
  `show_print_invoice` tinyint(1) NOT NULL DEFAULT '1',
  `invoice_option` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `thermal_invoice_size` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '80',
  `cash_register` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `pos_setting_customer_id_index` (`customer_id`),
  KEY `pos_setting_warehouse_id_index` (`warehouse_id`),
  KEY `pos_setting_biller_id_index` (`biller_id`),
  CONSTRAINT `pos_setting_biller_id_foreign` FOREIGN KEY (`biller_id`) REFERENCES `billers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `pos_setting_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `pos_setting_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pos_setting`
--

LOCK TABLES `pos_setting` WRITE;
/*!40000 ALTER TABLE `pos_setting` DISABLE KEYS */;
INSERT INTO `pos_setting` VALUES (1,1,1,1,2,1,0,0,NULL,NULL,NULL,NULL,NULL,'cash,card,cheque,gift_card,deposit,paypal',1,'thermal','80',0,'2026-01-03 02:00:15','2026-01-03 02:00:15');
/*!40000 ALTER TABLE `pos_setting` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `printers`
--

DROP TABLE IF EXISTS `printers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `printers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `connection_type` enum('network','windows','linux') COLLATE utf8mb4_unicode_ci NOT NULL,
  `capability_profile` enum('default','simple','SP2000','TEP-200M','TM-U220','RP326','P822D') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'default',
  `char_per_line` int NOT NULL DEFAULT '42',
  `ip_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `port` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `printers_warehouse_id_index` (`warehouse_id`),
  KEY `printers_created_by_index` (`created_by`),
  CONSTRAINT `printers_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `printers_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `printers`
--

LOCK TABLES `printers` WRITE;
/*!40000 ALTER TABLE `printers` DISABLE KEYS */;
/*!40000 ALTER TABLE `printers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_adjustments`
--

DROP TABLE IF EXISTS `product_adjustments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_adjustments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `adjustment_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `variant_id` bigint unsigned DEFAULT NULL,
  `unit_cost` double DEFAULT NULL,
  `qty` double NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_adjustments_adjustment_id_index` (`adjustment_id`),
  KEY `product_adjustments_product_id_index` (`product_id`),
  KEY `product_adjustments_variant_id_index` (`variant_id`),
  CONSTRAINT `product_adjustments_adjustment_id_foreign` FOREIGN KEY (`adjustment_id`) REFERENCES `adjustments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_adjustments_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `product_adjustments_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_adjustments`
--

LOCK TABLES `product_adjustments` WRITE;
/*!40000 ALTER TABLE `product_adjustments` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_adjustments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_batches`
--

DROP TABLE IF EXISTS `product_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_batches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `batch_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expired_date` date NOT NULL,
  `qty` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_batches_product_id_index` (`product_id`),
  KEY `product_batches_batch_no_index` (`batch_no`),
  KEY `product_batches_expired_date_index` (`expired_date`),
  CONSTRAINT `product_batches_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_batches`
--

LOCK TABLES `product_batches` WRITE;
/*!40000 ALTER TABLE `product_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_productions`
--

DROP TABLE IF EXISTS `product_productions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_productions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `production_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `qty` double NOT NULL,
  `recieved` double NOT NULL,
  `purchase_unit_id` bigint unsigned NOT NULL,
  `net_unit_cost` double NOT NULL,
  `tax_rate` double NOT NULL,
  `tax` double NOT NULL,
  `total` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_productions_production_id_index` (`production_id`),
  KEY `product_productions_product_id_index` (`product_id`),
  KEY `product_productions_purchase_unit_id_index` (`purchase_unit_id`),
  CONSTRAINT `product_productions_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `product_productions_production_id_foreign` FOREIGN KEY (`production_id`) REFERENCES `productions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_productions_purchase_unit_id_foreign` FOREIGN KEY (`purchase_unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_productions`
--

LOCK TABLES `product_productions` WRITE;
/*!40000 ALTER TABLE `product_productions` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_productions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_purchases`
--

DROP TABLE IF EXISTS `product_purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_purchases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `purchase_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_batch_id` bigint unsigned DEFAULT NULL,
  `variant_id` bigint unsigned DEFAULT NULL,
  `imei_number` text COLLATE utf8mb4_unicode_ci,
  `qty` double NOT NULL,
  `recieved` double NOT NULL,
  `return_qty` double NOT NULL DEFAULT '0',
  `purchase_unit_id` bigint unsigned NOT NULL,
  `net_unit_cost` double NOT NULL,
  `net_unit_margin` decimal(8,2) NOT NULL DEFAULT '0.00',
  `net_unit_margin_type` enum('flat','percentage') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percentage',
  `net_unit_price` decimal(8,2) NOT NULL DEFAULT '0.00',
  `discount` double NOT NULL,
  `tax_rate` double NOT NULL,
  `tax` double NOT NULL,
  `total` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_purchases_purchase_id_index` (`purchase_id`),
  KEY `product_purchases_product_id_index` (`product_id`),
  KEY `product_purchases_variant_id_index` (`variant_id`),
  KEY `product_purchases_purchase_unit_id_index` (`purchase_unit_id`),
  KEY `product_purchases_product_batch_id_foreign` (`product_batch_id`),
  CONSTRAINT `product_purchases_product_batch_id_foreign` FOREIGN KEY (`product_batch_id`) REFERENCES `product_batches` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `product_purchases_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `product_purchases_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_purchases_purchase_unit_id_foreign` FOREIGN KEY (`purchase_unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `product_purchases_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_purchases`
--

LOCK TABLES `product_purchases` WRITE;
/*!40000 ALTER TABLE `product_purchases` DISABLE KEYS */;
INSERT INTO `product_purchases` VALUES (1,1,1,NULL,NULL,NULL,10,10,0,1,10,0.00,'percentage',0.00,0,10,10,110,'2026-01-03 02:00:15','2026-01-03 02:00:15');
/*!40000 ALTER TABLE `product_purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_quotation`
--

DROP TABLE IF EXISTS `product_quotation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_quotation` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `quotation_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_batch_id` bigint unsigned DEFAULT NULL,
  `variant_id` bigint unsigned DEFAULT NULL,
  `qty` double NOT NULL,
  `sale_unit_id` bigint unsigned NOT NULL,
  `net_unit_price` double NOT NULL,
  `discount` double NOT NULL,
  `tax_rate` double NOT NULL,
  `tax` double NOT NULL,
  `total` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_quotation_quotation_id_index` (`quotation_id`),
  KEY `product_quotation_product_id_index` (`product_id`),
  KEY `product_quotation_variant_id_index` (`variant_id`),
  KEY `product_quotation_sale_unit_id_index` (`sale_unit_id`),
  KEY `product_quotation_product_batch_id_foreign` (`product_batch_id`),
  CONSTRAINT `product_quotation_product_batch_id_foreign` FOREIGN KEY (`product_batch_id`) REFERENCES `product_batches` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `product_quotation_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `product_quotation_quotation_id_foreign` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_quotation_sale_unit_id_foreign` FOREIGN KEY (`sale_unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `product_quotation_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_quotation`
--

LOCK TABLES `product_quotation` WRITE;
/*!40000 ALTER TABLE `product_quotation` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_quotation` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_returns`
--

DROP TABLE IF EXISTS `product_returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_returns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `return_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_batch_id` bigint unsigned DEFAULT NULL,
  `variant_id` bigint unsigned DEFAULT NULL,
  `imei_number` text COLLATE utf8mb4_unicode_ci,
  `qty` double NOT NULL,
  `sale_unit_id` bigint unsigned NOT NULL,
  `net_unit_price` double NOT NULL,
  `discount` double NOT NULL,
  `tax_rate` double NOT NULL,
  `tax` double NOT NULL,
  `total` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_returns_return_id_index` (`return_id`),
  KEY `product_returns_product_id_index` (`product_id`),
  KEY `product_returns_variant_id_index` (`variant_id`),
  KEY `product_returns_sale_unit_id_index` (`sale_unit_id`),
  KEY `product_returns_product_batch_id_foreign` (`product_batch_id`),
  CONSTRAINT `product_returns_product_batch_id_foreign` FOREIGN KEY (`product_batch_id`) REFERENCES `product_batches` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `product_returns_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `product_returns_return_id_foreign` FOREIGN KEY (`return_id`) REFERENCES `returns` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_returns_sale_unit_id_foreign` FOREIGN KEY (`sale_unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `product_returns_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_returns`
--

LOCK TABLES `product_returns` WRITE;
/*!40000 ALTER TABLE `product_returns` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_reviews`
--

DROP TABLE IF EXISTS `product_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `customer_id` bigint unsigned DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating` tinyint unsigned DEFAULT NULL,
  `review` text COLLATE utf8mb4_unicode_ci,
  `approved` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_reviews_product_id_index` (`product_id`),
  KEY `product_reviews_customer_id_index` (`customer_id`),
  KEY `product_reviews_approved_index` (`approved`),
  KEY `product_reviews_rating_index` (`rating`),
  CONSTRAINT `product_reviews_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `product_reviews_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_reviews`
--

LOCK TABLES `product_reviews` WRITE;
/*!40000 ALTER TABLE `product_reviews` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_reviews` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_sales`
--

DROP TABLE IF EXISTS `product_sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_sales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sale_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_batch_id` bigint unsigned DEFAULT NULL,
  `variant_id` bigint unsigned DEFAULT NULL,
  `imei_number` text COLLATE utf8mb4_unicode_ci,
  `qty` double NOT NULL,
  `return_qty` double NOT NULL DEFAULT '0',
  `sale_unit_id` bigint unsigned NOT NULL,
  `net_unit_price` double NOT NULL,
  `discount` double NOT NULL,
  `tax_rate` double NOT NULL,
  `tax` double NOT NULL,
  `total` double NOT NULL,
  `is_delivered` tinyint(1) DEFAULT NULL,
  `is_packing` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_sales_sale_id_index` (`sale_id`),
  KEY `product_sales_product_id_index` (`product_id`),
  KEY `product_sales_variant_id_index` (`variant_id`),
  KEY `product_sales_sale_unit_id_index` (`sale_unit_id`),
  KEY `product_sales_product_batch_id_foreign` (`product_batch_id`),
  CONSTRAINT `product_sales_product_batch_id_foreign` FOREIGN KEY (`product_batch_id`) REFERENCES `product_batches` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `product_sales_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `product_sales_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_sales_sale_unit_id_foreign` FOREIGN KEY (`sale_unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `product_sales_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_sales`
--

LOCK TABLES `product_sales` WRITE;
/*!40000 ALTER TABLE `product_sales` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_supplier`
--

DROP TABLE IF EXISTS `product_supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_supplier` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` bigint unsigned NOT NULL,
  `qty` double NOT NULL,
  `price` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_supplier_product_code_index` (`product_code`),
  KEY `product_supplier_supplier_id_index` (`supplier_id`),
  KEY `product_supplier_product_code_supplier_id_index` (`product_code`,`supplier_id`),
  CONSTRAINT `product_supplier_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_supplier`
--

LOCK TABLES `product_supplier` WRITE;
/*!40000 ALTER TABLE `product_supplier` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_supplier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_transfer`
--

DROP TABLE IF EXISTS `product_transfer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_transfer` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transfer_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_batch_id` bigint unsigned DEFAULT NULL,
  `variant_id` bigint unsigned DEFAULT NULL,
  `imei_number` text COLLATE utf8mb4_unicode_ci,
  `qty` double NOT NULL,
  `purchase_unit_id` bigint unsigned NOT NULL,
  `net_unit_cost` double NOT NULL,
  `tax_rate` double NOT NULL,
  `tax` double NOT NULL,
  `total` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_transfer_transfer_id_index` (`transfer_id`),
  KEY `product_transfer_product_id_index` (`product_id`),
  KEY `product_transfer_variant_id_index` (`variant_id`),
  KEY `product_transfer_purchase_unit_id_index` (`purchase_unit_id`),
  KEY `product_transfer_product_batch_id_foreign` (`product_batch_id`),
  CONSTRAINT `product_transfer_product_batch_id_foreign` FOREIGN KEY (`product_batch_id`) REFERENCES `product_batches` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `product_transfer_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `product_transfer_purchase_unit_id_foreign` FOREIGN KEY (`purchase_unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `product_transfer_transfer_id_foreign` FOREIGN KEY (`transfer_id`) REFERENCES `transfers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_transfer_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_transfer`
--

LOCK TABLES `product_transfer` WRITE;
/*!40000 ALTER TABLE `product_transfer` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_transfer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_variants`
--

DROP TABLE IF EXISTS `product_variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `variant_id` bigint unsigned NOT NULL,
  `position` int NOT NULL,
  `item_code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `additional_cost` double DEFAULT NULL,
  `additional_price` double DEFAULT NULL,
  `qty` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `product_variants_product_id_variant_id_unique` (`product_id`,`variant_id`),
  KEY `product_variants_product_id_index` (`product_id`),
  KEY `product_variants_variant_id_index` (`variant_id`),
  KEY `product_variants_item_code_index` (`item_code`),
  CONSTRAINT `product_variants_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_variants_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_variants`
--

LOCK TABLES `product_variants` WRITE;
/*!40000 ALTER TABLE `product_variants` DISABLE KEYS */;
/*!40000 ALTER TABLE `product_variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `product_warehouse`
--

DROP TABLE IF EXISTS `product_warehouse`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `product_warehouse` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `product_id` bigint unsigned NOT NULL,
  `product_batch_id` bigint unsigned DEFAULT NULL,
  `variant_id` bigint unsigned DEFAULT NULL,
  `imei_number` text COLLATE utf8mb4_unicode_ci,
  `warehouse_id` bigint unsigned NOT NULL,
  `qty` double NOT NULL,
  `price` double DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `product_warehouse_product_id_index` (`product_id`),
  KEY `product_warehouse_warehouse_id_index` (`warehouse_id`),
  KEY `product_warehouse_variant_id_index` (`variant_id`),
  KEY `product_warehouse_product_batch_id_index` (`product_batch_id`),
  KEY `pw_variant_batch_idx` (`product_id`,`warehouse_id`,`variant_id`,`product_batch_id`),
  CONSTRAINT `product_warehouse_product_batch_id_foreign` FOREIGN KEY (`product_batch_id`) REFERENCES `product_batches` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `product_warehouse_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `product_warehouse_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `product_warehouse_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `product_warehouse`
--

LOCK TABLES `product_warehouse` WRITE;
/*!40000 ALTER TABLE `product_warehouse` DISABLE KEYS */;
INSERT INTO `product_warehouse` VALUES (1,1,NULL,NULL,NULL,1,10,20,'2026-01-03 02:00:15','2026-01-03 02:00:15');
/*!40000 ALTER TABLE `product_warehouse` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `productions`
--

DROP TABLE IF EXISTS `productions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `productions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `item` int NOT NULL,
  `total_qty` int NOT NULL,
  `total_tax` double NOT NULL,
  `total_cost` double NOT NULL,
  `shipping_cost` double DEFAULT NULL,
  `production_cost` double NOT NULL DEFAULT '0',
  `grand_total` double NOT NULL,
  `status` int NOT NULL,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `production_units_ids` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wastage_percent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_list` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty_list` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_list` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `productions_reference_no_index` (`reference_no`),
  KEY `productions_warehouse_id_index` (`warehouse_id`),
  KEY `productions_user_id_index` (`user_id`),
  KEY `productions_status_index` (`status`),
  KEY `productions_created_at_index` (`created_at`),
  CONSTRAINT `productions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `productions_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `productions`
--

LOCK TABLES `productions` WRITE;
/*!40000 ALTER TABLE `productions` DISABLE KEYS */;
/*!40000 ALTER TABLE `productions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `barcode_symbology` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand_id` bigint unsigned DEFAULT NULL,
  `category_id` bigint unsigned NOT NULL,
  `unit_id` bigint unsigned NOT NULL,
  `purchase_unit_id` bigint unsigned NOT NULL,
  `sale_unit_id` bigint unsigned NOT NULL,
  `cost` double NOT NULL,
  `price` double NOT NULL,
  `profit_margin` decimal(8,2) NOT NULL DEFAULT '0.00',
  `profit_margin_type` enum('flat','percentage') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'percentage',
  `wholesale_price` double DEFAULT NULL,
  `qty` double DEFAULT NULL,
  `alert_quantity` double DEFAULT NULL,
  `daily_sale_objective` double DEFAULT NULL,
  `promotion` tinyint DEFAULT NULL,
  `promotion_price` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `starting_date` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_date` date DEFAULT NULL,
  `tax_id` bigint unsigned DEFAULT NULL,
  `tax_method` int DEFAULT NULL,
  `image` longtext COLLATE utf8mb4_unicode_ci,
  `file` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_embeded` tinyint(1) DEFAULT NULL,
  `is_variant` tinyint(1) DEFAULT NULL,
  `is_batch` tinyint(1) DEFAULT NULL,
  `is_diffPrice` tinyint(1) DEFAULT NULL,
  `is_imei` tinyint(1) DEFAULT NULL,
  `featured` tinyint DEFAULT NULL,
  `is_online` tinyint DEFAULT NULL,
  `in_stock` tinyint DEFAULT NULL,
  `track_inventory` tinyint NOT NULL DEFAULT '0',
  `product_list` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `variant_list` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty_list` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price_list` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_details` text COLLATE utf8mb4_unicode_ci,
  `short_description` text COLLATE utf8mb4_unicode_ci,
  `specification` text COLLATE utf8mb4_unicode_ci,
  `meta_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text COLLATE utf8mb4_unicode_ci,
  `related_products` longtext COLLATE utf8mb4_unicode_ci,
  `variant_option` text COLLATE utf8mb4_unicode_ci,
  `variant_value` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) DEFAULT NULL,
  `is_sync_disable` tinyint DEFAULT NULL,
  `woocommerce_product_id` int DEFAULT NULL,
  `woocommerce_media_id` int DEFAULT NULL,
  `guarantee` int DEFAULT NULL,
  `warranty` int DEFAULT NULL,
  `guarantee_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `warranty_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wastage_percent` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `combo_unit_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `production_cost` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `is_recipe` bigint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `products_purchase_unit_id_foreign` (`purchase_unit_id`),
  KEY `products_sale_unit_id_foreign` (`sale_unit_id`),
  KEY `products_code_index` (`code`),
  KEY `products_slug_index` (`slug`),
  KEY `products_brand_id_index` (`brand_id`),
  KEY `products_category_id_index` (`category_id`),
  KEY `products_unit_id_index` (`unit_id`),
  KEY `products_tax_id_index` (`tax_id`),
  KEY `products_type_index` (`type`),
  KEY `products_is_active_index` (`is_active`),
  KEY `products_is_variant_index` (`is_variant`),
  KEY `products_is_batch_index` (`is_batch`),
  KEY `products_is_imei_index` (`is_imei`),
  CONSTRAINT `products_brand_id_foreign` FOREIGN KEY (`brand_id`) REFERENCES `brands` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `products_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `products_purchase_unit_id_foreign` FOREIGN KEY (`purchase_unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `products_sale_unit_id_foreign` FOREIGN KEY (`sale_unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `products_tax_id_foreign` FOREIGN KEY (`tax_id`) REFERENCES `taxes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `products_unit_id_foreign` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES (1,'Zenbook 14 OLED (UX3402)｜Laptops For Home – ASUS',NULL,NULL,'59028109','standard','C128',2,6,1,1,1,1099.99,1299.99,0.00,'percentage',NULL,624.7,NULL,NULL,1,'1050.99','2024-01-08',NULL,1,2,'202401081146401.png',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(2,'2021 Apple 12.9-inch iPad Pro Wi-Fi 512GB',NULL,NULL,'20358923','standard','C128',3,6,1,1,1,1000,1249,0.00,'percentage',NULL,-152.5,NULL,NULL,1,'1200.00','2024-01-08',NULL,1,2,'202401081246041.png,202401081246062.png,202401081246063.png,202401081246064.png',NULL,0,NULL,NULL,0,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(3,'Apple iPhone 11 (4GB-64GB) Black',NULL,NULL,'49251814','standard','C128',1,1,1,1,1,300,350,0.00,'percentage',NULL,-47.7,NULL,NULL,1,'330','2024-01-08',NULL,1,2,'202401081255081.png,202401081255112.png,202401081255123.png,202401081255134.png,202401081255135.png',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(4,'Samsung Galaxy Chromebook Go, 14″ HD LED, Intel Celeron N4500',NULL,NULL,'28090345','standard','C128',2,6,1,1,1,900,1050,0.00,'percentage',NULL,-18.78,NULL,NULL,NULL,NULL,NULL,NULL,1,2,'202401080121221.png,202401080121242.png,202401080121243.png,202401080121254.png',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(5,'SAMSUNG Galaxy Book Pro 15.6 Laptop – Intel Core i5',NULL,NULL,'67015642','standard','C128',2,6,1,1,1,950.99,1150.99,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,1,2,'202401080124321.png,202401080124342.png,202401080124353.png',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(6,'Microsoft – Surface Laptop 4 13.5” Touch-Screen – AMD Ryzen 5',NULL,NULL,'24005329','standard','C128',3,6,1,1,1,999.99,1111.99,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202401080127451.png,202401080127462.png,202401080127473.jpg,202401080127484.jpg,202401080127485.jpg',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(7,'Acer Chromebook 315, 15.6 HD – Intel Celeron N4000',NULL,NULL,'30798200','standard','C128',4,6,1,1,1,899.99,999.99,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,1,2,'202401080130241.png,202401080130242.png,202401080130253.png',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(8,'HP Victus 16-e00244AX GTX 1650 Gaming Laptop 16.1” FHD 144Hz',NULL,NULL,'81526930','standard','C128',4,6,1,1,1,1199,1300,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,1,2,'202401080134061.png,202401080134072.png,202401080134073.png',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(9,'Epson Inkjet WorkForce Pro WF-3820DWF',NULL,NULL,'20142029','standard','C128',2,6,1,1,1,399,559,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202401080141091.png,202401080141102.png,202401080141103.png',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(10,'iPhone 14 Pro 256GB Gold',NULL,NULL,'29733132','standard','C128',1,1,1,1,1,990,1250,0.00,'percentage',NULL,84,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202401080143591.png,202401080144002.png,202401080144013.png,202401080144014.png',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(14,'Sony Bravia 55X90J 4K Ultra HD 55″ 140 Screen Google Smart LED TV',NULL,NULL,'16530612','standard','C128',3,23,1,1,1,350,499,0.00,'percentage',NULL,-1,NULL,NULL,NULL,NULL,NULL,NULL,1,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(15,'Samsung 43AU7000 4K Ultra HD 43″ 109 Screen Smart LED TV',NULL,NULL,'73189124','standard','C128',2,23,1,1,1,499,547,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202401130357131.png,202401130357152.png,202401130357153.png',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(16,'Apple TV HD 32GB (2nd Generation)',NULL,NULL,'71493353','standard','C128',1,23,1,1,1,79,109,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202401130401491.png,202401130401522.png,202401130401533.png,202401130401544.png',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(17,'Apple Watch SE GPS + Cellular 40mm Space Gray',NULL,NULL,'92178104','standard','C128',1,12,1,1,1,349,499,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202401130410191.png,202401130410222.jpg,202401130410233.jpg',NULL,0,NULL,NULL,0,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(18,'Xbox One Wireless Controller Black Color',NULL,NULL,'93060790','standard','C128',NULL,1,1,1,1,459,599,0.00,'percentage',NULL,-5,NULL,NULL,NULL,NULL,NULL,NULL,1,2,'202401150808421.jpg,202401150808432.jpg',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<div class=@item-description@>\r\n                    <p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                    <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>\r\n                </div>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(19,'Apple iPhone XS Max-64GB -white',NULL,NULL,'22061536','standard','C128',1,1,1,1,1,899,1059,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,1,2,'202401150814131.jpg',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<div class=@item-description@>\r\n                    <p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                    <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>\r\n                </div>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(20,'Apple Watch Series 8 GPS 45mm Midnight Aluminum Case',NULL,NULL,'31429623','standard','C128',1,12,1,1,1,399,499,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202401151009571.png,202401151009582.png,202401151009583.jpg',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<div class=@item-description@>\r\n                    <p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                    <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>\r\n                </div>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(21,'Huawei Watch GT 2 Sport Stainless Steel 46mm',NULL,NULL,'02456392','standard','C128',3,12,1,1,1,369,599,0.00,'percentage',NULL,0,NULL,NULL,1,'499','2024-01-15',NULL,NULL,1,'202401151013061.png,202401151013062.png,202401151013073.png',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<div class=@item-description@>\r\n                    <p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                    <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>\r\n                </div>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(22,'Samsung Galaxy Active 2 R835U Smartwatch 40mm',NULL,NULL,'10203743','standard','C128',2,12,1,1,1,275,399,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202401151019301.png,202401151019302.png,202401151019313.png',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<div class=@item-description@>\r\n                    <p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                    <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>\r\n                </div>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(23,'Canon EOS R10 RF-S 18-45 IS STM',NULL,NULL,'13929367','standard','C128',NULL,1,1,1,1,439,577,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202401151024231.png,202401151024232.png,202401151024233.png',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<div class=@item-description@>\r\n                    <p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                    <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>\r\n                </div>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(24,'Sony A7 III Mirrorless Camera Body Only',NULL,NULL,'99421096','standard','C128',2,1,1,1,1,299,379,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,1,2,'202401151026581.png,202401151026592.png',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<div class=@item-description@>\r\n                    <p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                    <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>\r\n                </div>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(25,'WOLFANG GA420 Action Camera 4K 60FPS 24MP',NULL,NULL,'99218280','standard','C128',4,1,1,1,1,130,157.99,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202401151029321.png,202401151029332.jpg,202401151029343.jpg',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<div class=@item-description@>\r\n                    <p>Quisque varius diam vel metus mattis, id aliquam diam rhoncus. Proin vitae magna in dui finibus malesuada et at nulla. Morbi elit ex, viverra vitae ante vel, blandit feugiat ligula. Fusce fermentum iaculis nibh, at sodales leo maximus a. Nullam ultricies sodales nunc, in pellentesque lorem mattis quis. Cras imperdiet est in nunc tristique lacinia. Nullam aliquam mauris eu accumsan tincidunt. Suspendisse velit ex, aliquet vel ornare vel, dignissim a tortor.</p>\r\n                    <p>Morbi ut sapien vitae odio accumsan gravida. Morbi vitae erat auctor, eleifend nunc a, lobortis neque. Praesent aliquam dignissim viverra. Maecenas lacus odio, feugiat eu nunc sit amet, maximus sagittis dolor. Vivamus nisi sapien, elementum sit amet eros sit amet, ultricies cursus ipsum. Sed consequat luctus ligula. Curabitur laoreet rhoncus blandit. Aenean vel diam ut arcu pharetra dignissim ut sed leo. Vivamus faucibus, ipsum in vestibulum vulputate, lorem orci convallis quam, sit amet consequat nulla felis pharetra lacus. Duis semper erat mauris, sed egestas purus commodo vel.</p>\r\n                </div>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(26,'Fresh Organic Navel Orange',NULL,NULL,'33887520','standard','C128',NULL,29,1,1,1,2.99,3.99,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202401151115301.png',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>Fresh Organic Navel Orange</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(27,'Banana (pack of 12)',NULL,NULL,'27583341','standard','C128',NULL,29,1,1,1,0.89,1.29,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202401151118271.png',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(28,'Water Melon ~ 3KG',NULL,NULL,'19186147','standard','C128',NULL,29,1,1,1,2.39,3.3,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202401151142511.png',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>Water Melon ~ 3KG</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(29,'Gala Original Apple - 1KG',NULL,NULL,'80912386','standard','C128',NULL,29,1,1,1,2.39,3.19,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202401151144271.png',NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>Gala Original Apple - 1KG</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(31,'Men&#039;s Premium Egyptian Cotton T-shirt',NULL,NULL,'30282941','standard','C128',NULL,39,1,1,1,50.5,70.99,0.00,'percentage',NULL,-13,NULL,NULL,NULL,NULL,NULL,NULL,1,2,'202402040508081.jpg',NULL,0,1,NULL,0,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'[\"Size\",\"Color\"]','[\"S,M,L,XL,XXL\",\"red,green,blue\"]',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(34,'Bon Sprayer',NULL,NULL,'09138264','standard','C128',1,2,1,1,1,115,130,0.00,'percentage',NULL,338.5,5,NULL,NULL,NULL,NULL,NULL,NULL,1,'zummXD2dvAtI.png',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'[\"Color\"]','[\"Red,Yellow,Green,Bule\"]',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(35,'Toffee',NULL,NULL,'76722958','standard','C128',1,1,1,1,1,10,20,0.00,'percentage',NULL,48,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'zummXD2dvAtI.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(36,'AMD RYZEN 5 5600G',NULL,NULL,'1001','standard','C128',1,2,1,1,1,2500,3500,0.00,'percentage',NULL,6,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'zummXD2dvAtI.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(37,'KINGSTON 8GB RAM',NULL,NULL,'1002','standard','C128',1,2,1,1,1,1000,1450,0.00,'percentage',NULL,8,5,NULL,NULL,NULL,NULL,NULL,NULL,1,'202403080446151.jpg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(38,'MI BUILD PACKAGE',NULL,NULL,'1004','combo','C128',1,1,1,1,1,0,4950,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202403080452061.JPG',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,'36,37',',','1,1','3500,1450','',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(39,'Irene Jack',NULL,NULL,'3456','standard','C128',NULL,1,1,1,1,1000,899,0.00,'percentage',NULL,84,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'zummXD2dvAtI.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(41,'off white Tshirt',NULL,NULL,'75308742','standard','C128',NULL,39,1,1,1,4.8,8,0.00,'percentage',NULL,-1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'zummXD2dvAtI.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(42,'test',NULL,NULL,'125','standard','C128',1,1,1,1,1,12,124,0.00,'percentage',NULL,0,NULL,-1,NULL,NULL,NULL,NULL,NULL,1,'zummXD2dvAtI.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(43,'samsung laptop',NULL,NULL,'65317202','standard','C128',2,6,1,1,1,50000,55000,0.00,'percentage',NULL,-5,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'zummXD2dvAtI.png',NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(44,'samsung laptop 15',NULL,NULL,'67600232','standard','C128',2,6,1,1,1,55000,60000,0.00,'percentage',NULL,3,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'zummXD2dvAtI.png',NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(45,'TAKA',NULL,NULL,'81639204','standard','C128',2,6,1,1,1,3000,3500,0.00,'percentage',NULL,2,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'zummXD2dvAtI.png',NULL,1,NULL,NULL,NULL,1,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(47,'Apple 14',NULL,NULL,'apple14','standard','C128',1,3,1,1,1,80000,85000,0.00,'percentage',NULL,19,5,10,NULL,NULL,NULL,NULL,NULL,2,'zummXD2dvAtI.png',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(48,'Laptop11',NULL,NULL,'1111111','standard','C128',2,6,1,1,1,30000,32500,0.00,'percentage',NULL,0,2,NULL,NULL,NULL,NULL,NULL,NULL,1,'zummXD2dvAtI.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(49,'Shirt',NULL,NULL,'112233','service','C39',1,1,1,1,1,0,10,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'zummXD2dvAtI.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(50,'14 pro max',NULL,NULL,'34692007','standard','C128',1,1,1,1,1,15000,16000,0.00,'percentage',NULL,4,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'zummXD2dvAtI.png',NULL,1,1,NULL,NULL,1,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'[\"RAM | ROM\",\"Color\"]','[\"128GB,256GB,512GB\",\"SpaceBlack,Silver,Gold,DeepPurple\"]',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(51,'Iphone 15 Pro Max',NULL,NULL,'63028277','standard','C128',1,1,1,1,1,0,0,0.00,'percentage',NULL,145,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202405130525171.jpg',NULL,NULL,1,NULL,NULL,1,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'[\"Condition\",\"RAM | ROM\",\"Color\"]','[\"Brand New,Pre-Owned\",\"256GB,512GB\",\"BlackTitanium,WhiteTitanium,BlueTitanium,NaturalTitanium\"]',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(52,'Product Test',NULL,NULL,'KK','standard','C128',1,1,1,1,1,44,23,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,'2024-05-18',NULL,NULL,1,'202405180442251.png',NULL,NULL,1,NULL,1,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'[\"Quantity\",\"Size\",\"Price\",\"Color\"]','[\"3KG,2KG,5KG\",\"Large,Medium,Small\",\"120,500,70\",\"RED,GReen,Blue\"]',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(58,'PRUEBA',NULL,NULL,'000','standard','C128',1,3,1,1,1,7,7,0.00,'percentage',7,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202406111018041.jpg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(59,'Prueba Easy',NULL,NULL,'190','standard','C128',1,3,1,1,1,777,777,0.00,'percentage',777,0,120,65,1,'150','2024-06-11','2024-06-20',1,1,'202406111023031.jpg',NULL,1,1,NULL,NULL,NULL,1,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,'[\"BLANCO\",\"NEGRO\"]','[\"199\",\"299\"]',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(60,'Producto Prueba',NULL,NULL,'777','standard','C128',2,4,1,1,1,200,200,0.00,'percentage',200,0,10,10,1,'175',NULL,'2024-06-25',NULL,1,'202406111027511.png,202406111027512.jpg,202406111027513.jpg,202406111027514.jpg',NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>Prueba de imagenes easymax</p>',NULL,NULL,NULL,NULL,NULL,'[\"NEGRO\",\"NEGRO\",\"NEGRO\",\"NEGRO\"]','[\"255\",\"255\",\"255\",\"255\"]',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(61,'IPHONE 14 PRO MAX',NULL,NULL,'01234','standard','C128',1,3,1,1,1,1500,1500,0.00,'percentage',1499,0,NULL,15,1,'1299','2024-06-11','2024-06-25',NULL,1,'202406111040331.jpg,202406111040342.jpg,202406111040343.jpg',NULL,NULL,1,NULL,1,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p><a class=@sh-anchor@ href=@https://www.bing.com/ck/a?!&amp;&amp;p=3057d02af68c6961JmltdHM9MTcxODA2NDAwMCZpZ3VpZD0zZTNlNjhiMy1jOTY5LTZkYzYtMzJjMS03Y2Q0Yzg1NjZjMDUmaW5zaWQ9NjU4Mw&amp;ptn=3&amp;ver=2&amp;hsh=3&amp;fclid=3e3e68b3-c969-6dc6-32c1-7cd4c8566c05&amp;psq=apple+descripcion&amp;u=a1aHR0cHM6Ly9odW1hbmlkYWRlcy5jb20vYXBwbGUv&amp;ntb=1@ target=@_blank@ rel=@noopener@ data-tg-citations=@1;2@ data-tgpsgid=@d_anstgsen0@>Apple es una&nbsp;<strong>empresa multinacional estadounidense que dise&ntilde;a, fabrica y vende productos electr&oacute;nicos y de software</strong></a><a class=@sup-target@ href=@https://www.bing.com/ck/a?!&amp;&amp;p=d1b09c46723a5d2aJmltdHM9MTcxODA2NDAwMCZpZ3VpZD0zZTNlNjhiMy1jOTY5LTZkYzYtMzJjMS03Y2Q0Yzg1NjZjMDUmaW5zaWQ9NjU4NA&amp;ptn=3&amp;ver=2&amp;hsh=3&amp;fclid=3e3e68b3-c969-6dc6-32c1-7cd4c8566c05&amp;psq=apple+descripcion&amp;u=a1aHR0cHM6Ly9odW1hbmlkYWRlcy5jb20vYXBwbGUv&amp;ntb=1@ target=@_blank@ rel=@noopener@ data-tgpsgid=@d_anstgpsg1@><sup>1</sup></a><a class=@sup-target@ href=@https://www.bing.com/ck/a?!&amp;&amp;p=21491c7be3d7d5d9JmltdHM9MTcxODA2NDAwMCZpZ3VpZD0zZTNlNjhiMy1jOTY5LTZkYzYtMzJjMS03Y2Q0Yzg1NjZjMDUmaW5zaWQ9NjU4NQ&amp;ptn=3&amp;ver=2&amp;hsh=3&amp;fclid=3e3e68b3-c969-6dc6-32c1-7cd4c8566c05&amp;psq=apple+descripcion&amp;u=a1aHR0cHM6Ly93d3cuMTJjYXJhY3RlcmlzdGljYXMuY29tL2FwcGxlLw&amp;ntb=1@ target=@_blank@ rel=@noopener@ data-tgpsgid=@d_anstgpsg2@><sup>2</sup></a>.&nbsp;<a class=@sh-anchor@ href=@https://www.bing.com/ck/a?!&amp;&amp;p=c80ea6db0a534e3eJmltdHM9MTcxODA2NDAwMCZpZ3VpZD0zZTNlNjhiMy1jOTY5LTZkYzYtMzJjMS03Y2Q0Yzg1NjZjMDUmaW5zaWQ9NjU4Ng&amp;ptn=3&amp;ver=2&amp;hsh=3&amp;fclid=3e3e68b3-c969-6dc6-32c1-7cd4c8566c05&amp;psq=apple+descripcion&amp;u=a1aHR0cHM6Ly93d3cuMTJjYXJhY3RlcmlzdGljYXMuY29tL2FwcGxlLw&amp;ntb=1@ target=@_blank@ rel=@noopener@ data-tg-citations=@2@ data-tgpsgid=@d_anstgsen1@>Entre sus productos m&aacute;s conocidos se encuentran el iPhone, el iPad, el Mac, el iPod, el Apple Watch y el Apple TV. Tambi&eacute;n ofrece servicios en l&iacute;nea como iTunes, iCloud, Apple Music y Apple Pay. Apple tiene su sede en el Apple Park, en Cupertino, California, y su centro europeo en Cork, Irlanda</a></p>',NULL,NULL,NULL,NULL,NULL,'[\"Color Blanco\",\"Color Rosa\",\"RAM\",\"Almacenamiento\",\"Color Blanco\",\"Color Rosa\",\"RAM\",\"Almacenamiento\",\"Color Blanco\",\"Color Rosa\",\"RAM\",\"Almacenamiento\"]','[\"1600\",\"1699\",\"8,16,32\",\"32,64,128\",\"1600\",\"1699\",\"8,16,32\",\"32,64,128\",\"1600\",\"1699\",\"8,16,32\",\"32,64,128\"]',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(62,'T-Shirt',NULL,NULL,'003','standard','C128',NULL,4,1,1,1,8000,9500,0.00,'percentage',NULL,0,3,NULL,NULL,NULL,'2024-06-21',NULL,NULL,1,'202406210233561.jpg',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(63,'Laptop',NULL,NULL,'83058761','standard','C39',2,6,1,1,1,1000,2000,0.00,'percentage',500,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'zummXD2dvAtI.png',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(64,'Glass',NULL,NULL,'37580174','standard','UPCA',NULL,4,1,1,1,70,100,0.00,'percentage',60,0,5,3,NULL,NULL,NULL,NULL,NULL,1,'202407010954551.png',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(65,'test prod',NULL,NULL,'862837','standard','C128',1,3,1,1,1,100,150,0.00,'percentage',120,27,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'zummXD2dvAtI.png',NULL,1,1,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'<p>test desc</p>',NULL,NULL,NULL,NULL,NULL,'[\"Size\",\"Colour\"]','[\"S,M,L\",\"R,g,b\"]',1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(66,'Earphone True Wireless G70',NULL,NULL,'2312021280054','standard','C128',1,3,1,1,1,58,17,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202409080650101.jpg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'<h1 style=@text-align: right;@>هیدفۆنی وایەرلێس جی٧٠</h1>\r\n                <p style=@text-align: right;@>&nbsp;</p>\r\n                <h2 style=@text-align: right;@>تایبەتمەندیەکانی</h2>\r\n                <p style=@text-align: right;@>هیدفۆنی بێ وایەر</p>\r\n                <p style=@text-align: right;@>سیستەمی گونجاو :&nbsp;ئەندرۆید / ئی ئۆ ئێس / ویندۆس</p>\r\n                <p style=@text-align: right;@>وەشانی بلوتوز :&nbsp;٥.٣</p>\r\n                <p style=@text-align: right;@>توانای پاتری :&nbsp;٣٠ میلی ئەمپێر</p>\r\n                <p style=@text-align: right;@>توانای پاتری سندوقی شەحنکردنەوە :&nbsp;٢٥٠ میلی ئەمپێر</p>\r\n                <p style=@text-align: right;@>شێوازی شەحنکردنەوە :&nbsp;شەحنکردنەوەی جۆری سی</p>\r\n                <p style=@text-align: right;@>تەمەنی پاتری : نزیکەی ٤ بۆ ٥ کاتژمێر</p>\r\n                <p style=@text-align: right;@>&nbsp;</p>\r\n                <h1>Earphone True Wireless G70</h1>\r\n                <p style=@text-align: justify;@>&nbsp;</p>\r\n                <h2>Specification</h2>\r\n                <p>Product Type :&nbsp;Wireless Earbuds</p>\r\n                <p>Brand :&nbsp;UiiSii</p>\r\n                <p>Model :&nbsp;TWS-G70</p>\r\n                <p>Compatible Systems :&nbsp;ios/android/Windows</p>\r\n                <p>Bluetooth Version :&nbsp;5.3</p>\r\n                <p>Battery Capacity :&nbsp;30 mAh</p>\r\n                <p>Charging Box Battery Capacity :&nbsp;250 mAh</p>\r\n                <p>Charging Method :&nbsp;TYPE-C charging</p>\r\n                <p>Buds Battery Life :&nbsp;About 4 to 5 hours</p>\r\n                <p>&nbsp;</p>',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(67,'T shirt',NULL,NULL,'07116185','standard','C128',1,3,1,1,1,500,1000,0.00,'percentage',700,0,100,100,NULL,NULL,'2024-10-12',NULL,NULL,1,'zummXD2dvAtI.png',NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL),(68,'kannadasan yogaraja',NULL,NULL,'38259140','standard','C128',2,2,1,1,1,200,10,0.00,'percentage',NULL,0,NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,'202410261213471.png,202410261213482.jpeg',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,0,NULL,NULL,NULL,NULL,'',NULL,NULL,NULL,NULL,NULL,NULL,NULL,1,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'0',NULL,'0',0,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_product_return`
--

DROP TABLE IF EXISTS `purchase_product_return`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchase_product_return` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `return_id` bigint unsigned NOT NULL,
  `product_id` bigint unsigned NOT NULL,
  `product_batch_id` bigint unsigned DEFAULT NULL,
  `variant_id` bigint unsigned DEFAULT NULL,
  `imei_number` text COLLATE utf8mb4_unicode_ci,
  `qty` double NOT NULL,
  `purchase_unit_id` bigint unsigned NOT NULL,
  `net_unit_cost` double NOT NULL,
  `discount` double NOT NULL,
  `tax_rate` double NOT NULL,
  `tax` double NOT NULL,
  `total` double NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_product_return_return_id_index` (`return_id`),
  KEY `purchase_product_return_product_id_index` (`product_id`),
  KEY `purchase_product_return_variant_id_index` (`variant_id`),
  KEY `purchase_product_return_purchase_unit_id_index` (`purchase_unit_id`),
  KEY `purchase_product_return_product_batch_id_foreign` (`product_batch_id`),
  CONSTRAINT `purchase_product_return_product_batch_id_foreign` FOREIGN KEY (`product_batch_id`) REFERENCES `product_batches` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `purchase_product_return_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `purchase_product_return_purchase_unit_id_foreign` FOREIGN KEY (`purchase_unit_id`) REFERENCES `units` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `purchase_product_return_return_id_foreign` FOREIGN KEY (`return_id`) REFERENCES `return_purchases` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `purchase_product_return_variant_id_foreign` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_product_return`
--

LOCK TABLES `purchase_product_return` WRITE;
/*!40000 ALTER TABLE `purchase_product_return` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_product_return` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `purchases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `currency_id` bigint unsigned DEFAULT NULL,
  `exchange_rate` double DEFAULT NULL,
  `item` int NOT NULL,
  `total_qty` double NOT NULL,
  `total_discount` double NOT NULL,
  `total_tax` double NOT NULL,
  `total_cost` double NOT NULL,
  `order_tax_rate` double DEFAULT NULL,
  `order_tax` double DEFAULT NULL,
  `order_discount` double DEFAULT NULL,
  `shipping_cost` double DEFAULT NULL,
  `grand_total` double NOT NULL,
  `paid_amount` double NOT NULL,
  `status` int NOT NULL,
  `payment_status` int NOT NULL,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `purchase_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchases_currency_id_foreign` (`currency_id`),
  KEY `purchases_deleted_by_foreign` (`deleted_by`),
  KEY `purchases_reference_no_index` (`reference_no`),
  KEY `purchases_user_id_index` (`user_id`),
  KEY `purchases_warehouse_id_index` (`warehouse_id`),
  KEY `purchases_supplier_id_index` (`supplier_id`),
  KEY `purchases_status_index` (`status`),
  KEY `purchases_payment_status_index` (`payment_status`),
  KEY `purchases_created_at_index` (`created_at`),
  CONSTRAINT `purchases_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `purchases_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `purchases_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `purchases_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `purchases_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchases`
--

LOCK TABLES `purchases` WRITE;
/*!40000 ALTER TABLE `purchases` DISABLE KEYS */;
INSERT INTO `purchases` VALUES (1,'pr-20230528-125929',1,1,NULL,1,1,1,10,0,10,110,0,0,0,0,110,0,1,1,NULL,NULL,NULL,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL);
/*!40000 ALTER TABLE `purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `quotations`
--

DROP TABLE IF EXISTS `quotations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `quotations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `biller_id` bigint unsigned NOT NULL,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `item` int NOT NULL,
  `total_qty` double NOT NULL,
  `total_discount` double NOT NULL,
  `total_tax` double NOT NULL,
  `total_price` double NOT NULL,
  `order_tax_rate` double DEFAULT NULL,
  `order_tax` double DEFAULT NULL,
  `order_discount` double DEFAULT NULL,
  `shipping_cost` double DEFAULT NULL,
  `grand_total` double NOT NULL,
  `quotation_status` int NOT NULL,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `quotations_supplier_id_foreign` (`supplier_id`),
  KEY `quotations_reference_no_index` (`reference_no`),
  KEY `quotations_user_id_index` (`user_id`),
  KEY `quotations_biller_id_index` (`biller_id`),
  KEY `quotations_customer_id_index` (`customer_id`),
  KEY `quotations_warehouse_id_index` (`warehouse_id`),
  KEY `quotations_quotation_status_index` (`quotation_status`),
  KEY `quotations_created_at_index` (`created_at`),
  CONSTRAINT `quotations_biller_id_foreign` FOREIGN KEY (`biller_id`) REFERENCES `billers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `quotations_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `quotations_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `quotations_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `quotations_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `quotations`
--

LOCK TABLES `quotations` WRITE;
/*!40000 ALTER TABLE `quotations` DISABLE KEYS */;
/*!40000 ALTER TABLE `quotations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `return_purchases`
--

DROP TABLE IF EXISTS `return_purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `return_purchases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `supplier_id` bigint unsigned DEFAULT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `purchase_id` bigint unsigned DEFAULT NULL,
  `account_id` bigint unsigned NOT NULL,
  `currency_id` bigint unsigned DEFAULT NULL,
  `exchange_rate` double DEFAULT NULL,
  `item` int NOT NULL,
  `total_qty` double NOT NULL,
  `total_discount` double NOT NULL,
  `total_tax` double NOT NULL,
  `total_cost` double NOT NULL,
  `order_tax_rate` double DEFAULT NULL,
  `order_tax` double DEFAULT NULL,
  `grand_total` double NOT NULL,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `return_note` text COLLATE utf8mb4_unicode_ci,
  `staff_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `return_purchases_account_id_foreign` (`account_id`),
  KEY `return_purchases_currency_id_foreign` (`currency_id`),
  KEY `return_purchases_reference_no_index` (`reference_no`),
  KEY `return_purchases_supplier_id_index` (`supplier_id`),
  KEY `return_purchases_warehouse_id_index` (`warehouse_id`),
  KEY `return_purchases_user_id_index` (`user_id`),
  KEY `return_purchases_purchase_id_index` (`purchase_id`),
  KEY `return_purchases_created_at_index` (`created_at`),
  CONSTRAINT `return_purchases_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `return_purchases_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `return_purchases_purchase_id_foreign` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `return_purchases_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `return_purchases_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `return_purchases_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `return_purchases`
--

LOCK TABLES `return_purchases` WRITE;
/*!40000 ALTER TABLE `return_purchases` DISABLE KEYS */;
/*!40000 ALTER TABLE `return_purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `returns`
--

DROP TABLE IF EXISTS `returns`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `returns` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `sale_id` bigint unsigned DEFAULT NULL,
  `cash_register_id` bigint unsigned DEFAULT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `biller_id` bigint unsigned NOT NULL,
  `account_id` bigint unsigned NOT NULL,
  `currency_id` bigint unsigned DEFAULT NULL,
  `exchange_rate` double DEFAULT NULL,
  `item` int NOT NULL,
  `total_qty` double NOT NULL,
  `total_discount` double NOT NULL,
  `total_tax` double NOT NULL,
  `total_price` double NOT NULL,
  `order_tax_rate` double DEFAULT NULL,
  `order_tax` double DEFAULT NULL,
  `grand_total` double NOT NULL,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `return_note` text COLLATE utf8mb4_unicode_ci,
  `staff_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `returns_biller_id_foreign` (`biller_id`),
  KEY `returns_account_id_foreign` (`account_id`),
  KEY `returns_currency_id_foreign` (`currency_id`),
  KEY `returns_reference_no_index` (`reference_no`),
  KEY `returns_user_id_index` (`user_id`),
  KEY `returns_sale_id_index` (`sale_id`),
  KEY `returns_customer_id_index` (`customer_id`),
  KEY `returns_warehouse_id_index` (`warehouse_id`),
  KEY `returns_created_at_index` (`created_at`),
  CONSTRAINT `returns_account_id_foreign` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `returns_biller_id_foreign` FOREIGN KEY (`biller_id`) REFERENCES `billers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `returns_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `returns_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `returns_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `returns_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `returns_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `returns`
--

LOCK TABLES `returns` WRITE;
/*!40000 ALTER TABLE `returns` DISABLE KEYS */;
/*!40000 ALTER TABLE `returns` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reward_point_settings`
--

DROP TABLE IF EXISTS `reward_point_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reward_point_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `per_point_amount` double NOT NULL,
  `minimum_amount` double NOT NULL,
  `duration` int DEFAULT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL,
  `redeem_amount_per_unit_rp` decimal(10,2) DEFAULT NULL,
  `min_order_total_for_redeem` decimal(10,2) DEFAULT NULL,
  `min_redeem_point` int DEFAULT NULL,
  `max_redeem_point` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reward_point_settings_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reward_point_settings`
--

LOCK TABLES `reward_point_settings` WRITE;
/*!40000 ALTER TABLE `reward_point_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `reward_point_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reward_points`
--

DROP TABLE IF EXISTS `reward_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reward_points` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` bigint unsigned NOT NULL,
  `reward_point_type` enum('manual','automatic') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'automatic',
  `points` decimal(8,2) NOT NULL DEFAULT '0.00',
  `deducted_points` decimal(8,2) NOT NULL DEFAULT '0.00',
  `note` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expired_at` datetime DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `sale_id` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `reward_points_created_by_foreign` (`created_by`),
  KEY `reward_points_updated_by_foreign` (`updated_by`),
  KEY `reward_points_customer_id_index` (`customer_id`),
  KEY `reward_points_reward_point_type_index` (`reward_point_type`),
  KEY `reward_points_expired_at_index` (`expired_at`),
  KEY `reward_points_sale_id_index` (`sale_id`),
  CONSTRAINT `reward_points_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `reward_points_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `reward_points_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `reward_points_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reward_points`
--

LOCK TABLES `reward_points` WRITE;
/*!40000 ALTER TABLE `reward_points` DISABLE KEYS */;
/*!40000 ALTER TABLE `reward_points` ENABLE KEYS */;
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
INSERT INTO `role_has_permissions` VALUES (4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(35,1),(36,1),(37,1),(38,1),(39,1),(40,1),(41,1),(42,1),(43,1),(44,1),(45,1),(46,1),(47,1),(48,1),(49,1),(50,1),(51,1),(52,1),(53,1),(54,1),(55,1),(56,1),(57,1),(58,1),(59,1),(60,1),(61,1),(62,1),(63,1),(64,1),(65,1),(66,1),(67,1),(68,1),(69,1),(70,1),(71,1),(72,1),(73,1),(74,1),(75,1),(76,1),(77,1),(78,1),(79,1),(80,1),(81,1),(82,1),(83,1),(84,1),(85,1),(86,1),(87,1),(88,1),(89,1),(90,1),(91,1),(92,1),(93,1),(94,1),(95,1),(96,1),(97,1),(98,1),(99,1),(100,1),(101,1),(102,1),(103,1),(104,1),(105,1),(106,1),(107,1),(108,1),(109,1),(110,1),(111,1),(112,1),(113,1),(114,1),(115,1),(116,1),(117,1),(118,1),(119,1),(120,1),(121,1),(122,1),(123,1),(124,1),(125,1),(126,1),(127,1),(128,1),(129,1),(130,1),(131,1),(132,1),(133,1),(134,1),(135,1),(136,1),(137,1),(138,1),(139,1),(140,1),(145,1),(146,1),(147,1),(148,1),(149,1),(150,1),(151,1),(152,1),(153,1),(154,1),(155,1),(156,1),(157,1),(158,1),(159,1),(160,1),(161,1),(162,1),(163,1),(164,1),(165,1),(166,1),(167,1),(168,1),(169,1),(170,1),(171,1),(172,1),(173,1),(174,1),(175,1),(176,1),(177,1),(178,1),(179,1),(180,1),(181,1);
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
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Admin','admin can access all data...',1,'web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(2,'Owner','Staff of shop',1,'web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(4,'staff','staff has specific access...',1,'web','2026-01-03 02:00:15','2026-01-03 02:00:15'),(5,'Customer',NULL,1,'web','2026-01-03 02:00:15','2026-01-03 02:00:15');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sales`
--

DROP TABLE IF EXISTS `sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sales` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `cash_register_id` bigint unsigned DEFAULT NULL,
  `table_id` bigint unsigned DEFAULT NULL,
  `queue` int DEFAULT NULL,
  `customer_id` bigint unsigned NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `biller_id` bigint unsigned DEFAULT NULL,
  `item` int NOT NULL,
  `total_qty` double NOT NULL,
  `total_discount` double NOT NULL,
  `total_tax` double NOT NULL,
  `total_price` double NOT NULL,
  `grand_total` double NOT NULL,
  `steadfast` tinyint(1) NOT NULL DEFAULT '0',
  `currency_id` bigint unsigned DEFAULT NULL,
  `exchange_rate` double DEFAULT NULL,
  `order_tax_rate` double DEFAULT NULL,
  `order_tax` double DEFAULT NULL,
  `order_discount_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order_discount_value` double DEFAULT NULL,
  `order_discount` double DEFAULT NULL,
  `coupon_id` bigint unsigned DEFAULT NULL,
  `coupon_discount` double DEFAULT NULL,
  `shipping_cost` double DEFAULT NULL,
  `sale_status` int NOT NULL,
  `payment_status` int NOT NULL,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_amount` double DEFAULT NULL,
  `billing_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `billing_zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_city` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `shipping_zip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sale_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pos',
  `payment_mode` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sale_note` text COLLATE utf8mb4_unicode_ci,
  `staff_note` text COLLATE utf8mb4_unicode_ci,
  `woocommerce_order_id` int DEFAULT NULL,
  `deleted_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sales_currency_id_foreign` (`currency_id`),
  KEY `sales_deleted_by_foreign` (`deleted_by`),
  KEY `sales_reference_no_index` (`reference_no`),
  KEY `sales_user_id_index` (`user_id`),
  KEY `sales_customer_id_index` (`customer_id`),
  KEY `sales_warehouse_id_index` (`warehouse_id`),
  KEY `sales_biller_id_index` (`biller_id`),
  KEY `sales_sale_status_index` (`sale_status`),
  KEY `sales_payment_status_index` (`payment_status`),
  KEY `sales_sale_type_index` (`sale_type`),
  KEY `sales_created_at_index` (`created_at`),
  KEY `sales_table_id_foreign` (`table_id`),
  KEY `sales_cash_register_id_foreign` (`cash_register_id`),
  KEY `sales_coupon_id_foreign` (`coupon_id`),
  CONSTRAINT `sales_biller_id_foreign` FOREIGN KEY (`biller_id`) REFERENCES `billers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `sales_cash_register_id_foreign` FOREIGN KEY (`cash_register_id`) REFERENCES `cash_registers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `sales_coupon_id_foreign` FOREIGN KEY (`coupon_id`) REFERENCES `coupons` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `sales_currency_id_foreign` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `sales_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `sales_deleted_by_foreign` FOREIGN KEY (`deleted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `sales_table_id_foreign` FOREIGN KEY (`table_id`) REFERENCES `tables` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `sales_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `sales_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sales`
--

LOCK TABLES `sales` WRITE;
/*!40000 ALTER TABLE `sales` DISABLE KEYS */;
/*!40000 ALTER TABLE `sales` ENABLE KEYS */;
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
INSERT INTO `sessions` VALUES ('8QXGSGWJmJhNQz2uNhjBzvFBXz5Q0JH0xAhfhRgg',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVnFraW0yMjBLbmtSOXB2dTJKaTBtQVB5RElUQWhibXNXWGFCYUxhRCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzU6Imh0dHA6Ly9xdWljay1tYXJ0LWFwaS50ZXN0L2RvY3MvYXBpIjtzOjU6InJvdXRlIjtzOjE2OiJzY3JhbWJsZS5kb2NzLnVpIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1767410845);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shifts`
--

DROP TABLE IF EXISTS `shifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `shifts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `grace_in` int NOT NULL DEFAULT '0' COMMENT 'Grace period (minutes) before marking late',
  `grace_out` int NOT NULL DEFAULT '0' COMMENT 'Grace period (minutes) before marking early leave',
  `total_hours` decimal(5,2) DEFAULT NULL COMMENT 'Total working hours for the shift',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `shifts_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shifts`
--

LOCK TABLES `shifts` WRITE;
/*!40000 ALTER TABLE `shifts` DISABLE KEYS */;
/*!40000 ALTER TABLE `shifts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sliders`
--

DROP TABLE IF EXISTS `sliders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sliders` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image1` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image3` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `status` int NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sliders_order_index` (`order`),
  KEY `sliders_status_index` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sliders`
--

LOCK TABLES `sliders` WRITE;
/*!40000 ALTER TABLE `sliders` DISABLE KEYS */;
/*!40000 ALTER TABLE `sliders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sms_templates`
--

DROP TABLE IF EXISTS `sms_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sms_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT '0',
  `is_default_ecommerce` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sms_templates_is_default_index` (`is_default`),
  KEY `sms_templates_is_default_ecommerce_index` (`is_default_ecommerce`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sms_templates`
--

LOCK TABLES `sms_templates` WRITE;
/*!40000 ALTER TABLE `sms_templates` DISABLE KEYS */;
/*!40000 ALTER TABLE `sms_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `social_links`
--

DROP TABLE IF EXISTS `social_links`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `social_links` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `icon` text COLLATE utf8mb4_unicode_ci,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `order` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `social_links_order_index` (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `social_links`
--

LOCK TABLES `social_links` WRITE;
/*!40000 ALTER TABLE `social_links` DISABLE KEYS */;
/*!40000 ALTER TABLE `social_links` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_counts`
--

DROP TABLE IF EXISTS `stock_counts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_counts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `warehouse_id` bigint unsigned NOT NULL,
  `category_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `initial_file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `final_file` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `is_adjusted` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `stock_counts_reference_no_index` (`reference_no`),
  KEY `stock_counts_warehouse_id_index` (`warehouse_id`),
  KEY `stock_counts_user_id_index` (`user_id`),
  KEY `stock_counts_type_index` (`type`),
  KEY `stock_counts_is_adjusted_index` (`is_adjusted`),
  CONSTRAINT `stock_counts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `stock_counts_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_counts`
--

LOCK TABLES `stock_counts` WRITE;
/*!40000 ALTER TABLE `stock_counts` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_counts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `suppliers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `vat_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `wa_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postal_code` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opening_balance` double NOT NULL DEFAULT '0',
  `pay_term_no` int DEFAULT NULL,
  `pay_term_period` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `suppliers_email_index` (`email`),
  KEY `suppliers_phone_number_index` (`phone_number`),
  KEY `suppliers_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `suppliers`
--

LOCK TABLES `suppliers` WRITE;
/*!40000 ALTER TABLE `suppliers` DISABLE KEYS */;
INSERT INTO `suppliers` VALUES (1,'John Doe',NULL,'Test Company',NULL,'john@gmail.com','231312',NULL,'Test address','Test City',NULL,NULL,NULL,0,NULL,NULL,1,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL);
/*!40000 ALTER TABLE `suppliers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tables`
--

DROP TABLE IF EXISTS `tables`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tables` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `number_of_person` int DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `floor_id` tinyint NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tables_floor_id_index` (`floor_id`),
  KEY `tables_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tables`
--

LOCK TABLES `tables` WRITE;
/*!40000 ALTER TABLE `tables` DISABLE KEYS */;
/*!40000 ALTER TABLE `tables` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `taxes`
--

DROP TABLE IF EXISTS `taxes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `taxes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` double NOT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `woocommerce_tax_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `taxes_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `taxes`
--

LOCK TABLES `taxes` WRITE;
/*!40000 ALTER TABLE `taxes` DISABLE KEYS */;
INSERT INTO `taxes` VALUES (1,'VAT 10%',10,1,NULL,'2026-01-03 02:00:15','2026-01-03 02:00:15');
/*!40000 ALTER TABLE `taxes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tenants`
--

DROP TABLE IF EXISTS `tenants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tenants` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `data` json DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tenants`
--

LOCK TABLES `tenants` WRITE;
/*!40000 ALTER TABLE `tenants` DISABLE KEYS */;
/*!40000 ALTER TABLE `tenants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transfers`
--

DROP TABLE IF EXISTS `transfers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transfers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `reference_no` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `status` int NOT NULL,
  `from_warehouse_id` bigint unsigned NOT NULL,
  `to_warehouse_id` bigint unsigned NOT NULL,
  `item` int NOT NULL,
  `total_qty` double NOT NULL,
  `total_tax` double NOT NULL,
  `total_cost` double NOT NULL,
  `shipping_cost` double DEFAULT NULL,
  `grand_total` double NOT NULL,
  `document` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `is_sent` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `transfers_reference_no_index` (`reference_no`),
  KEY `transfers_user_id_index` (`user_id`),
  KEY `transfers_from_warehouse_id_index` (`from_warehouse_id`),
  KEY `transfers_to_warehouse_id_index` (`to_warehouse_id`),
  KEY `transfers_status_index` (`status`),
  KEY `transfers_created_at_index` (`created_at`),
  CONSTRAINT `transfers_from_warehouse_id_foreign` FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `transfers_to_warehouse_id_foreign` FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `transfers_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transfers`
--

LOCK TABLES `transfers` WRITE;
/*!40000 ALTER TABLE `transfers` DISABLE KEYS */;
/*!40000 ALTER TABLE `transfers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `translations`
--

DROP TABLE IF EXISTS `translations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `translations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `locale` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `group` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'db',
  `key` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `translations_locale_index` (`locale`),
  KEY `translations_group_index` (`group`),
  KEY `translations_locale_group_index` (`locale`,`group`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `translations`
--

LOCK TABLES `translations` WRITE;
/*!40000 ALTER TABLE `translations` DISABLE KEYS */;
/*!40000 ALTER TABLE `translations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `units` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `base_unit` bigint unsigned DEFAULT NULL,
  `operator` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operation_value` double DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `units_base_unit_index` (`base_unit`),
  KEY `units_is_active_index` (`is_active`),
  CONSTRAINT `units_base_unit_foreign` FOREIGN KEY (`base_unit`) REFERENCES `units` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units`
--

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
INSERT INTO `units` VALUES (1,'Pc','piece',NULL,'*',1,1,'2026-01-03 02:00:15','2026-01-03 02:00:15');
/*!40000 ALTER TABLE `units` ENABLE KEYS */;
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
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `role_id` bigint unsigned DEFAULT NULL,
  `biller_id` bigint unsigned DEFAULT NULL,
  `warehouse_id` bigint unsigned DEFAULT NULL,
  `kitchen_id` bigint unsigned DEFAULT NULL,
  `service_staff` tinyint(1) NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_role_id_foreign` (`role_id`),
  KEY `users_biller_id_foreign` (`biller_id`),
  KEY `users_warehouse_id_foreign` (`warehouse_id`),
  CONSTRAINT `users_biller_id_foreign` FOREIGN KEY (`biller_id`) REFERENCES `billers` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','admin@gmail.com','12112','Softmax Technologies',1,NULL,NULL,NULL,0,1,0,'2026-01-03 02:00:15','$2y$10$DWAHTfjcvwCpOCXaJg11MOhsqns03uvlwiSUOQwkHL2YYrtrXPcL6','6mN44MyRiQZfCi0QvFFIYAU9LXIUz9CdNIlrRS5Lg8wBoJmxVu8auzTP42ZW','2026-01-03 02:00:15','2026-01-03 02:00:15'),(2,'John Doe','john.doe@example.com','+1234567890','Acme Corporation',1,1,1,NULL,0,1,0,'2026-01-03 02:24:28','$2y$12$JkZUfLRJufB/QDlRfE21WOVM1hj94/ekiOH9FbCwT4F6yYF580OlK',NULL,'2026-01-03 02:01:48','2026-01-03 02:24:28');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `variants`
--

DROP TABLE IF EXISTS `variants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `variants` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `variants_name_index` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `variants`
--

LOCK TABLES `variants` WRITE;
/*!40000 ALTER TABLE `variants` DISABLE KEYS */;
/*!40000 ALTER TABLE `variants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `warehouses`
--

DROP TABLE IF EXISTS `warehouses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `warehouses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `warehouses_is_active_index` (`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `warehouses`
--

LOCK TABLES `warehouses` WRITE;
/*!40000 ALTER TABLE `warehouses` DISABLE KEYS */;
INSERT INTO `warehouses` VALUES (1,'Test Shop','9991111',NULL,'Test address',1,'2026-01-03 02:00:15','2026-01-03 02:00:15',NULL);
/*!40000 ALTER TABLE `warehouses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `whatsapp_settings`
--

DROP TABLE IF EXISTS `whatsapp_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `whatsapp_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `phone_number_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_account_id` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `permanent_access_token` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `whatsapp_settings`
--

LOCK TABLES `whatsapp_settings` WRITE;
/*!40000 ALTER TABLE `whatsapp_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `whatsapp_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `widgets`
--

DROP TABLE IF EXISTS `widgets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `widgets` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `order` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `feature_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feature_secondary_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feature_icon` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_info_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_info_description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_info_address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_info_phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_info_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `site_info_hours` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `newsletter_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `newsletter_text` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quick_links_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quick_links_menu` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `text_content` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `widgets_location_index` (`location`),
  KEY `widgets_order_index` (`order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `widgets`
--

LOCK TABLES `widgets` WRITE;
/*!40000 ALTER TABLE `widgets` DISABLE KEYS */;
/*!40000 ALTER TABLE `widgets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `woocommerce_settings`
--

DROP TABLE IF EXISTS `woocommerce_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `woocommerce_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `woocomerce_app_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `woocomerce_consumer_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `woocomerce_consumer_secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `default_tax_class` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_tax_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manage_stock` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stock_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `product_status` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_group_id` bigint unsigned DEFAULT NULL,
  `warehouse_id` bigint unsigned DEFAULT NULL,
  `biller_id` bigint unsigned DEFAULT NULL,
  `order_status_pending` tinyint DEFAULT NULL,
  `order_status_processing` tinyint DEFAULT NULL,
  `order_status_on_hold` tinyint DEFAULT NULL,
  `order_status_completed` tinyint DEFAULT NULL,
  `order_status_draft` tinyint DEFAULT NULL,
  `webhook_secret_order_created` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `webhook_secret_order_updated` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `webhook_secret_order_deleted` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `webhook_secret_order_restored` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `woocommerce_settings_customer_group_id_index` (`customer_group_id`),
  KEY `woocommerce_settings_warehouse_id_index` (`warehouse_id`),
  KEY `woocommerce_settings_biller_id_index` (`biller_id`),
  CONSTRAINT `woocommerce_settings_biller_id_foreign` FOREIGN KEY (`biller_id`) REFERENCES `billers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `woocommerce_settings_customer_group_id_foreign` FOREIGN KEY (`customer_group_id`) REFERENCES `customer_groups` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `woocommerce_settings_warehouse_id_foreign` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `woocommerce_settings`
--

LOCK TABLES `woocommerce_settings` WRITE;
/*!40000 ALTER TABLE `woocommerce_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `woocommerce_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `woocommerce_sync_logs`
--

DROP TABLE IF EXISTS `woocommerce_sync_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `woocommerce_sync_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sync_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `operation` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `records` longtext COLLATE utf8mb4_unicode_ci,
  `synced_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `woocommerce_sync_logs_sync_type_index` (`sync_type`),
  KEY `woocommerce_sync_logs_operation_index` (`operation`),
  KEY `woocommerce_sync_logs_synced_by_index` (`synced_by`),
  KEY `woocommerce_sync_logs_created_at_index` (`created_at`),
  CONSTRAINT `woocommerce_sync_logs_synced_by_foreign` FOREIGN KEY (`synced_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `woocommerce_sync_logs`
--

LOCK TABLES `woocommerce_sync_logs` WRITE;
/*!40000 ALTER TABLE `woocommerce_sync_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `woocommerce_sync_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'quick_mart_api'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-07  3:51:58
