<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Project;
use App\Models\Attendance;
use App\Models\Leave;
use App\Models\User;

class TestDatabaseAccess extends Command
{
    protected $signature = 'test:database-access';
    protected $description = 'Test direct database access to verify data availability';

    public function handle()
    {
        $this->info('🔍 Testing Direct Database Access...');
        $this->newLine();

        // Test Employee data
        $this->info('👥 Employee Data:');
        try {
            $totalEmployees = Employee::count();
            $activeEmployees = Employee::whereNull('leaving_date')->count();
            $this->info("• Total Employees: {$totalEmployees}");
            $this->info("• Active Employees: {$activeEmployees}");
        } catch (\Exception $e) {
            $this->error("❌ Employee Error: " . $e->getMessage());
        }
        $this->newLine();

        // Test Department data
        $this->info('🏢 Department Data:');
        try {
            $totalDepartments = Department::count();
            $this->info("• Total Departments: {$totalDepartments}");
            
            $departments = Department::take(5)->get();
            foreach ($departments as $dept) {
                $empCount = Employee::where('department_id', $dept->id)->count();
                $deptName = $dept->department_name ?? $dept->department ?? 'Unknown';
                $this->info("  - {$deptName}: {$empCount} employees");
            }
        } catch (\Exception $e) {
            $this->error("❌ Department Error: " . $e->getMessage());
        }
        $this->newLine();

        // Test Project data
        $this->info('📊 Project Data:');
        try {
            $totalProjects = Project::count();
            $activeProjects = Project::where('status', 'in_progress')->count();
            $completedProjects = Project::where('status', 'completed')->count();
            $this->info("• Total Projects: {$totalProjects}");
            $this->info("• Active Projects: {$activeProjects}");
            $this->info("• Completed Projects: {$completedProjects}");
        } catch (\Exception $e) {
            $this->error("❌ Project Error: " . $e->getMessage());
        }
        $this->newLine();

        // Test Attendance data
        $this->info('📅 Attendance Data (Today):');
        try {
            $today = now()->format('Y-m-d');
            $presentToday = Attendance::whereDate('date', $today)->whereNotNull('clock_in')->count();
            $this->info("• Present Today: {$presentToday}");
        } catch (\Exception $e) {
            $this->error("❌ Attendance Error: " . $e->getMessage());
        }
        $this->newLine();

        // Test Leave data
        $this->info('🏖️ Leave Data:');
        try {
            $pendingLeaves = Leave::where('status', 'pending')->count();
            $approvedLeaves = Leave::where('status', 'approved')->count();
            $this->info("• Pending Leave Requests: {$pendingLeaves}");
            $this->info("• Approved Leave Requests: {$approvedLeaves}");
        } catch (\Exception $e) {
            $this->error("❌ Leave Error: " . $e->getMessage());
        }
        $this->newLine();

        // Test User data
        $this->info('👤 User Data:');
        try {
            $totalUsers = User::count();
            $adminUsers = User::where('role_users_id', 1)->count();
            $this->info("• Total Users: {$totalUsers}");
            $this->info("• Admin Users: {$adminUsers}");
        } catch (\Exception $e) {
            $this->error("❌ User Error: " . $e->getMessage());
        }
        $this->newLine();

        $this->info('✅ Database access test completed!');
        return 0;
    }
}
