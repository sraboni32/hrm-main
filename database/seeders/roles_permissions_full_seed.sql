-- Create permissions table if it does not exist
CREATE TABLE IF NOT EXISTS `permissions` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create roles table if it does not exist
CREATE TABLE IF NOT EXISTS `roles` (
    `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
    `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
    `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
    `created_at` timestamp NULL DEFAULT NULL,
    `updated_at` timestamp NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create role_has_permissions table if it does not exist
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
    `permission_id` bigint(20) unsigned NOT NULL,
    `role_id` bigint(20) unsigned NOT NULL,
    PRIMARY KEY (`permission_id`,`role_id`),
    KEY `role_has_permissions_role_id_foreign` (`role_id`),
    CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert all permissions
INSERT IGNORE INTO permissions (name, guard_name)
VALUES
    ('employee_view', 'web'),
    ('employee_add', 'web'),
    ('employee_edit', 'web'),
    ('employee_delete', 'web'),
    ('user_view', 'web'),
    ('user_add', 'web'),
    ('user_edit', 'web'),
    ('user_delete', 'web'),
    ('company_view', 'web'),
    ('company_add', 'web'),
    ('company_edit', 'web'),
    ('company_delete', 'web'),
    ('department_view', 'web'),
    ('department_add', 'web'),
    ('department_edit', 'web'),
    ('department_delete', 'web'),
    ('designation_view', 'web'),
    ('designation_add', 'web'),
    ('designation_edit', 'web'),
    ('designation_delete', 'web'),
    ('policy_view', 'web'),
    ('policy_add', 'web'),
    ('policy_edit', 'web'),
    ('policy_delete', 'web'),
    ('announcement_view', 'web'),
    ('announcement_add', 'web'),
    ('announcement_edit', 'web'),
    ('announcement_delete', 'web'),
    ('office_shift_view', 'web'),
    ('office_shift_add', 'web'),
    ('office_shift_edit', 'web'),
    ('office_shift_delete', 'web'),
    ('event_view', 'web'),
    ('event_add', 'web'),
    ('event_edit', 'web'),
    ('event_delete', 'web'),
    ('holiday_view', 'web'),
    ('holiday_add', 'web'),
    ('holiday_edit', 'web'),
    ('holiday_delete', 'web'),
    ('award_view', 'web'),
    ('award_add', 'web'),
    ('award_edit', 'web'),
    ('award_delete', 'web'),
    ('award_type', 'web'),
    ('complaint_view', 'web'),
    ('complaint_add', 'web'),
    ('complaint_edit', 'web'),
    ('complaint_delete', 'web'),
    ('travel_view', 'web'),
    ('travel_add', 'web'),
    ('travel_edit', 'web'),
    ('travel_delete', 'web'),
    ('arrangement_type', 'web'),
    ('attendance_view', 'web'),
    ('attendance_add', 'web'),
    ('attendance_edit', 'web'),
    ('attendance_delete', 'web'),
    ('account_view', 'web'),
    ('account_add', 'web'),
    ('account_edit', 'web'),
    ('account_delete', 'web'),
    ('deposit_view', 'web'),
    ('deposit_add', 'web'),
    ('deposit_edit', 'web'),
    ('deposit_delete', 'web'),
    ('expense_view', 'web'),
    ('expense_add', 'web'),
    ('expense_edit', 'web'),
    ('expense_delete', 'web'),
    ('client_view', 'web'),
    ('client_add', 'web'),
    ('client_edit', 'web'),
    ('client_delete', 'web'),
    ('deposit_category', 'web'),
    ('payment_method', 'web'),
    ('expense_category', 'web'),
    ('project_view', 'web'),
    ('project_add', 'web'),
    ('project_edit', 'web'),
    ('project_delete', 'web'),
    ('task_view', 'web'),
    ('task_add', 'web'),
    ('task_edit', 'web'),
    ('task_delete', 'web'),
    ('leave_view', 'web'),
    ('leave_add', 'web'),
    ('leave_edit', 'web'),
    ('leave_delete', 'web'),
    ('training_view', 'web'),
    ('training_add', 'web'),
    ('training_edit', 'web'),
    ('training_delete', 'web'),
    ('trainer', 'web'),
    ('training_skills', 'web'),
    ('settings', 'web'),
    ('currency', 'web'),
    ('backup', 'web'),
    ('group_permission', 'web'),
    ('attendance_report', 'web'),
    ('employee_report', 'web'),
    ('project_report', 'web'),
    ('task_report', 'web'),
    ('expense_report', 'web'),
    ('deposit_report', 'web'),
    ('employee_details', 'web'),
    ('leave_type', 'web'),
    ('project_details', 'web'),
    ('task_details', 'web'),
    ('module_settings', 'web'),
    ('kanban_task', 'web'),
    ('kpi_report', 'web'),
    ('salary_disbursement_report', 'web'),
    ('leave_absence_report', 'web'),
    ('task_view_own', 'web');

-- Insert roles
INSERT IGNORE INTO roles (id, name, description, guard_name)
VALUES
    (1, 'Super Admin', 'Super Admin', 'web'),
    (2, 'Employee', 'Employee Access', 'web'),
    (3, 'Client', 'Client Access', 'web');

-- Assign all permissions to Super Admin
INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, 1
FROM permissions p
WHERE p.name IN (
    'employee_view', 'employee_add', 'employee_edit', 'employee_delete',
    'user_view', 'user_add', 'user_edit', 'user_delete',
    'company_view', 'company_add', 'company_edit', 'company_delete',
    'department_view', 'department_add', 'department_edit', 'department_delete',
    'designation_view', 'designation_add', 'designation_edit', 'designation_delete',
    'policy_view', 'policy_add', 'policy_edit', 'policy_delete',
    'announcement_view', 'announcement_add', 'announcement_edit', 'announcement_delete',
    'office_shift_view', 'office_shift_add', 'office_shift_edit', 'office_shift_delete',
    'event_view', 'event_add', 'event_edit', 'event_delete',
    'holiday_view', 'holiday_add', 'holiday_edit', 'holiday_delete',
    'award_view', 'award_add', 'award_edit', 'award_delete', 'award_type',
    'complaint_view', 'complaint_add', 'complaint_edit', 'complaint_delete',
    'travel_view', 'travel_add', 'travel_edit', 'travel_delete', 'arrangement_type',
    'attendance_view', 'attendance_add', 'attendance_edit', 'attendance_delete',
    'account_view', 'account_add', 'account_edit', 'account_delete',
    'deposit_view', 'deposit_add', 'deposit_edit', 'deposit_delete',
    'expense_view', 'expense_add', 'expense_edit', 'expense_delete',
    'client_view', 'client_add', 'client_edit', 'client_delete',
    'deposit_category', 'payment_method', 'expense_category',
    'project_view', 'project_add', 'project_edit', 'project_delete',
    'task_view', 'task_add', 'task_edit', 'task_delete',
    'leave_view', 'leave_add', 'leave_edit', 'leave_delete',
    'training_view', 'training_add', 'training_edit', 'training_delete',
    'trainer', 'training_skills', 'settings', 'currency', 'backup', 'group_permission',
    'attendance_report', 'employee_report', 'project_report', 'task_report', 'expense_report', 'deposit_report',
    'employee_details', 'leave_type', 'project_details', 'task_details', 'module_settings', 'kanban_task', 'kpi_report',
    'salary_disbursement_report', 'leave_absence_report', 'task_view_own'
);

-- Assign subset of permissions to Employee
INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, 2
FROM permissions p
WHERE p.name IN (
    'employee_view', 'attendance_view', 'attendance_add', 'attendance_edit', 'attendance_delete',
    'task_view', 'task_add', 'task_edit', 'task_delete', 'task_view_own'
);

-- Assign subset of permissions to Client
INSERT IGNORE INTO role_has_permissions (permission_id, role_id)
SELECT p.id, 3
FROM permissions p
WHERE p.name IN (
    'client_view', 'project_view', 'task_view'
); 