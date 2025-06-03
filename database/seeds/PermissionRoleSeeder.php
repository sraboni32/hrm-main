<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // List all permissions
        $permissions = [
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
        ];

        // Assign all permissions to Super Admin
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions($permissions);
        }

        // Optionally assign a subset to Employee and Client
        $employeePermissions = [
            'employee_view', 'attendance_view', 'attendance_add', 'attendance_edit', 'attendance_delete',
            'task_view', 'task_add', 'task_edit', 'task_delete', 'task_view_own',
            // ...add more as needed
        ];
        $employee = Role::where('name', 'Employee')->first();
        if ($employee) {
            $employee->syncPermissions($employeePermissions);
        }

        $clientPermissions = [
            'client_view', 'project_view', 'task_view',
            // ...add more as needed
        ];
        $client = Role::where('name', 'Client')->first();
        if ($client) {
            $client->syncPermissions($clientPermissions);
        }
    }
}
