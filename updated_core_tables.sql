-- --------------------------------------------------------
-- Table structure for table `office_shifts`
--
CREATE TABLE IF NOT EXISTS `office_shifts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `name` varchar(191) NOT NULL,
  `is_flexible` tinyint(1) NOT NULL DEFAULT 0,
  `expected_hours` decimal(5,2) DEFAULT NULL,
  `weekend_days` varchar(255) DEFAULT NULL,
  `half_day_of_week` tinyint DEFAULT NULL,
  `half_day_expected_hours` decimal(5,2) DEFAULT NULL,
  `monday_in` varchar(191) DEFAULT NULL,
  `monday_out` varchar(191) DEFAULT NULL,
  `tuesday_in` varchar(191) DEFAULT NULL,
  `tuesday_out` varchar(191) DEFAULT NULL,
  `wednesday_in` varchar(191) DEFAULT NULL,
  `wednesday_out` varchar(191) DEFAULT NULL,
  `thursday_in` varchar(191) DEFAULT NULL,
  `thursday_out` varchar(191) DEFAULT NULL,
  `friday_in` varchar(191) DEFAULT NULL,
  `friday_out` varchar(191) DEFAULT NULL,
  `saturday_in` varchar(191) DEFAULT NULL,
  `saturday_out` varchar(191) DEFAULT NULL,
  `sunday_in` varchar(191) DEFAULT NULL,
  `sunday_out` varchar(191) DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `office_shift_company_id` (`company_id`),
  CONSTRAINT `office_shift_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add columns to office_shifts only if they do not exist (safe for repeated runs)
SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'office_shifts' AND COLUMN_NAME = 'is_flexible');
SET @sql := IF(@col = 0, 'ALTER TABLE `office_shifts` ADD COLUMN `is_flexible` tinyint(1) NOT NULL DEFAULT 0 AFTER `name`;', 'SELECT 1;');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'office_shifts' AND COLUMN_NAME = 'expected_hours');
SET @sql := IF(@col = 0, 'ALTER TABLE `office_shifts` ADD COLUMN `expected_hours` decimal(5,2) DEFAULT NULL AFTER `is_flexible`;', 'SELECT 1;');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'office_shifts' AND COLUMN_NAME = 'weekend_days');
SET @sql := IF(@col = 0, 'ALTER TABLE `office_shifts` ADD COLUMN `weekend_days` varchar(255) DEFAULT NULL AFTER `expected_hours`;', 'SELECT 1;');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'office_shifts' AND COLUMN_NAME = 'half_day_of_week');
SET @sql := IF(@col = 0, 'ALTER TABLE `office_shifts` ADD COLUMN `half_day_of_week` tinyint DEFAULT NULL AFTER `weekend_days`;', 'SELECT 1;');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col := (SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'office_shifts' AND COLUMN_NAME = 'half_day_expected_hours');
SET @sql := IF(@col = 0, 'ALTER TABLE `office_shifts` ADD COLUMN `half_day_expected_hours` decimal(5,2) DEFAULT NULL AFTER `half_day_of_week`;', 'SELECT 1;');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- Table structure for table `employees`
--
CREATE TABLE IF NOT EXISTS `employees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_users_id` bigint unsigned NOT NULL,
  `firstname` varchar(192) NOT NULL,
  `lastname` varchar(192) NOT NULL,
  `username` varchar(191) NOT NULL,
  `email` varchar(192) NOT NULL,
  `phone` varchar(192) NOT NULL,
  `country` varchar(192) NOT NULL,
  `city` varchar(192) DEFAULT NULL,
  `province` varchar(192) DEFAULT NULL,
  `zipcode` varchar(192) DEFAULT NULL,
  `address` varchar(192) DEFAULT NULL,
  `gender` varchar(192) NOT NULL,
  `resume` varchar(192) DEFAULT NULL,
  `avatar` varchar(192) DEFAULT 'no_avatar.png',
  `document` varchar(192) DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `company_id` int NOT NULL,
  `department_id` int NOT NULL,
  `designation_id` int NOT NULL,
  `office_shift_id` int NOT NULL,
  `remaining_leave` tinyint DEFAULT 0,
  `total_leave` tinyint DEFAULT 0,
  `hourly_rate` decimal(10,2) DEFAULT 0.00,
  `basic_salary` decimal(10,2) DEFAULT 0.00,
  `employment_type` varchar(192) DEFAULT 'full_time',
  `leaving_date` date DEFAULT NULL,
  `marital_status` varchar(192) DEFAULT 'single',
  `facebook` varchar(192) DEFAULT NULL,
  `skype` varchar(192) DEFAULT NULL,
  `whatsapp` varchar(192) DEFAULT NULL,
  `twitter` varchar(192) DEFAULT NULL,
  `linkedin` varchar(192) DEFAULT NULL,
  `mode` varchar(32) DEFAULT NULL,
  `expected_hours` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `employees_company_id` (`company_id`),
  KEY `employees_department_id` (`department_id`),
  KEY `employees_designation_id` (`designation_id`),
  KEY `employees_office_shift_id` (`office_shift_id`),
  KEY `employees_role_users_id` (`role_users_id`),
  CONSTRAINT `employees_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `employees_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `employees_designation_id` FOREIGN KEY (`designation_id`) REFERENCES `designations` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `employees_office_shift_id` FOREIGN KEY (`office_shift_id`) REFERENCES `office_shifts` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `employees_role_users_id` FOREIGN KEY (`role_users_id`) REFERENCES `roles` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `tasks`
