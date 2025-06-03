-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 05, 2025 at 09:53 PM
-- Server version: 8.0.41-0ubuntu0.22.04.1
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `taravel_docker`
--

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `id` int NOT NULL,
  `account_num` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_name` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `initial_balance` decimal(10,2) NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`id`, `account_num`, `account_name`, `initial_balance`, `note`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, '1243242', 'Onchain', '10100.00', 'Citybank', '2025-03-25 11:58:37.000000', '2025-03-25 13:44:26.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `company_id` int NOT NULL,
  `department_id` int DEFAULT NULL,
  `summary` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `start_date`, `end_date`, `company_id`, `department_id`, `summary`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Announcement testing', '2025-03-25', '2025-03-31', 2, 2, 'Brief Summary of Announcement', 'Everyone should be maintaining this description', '2025-03-25 11:18:20.000000', '2025-03-25 11:18:20.000000', NULL),
(2, 'Test', '2025-04-27', '2025-04-29', 2, 3, 'test', 'Test', '2025-04-27 14:41:06.000000', '2025-04-27 14:41:06.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `arrangement_types`
--

CREATE TABLE `arrangement_types` (
  `id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendances`
--

CREATE TABLE `attendances` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `employee_id` int NOT NULL,
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
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendances`
--

INSERT INTO `attendances` (`id`, `company_id`, `employee_id`, `date`, `clock_in`, `clock_in_ip`, `clock_out`, `clock_out_ip`, `clock_in_out`, `depart_early`, `late_time`, `overtime`, `total_work`, `total_rest`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 3, '2025-03-25', '16:48', '::1', '', '', 1, '00:00', '04:19', '00:00', '00:00', '00:00', 'present', '2025-03-25 10:48:42.000000', '2025-03-25 10:48:42.000000', NULL),
(2, 1, 3, '2025-04-27', '12:36', '103.156.167.11', '', '', 1, '00:00', '00:00', '00:00', '00:00', '00:00', 'present', '2025-04-27 12:36:57.000000', '2025-04-27 12:36:57.000000', NULL),
(3, 1, 3, '2025-05-03', '16:28', '103.73.225.0', '16:29', '', 0, '02:01', '03:58', '00:00', '00:01', '00:00', 'present', '2025-05-03 16:28:04.000000', '2025-05-03 16:29:12.000000', NULL),
(4, 1, 3, '2025-05-03', '16:30', '103.73.225.0', '16:33', '', 0, '01:57', '00:00', '00:00', '00:03', '00:01', 'present', '2025-05-03 16:30:18.000000', '2025-05-03 16:33:42.000000', NULL),
(5, 1, 3, '2025-05-03', '16:34', '103.73.225.0', '16:41', '', 0, '01:49', '00:00', '00:00', '00:07', '00:01', 'present', '2025-05-03 16:34:30.000000', '2025-05-03 16:41:09.000000', NULL),
(6, 1, 3, '2025-05-03', '16:41', '103.73.225.0', '16:41', '', 0, '01:49', '00:00', '00:00', '00:00', '00:00', 'present', '2025-05-03 16:41:22.000000', '2025-05-03 16:41:35.000000', NULL),
(7, 1, 3, '2025-05-03', '16:42', '103.73.225.0', '16:46', '', 0, '01:44', '00:00', '00:00', '00:04', '00:01', 'present', '2025-05-03 16:42:01.000000', '2025-05-03 16:46:00.000000', NULL),
(8, 1, 3, '2025-05-03', '16:47', '103.73.225.0', '16:49', '', 0, '01:41', '00:00', '00:00', '00:02', '00:01', 'present', '2025-05-03 16:47:04.000000', '2025-05-03 16:49:03.000000', NULL),
(9, 1, 3, '2025-05-03', '16:49', '103.73.225.0', '16:50', '', 0, '01:40', '00:00', '00:00', '00:01', '00:00', 'present', '2025-05-03 16:49:26.000000', '2025-05-03 16:50:48.000000', NULL),
(10, 1, 3, '2025-05-03', '16:54', '103.73.225.0', '16:56', '', 0, '01:34', '00:00', '00:00', '00:02', '00:04', 'present', '2025-05-03 16:54:11.000000', '2025-05-03 16:56:50.000000', NULL),
(11, 1, 3, '2025-05-03', '16:58', '103.73.225.0', '17:01', '', 0, '01:29', '00:00', '00:00', '00:03', '00:02', 'present', '2025-05-03 16:58:21.000000', '2025-05-03 17:01:24.000000', NULL),
(12, 1, 3, '2025-05-03', '17:02', '103.73.225.0', '17:09', '', 0, '01:21', '00:00', '00:00', '00:07', '00:01', 'present', '2025-05-03 17:02:29.000000', '2025-05-03 17:09:21.000000', NULL),
(13, 1, 3, '2025-05-03', '17:11', '103.73.225.0', '17:20', '', 0, '01:10', '00:00', '00:00', '00:09', '00:02', 'present', '2025-05-03 17:11:24.000000', '2025-05-03 17:20:24.000000', NULL),
(14, 1, 3, '2025-05-03', '17:20', '103.73.225.0', '17:22', '', 0, '01:08', '00:00', '00:00', '00:02', '00:00', 'present', '2025-05-03 17:20:56.000000', '2025-05-03 17:22:04.000000', NULL),
(15, 1, 3, '2025-05-03', '17:22', '103.73.225.0', '17:26', '', 0, '01:04', '00:00', '00:00', '00:04', '00:00', 'present', '2025-05-03 17:22:52.000000', '2025-05-03 17:26:26.000000', NULL),
(16, 1, 3, '2025-05-03', '17:28', '103.73.225.0', '17:29', '', 0, '01:01', '00:00', '00:00', '00:01', '00:02', 'present', '2025-05-03 17:28:21.000000', '2025-05-03 17:29:02.000000', NULL),
(17, 1, 3, '2025-05-03', '17:30', '103.73.225.0', '17:34', '', 0, '00:56', '00:00', '00:00', '00:04', '00:01', 'present', '2025-05-03 17:30:56.000000', '2025-05-03 17:34:48.000000', NULL),
(18, 1, 3, '2025-05-03', '17:36', '103.73.225.0', '17:36', '', 0, '00:54', '00:00', '00:00', '00:00', '00:02', 'present', '2025-05-03 17:36:08.000000', '2025-05-03 17:36:38.000000', NULL),
(19, 1, 3, '2025-05-03', '17:36', '103.73.225.0', '17:37', '', 0, '00:53', '00:00', '00:00', '00:01', '00:00', 'present', '2025-05-03 17:36:49.000000', '2025-05-03 17:37:04.000000', NULL),
(20, 1, 3, '2025-05-03', '17:37', '103.73.225.0', '17:39', '', 0, '00:51', '00:00', '00:00', '00:02', '00:00', 'present', '2025-05-03 17:37:11.000000', '2025-05-03 17:39:52.000000', NULL),
(21, 1, 3, '2025-05-03', '17:40', '103.73.225.0', '17:40', '', 0, '00:50', '00:00', '00:00', '00:00', '00:01', 'present', '2025-05-03 17:40:02.000000', '2025-05-03 17:40:15.000000', NULL),
(22, 1, 3, '2025-05-03', '17:40', '103.73.225.0', '17:42', '', 0, '00:48', '00:00', '00:00', '00:02', '00:00', 'present', '2025-05-03 17:40:20.000000', '2025-05-03 17:42:27.000000', NULL),
(23, 1, 3, '2025-05-03', '17:42', '103.73.225.0', '17:42', '', 0, '00:48', '00:00', '00:00', '00:00', '00:00', 'present', '2025-05-03 17:42:31.000000', '2025-05-03 17:42:46.000000', NULL),
(24, 1, 3, '2025-05-03', '17:42', '103.73.225.0', '17:45', '', 0, '00:45', '00:00', '00:00', '00:03', '00:00', 'present', '2025-05-03 17:42:52.000000', '2025-05-03 17:45:32.000000', NULL),
(25, 1, 3, '2025-05-03', '17:45', '103.73.225.0', '17:46', '', 0, '00:44', '00:00', '00:00', '00:01', '00:00', 'present', '2025-05-03 17:45:46.000000', '2025-05-03 17:46:00.000000', NULL),
(26, 1, 3, '2025-05-03', '17:46', '103.73.225.0', '17:46', '', 0, '00:44', '00:00', '00:00', '00:00', '00:00', 'present', '2025-05-03 17:46:06.000000', '2025-05-03 17:46:10.000000', NULL),
(27, 1, 3, '2025-05-03', '17:57', '103.73.225.0', '17:57', '', 0, '00:33', '00:00', '00:00', '00:00', '00:11', 'present', '2025-05-03 17:57:20.000000', '2025-05-03 17:57:32.000000', NULL),
(28, 1, 3, '2025-05-03', '18:40', '103.73.225.0', '18:43', '', 0, '00:00', '00:00', '00:03', '00:03', '00:43', 'present', '2025-05-03 18:40:13.000000', '2025-05-03 18:43:33.000000', NULL),
(29, 3, 8, '2025-05-03', '20:11', '103.73.225.0', '20:11', '', 0, '00:00', '09:41', '00:00', '00:00', '00:00', 'present', '2025-05-03 20:11:17.000000', '2025-05-03 20:11:49.000000', NULL),
(30, 3, 8, '2025-05-03', '20:12', '103.73.225.0', '20:19', '', 0, '00:00', '00:00', '00:07', '00:07', '00:01', 'present', '2025-05-03 20:12:02.000000', '2025-05-03 20:19:48.000000', NULL),
(31, 3, 8, '2025-05-03', '20:20', '103.73.225.0', '21:00', '', 0, '00:00', '00:00', '00:40', '00:40', '00:01', 'present', '2025-05-03 20:20:38.000000', '2025-05-03 21:00:42.000000', NULL),
(32, 3, 9, '2025-05-04', '07:37', '103.73.225.0', '07:38', '', 0, '00:00', '00:00', '00:00', '00:01', '00:00', 'present', '2025-05-04 07:37:29.000000', '2025-05-04 07:38:10.000000', NULL),
(33, 3, 9, '2025-05-04', '08:00', '103.73.225.0', '08:00', '', 0, '10:30', '00:00', '00:00', '00:00', '00:22', 'present', '2025-05-04 08:00:13.000000', '2025-05-04 08:00:56.000000', NULL),
(34, 3, 9, '2025-05-04', '08:02', '103.73.225.0', '08:03', '', 0, '00:00', '00:00', '00:00', '00:01', '00:02', 'present', '2025-05-04 08:02:57.000000', '2025-05-04 08:03:00.000000', NULL),
(35, 3, 9, '2025-05-04', '08:59', '103.73.225.0', '20:30', '', 0, '00:00', '00:00', '00:00', '11:31', '00:56', 'present', '2025-05-04 08:59:59.000000', '2025-05-05 08:53:29.000000', NULL),
(36, 3, 8, '2025-05-04', '12:08', '103.23.255.99', '16:40', '', 0, '01:50', '01:38', '00:00', '04:32', '00:00', 'present', '2025-05-04 12:08:47.000000', '2025-05-04 16:40:51.000000', NULL),
(37, 3, 10, '2025-05-04', '12:49', '103.23.255.99', '16:40', '', 0, '01:50', '02:19', '00:00', '03:51', '00:00', 'present', '2025-05-04 12:49:32.000000', '2025-05-04 16:40:30.000000', NULL),
(38, 3, 13, '2025-05-04', '15:11', '180.148.210.41', '15:13', '', 0, '03:17', '04:41', '00:00', '00:02', '00:00', 'present', '2025-05-04 15:11:25.000000', '2025-05-04 15:13:14.000000', NULL),
(39, 3, 13, '2025-05-04', '15:17', '180.148.210.41', '15:23', '', 0, '03:07', '00:00', '00:00', '00:06', '00:04', 'present', '2025-05-04 15:17:51.000000', '2025-05-04 15:23:26.000000', NULL),
(40, 3, 13, '2025-05-04', '15:36', '180.148.210.41', '16:39', '', 0, '01:51', '00:00', '00:00', '01:03', '00:13', 'present', '2025-05-04 15:36:31.000000', '2025-05-04 16:39:37.000000', NULL),
(41, 3, 11, '2025-05-04', '15:56', '103.140.83.67', '16:39', '', 0, '01:51', '05:26', '00:00', '00:43', '00:00', 'present', '2025-05-04 15:56:27.000000', '2025-05-04 16:39:37.000000', NULL),
(42, 3, 8, '2025-05-04', '16:40', '103.23.255.99', '20:09', '', 0, '00:00', '00:00', '01:39', '03:29', '00:00', 'present', '2025-05-04 16:40:55.000000', '2025-05-04 20:09:52.000000', NULL),
(43, 3, 12, '2025-05-04', '16:45', '103.159.186.88', '20:34', '', 0, '00:00', '06:15', '02:04', '03:49', '00:00', 'present', '2025-05-04 16:45:57.000000', '2025-05-04 20:34:26.000000', NULL),
(44, 3, 14, '2025-05-04', '16:46', '103.23.255.99', '20:31', '', 0, '00:00', '00:00', '00:00', '03:45', '00:00', 'present', '2025-05-04 16:46:23.000000', '2025-05-05 10:40:31.000000', NULL),
(45, 3, 13, '2025-05-04', '16:54', '180.148.210.41', '17:04', '', 0, '01:26', '00:00', '00:00', '00:10', '00:15', 'present', '2025-05-04 16:54:55.000000', '2025-05-04 17:04:13.000000', NULL),
(46, 3, 11, '2025-05-04', '16:54', '103.140.83.67', '18:12', '', 0, '00:18', '00:00', '00:00', '01:18', '00:15', 'present', '2025-05-04 16:54:58.000000', '2025-05-04 18:12:32.000000', NULL),
(47, 3, 13, '2025-05-04', '17:20', '180.148.210.41', '18:25', '', 0, '00:05', '00:00', '00:00', '01:05', '00:16', 'present', '2025-05-04 17:20:54.000000', '2025-05-04 18:25:47.000000', NULL),
(48, 3, 9, '2025-05-05', '06:57', '103.73.225.0', '08:13', '', 0, '00:00', '00:00', '00:00', '01:16', '00:00', 'present', '2025-05-05 06:57:16.000000', '2025-05-05 08:13:36.000000', NULL),
(49, 3, 9, '2025-05-05', '08:13', '103.73.225.0', '08:50', '', 0, '00:00', '00:00', '00:00', '00:37', '00:00', 'present', '2025-05-05 08:13:48.000000', '2025-05-05 08:53:06.000000', NULL),
(50, 1, 3, '2025-05-05', '08:23', '103.73.225.0', '', '', 1, '00:00', '08:18', '00:00', '00:00', '00:00', 'present', '2025-05-05 08:23:49.000000', '2025-05-05 08:43:07.000000', '2025-05-05 08:43:07'),
(51, 1, 3, '2025-05-05', '08:43', '103.73.225.0', '', '', 1, '00:00', '08:38', '00:00', '00:00', '00:00', 'present', '2025-05-05 08:43:34.000000', '2025-05-05 08:50:30.000000', '2025-05-05 08:50:30'),
(52, 1, 3, '2025-05-05', '08:51', '103.73.225.0', '08:51', '', 0, '00:00', '08:46', '00:00', '00:00', '00:00', 'present', '2025-05-05 08:51:21.000000', '2025-05-05 08:52:18.000000', '2025-05-05 08:52:18'),
(53, 3, 9, '2025-05-05', '09:28', '103.73.225.0', '10:20', '', 0, '00:00', '00:00', '00:00', '00:52', '00:38', 'present', '2025-05-05 09:28:27.000000', '2025-05-05 10:36:15.000000', NULL),
(54, 3, 13, '2025-05-05', '10:30', '180.148.210.41', '09:58', '', 0, '08:32', '00:00', '00:00', '00:32', '00:00', 'present', '2025-05-05 09:35:37.000000', '2025-05-05 09:58:04.000000', NULL),
(55, 3, 11, '2025-05-05', '10:30', '103.140.83.67', '12:50', '', 0, '05:40', '00:00', '00:00', '02:20', '00:00', 'present', '2025-05-05 09:54:49.000000', '2025-05-05 12:50:54.000000', NULL),
(56, 3, 13, '2025-05-05', '10:00', '180.148.210.41', '10:54', '', 0, '07:36', '00:00', '00:00', '00:54', '00:02', 'present', '2025-05-05 10:00:02.000000', '2025-05-05 10:54:25.000000', NULL),
(57, 3, 9, '2025-05-05', '10:41', '103.23.255.99', '13:02', '', 0, '00:00', '00:00', '00:00', '02:21', '00:21', 'present', '2025-05-05 10:41:03.000000', '2025-05-05 13:02:02.000000', NULL),
(58, 3, 13, '2025-05-05', '10:54', '180.148.210.41', '11:54', '', 0, '06:36', '00:00', '00:00', '01:00', '00:00', 'present', '2025-05-05 10:54:29.000000', '2025-05-05 11:54:33.000000', NULL),
(59, 3, 12, '2025-05-05', '11:37', '103.23.255.99', '', '', 1, '00:00', '01:07', '00:00', '00:00', '00:00', 'present', '2025-05-05 11:37:26.000000', '2025-05-05 11:37:26.000000', NULL),
(60, 3, 8, '2025-05-05', '12:18', '103.23.255.99', '20:54', '', 0, '00:00', '01:48', '02:24', '08:36', '00:00', 'present', '2025-05-05 12:18:37.000000', '2025-05-05 20:54:31.000000', NULL),
(61, 3, 10, '2025-05-05', '12:38', '103.23.255.99', '20:56', '', 0, '00:00', '02:08', '02:26', '08:18', '00:00', 'present', '2025-05-05 12:38:21.000000', '2025-05-05 20:56:31.000000', NULL),
(62, 3, 14, '2025-05-05', '12:52', '103.23.255.99', '', '', 1, '00:00', '02:22', '00:00', '00:00', '00:00', 'present', '2025-05-05 12:52:14.000000', '2025-05-05 12:52:14.000000', NULL),
(63, 3, 13, '2025-05-05', '12:57', '180.148.210.41', '16:34', '', 0, '01:56', '00:00', '00:00', '03:37', '01:03', 'present', '2025-05-05 12:57:12.000000', '2025-05-05 16:34:35.000000', NULL),
(64, 3, 11, '2025-05-05', '13:35', '103.140.83.67', '18:16', '', 0, '00:14', '00:00', '00:00', '04:41', '00:45', 'present', '2025-05-05 13:35:04.000000', '2025-05-05 18:16:22.000000', NULL),
(65, 3, 13, '2025-05-05', '16:57', '180.148.210.41', '19:04', '', 0, '00:00', '00:00', '00:34', '02:07', '00:23', 'present', '2025-05-05 16:57:05.000000', '2025-05-05 19:04:11.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `awards`
--

CREATE TABLE `awards` (
  `id` int NOT NULL,
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
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `award_types`
--

CREATE TABLE `award_types` (
  `id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int NOT NULL,
  `firstname` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lastname` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_users_id` bigint UNSIGNED NOT NULL,
  `code` int NOT NULL,
  `email` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `firstname`, `lastname`, `username`, `role_users_id`, `code`, `email`, `country`, `city`, `phone`, `address`, `created_at`, `updated_at`, `deleted_at`) VALUES
(4, 'MD. jesus', 'Hoque', 'MD. jesus Hoque', 3, 1, 'melosryz1@gmail.com', 'Bangladesh', 'Dhaka', '01708020605', 'Khilkhet', '2025-03-25 10:52:13.000000', '2025-05-05 20:01:39.000000', '2025-05-05 20:01:39'),
(15, 'Onchain Software', '& Research limited', 'Onchain Software & Research limited', 3, 2, 'onchainsoftwareresearch@gmail.com', 'Bangaldesh', 'Dhaka', '', '', '2025-05-04 23:51:51.000000', '2025-05-04 23:51:51.000000', NULL),
(16, 'Banglachain', 'Foundation', 'Banglachain Foundation', 3, 3, 'Banglachain@banglachain.com', '', '', '', '', '2025-05-04 23:52:39.000000', '2025-05-04 23:52:39.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `email`, `phone`, `country`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Melodic', 'melosryz@gmail.com', '01708020605', 'Bangladesh', '2025-03-25 10:40:15.000000', '2025-03-25 10:40:15.000000', NULL),
(2, 'Uday Inc', 'uday@gmail.com', '01717326286', 'USA', '2025-03-25 11:14:49.000000', '2025-05-03 19:57:42.000000', '2025-05-03 19:57:42'),
(3, 'Onchain Software & Research Limited', 'onchainsoftwareresearch@gmail.com', '09643112277', 'Bangaldesh', '2025-05-03 19:58:48.000000', '2025-05-03 19:58:48.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int NOT NULL,
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
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `currencies`
--

CREATE TABLE `currencies` (
  `id` int NOT NULL,
  `code` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `currencies`
--

INSERT INTO `currencies` (`id`, `code`, `name`, `symbol`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'USD', 'US Dollar', '$', NULL, '2025-03-25 12:00:14.000000', '2025-03-25 12:00:14'),
(2, 'Taka', 'Taka', 'BDT', '2025-03-25 12:00:03.000000', '2025-03-25 12:00:03.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int NOT NULL,
  `department` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int NOT NULL,
  `department_head` int DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `department`, `company_id`, `department_head`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Programming', 1, NULL, '2025-03-25 10:43:11.000000', '2025-03-25 10:43:11.000000', NULL),
(2, 'Product', 2, NULL, '2025-03-25 11:15:12.000000', '2025-03-25 11:15:12.000000', NULL),
(3, 'Marketing', 2, NULL, '2025-03-25 11:15:22.000000', '2025-03-25 11:15:22.000000', NULL),
(4, 'Product', 3, NULL, '2025-05-03 20:07:35.000000', '2025-05-03 20:07:35.000000', NULL),
(5, 'Operations', 3, NULL, '2025-05-03 20:07:46.000000', '2025-05-03 20:07:46.000000', NULL),
(6, 'Software', 3, NULL, '2025-05-03 20:08:11.000000', '2025-05-03 20:08:11.000000', NULL),
(7, 'Research', 3, NULL, '2025-05-03 20:08:21.000000', '2025-05-03 20:08:21.000000', NULL),
(8, 'Management', 3, NULL, '2025-05-03 20:26:01.000000', '2025-05-03 20:26:01.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `deposits`
--

CREATE TABLE `deposits` (
  `id` int NOT NULL,
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
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deposits`
--

INSERT INTO `deposits` (`id`, `account_id`, `deposit_category_id`, `amount`, `payment_method_id`, `date`, `deposit_ref`, `description`, `attachment`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, '12000.00', 1, '2025-03-25', 'Testing', '', NULL, '2025-03-25 13:43:53.000000', '2025-03-25 13:43:53.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `deposit_categories`
--

CREATE TABLE `deposit_categories` (
  `id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `deposit_categories`
--

INSERT INTO `deposit_categories` (`id`, `title`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Deposit Category', '2025-03-25 13:42:37.000000', '2025-03-25 13:42:37.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `designations`
--

CREATE TABLE `designations` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `department_id` int NOT NULL,
  `designation` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `designations`
--

INSERT INTO `designations` (`id`, `company_id`, `department_id`, `designation`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Main Programmer', '2025-03-25 10:43:36.000000', '2025-03-25 10:43:36.000000', NULL),
(2, 2, 2, 'VP,Products', '2025-03-25 11:15:48.000000', '2025-05-03 20:09:01.000000', '2025-05-03 20:09:01'),
(3, 2, 3, 'VP, Marketing', '2025-03-25 11:16:05.000000', '2025-05-03 20:08:56.000000', '2025-05-03 20:08:56'),
(4, 3, 4, 'VP, Products', '2025-05-03 20:08:49.000000', '2025-05-03 20:08:49.000000', NULL),
(5, 3, 5, 'Assistant Manager', '2025-05-03 20:24:05.000000', '2025-05-03 20:24:05.000000', NULL),
(6, 3, 6, 'Fullstack Intern', '2025-05-03 20:27:09.000000', '2025-05-03 20:27:09.000000', NULL),
(7, 3, 6, 'Developer', '2025-05-03 20:27:44.000000', '2025-05-03 20:27:44.000000', NULL),
(8, 3, 7, 'Researcher, Blockchain', '2025-05-03 20:30:21.000000', '2025-05-03 20:30:21.000000', NULL),
(9, 3, 8, 'President', '2025-05-03 20:46:10.000000', '2025-05-03 20:46:10.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int NOT NULL,
  `role_users_id` bigint UNSIGNED NOT NULL,
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
  `leaving_date` date DEFAULT NULL,
  `marital_status` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'single',
  `facebook` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `skype` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `whatsapp` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `twitter` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkedin` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `role_users_id`, `firstname`, `lastname`, `username`, `email`, `phone`, `country`, `city`, `province`, `zipcode`, `address`, `gender`, `resume`, `avatar`, `document`, `birth_date`, `joining_date`, `company_id`, `department_id`, `designation_id`, `office_shift_id`, `remaining_leave`, `total_leave`, `hourly_rate`, `basic_salary`, `employment_type`, `leaving_date`, `marital_status`, `facebook`, `skype`, `whatsapp`, `twitter`, `linkedin`, `created_at`, `updated_at`, `deleted_at`) VALUES
(3, 2, 'MD. Nizamul', 'Hoque', 'MD. Nizamul Hoque', 'melosryz@gmail.com', '01708020605', 'Bangladesh', NULL, NULL, NULL, NULL, 'male', NULL, 'no_avatar.png', NULL, '1994-09-21', '2025-03-01', 1, 1, 1, 1, 9, 30, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-25 10:48:24.000000', '2025-05-05 18:29:55.000000', NULL),
(6, 2, 'Uday', 'Uday', 'Uday Uday', 'uday1@gmail.com', '01717326286', 'Bangaldesh', NULL, NULL, NULL, NULL, 'male', NULL, 'no_avatar.png', NULL, '0000-00-00', '0000-00-00', 2, 2, 2, 2, 0, 0, '0.00', '0.00', 'full_time', NULL, 'single', NULL, NULL, NULL, NULL, NULL, '2025-03-25 11:31:11.000000', '2025-03-25 11:31:11.000000', NULL),
(8, 2, 'Thajid Ibna Rouf', 'Uday', 'Thajid Ibna Rouf Uday', 'uday@onchain.com.bd', '01717326286', 'Bangaldesh', NULL, NULL, NULL, NULL, 'male', NULL, 'no_avatar.png', NULL, '1994-02-15', '2025-02-05', 3, 4, 4, 3, 21, 21, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-03 20:10:57.000000', '2025-05-05 18:28:24.000000', NULL),
(9, 2, 'MD NIZAMUL', 'HOQUE', 'MD NIZAMUL HOQUE', 'melo.nizam16@gmail.com', '+8801629903608', 'Bangaldesh', NULL, NULL, NULL, NULL, 'male', NULL, 'no_avatar.png', NULL, '1993-09-18', '2025-02-05', 3, 6, 7, 4, 19, 21, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-03 20:39:24.000000', '2025-05-05 18:32:19.000000', NULL),
(10, 2, 'Mohaiminul Bashar', 'Raj', 'Mohaiminul Bashar Raj', 'mohaiminul252@gmail.com', '+8801312935376', 'Bangaldesh', NULL, NULL, NULL, NULL, 'male', NULL, 'no_avatar.png', NULL, '1993-07-22', '2025-02-05', 3, 8, 9, 3, 0, 0, '0.00', '0.00', 'full_time', NULL, 'single', NULL, NULL, NULL, NULL, NULL, '2025-05-03 20:48:12.000000', '2025-05-03 20:48:12.000000', NULL),
(11, 2, 'Sraboni', 'Akter', 'Sraboni Akter', 'dev.intern.onchain@gmail.com', '01319479694', 'Bangaldesh', NULL, NULL, NULL, NULL, 'female', NULL, 'no_avatar.png', NULL, '1999-10-15', '2025-04-28', 3, 6, 6, 3, 0, 0, '0.00', '0.00', 'full_time', NULL, 'single', NULL, NULL, NULL, NULL, NULL, '2025-05-03 20:57:03.000000', '2025-05-03 20:57:03.000000', NULL),
(12, 2, 'Sabrina Shahrin', 'Rashid Hia', 'Sabrina Shahrin Rashid Hia', 'asstmgr.onchain@gmail.com', '01623828068', 'Bangaldesh', 'Dhaka', 'Mohakhali', 'Dhaka-1212', '', 'female', NULL, 'no_avatar.png', NULL, '1998-01-26', '2025-05-04', 3, 5, 5, 3, 0, 0, '0.00', '0.00', 'full_time', NULL, 'single', NULL, NULL, NULL, NULL, NULL, '2025-05-04 14:12:58.000000', '2025-05-05 16:44:43.000000', NULL),
(13, 2, 'SM', 'Shamim', 'SM Shamim', 'dev.intern.onchain2@gmail.com', '01611052723', 'Bangaldesh', NULL, NULL, NULL, NULL, 'male', NULL, 'no_avatar.png', NULL, '1999-04-04', '2025-04-28', 3, 6, 6, 3, 0, 0, '0.00', '0.00', 'full_time', NULL, 'single', NULL, NULL, NULL, NULL, NULL, '2025-05-04 15:05:20.000000', '2025-05-04 15:05:20.000000', NULL),
(14, 2, 'Alamgir', 'Kabir', 'Alamgir Kabir', 'alamgirmohammad_ece@icloud.com', '01331733003', 'Bangaldesh', NULL, NULL, NULL, NULL, 'male', NULL, 'no_avatar.png', NULL, '1994-02-02', '2025-02-05', 3, 7, 8, 3, 0, 0, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-04 15:14:18.000000', '2025-05-05 20:12:42.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_accounts`
--

CREATE TABLE `employee_accounts` (
  `id` int NOT NULL,
  `employee_id` int NOT NULL,
  `bank_name` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bank_branch` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `account_no` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_documents`
--

CREATE TABLE `employee_documents` (
  `id` int NOT NULL,
  `employee_id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attachment` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_documents`
--

INSERT INTO `employee_documents` (`id`, `employee_id`, `title`, `description`, `attachment`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 3, 'azaira', 'erwerwer', '1746447415.pdf', '2025-05-05 18:16:55.000000', '2025-05-05 18:16:55.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_experiences`
--

CREATE TABLE `employee_experiences` (
  `id` int NOT NULL,
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
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_project`
--

CREATE TABLE `employee_project` (
  `employee_id` int NOT NULL,
  `project_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_project`
--

INSERT INTO `employee_project` (`employee_id`, `project_id`) VALUES
(3, 1),
(12, 2),
(10, 2),
(9, 2),
(8, 2),
(8, 3),
(9, 3),
(10, 3),
(8, 4),
(10, 4),
(10, 5),
(8, 5),
(8, 6),
(12, 6);

-- --------------------------------------------------------

--
-- Table structure for table `employee_task`
--

CREATE TABLE `employee_task` (
  `employee_id` int NOT NULL,
  `task_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_task`
--

INSERT INTO `employee_task` (`employee_id`, `task_id`) VALUES
(3, 1),
(6, 2),
(12, 3),
(10, 3),
(8, 3),
(9, 4),
(11, 4),
(13, 4),
(12, 5),
(12, 6),
(12, 7),
(12, 8),
(8, 8);

-- --------------------------------------------------------

--
-- Table structure for table `employee_training`
--

CREATE TABLE `employee_training` (
  `employee_id` int NOT NULL,
  `training_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

CREATE TABLE `events` (
  `id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int NOT NULL,
  `department_id` int NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `title`, `company_id`, `department_id`, `date`, `time`, `note`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Test Event', 2, 2, '2025-03-25', '08:19:00', 'Testing event', 'pending', '2025-03-25 11:21:33.000000', '2025-03-25 11:21:33.000000', NULL),
(2, 'Event test 2', 2, 3, '2025-03-25', '05:25:00', 'Test event 2', 'approved', '2025-03-25 11:22:13.000000', '2025-03-25 11:22:13.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `expenses`
--

CREATE TABLE `expenses` (
  `id` int NOT NULL,
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
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expenses`
--

INSERT INTO `expenses` (`id`, `account_id`, `expense_category_id`, `amount`, `payment_method_id`, `date`, `expense_ref`, `description`, `attachment`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, '2000.00', 1, '2025-03-18', 'teting', '', NULL, '2025-03-25 13:44:26.000000', '2025-03-25 13:44:26.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `expense_categories`
--

CREATE TABLE `expense_categories` (
  `id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `expense_categories`
--

INSERT INTO `expense_categories` (`id`, `title`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Testing Category', '2025-03-25 13:41:11.000000', '2025-03-25 13:41:11.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `holidays`
--

CREATE TABLE `holidays` (
  `id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `holidays`
--

INSERT INTO `holidays` (`id`, `title`, `company_id`, `start_date`, `end_date`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Eid Vacation', 2, '2025-03-29', '2025-04-03', 'Vacation', '2025-03-25 11:23:08.000000', '2025-03-25 11:23:08.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `leaves`
--

CREATE TABLE `leaves` (
  `id` int NOT NULL,
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
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leaves`
--

INSERT INTO `leaves` (`id`, `employee_id`, `company_id`, `department_id`, `leave_type_id`, `start_date`, `end_date`, `days`, `reason`, `attachment`, `half_day`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 9, 3, 6, 1, '2025-05-21', '2025-05-22', '2', 'bbb', 'no_image.png', 0, 'approved', '2025-05-05 18:31:03.000000', '2025-05-05 18:32:19.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`id`, `title`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Casual Leave', '2025-05-05 18:23:56.000000', '2025-05-05 18:23:56.000000', NULL),
(2, 'Sick Leave', '2025-05-05 18:24:05.000000', '2025-05-05 18:26:10.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2022_02_18_161351_create_accounts_table', 1),
(2, '2022_02_18_161351_create_announcements_table', 1),
(3, '2022_02_18_161351_create_arrangement_types_table', 1),
(4, '2022_02_18_161351_create_attendances_table', 1),
(5, '2022_02_18_161351_create_award_types_table', 1),
(6, '2022_02_18_161351_create_awards_table', 1),
(7, '2022_02_18_161351_create_clients_table', 1),
(8, '2022_02_18_161351_create_companies_table', 1),
(9, '2022_02_18_161351_create_complaints_table', 1),
(10, '2022_02_18_161351_create_currencies_table', 1),
(11, '2022_02_18_161351_create_departments_table', 1),
(12, '2022_02_18_161351_create_deposit_categories_table', 1),
(13, '2022_02_18_161351_create_deposits_table', 1),
(14, '2022_02_18_161351_create_designations_table', 1),
(15, '2022_02_18_161351_create_employee_accounts_table', 1),
(16, '2022_02_18_161351_create_employee_documents_table', 1),
(17, '2022_02_18_161351_create_employee_experiences_table', 1),
(18, '2022_02_18_161351_create_employee_project_table', 1),
(19, '2022_02_18_161351_create_employee_task_table', 1),
(20, '2022_02_18_161351_create_employee_training_table', 1),
(21, '2022_02_18_161351_create_employees_table', 1),
(22, '2022_02_18_161351_create_events_table', 1),
(23, '2022_02_18_161351_create_expense_categories_table', 1),
(24, '2022_02_18_161351_create_expenses_table', 1),
(25, '2022_02_18_161351_create_holidays_table', 1),
(26, '2022_02_18_161351_create_leave_types_table', 1),
(27, '2022_02_18_161351_create_leaves_table', 1),
(28, '2022_02_18_161351_create_model_has_permissions_table', 1),
(29, '2022_02_18_161351_create_model_has_roles_table', 1),
(30, '2022_02_18_161351_create_office_shifts_table', 1),
(31, '2022_02_18_161351_create_password_resets_table', 1),
(32, '2022_02_18_161351_create_payment_methods_table', 1),
(33, '2022_02_18_161351_create_permissions_table', 1),
(34, '2022_02_18_161351_create_policies_table', 1),
(35, '2022_02_18_161351_create_project_discussions_table', 1),
(36, '2022_02_18_161351_create_project_documents_table', 1),
(37, '2022_02_18_161351_create_project_issues_table', 1),
(38, '2022_02_18_161351_create_projects_table', 1),
(39, '2022_02_18_161351_create_role_has_permissions_table', 1),
(40, '2022_02_18_161351_create_roles_table', 1),
(41, '2022_02_18_161351_create_settings_table', 1),
(42, '2022_02_18_161351_create_task_discussions_table', 1),
(43, '2022_02_18_161351_create_task_documents_table', 1),
(44, '2022_02_18_161351_create_tasks_table', 1),
(45, '2022_02_18_161351_create_trainers_table', 1),
(46, '2022_02_18_161351_create_training_skills_table', 1),
(47, '2022_02_18_161351_create_trainings_table', 1),
(48, '2022_02_18_161351_create_travel_table', 1),
(49, '2022_02_18_161351_create_users_table', 1),
(50, '2022_02_18_161355_add_foreign_keys_to_announcements_table', 1),
(51, '2022_02_18_161355_add_foreign_keys_to_attendances_table', 1),
(52, '2022_02_18_161355_add_foreign_keys_to_awards_table', 1),
(53, '2022_02_18_161355_add_foreign_keys_to_clients_table', 1),
(54, '2022_02_18_161355_add_foreign_keys_to_complaints_table', 1),
(55, '2022_02_18_161355_add_foreign_keys_to_departments_table', 1),
(56, '2022_02_18_161355_add_foreign_keys_to_deposits_table', 1),
(57, '2022_02_18_161355_add_foreign_keys_to_designations_table', 1),
(58, '2022_02_18_161355_add_foreign_keys_to_employee_accounts_table', 1),
(59, '2022_02_18_161355_add_foreign_keys_to_employee_documents_table', 1),
(60, '2022_02_18_161355_add_foreign_keys_to_employee_experiences_table', 1),
(61, '2022_02_18_161355_add_foreign_keys_to_employee_project_table', 1),
(62, '2022_02_18_161355_add_foreign_keys_to_employee_task_table', 1),
(63, '2022_02_18_161355_add_foreign_keys_to_employee_training_table', 1),
(64, '2022_02_18_161355_add_foreign_keys_to_employees_table', 1),
(65, '2022_02_18_161355_add_foreign_keys_to_events_table', 1),
(66, '2022_02_18_161355_add_foreign_keys_to_expenses_table', 1),
(67, '2022_02_18_161355_add_foreign_keys_to_holidays_table', 1),
(68, '2022_02_18_161355_add_foreign_keys_to_leaves_table', 1),
(69, '2022_02_18_161355_add_foreign_keys_to_model_has_permissions_table', 1),
(70, '2022_02_18_161355_add_foreign_keys_to_model_has_roles_table', 1),
(71, '2022_02_18_161355_add_foreign_keys_to_office_shifts_table', 1),
(72, '2022_02_18_161355_add_foreign_keys_to_policies_table', 1),
(73, '2022_02_18_161355_add_foreign_keys_to_project_discussions_table', 1),
(74, '2022_02_18_161355_add_foreign_keys_to_project_documents_table', 1),
(75, '2022_02_18_161355_add_foreign_keys_to_project_issues_table', 1),
(76, '2022_02_18_161355_add_foreign_keys_to_projects_table', 1),
(77, '2022_02_18_161355_add_foreign_keys_to_role_has_permissions_table', 1),
(78, '2022_02_18_161355_add_foreign_keys_to_settings_table', 1),
(79, '2022_02_18_161355_add_foreign_keys_to_task_discussions_table', 1),
(80, '2022_02_18_161355_add_foreign_keys_to_task_documents_table', 1),
(81, '2022_02_18_161355_add_foreign_keys_to_tasks_table', 1),
(82, '2022_02_18_161355_add_foreign_keys_to_trainers_table', 1),
(83, '2022_02_18_161355_add_foreign_keys_to_trainings_table', 1),
(84, '2022_02_18_161355_add_foreign_keys_to_travel_table', 1),
(85, '2022_02_18_161355_add_foreign_keys_to_users_table', 1),
(86, '2025_04_16_191417_create_posts_table', 2),
(87, '2024_05_18_000000_add_is_flexible_to_office_shifts_table', 3);

-- --------------------------------------------------------

--
-- Table structure for table `model_has_permissions`
--

CREATE TABLE `model_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `model_has_roles`
--

CREATE TABLE `model_has_roles` (
  `role_id` bigint UNSIGNED NOT NULL,
  `model_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `model_has_roles`
--

INSERT INTO `model_has_roles` (`role_id`, `model_type`, `model_id`) VALUES
(1, 'App\\Models\\User', 1),
(1, 'App\\Models\\User', 2),
(2, 'App\\Models\\User', 3),
(3, 'App\\Models\\User', 4),
(1, 'App\\Models\\User', 5),
(2, 'App\\Models\\User', 6),
(1, 'App\\Models\\User', 7),
(2, 'App\\Models\\User', 8),
(2, 'App\\Models\\User', 9),
(2, 'App\\Models\\User', 10),
(2, 'App\\Models\\User', 11),
(2, 'App\\Models\\User', 12),
(2, 'App\\Models\\User', 13),
(2, 'App\\Models\\User', 14),
(3, 'App\\Models\\User', 15),
(3, 'App\\Models\\User', 16);

-- --------------------------------------------------------

--
-- Table structure for table `office_shifts`
--

CREATE TABLE `office_shifts` (
  `id` int NOT NULL,
  `is_flexible` tinyint(1) NOT NULL DEFAULT '0',
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
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `office_shifts`
--

INSERT INTO `office_shifts` (`id`, `is_flexible`, `company_id`, `name`, `monday_in`, `monday_out`, `tuesday_in`, `tuesday_out`, `wednesday_in`, `wednesday_out`, `thursday_in`, `thursday_out`, `friday_in`, `friday_out`, `saturday_in`, `saturday_out`, `sunday_in`, `sunday_out`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 0, 1, 'Melodic', '00:05AM', '08:29AM', '12:29PM', '06:30AM', '12:00PM', '06:30AM', '12:30PM', '18:30PM', NULL, NULL, '12:30PM', '18:30PM', NULL, NULL, '2025-03-25 10:47:36.000000', '2025-03-25 10:47:36.000000', NULL),
(2, 0, 2, 'Onchain Shift', '10:00AM', '08:00AM', NULL, NULL, '10:00AM', '08:00AM', NULL, NULL, '10:00AM', '08:00AM', NULL, NULL, '10:00AM', '08:00AM', '2025-03-25 11:20:43.000000', '2025-03-25 11:20:43.000000', NULL),
(3, 0, 3, 'Onchain HQ', '10:30AM', '18:30PM', '10:30AM', '18:30PM', '10:30AM', '18:30PM', '10:30AM', '18:30PM', NULL, NULL, '10:30AM', '18:30PM', '10:30AM', '18:30PM', '2025-05-03 20:05:09.000000', '2025-05-03 20:33:59.000000', NULL),
(4, 1, 3, 'test', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-05-04 07:36:11.000000', '2025-05-04 07:36:11.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `title`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Payment methods', '2025-03-25 13:42:55.000000', '2025-03-25 13:42:55.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `guard_name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'employee_view', 'web', NULL, NULL, NULL),
(2, 'employee_add', 'web', NULL, NULL, NULL),
(3, 'employee_edit', 'web', NULL, NULL, NULL),
(4, 'employee_delete', 'web', NULL, NULL, NULL),
(5, 'user_view', 'web', NULL, NULL, NULL),
(6, 'user_add', 'web', NULL, NULL, NULL),
(7, 'user_edit', 'web', NULL, NULL, NULL),
(8, 'user_delete', 'web', NULL, NULL, NULL),
(9, 'company_view', 'web', NULL, NULL, NULL),
(10, 'company_add', 'web', NULL, NULL, NULL),
(11, 'company_edit', 'web', NULL, NULL, NULL),
(12, 'company_delete', 'web', NULL, NULL, NULL),
(13, 'department_view', 'web', NULL, NULL, NULL),
(14, 'department_add', 'web', NULL, NULL, NULL),
(15, 'department_edit', 'web', NULL, NULL, NULL),
(16, 'department_delete', 'web', NULL, NULL, NULL),
(17, 'designation_view', 'web', NULL, NULL, NULL),
(18, 'designation_add', 'web', NULL, NULL, NULL),
(19, 'designation_edit', 'web', NULL, NULL, NULL),
(20, 'designation_delete', 'web', NULL, NULL, NULL),
(21, 'policy_view', 'web', NULL, NULL, NULL),
(22, 'policy_add', 'web', NULL, NULL, NULL),
(23, 'policy_edit', 'web', NULL, NULL, NULL),
(24, 'policy_delete', 'web', NULL, NULL, NULL),
(25, 'announcement_view', 'web', NULL, NULL, NULL),
(26, 'announcement_add', 'web', NULL, NULL, NULL),
(27, 'announcement_edit', 'web', NULL, NULL, NULL),
(28, 'announcement_delete', 'web', NULL, NULL, NULL),
(29, 'office_shift_view', 'web', NULL, NULL, NULL),
(30, 'office_shift_add', 'web', NULL, NULL, NULL),
(31, 'office_shift_edit', 'web', NULL, NULL, NULL),
(32, 'office_shift_delete', 'web', NULL, NULL, NULL),
(33, 'event_view', 'web', NULL, NULL, NULL),
(34, 'event_add', 'web', NULL, NULL, NULL),
(35, 'event_edit', 'web', NULL, NULL, NULL),
(36, 'event_delete', 'web', NULL, NULL, NULL),
(37, 'holiday_view', 'web', NULL, NULL, NULL),
(38, 'holiday_add', 'web', NULL, NULL, NULL),
(39, 'holiday_edit', 'web', NULL, NULL, NULL),
(40, 'holiday_delete', 'web', NULL, NULL, NULL),
(41, 'award_view', 'web', NULL, NULL, NULL),
(42, 'award_add', 'web', NULL, NULL, NULL),
(43, 'award_edit', 'web', NULL, NULL, NULL),
(44, 'award_delete', 'web', NULL, NULL, NULL),
(45, 'award_type', 'web', NULL, NULL, NULL),
(46, 'complaint_view', 'web', NULL, NULL, NULL),
(47, 'complaint_add', 'web', NULL, NULL, NULL),
(48, 'complaint_edit', 'web', NULL, NULL, NULL),
(49, 'complaint_delete', 'web', NULL, NULL, NULL),
(50, 'travel_view', 'web', NULL, NULL, NULL),
(51, 'travel_add', 'web', NULL, NULL, NULL),
(52, 'travel_edit', 'web', NULL, NULL, NULL),
(53, 'travel_delete', 'web', NULL, NULL, NULL),
(54, 'arrangement_type', 'web', NULL, NULL, NULL),
(55, 'attendance_view', 'web', NULL, NULL, NULL),
(56, 'attendance_add', 'web', NULL, NULL, NULL),
(57, 'attendance_edit', 'web', NULL, NULL, NULL),
(58, 'attendance_delete', 'web', NULL, NULL, NULL),
(59, 'account_view', 'web', NULL, NULL, NULL),
(60, 'account_add', 'web', NULL, NULL, NULL),
(61, 'account_edit', 'web', NULL, NULL, NULL),
(62, 'account_delete', 'web', NULL, NULL, NULL),
(63, 'deposit_view', 'web', NULL, NULL, NULL),
(64, 'deposit_add', 'web', NULL, NULL, NULL),
(65, 'deposit_edit', 'web', NULL, NULL, NULL),
(66, 'deposit_delete', 'web', NULL, NULL, NULL),
(67, 'expense_view', 'web', NULL, NULL, NULL),
(68, 'expense_add', 'web', NULL, NULL, NULL),
(69, 'expense_edit', 'web', NULL, NULL, NULL),
(70, 'expense_delete', 'web', NULL, NULL, NULL),
(71, 'client_view', 'web', NULL, NULL, NULL),
(72, 'client_add', 'web', NULL, NULL, NULL),
(73, 'client_edit', 'web', NULL, NULL, NULL),
(74, 'client_delete', 'web', NULL, NULL, NULL),
(75, 'deposit_category', 'web', NULL, NULL, NULL),
(76, 'payment_method', 'web', NULL, NULL, NULL),
(77, 'expense_category', 'web', NULL, NULL, NULL),
(78, 'project_view', 'web', NULL, NULL, NULL),
(79, 'project_add', 'web', NULL, NULL, NULL),
(80, 'project_edit', 'web', NULL, NULL, NULL),
(81, 'project_delete', 'web', NULL, NULL, NULL),
(82, 'task_view', 'web', NULL, NULL, NULL),
(83, 'task_add', 'web', NULL, NULL, NULL),
(84, 'task_edit', 'web', NULL, NULL, NULL),
(85, 'task_delete', 'web', NULL, NULL, NULL),
(86, 'leave_view', 'web', NULL, NULL, NULL),
(87, 'leave_add', 'web', NULL, NULL, NULL),
(88, 'leave_edit', 'web', NULL, NULL, NULL),
(89, 'leave_delete', 'web', NULL, NULL, NULL),
(90, 'training_view', 'web', NULL, NULL, NULL),
(91, 'training_add', 'web', NULL, NULL, NULL),
(92, 'training_edit', 'web', NULL, NULL, NULL),
(93, 'training_delete', 'web', NULL, NULL, NULL),
(94, 'trainer', 'web', NULL, NULL, NULL),
(95, 'training_skills', 'web', NULL, NULL, NULL),
(96, 'settings', 'web', NULL, NULL, NULL),
(97, 'currency', 'web', NULL, NULL, NULL),
(98, 'backup', 'web', NULL, NULL, NULL),
(99, 'group_permission', 'web', NULL, NULL, NULL),
(100, 'attendance_report', 'web', NULL, NULL, NULL),
(101, 'employee_report', 'web', NULL, NULL, NULL),
(102, 'project_report', 'web', NULL, NULL, NULL),
(103, 'task_report', 'web', NULL, NULL, NULL),
(104, 'expense_report', 'web', NULL, NULL, NULL),
(105, 'deposit_report', 'web', NULL, NULL, NULL),
(106, 'employee_details', 'web', NULL, NULL, NULL),
(107, 'leave_type', 'web', NULL, NULL, NULL),
(108, 'project_details', 'web', NULL, NULL, NULL),
(109, 'task_details', 'web', NULL, NULL, NULL),
(110, 'module_settings', 'web', NULL, NULL, NULL),
(111, 'kanban_task', 'web', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `policies`
--

CREATE TABLE `policies` (
  `id` int NOT NULL,
  `company_id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `policies`
--

INSERT INTO `policies` (`id`, `company_id`, `title`, `description`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 2, 'Office Time', 'Every Employee should come to office within 10.30am morning. After 10.30am entry shall count as late entry.', '2025-03-25 11:17:18.000000', '2025-03-25 11:17:18.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` int NOT NULL,
  `title` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`id`, `user_id`, `title`, `content`, `slug`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'set', 'eerwer', 'erwer', '2025-04-17 09:59:34', '2025-04-17 09:59:34', NULL),
(2, 1, 'hgnhgk', 'gdcgdhgd', 'cxfgdhg', '2025-04-26 21:53:35', '2025-04-26 21:53:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int NOT NULL,
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
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `client_id`, `priority`, `start_date`, `end_date`, `summary`, `description`, `company_id`, `project_progress`, `status`, `project_note`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Voting', 4, 'medium', '2025-01-25', '2025-04-30', 'Create voting system', 'ksldfjhsdlikf dsfjhsdoifhoi sdflohisdoifkbnhslkd sdlkfghoisduf', 1, '12', 'progress', NULL, '2025-03-25 10:54:23.000000', '2025-03-25 11:03:22.000000', NULL),
(2, 'Onchain Website', 15, 'urgent', '2025-05-05', '2025-05-29', 'The website will serve as the digital presence for the company,', 'Onchain Software and Research Limited is a forward-thinking blockchain-based research and development company, specializing in integrating cutting-edge technologies such as AI, blockchain, and cloud computing to create scalable, user-centric solutions. The website will serve as the digital presence for the company, providing detailed information on the company’s services, vision, mission, and groundbreaking work in the technology sector.\nObjectives:\nTo create an engaging, visually appealing, and professional website that reflects Onchain’s innovation-driven approach.\nTo provide easy-to-navigate sections for both visitors and potential clients.\nTo showcase services in blockchain, AI, and cloud computing, particularly focusing on the government, enterprise, and NGO sectors.\nTo build trust and reliability through clear, transparent messaging.', 3, '0', 'progress', NULL, '2025-05-04 23:54:55.000000', '2025-05-04 23:54:55.000000', NULL),
(3, 'Hub: Gamified Problem Solving Platfor', 16, 'high', '2025-05-08', '2025-08-31', 'Gamified Problem-Solving Platform: A Blockchain-Powered Ecosystem for Real-World Impact', 'This concept note presents an innovative platform that bridges the gap between global problem solvers and real-world challenges through gamification and blockchain technology. The platform creates a meritocratic ecosystem where individuals can apply their expertise to meaningful problems while earning tangible rewards, ultimately transforming how we approach complex social, environmental, and technological issues in Bangladesh and beyond.', 3, '0', 'not_started', NULL, '2025-05-04 23:58:53.000000', '2025-05-04 23:58:53.000000', NULL),
(4, 'Plant Network', 16, 'high', '2025-04-15', '2025-08-30', 'The Plant Network is a digital ecosystem designed to identify, monitor, and track the environmental impact of significant and mature plants-especially those in public spaces such as roadsides, parks, and forests', 'The Plant Network is a digital ecosystem designed to identify, monitor, and track the environmental impact of significant and mature plants-especially those in public spaces such as roadsides, parks, and forests. The system is first deployed as a Web2 solution for rapid adoption and scalability, with a planned transition to Web3 using BanglaChain for enhanced transparency, automation, and accountability.', 3, '0', 'progress', NULL, '2025-05-05 00:58:01.000000', '2025-05-05 00:58:01.000000', NULL),
(5, 'BanglaChain Foundation', 16, 'high', '2025-04-15', '2025-05-31', 'Banglachain foundation Registration and Launch', 'প্রাণ-প্রকৃতির সুরক্ষা নিশ্চিত করে মানব সভ্যতার অগ্রগতিই  বাংলাচেইন ফাউন্ডেশন এর  মূল লক্ষ্য ও উদ্দেশ্য। জলবায়ু পরিবর্তন ও পরিবেশ বিপর্যয় এই শতকে আমাদের সামনে সবচেয়ে বড় সংকট হিসেবে হাজির হয়েছে৷ আধুনিক মানুষ হিসেবে প্রাণ-প্রকৃতির সাথে আমাদের বৈরিতাপূর্ণ সম্পর্কই এই সংকটের মূল উৎস৷ যান্ত্রিক উৎপাদনের যুগে প্রকৃতি হয়েছে মানুষের অধীনস্ত, যেখানে প্রযুক্তিগত উৎকর্ষ আর প্রগতির ধারণা হয়ে উঠেছে প্রকৃতিকে নিয়ন্ত্রণ ও শোষণ করারই নামান্তর। তারই প্রতিক্রিয়ায় প্রকৃতিও হয়ে উঠছে বিরূপ৷ মহামারি, বন্যা, খরাসহ নানা বিপর্যয় মানবসভ্যতাকে ফেলে দিয়েছে হুমকির মুখে। ফলে এই সংকট মোকাবিলায় মানুষ ও প্রকৃতির সহাবস্থানে সভ্যতার এক নতুন গতিপথ আমাদের আবিষ্কার করতে হবে। বুদ্ধিদীপ্ত উদ্ভাবন ও দূরদর্শী পরিকল্পনার মাধ্যমেই তা সম্ভব বলে আমরা বিশ্বাস করি। এ কারণেই মানুষ ও প্রকৃতির সম্পর্ক পুনর্বিবেচনায় জ্ঞানজগতের পুনর্বিন্যাস জরুরি বলে আমরা মনে করি। বহুশাস্ত্রীয় মিথোষ্ক্রিয়ায় ব্যক্তি ও গোষ্ঠীগত উদ্যোগ এবং বিবিধ প্রতিষ্ঠানের অংশীদারিত্বপূর্ণ প্রচেষ্টার মধ্য দিয়ে একটি টেকসই বাস্তুসংস্থান তৈরির লক্ষ্যেই তাই আমাদের পথচলা৷', 3, '0', 'progress', NULL, '2025-05-05 01:00:27.000000', '2025-05-05 01:00:27.000000', NULL),
(6, 'Onchain D2D Operations', 15, 'high', '2025-05-01', '2025-05-31', 'Day to Day operations', '', 3, '0', 'not_started', NULL, '2025-05-05 01:10:21.000000', '2025-05-05 01:10:21.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_discussions`
--

CREATE TABLE `project_discussions` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `user_id` int NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_discussions`
--

INSERT INTO `project_discussions` (`id`, `project_id`, `user_id`, `message`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'vfsdgsdg', '2025-03-25 11:02:10.000000', '2025-03-25 11:02:10.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `project_documents`
--

CREATE TABLE `project_documents` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attachment` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `project_issues`
--

CREATE TABLE `project_issues` (
  `id` int NOT NULL,
  `project_id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attachment` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `project_issues`
--

INSERT INTO `project_issues` (`id`, `project_id`, `title`, `comment`, `label`, `attachment`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'sdgsdg', 'sdgsdgsdgdsg', 'invalid', NULL, 'pending', '2025-03-25 11:02:23.000000', '2025-03-25 11:02:23.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `guard_name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `guard_name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'Super Admin', 'Super Admin', 'web', NULL, NULL, NULL),
(2, 'Employee', 'Employee Access', 'web', NULL, NULL, NULL),
(3, 'Client', 'Client Access', 'web', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_has_permissions`
--

CREATE TABLE `role_has_permissions` (
  `permission_id` bigint UNSIGNED NOT NULL,
  `role_id` bigint UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_has_permissions`
--

INSERT INTO `role_has_permissions` (`permission_id`, `role_id`) VALUES
(1, 1),
(2, 1),
(3, 1),
(4, 1),
(5, 1),
(6, 1),
(7, 1),
(8, 1),
(9, 1),
(10, 1),
(11, 1),
(12, 1),
(13, 1),
(14, 1),
(15, 1),
(16, 1),
(17, 1),
(18, 1),
(19, 1),
(20, 1),
(21, 1),
(22, 1),
(23, 1),
(24, 1),
(25, 1),
(26, 1),
(27, 1),
(28, 1),
(29, 1),
(30, 1),
(31, 1),
(32, 1),
(33, 1),
(34, 1),
(35, 1),
(36, 1),
(37, 1),
(38, 1),
(39, 1),
(40, 1),
(41, 1),
(42, 1),
(43, 1),
(44, 1),
(45, 1),
(46, 1),
(47, 1),
(48, 1),
(49, 1),
(50, 1),
(51, 1),
(52, 1),
(53, 1),
(54, 1),
(55, 1),
(56, 1),
(57, 1),
(58, 1),
(59, 1),
(60, 1),
(61, 1),
(62, 1),
(63, 1),
(64, 1),
(65, 1),
(66, 1),
(67, 1),
(68, 1),
(69, 1),
(70, 1),
(71, 1),
(72, 1),
(73, 1),
(74, 1),
(75, 1),
(76, 1),
(77, 1),
(78, 1),
(79, 1),
(80, 1),
(81, 1),
(82, 1),
(83, 1),
(84, 1),
(85, 1),
(86, 1),
(87, 1),
(88, 1),
(89, 1),
(90, 1),
(91, 1),
(92, 1),
(93, 1),
(94, 1),
(95, 1),
(96, 1),
(97, 1),
(98, 1),
(99, 1),
(100, 1),
(101, 1),
(102, 1),
(103, 1),
(104, 1),
(105, 1),
(106, 1),
(107, 1),
(108, 1),
(109, 1),
(110, 1),
(111, 1),
(21, 2),
(25, 2),
(55, 2);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
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
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `currency_id`, `email`, `CompanyName`, `CompanyPhone`, `CompanyAdress`, `footer`, `developed_by`, `logo`, `default_language`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'admin@example.com', 'Onchain', '6315996770', 'Baridhara DOHS', 'Onchain', 'melodic', 'logo-default.png', 'en', NULL, '2025-04-26 21:57:22.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int NOT NULL,
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
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `title`, `project_id`, `company_id`, `start_date`, `end_date`, `estimated_hour`, `task_progress`, `summary`, `description`, `status`, `priority`, `note`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'login', 1, 1, '2025-03-02', '2025-03-10', NULL, '100', 'Create voting system', 'sdgfswdgsdgsdgsdgsdg sdfgsdfgsd', 'completed', 'medium', NULL, '2025-03-25 10:57:19.000000', '2025-03-25 11:02:44.000000', NULL),
(2, 'Testing task', 1, 2, '2025-03-20', '2025-03-23', NULL, '47', 'Testing summary', '', 'completed', 'high', NULL, '2025-03-25 13:45:13.000000', '2025-03-25 13:45:55.000000', NULL),
(3, 'Onchain Website UI', 2, 3, '2025-05-05', '2025-05-15', NULL, '0', 'https://docs.google.com/document/d/1mr8aW0mpfMxEmIzJTwYILy7RoFSkZlp4oj8WwwiY7PI/edit?usp=sharing', 'Onchain Software and Research Limited is a forward-thinking blockchain-based research and development company, specializing in integrating cutting-edge technologies such as AI, blockchain, and cloud computing to create scalable, user-centric solutions. The website will serve as the digital presence for the company, providing detailed information on the company’s services, vision, mission, and groundbreaking work in the technology sector.\nObjectives:\nTo create an engaging, visually appealing, and professional website that reflects Onchain’s innovation-driven approach.\nTo provide easy-to-navigate sections for both visitors and potential clients.\nTo showcase services in blockchain, AI, and cloud computing, particularly focusing on the government, enterprise, and NGO sectors.\nTo build trust and reliability through clear, transparent messaging.', 'progress', 'urgent', NULL, '2025-05-04 23:49:39.000000', '2025-05-05 01:01:27.000000', NULL),
(4, 'Developing Onchain Webpage', 2, 3, '2025-05-16', '2025-05-28', NULL, '0', 'Developing Webpage', 'Update after completing the UI design', 'not_started', 'urgent', NULL, '2025-05-05 01:06:49.000000', '2025-05-05 01:06:49.000000', NULL),
(5, 'ID card design and send to printing', 6, 3, '2025-05-05', '2025-05-08', NULL, '0', 'Design and send for print', '1. Ask President for .ai design file. 2. Design according to information for Assistant Manager, Fullstack Developer - Intern (2). 3. Review and confirm the information. 4. send it for printing.', 'not_started', 'medium', NULL, '2025-05-05 01:13:07.000000', '2025-05-05 01:13:07.000000', NULL),
(6, 'new member onboarding design', 6, 3, '2025-05-05', '2025-05-08', NULL, '0', 'new member onboarding social media post', '1. New member onboarding post for social media design. 2. Approve. 3. Post on social media \nnew members are: 1. Assistant manager, 2. Fullstack Developer inter 3. Fullstack Developer inter \nInformation: https://docs.google.com/spreadsheets/d/1KO7Kw5DL4gPz9RE5pMv8FIZyzD9034HwgtIDGHUAh0A/edit?usp=sharing', 'progress', 'medium', NULL, '2025-05-05 01:15:53.000000', '2025-05-05 16:38:42.000000', NULL),
(7, 'Introducing President, VP and Management post', 6, 3, '2025-05-05', '2025-05-08', NULL, '0', 'Design and post to introduce team', 'Design and post to Introducing President, VP and Management', 'not_started', 'medium', NULL, '2025-05-05 01:17:31.000000', '2025-05-05 01:17:31.000000', NULL),
(8, 'Developed SOP', 6, 3, '2025-05-05', '2025-05-15', NULL, '0', 'Developed SOP for hiring and fixed tasks that we should maintaining each position', 'Developed SOP for hiring and fixed tasks that we should maintaining each position.', 'not_started', 'low', NULL, '2025-05-05 01:21:23.000000', '2025-05-05 01:21:23.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `task_discussions`
--

CREATE TABLE `task_discussions` (
  `id` int NOT NULL,
  `task_id` int NOT NULL,
  `user_id` int NOT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_documents`
--

CREATE TABLE `task_documents` (
  `id` int NOT NULL,
  `task_id` int NOT NULL,
  `title` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `attachment` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `task_documents`
--

INSERT INTO `task_documents` (`id`, `task_id`, `title`, `description`, `attachment`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 3, 'Requirements', 'https://docs.google.com/document/d/1mr8aW0mpfMxEmIzJTwYILy7RoFSkZlp4oj8WwwiY7PI/edit?tab=t.0', '1746385464.png', '2025-05-05 01:04:24.000000', '2025-05-05 01:04:24.000000', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `trainers`
--

CREATE TABLE `trainers` (
  `id` int NOT NULL,
  `name` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `company_id` int NOT NULL,
  `phone` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `trainings`
--

CREATE TABLE `trainings` (
  `id` int NOT NULL,
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
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `training_skills`
--

CREATE TABLE `training_skills` (
  `id` int NOT NULL,
  `training_skill` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `travel`
--

CREATE TABLE `travel` (
  `id` int NOT NULL,
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
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `avatar` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `role_users_id` bigint UNSIGNED NOT NULL,
  `password` varchar(192) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `email_verified_at`, `avatar`, `status`, `role_users_id`, `password`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'William Castillo', 'admin@example.com', NULL, 'no_avatar.png', 1, 1, '$2y$10$IFj6SwqC0Sxrsiv4YkCt.OJv1UV4mZrWuyLoRG7qt47mseP9mJ58u', NULL, NULL, NULL, NULL),
(2, 'melodic1', 'admin2@example.com', NULL, 'no_avatar.png', 0, 1, '$2y$10$BbhuOwogNBFb5VNHigVYZu49lpt1HDMi/.ahjFQDeq04Hn3RpNpqC', NULL, '2025-03-25 10:42:06.000000', '2025-03-25 10:42:20.000000', NULL),
(3, 'MD. Nizamul Hoque', 'melosryz@gmail.com', NULL, 'no_avatar.png', 1, 2, '$2y$10$k2MAZifugtNmkB0dqjldLOkuhT9eYTaKx9rRpqQm3BlQNmvlfSr.y', NULL, '2025-03-25 10:48:24.000000', '2025-05-05 18:29:55.000000', NULL),
(4, 'MD. jesus Hoque', 'melosryz1@gmail.com', NULL, 'no_avatar.png', 0, 3, '$2y$10$Lv7JtqRgayM4C7cyCV3qNuCWsj0ZyH0sKnnZy8Vl5BaCQBmD5Gkwu', NULL, '2025-03-25 10:52:13.000000', '2025-05-05 20:01:39.000000', NULL),
(5, 'Uday', 'uday@gmail.com', NULL, 'no_avatar.png', 0, 1, '$2y$10$Bb0YT6UN4gZHFJsaOsLWpOi8ob0RU.9E64iJfqexKC3TLlvIiVzOi', NULL, '2025-03-25 11:29:57.000000', '2025-04-27 12:09:41.000000', NULL),
(6, 'Uday Uday', 'uday1@gmail.com', NULL, 'no_avatar.png', 0, 2, '$2y$10$jnilANDuF/HjOUWV6oVpweUWY4.8Ojr.9X6xfVjCzVqoyrsZWT65q', NULL, '2025-03-25 11:31:11.000000', '2025-04-27 12:09:34.000000', NULL),
(7, 'Uday Thajid Ibna Rouf', 'ibnauday@gmail.com', NULL, '1745734269.jpg', 1, 1, '$2y$10$/0qN1Qhdhcxu5nBeVf/WfefCiLSuD5WbBXFHGJAEsqcvCFGYmL4da', NULL, '2025-04-27 12:11:09.000000', '2025-04-27 12:11:09.000000', NULL),
(8, 'Thajid Ibna Rouf Uday', 'uday@onchain.com.bd', NULL, 'no_avatar.png', 1, 2, '$2y$10$00aKMjcUlDRPVnZJCuSt4O0DtpAPYvuTXHi.TbWHOJM0QggOwD1fG', NULL, '2025-05-03 20:10:57.000000', '2025-05-05 18:28:24.000000', NULL),
(9, 'MD NIZAMUL HOQUE', 'melo.nizam16@gmail.com', NULL, 'no_avatar.png', 1, 2, '$2y$10$vCTdzaMis.RsPzkJ7K53oOQJ6SFeIYW9vT5tEQpu/EipYcVLPa/Fu', NULL, '2025-05-03 20:39:24.000000', '2025-05-05 18:30:39.000000', NULL),
(10, 'Mohaiminul Bashar Raj', 'mohaiminul252@gmail.com', NULL, 'no_avatar.png', 1, 2, '$2y$10$GO2X6a1irOTnLmgF6LcCwuA35m8w3Uu/jH6O9mfBj/KHXyGsfUTCa', NULL, '2025-05-03 20:48:12.000000', '2025-05-03 20:48:12.000000', NULL),
(11, 'Sraboni Akter', 'dev.intern.onchain@gmail.com', NULL, 'no_avatar.png', 1, 2, '$2y$10$rW2VAi9tOQoAu1P0bjnRPuuGJWHjMHdU8GyQHbl6O5Diuw7UuadEK', NULL, '2025-05-03 20:57:03.000000', '2025-05-03 20:57:03.000000', NULL),
(12, 'Sabrina Shahrin Rashid Hia', 'asstmgr.onchain@gmail.com', NULL, 'no_avatar.png', 1, 2, '$2y$10$N4NnMpcb.wIg.4isg3DiX.X05UacrWFgmiMFLYUb5tgOqcy6ziqwO', NULL, '2025-05-04 14:12:58.000000', '2025-05-05 16:44:43.000000', NULL),
(13, 'SM Shamim', 'dev.intern.onchain2@gmail.com', NULL, 'no_avatar.png', 1, 2, '$2y$10$.w7/mOQW.znxAgJZSbHlFeS6Ec0mvSbppIXhajyw7ABUTGgXv5CxO', NULL, '2025-05-04 15:05:20.000000', '2025-05-04 15:05:20.000000', NULL),
(14, 'Alamgir Kabir', 'alamgirmohammad_ece@icloud.com', NULL, 'no_avatar.png', 1, 2, '$2y$10$uzpQkUYbN5lZBgjyt1UMyOywoDW0jySKJQ3EwCMXdyR/64obBWuee', NULL, '2025-05-04 15:14:18.000000', '2025-05-05 20:13:37.000000', NULL),
(15, 'Onchain Software & Research limited', 'onchainsoftwareresearch@gmail.com', NULL, 'no_avatar.png', 1, 3, '$2y$10$Nn7swhUVphkxRZytTENNCes23RJwlU3ANZyTzbyIGFUclWf8uXHqW', NULL, '2025-05-04 23:51:51.000000', '2025-05-04 23:51:51.000000', NULL),
(16, 'Banglachain Foundation', 'Banglachain@banglachain.com', NULL, 'no_avatar.png', 1, 3, '$2y$10$8cgdGYHe1EoqcK1RhWV83.IBNExErchirVydwc4v15/K1JHzyDgue', NULL, '2025-05-04 23:52:39.000000', '2025-05-04 23:52:39.000000', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `announcements_company_id` (`company_id`),
  ADD KEY `announcements_department_id` (`department_id`);

--
-- Indexes for table `arrangement_types`
--
ALTER TABLE `arrangement_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendances`
--
ALTER TABLE `attendances`
  ADD PRIMARY KEY (`id`),
  ADD KEY `attendances_company_id` (`company_id`),
  ADD KEY `attendances_employee_id` (`employee_id`);

--
-- Indexes for table `awards`
--
ALTER TABLE `awards`
  ADD PRIMARY KEY (`id`),
  ADD KEY `awards_company_id` (`company_id`),
  ADD KEY `awards_department_id` (`department_id`),
  ADD KEY `awards_employee_id` (`employee_id`),
  ADD KEY `award_award_type_id` (`award_type_id`);

--
-- Indexes for table `award_types`
--
ALTER TABLE `award_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `clients_role_users_id` (`role_users_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `complaint_company_id` (`company_id`),
  ADD KEY `complaint_employee_from` (`employee_from`),
  ADD KEY `complaint_employee_against` (`employee_against`);

--
-- Indexes for table `currencies`
--
ALTER TABLE `currencies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `department_company_id` (`company_id`),
  ADD KEY `department_department_head` (`department_head`);

--
-- Indexes for table `deposits`
--
ALTER TABLE `deposits`
  ADD PRIMARY KEY (`id`),
  ADD KEY `deposit_account_id` (`account_id`),
  ADD KEY `deposit_category_id` (`deposit_category_id`),
  ADD KEY `deposit_payment_method_id` (`payment_method_id`);

--
-- Indexes for table `deposit_categories`
--
ALTER TABLE `deposit_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `designations`
--
ALTER TABLE `designations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `designation_company_id` (`company_id`),
  ADD KEY `designation_departement_id` (`department_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employees_role_users_id` (`role_users_id`),
  ADD KEY `employees_company_id` (`company_id`),
  ADD KEY `employees_department_id` (`department_id`),
  ADD KEY `employees_designation_id` (`designation_id`),
  ADD KEY `employees_office_shift_id` (`office_shift_id`);

--
-- Indexes for table `employee_accounts`
--
ALTER TABLE `employee_accounts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_accounts_employee_id` (`employee_id`);

--
-- Indexes for table `employee_documents`
--
ALTER TABLE `employee_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `documents_employee_id` (`employee_id`);

--
-- Indexes for table `employee_experiences`
--
ALTER TABLE `employee_experiences`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_experience_employee_id` (`employee_id`);

--
-- Indexes for table `employee_project`
--
ALTER TABLE `employee_project`
  ADD KEY `employee_project_employee_id` (`employee_id`),
  ADD KEY `employee_project_project_id` (`project_id`);

--
-- Indexes for table `employee_task`
--
ALTER TABLE `employee_task`
  ADD KEY `employee_task_employee_id` (`employee_id`),
  ADD KEY `employee_task_task_id` (`task_id`);

--
-- Indexes for table `employee_training`
--
ALTER TABLE `employee_training`
  ADD KEY `employee_training_employee_id` (`employee_id`),
  ADD KEY `employee_training_training_id` (`training_id`);

--
-- Indexes for table `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_company_id` (`company_id`),
  ADD KEY `event_department_id` (`department_id`);

--
-- Indexes for table `expenses`
--
ALTER TABLE `expenses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `expenses_account_id` (`account_id`),
  ADD KEY `expenses_category_id` (`expense_category_id`),
  ADD KEY `expenses_payment_method_id` (`payment_method_id`);

--
-- Indexes for table `expense_categories`
--
ALTER TABLE `expense_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `holidays`
--
ALTER TABLE `holidays`
  ADD PRIMARY KEY (`id`),
  ADD KEY `holidays_company_id` (`company_id`);

--
-- Indexes for table `leaves`
--
ALTER TABLE `leaves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `leave_employee_id` (`employee_id`),
  ADD KEY `leave_company_id` (`company_id`),
  ADD KEY `leave_department_id` (`department_id`),
  ADD KEY `leave_leave_type_id` (`leave_type_id`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  ADD KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  ADD KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`);

--
-- Indexes for table `office_shifts`
--
ALTER TABLE `office_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `office_shift_company_id` (`company_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD KEY `password_resets_email_index` (`email`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `policies`
--
ALTER TABLE `policies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `policy_company_id` (`company_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `posts_slug_unique` (`slug`),
  ADD KEY `posts_user_id_foreign` (`user_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `projects_client_id` (`client_id`),
  ADD KEY `projects_company_id` (`company_id`);

--
-- Indexes for table `project_discussions`
--
ALTER TABLE `project_discussions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_discussions_project_id` (`project_id`),
  ADD KEY `project_discussions_user_id` (`user_id`);

--
-- Indexes for table `project_documents`
--
ALTER TABLE `project_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_documents_projet_id` (`project_id`);

--
-- Indexes for table `project_issues`
--
ALTER TABLE `project_issues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_issues_project_id` (`project_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD PRIMARY KEY (`permission_id`,`role_id`),
  ADD KEY `role_has_permissions_role_id_foreign` (`role_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `settings_currency_id` (`currency_id`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `Tasks_project_id` (`project_id`),
  ADD KEY `Tasks_company_id` (`company_id`);

--
-- Indexes for table `task_discussions`
--
ALTER TABLE `task_discussions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_discussions_task_id` (`task_id`),
  ADD KEY `task_discussions_user_id` (`user_id`);

--
-- Indexes for table `task_documents`
--
ALTER TABLE `task_documents`
  ADD PRIMARY KEY (`id`),
  ADD KEY `tasks_doocuments_task_id` (`task_id`);

--
-- Indexes for table `trainers`
--
ALTER TABLE `trainers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainers_company_id` (`company_id`);

--
-- Indexes for table `trainings`
--
ALTER TABLE `trainings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `trainings_trainer_id` (`trainer_id`),
  ADD KEY `trainings_company_id` (`company_id`),
  ADD KEY `trainings_training_skill_id` (`training_skill_id`);

--
-- Indexes for table `training_skills`
--
ALTER TABLE `training_skills`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `travel`
--
ALTER TABLE `travel`
  ADD PRIMARY KEY (`id`),
  ADD KEY `travels_company_id` (`company_id`),
  ADD KEY `travels_employee_id` (`employee_id`),
  ADD KEY `travels_arrangement_type_id` (`arrangement_type_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `users_role_users_id` (`role_users_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `arrangement_types`
--
ALTER TABLE `arrangement_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendances`
--
ALTER TABLE `attendances`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT for table `awards`
--
ALTER TABLE `awards`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `award_types`
--
ALTER TABLE `award_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `currencies`
--
ALTER TABLE `currencies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `deposit_categories`
--
ALTER TABLE `deposit_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `designations`
--
ALTER TABLE `designations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `employee_accounts`
--
ALTER TABLE `employee_accounts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_documents`
--
ALTER TABLE `employee_documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employee_experiences`
--
ALTER TABLE `employee_experiences`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `events`
--
ALTER TABLE `events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `expenses`
--
ALTER TABLE `expenses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `expense_categories`
--
ALTER TABLE `expense_categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `holidays`
--
ALTER TABLE `holidays`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leaves`
--
ALTER TABLE `leaves`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `office_shifts`
--
ALTER TABLE `office_shifts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `policies`
--
ALTER TABLE `policies`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `project_discussions`
--
ALTER TABLE `project_discussions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `project_documents`
--
ALTER TABLE `project_documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_issues`
--
ALTER TABLE `project_issues`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `task_discussions`
--
ALTER TABLE `task_discussions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_documents`
--
ALTER TABLE `task_documents`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `trainers`
--
ALTER TABLE `trainers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `trainings`
--
ALTER TABLE `trainings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `training_skills`
--
ALTER TABLE `training_skills`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `travel`
--
ALTER TABLE `travel`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `announcements_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `announcements_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `attendances`
--
ALTER TABLE `attendances`
  ADD CONSTRAINT `attendances_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `attendances_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `awards`
--
ALTER TABLE `awards`
  ADD CONSTRAINT `award_award_type_id` FOREIGN KEY (`award_type_id`) REFERENCES `award_types` (`id`),
  ADD CONSTRAINT `awards_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `awards_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `awards_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `clients_role_users_id` FOREIGN KEY (`role_users_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `complaints`
--
ALTER TABLE `complaints`
  ADD CONSTRAINT `complaint_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `complaint_employee_against` FOREIGN KEY (`employee_against`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `complaint_employee_from` FOREIGN KEY (`employee_from`) REFERENCES `employees` (`id`);

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `department_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `department_department_head` FOREIGN KEY (`department_head`) REFERENCES `employees` (`id`);

--
-- Constraints for table `deposits`
--
ALTER TABLE `deposits`
  ADD CONSTRAINT `deposit_account_id` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `deposit_category_id` FOREIGN KEY (`deposit_category_id`) REFERENCES `deposit_categories` (`id`),
  ADD CONSTRAINT `deposit_payment_method_id` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`);

--
-- Constraints for table `designations`
--
ALTER TABLE `designations`
  ADD CONSTRAINT `designation_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `designation_departement_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `employees_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `employees_designation_id` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`),
  ADD CONSTRAINT `employees_office_shift_id` FOREIGN KEY (`office_shift_id`) REFERENCES `office_shifts` (`id`),
  ADD CONSTRAINT `employees_role_users_id` FOREIGN KEY (`role_users_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `employee_accounts`
--
ALTER TABLE `employee_accounts`
  ADD CONSTRAINT `employee_accounts_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `employee_documents`
--
ALTER TABLE `employee_documents`
  ADD CONSTRAINT `documents_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `employee_experiences`
--
ALTER TABLE `employee_experiences`
  ADD CONSTRAINT `employee_experience_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `employee_project`
--
ALTER TABLE `employee_project`
  ADD CONSTRAINT `employee_project_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `employee_project_project_id` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`);

--
-- Constraints for table `employee_task`
--
ALTER TABLE `employee_task`
  ADD CONSTRAINT `employee_task_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `employee_task_task_id` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`);

--
-- Constraints for table `employee_training`
--
ALTER TABLE `employee_training`
  ADD CONSTRAINT `employee_training_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `employee_training_training_id` FOREIGN KEY (`training_id`) REFERENCES `trainings` (`id`);

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `event_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `event_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`);

--
-- Constraints for table `expenses`
--
ALTER TABLE `expenses`
  ADD CONSTRAINT `expenses_account_id` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`),
  ADD CONSTRAINT `expenses_category_id` FOREIGN KEY (`expense_category_id`) REFERENCES `expense_categories` (`id`),
  ADD CONSTRAINT `expenses_payment_method_id` FOREIGN KEY (`payment_method_id`) REFERENCES `payment_methods` (`id`);

--
-- Constraints for table `holidays`
--
ALTER TABLE `holidays`
  ADD CONSTRAINT `holidays_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `leaves`
--
ALTER TABLE `leaves`
  ADD CONSTRAINT `leave_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `leave_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`),
  ADD CONSTRAINT `leave_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `leave_leave_type_id` FOREIGN KEY (`leave_type_id`) REFERENCES `leave_types` (`id`);

--
-- Constraints for table `model_has_permissions`
--
ALTER TABLE `model_has_permissions`
  ADD CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `model_has_roles`
--
ALTER TABLE `model_has_roles`
  ADD CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `office_shifts`
--
ALTER TABLE `office_shifts`
  ADD CONSTRAINT `office_shift_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `policies`
--
ALTER TABLE `policies`
  ADD CONSTRAINT `policy_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_client_id` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `projects_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `project_discussions`
--
ALTER TABLE `project_discussions`
  ADD CONSTRAINT `project_discussions_project_id` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  ADD CONSTRAINT `project_discussions_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `project_documents`
--
ALTER TABLE `project_documents`
  ADD CONSTRAINT `project_documents_projet_id` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`);

--
-- Constraints for table `project_issues`
--
ALTER TABLE `project_issues`
  ADD CONSTRAINT `project_issues_project_id` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`);

--
-- Constraints for table `role_has_permissions`
--
ALTER TABLE `role_has_permissions`
  ADD CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_currency_id` FOREIGN KEY (`currency_id`) REFERENCES `currencies` (`id`);

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `Tasks_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `Tasks_project_id` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`);

--
-- Constraints for table `task_discussions`
--
ALTER TABLE `task_discussions`
  ADD CONSTRAINT `task_discussions_task_id` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`),
  ADD CONSTRAINT `task_discussions_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `task_documents`
--
ALTER TABLE `task_documents`
  ADD CONSTRAINT `tasks_doocuments_task_id` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`);

--
-- Constraints for table `trainers`
--
ALTER TABLE `trainers`
  ADD CONSTRAINT `trainers_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `trainings`
--
ALTER TABLE `trainings`
  ADD CONSTRAINT `trainings_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `trainings_trainer_id` FOREIGN KEY (`trainer_id`) REFERENCES `trainers` (`id`),
  ADD CONSTRAINT `trainings_training_skill_id` FOREIGN KEY (`training_skill_id`) REFERENCES `training_skills` (`id`);

--
-- Constraints for table `travel`
--
ALTER TABLE `travel`
  ADD CONSTRAINT `travels_arrangement_type_id` FOREIGN KEY (`arrangement_type_id`) REFERENCES `arrangement_types` (`id`),
  ADD CONSTRAINT `travels_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `travels_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_role_users_id` FOREIGN KEY (`role_users_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
