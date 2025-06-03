-- Run each statement separately. If you get a 'duplicate column' error, the column already exists—just ignore and continue.

-- 1. Add employee_id to tasks
ALTER TABLE `tasks` ADD COLUMN `employee_id` INT NULL AFTER `company_id`;

-- 2. Add completed_at and quality_score to tasks
ALTER TABLE `tasks` ADD COLUMN `completed_at` TIMESTAMP NULL AFTER `note`;
ALTER TABLE `tasks` ADD COLUMN `quality_score` DECIMAL(5,2) NULL AFTER `completed_at`;

-- 3. Add mode and expected_hours to employees
ALTER TABLE `employees` ADD COLUMN `mode` VARCHAR(32) NULL AFTER `employment_type`;
ALTER TABLE `employees` ADD COLUMN `expected_hours` DECIMAL(5,2) NULL AFTER `mode`;

-- 4. Add foreign key from tasks.employee_id to employees.id
ALTER TABLE `tasks` ADD CONSTRAINT `tasks_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees`(`id`) ON UPDATE RESTRICT ON DELETE SET NULL;

-- Note: IF NOT EXISTS is supported in MySQL 8.0+. For older versions, run each ADD COLUMN separately and ignore errors if column exists. 