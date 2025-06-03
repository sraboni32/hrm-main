<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Employee;

class CheckUserRoles extends Command
{
    protected $signature = 'check:user-roles';
    protected $description = 'Check user roles and employee associations';

    public function handle()
    {
        $this->info('🔍 Checking User Roles and Employee Associations...');
        $this->newLine();

        $users = User::with(['employee.department', 'employee.designation'])->get();

        $this->info('📊 User Role Summary:');
        $this->newLine();

        foreach ($users as $user) {
            $this->info("👤 User ID: {$user->id} | Username: {$user->username}");
            $this->line("   Email: {$user->email}");
            $this->line("   Role ID: {$user->role_users_id}");
            
            // Determine role type
            $roleType = 'Unknown';
            if ($user->role_users_id == 1) {
                $roleType = 'Super Admin';
            } elseif ($user->employee) {
                $roleType = 'Employee/Admin';
            } else {
                $roleType = 'Basic User';
            }
            $this->line("   Role Type: {$roleType}");
            
            // Employee details
            if ($user->employee) {
                $emp = $user->employee;
                $this->line("   Employee ID: {$emp->id}");
                $this->line("   Name: " . trim($emp->firstname . ' ' . $emp->lastname));
                $this->line("   Department: " . ($emp->department ? $emp->department->department_name : 'No Department'));
                $this->line("   Designation: " . ($emp->designation ? $emp->designation->designation : 'No Designation'));
                $this->line("   Company ID: " . ($emp->company_id ?? 'No Company'));
                $this->line("   Department ID: " . ($emp->department_id ?? 'No Department'));
            } else {
                $this->line("   Employee Record: Not Found");
            }
            
            $this->newLine();
            $this->line('---');
            $this->newLine();
        }

        // Summary
        $superAdmins = $users->where('role_users_id', 1)->count();
        $employees = $users->whereNotNull('employee')->count();
        $basicUsers = $users->whereNull('employee')->where('role_users_id', '!=', 1)->count();

        $this->info('📈 Summary:');
        $this->line("Super Admins: {$superAdmins}");
        $this->line("Employees/Admins: {$employees}");
        $this->line("Basic Users: {$basicUsers}");
        $this->line("Total Users: {$users->count()}");

        $this->newLine();
        $this->info('💡 For testing:');
        $this->line('- Super Admin users: Use for full system access');
        $this->line('- Employee users: Use for department/personal data');
        $this->line('- Users with department_id: Can test admin features');

        return 0;
    }
}