--
CREATE TABLE IF NOT EXISTS `tasks` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(192) NOT NULL,
  `project_id` int NOT NULL,
  `company_id` int NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `estimated_hour` varchar(192) DEFAULT NULL,
  `task_progress` varchar(192) DEFAULT NULL,
  `summary` varchar(191) NOT NULL,
  `description` text,
  `status` varchar(192) NOT NULL,
  `priority` varchar(191) NOT NULL,
  `note` text,
  `quality_score` decimal(5,2) DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `Tasks_company_id` (`company_id`),
  KEY `Tasks_project_id` (`project_id`),
  CONSTRAINT `Tasks_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `Tasks_project_id` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `attendances`
--
CREATE TABLE IF NOT EXISTS `attendances` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `date` date NOT NULL,
  `clock_in` varchar(191) NOT NULL,
  `clock_in_ip` varchar(45) NOT NULL,
  `clock_out` varchar(191) NOT NULL,
  `clock_out_ip` varchar(191) NOT NULL,
  `clock_in_out` tinyint(1) NOT NULL,
  `depart_early` varchar(191) NOT NULL DEFAULT '00:00',
  `late_time` varchar(191) NOT NULL DEFAULT '00:00',
  `overtime` varchar(191) NOT NULL DEFAULT '00:00',
  `total_work` varchar(191) NOT NULL DEFAULT '00:00',
  `total_rest` varchar(191) NOT NULL DEFAULT '00:00',
  `status` varchar(191) NOT NULL DEFAULT 'present',
  `mode` varchar(32) DEFAULT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendances_company_id` (`company_id`),
  KEY `attendances_employee_id` (`employee_id`),
  CONSTRAINT `attendances_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `attendances_employee_id` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `notifications`
--
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` char(36) NOT NULL,
  `type` varchar(255) NOT NULL,
  `notifiable_type` varchar(255) NOT NULL,
  `notifiable_id` bigint(20) unsigned NOT NULL,
  `data` text NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `job_vacancies`
--
CREATE TABLE IF NOT EXISTS `job_vacancies` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `link` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_by` int NOT NULL,
  `company_id` int NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `job_vacancies_company_id` (`company_id`),
  KEY `job_vacancies_created_by` (`created_by`),
  CONSTRAINT `job_vacancies_company_id` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT,
  CONSTRAINT `job_vacancies_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON UPDATE RESTRICT ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `permissions`
--
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(192) NOT NULL,
  `guard_name` varchar(191) NOT NULL,
  `created_at` timestamp(6) NULL DEFAULT NULL,
  `updated_at` timestamp(6) NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `model_has_permissions`
--
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON UPDATE RESTRICT ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table structure for table `role_has_permissions`
--
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON UPDATE RESTRICT ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON UPDATE RESTRICT ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci; 