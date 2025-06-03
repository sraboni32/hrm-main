<?php

namespace App\Services;

use App\Models\User;
use App\Models\Employee;
use App\Models\Department;
use App\Models\Project;
use App\Models\Task;
use App\Models\Leave;
use App\Models\Attendance;
use App\Models\Client;
use App\Models\Company;
use App\Models\Designation;
use App\Models\SalaryDisbursement;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class AiDatabaseService
{
    /**
     * Get role-based data access for AI
     */
    public function getRoleBasedData($userId, $query = null)
    {
        try {
            $user = User::with(['employee.department', 'employee.designation', 'employee.company'])->find($userId);

            if (!$user) {
                return ['error' => 'User not found'];
            }

            $role = $this->getUserRole($user);
            $data = [];

            switch ($role) {
                case 'super_admin':
                    $data = $this->getSuperAdminData($user, $query);
                    break;
                case 'admin':
                    $data = $this->getAdminData($user, $query);
                    break;
                case 'employee':
                    $data = $this->getEmployeeData($user, $query);
                    break;
                case 'client':
                    $data = $this->getClientData($user, $query);
                    break;
                default:
                    $data = $this->getBasicData($user, $query);
            }

            return $data;

        } catch (\Exception $e) {
            \Log::error('AiDatabaseService Error: ' . $e->getMessage());
            return [
                'error' => 'Database access error',
                'role' => 'basic',
                'access_level' => 'limited',
                'message' => 'Unable to load role-based data'
            ];
        }
    }

    /**
     * Get user role
     */
    private function getUserRole($user)
    {
        if ($user->role_users_id == 1) {
            return 'super_admin';
        }

        $role = Role::find($user->role_users_id);

        if ($role) {
            return strtolower(str_replace(' ', '_', $role->name));
        }

        return 'employee';
    }

    /**
     * Super Admin - Full access to all data
     */
    private function getSuperAdminData($user, $query)
    {
        try {
            return [
                'role' => 'super_admin',
                'access_level' => 'full',
                'user_info' => $this->getUserInfo($user),
                'company_stats' => $this->getCompanyStats(),
                'employee_stats' => $this->getEmployeeStats(),
                'project_stats' => $this->getProjectStats(),
                'attendance_stats' => $this->getAttendanceStats(),
                'leave_stats' => $this->getLeaveStats(),
                'salary_stats' => $this->getSalaryStats(),
                'departments' => $this->getAllDepartments(),
                'recent_activities' => $this->getRecentActivities(),
                'system_info' => $this->getSystemInfo()
            ];
        } catch (\Exception $e) {
            \Log::error('getSuperAdminData Error: ' . $e->getMessage());
            return [
                'role' => 'super_admin',
                'access_level' => 'full',
                'user_info' => $this->getUserInfo($user),
                'error' => 'Some data unavailable: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Admin - Department/Company level access
     */
    private function getAdminData($user, $query)
    {
        $employee = $user->employee;
        $companyId = $employee ? $employee->company_id : null;
        $departmentId = $employee ? $employee->department_id : null;

        return [
            'role' => 'admin',
            'access_level' => 'company',
            'user_info' => $this->getUserInfo($user),
            'company_id' => $companyId,
            'department_id' => $departmentId,
            'company_stats' => $this->getCompanyStats($companyId),
            'employee_stats' => $this->getEmployeeStats($companyId),
            'project_stats' => $this->getProjectStats($companyId),
            'attendance_stats' => $this->getAttendanceStats($companyId),
            'leave_stats' => $this->getLeaveStats($companyId),
            'department_info' => $this->getDepartmentInfo($departmentId),
            'team_members' => $this->getTeamMembers($departmentId),
            'recent_activities' => $this->getRecentActivities($companyId)
            
        ];
    }

    /**
     * Employee - Personal data only
     */
    private function getEmployeeData($user, $query)
    {
        $employee = $user->employee;

        if (!$employee) {
            return ['error' => 'Employee record not found'];
        }

        return [
            'role' => 'employee',
            'access_level' => 'personal',
            'user_info' => $this->getUserInfo($user),
            'employee_info' => $this->getEmployeeInfo($employee),
            'personal_stats' => $this->getPersonalStats($employee),
            'my_projects' => $this->getMyProjects($employee->id),
            'my_tasks' => $this->getMyTasks($employee->id),
            'my_attendance' => $this->getMyAttendance($employee->id),
            'my_leaves' => $this->getMyLeaves($employee->id),
            'my_salary' => $this->getMySalary($employee->id),
            'department_info' => $this->getDepartmentInfo($employee->department_id),
            'colleagues' => $this->getColleagues($employee->department_id, $employee->id)
        ];
    }

    /**
     * Client - Project related data only
     */
    private function getClientData($user, $query)
    {
        // Assuming client data is stored in a separate table or identified by role
        $clientProjects = Project::where('client_id', $user->id)->get();

        return [
            'role' => 'client',
            'access_level' => 'projects',
            'user_info' => $this->getUserInfo($user),
            'my_projects' => $clientProjects,
            'project_stats' => $this->getClientProjectStats($user->id)
        ];
    }

    /**
     * Basic data for unknown roles
     */
    private function getBasicData($user, $query)
    {
        return [
            'role' => 'basic',
            'access_level' => 'limited',
            'user_info' => $this->getUserInfo($user),
            'message' => 'Limited access - contact administrator for more permissions'
        ];
    }

    /**
     * Get user basic info
     */
    private function getUserInfo($user)
    {
        $employee = $user->employee;

        return [
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'role_id' => $user->role_users_id,
            'name' => $employee ? trim($employee->firstname . ' ' . $employee->lastname) : $user->username,
            'department' => $employee && $employee->department ? $employee->department->department_name : null,
            'designation' => $employee && $employee->designation ? $employee->designation->designation : null,
            'joining_date' => $employee ? $employee->joining_date : null,
            'employee_id' => $employee ? $employee->id : null
        ];
    }

    /**
     * Get company statistics
     */
    private function getCompanyStats($companyId = null)
    {
        try {
            $query = Employee::query();
            if ($companyId) {
                $query->where('company_id', $companyId);
            }

            return [
                'total_employees' => $query->count(),
                'active_employees' => $query->whereNull('leaving_date')->count(),
                'departments_count' => Department::when($companyId, function($q) use ($companyId) {
                    return $q->where('company_id', $companyId);
                })->count(),
                'projects_count' => Project::when($companyId, function($q) use ($companyId) {
                    return $q->where('company_id', $companyId);
                })->count()
            ];
        } catch (\Exception $e) {
            return [
                'total_employees' => 0,
                'active_employees' => 0,
                'departments_count' => 0,
                'projects_count' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get employee statistics
     */
    private function getEmployeeStats($companyId = null)
    {
        $query = Employee::query();
        if ($companyId) {
            $query->where('company_id', $companyId);
        }

        return [
            'total' => $query->count(),
            'by_department' => $query->with('department')
                ->get()
                ->groupBy('department.department')
                ->map(function($employees) {
                    return $employees->count();
                }),
            'recent_joinings' => $query->where('joining_date', '>=', now()->subDays(30))->count(),
            'on_leave_today' => $this->getEmployeesOnLeaveToday($companyId)
        ];
    }

    /**
     * Get project statistics
     */
    private function getProjectStats($companyId = null)
    {
        try {
            $query = Project::query();
            if ($companyId) {
                $query->where('company_id', $companyId);
            }

            return [
                'total' => $query->count(),
                'active' => $query->where('status', 'in_progress')->count(),
                'completed' => $query->where('status', 'completed')->count(),
                'overdue' => $query->where('end_date', '<', now())->where('status', '!=', 'completed')->count(),
                'pending' => $query->where('status', 'pending')->count(),
                'on_hold' => $query->where('status', 'on_hold')->count()
            ];
        } catch (\Exception $e) {
            \Log::error('getProjectStats Error: ' . $e->getMessage());
            return [
                'total' => 0,
                'active' => 0,
                'completed' => 0,
                'overdue' => 0,
                'pending' => 0,
                'on_hold' => 0,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get attendance statistics
     */
    private function getAttendanceStats($companyId = null)
    {
        $today = now()->format('Y-m-d');

        $query = Attendance::whereDate('date', $today);
        if ($companyId) {
            $query->whereHas('employee', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        return [
            'present_today' => $query->whereNotNull('clock_in')->count(),
            'absent_today' => $this->getAbsentToday($companyId),
            'late_arrivals' => $query->where('clock_in', '>', '09:30:00')->count(),
            'average_hours' => $query->whereNotNull('clock_out')->avg(DB::raw('TIME_TO_SEC(TIMEDIFF(clock_out, clock_in))/3600'))
        ];
    }

    /**
     * Get leave statistics
     */
    private function getLeaveStats($companyId = null)
    {
        $query = Leave::query();
        if ($companyId) {
            $query->whereHas('employee', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        return [
            'pending_requests' => $query->where('status', 'pending')->count(),
            'approved_this_month' => $query->where('status', 'approved')
                ->whereMonth('start_date', now()->month)->count(),
            'on_leave_today' => $this->getEmployeesOnLeaveToday($companyId)
        ];
    }

    /**
     * Get salary statistics
     */
    private function getSalaryStats($companyId = null)
    {
        $query = SalaryDisbursement::query();
        if ($companyId) {
            $query->whereHas('employee', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        return [
            'total_disbursed_this_month' => $query->whereMonth('disbursement_date', now()->month)->sum('amount'),
            'pending_disbursements' => $query->where('status', 'pending')->count(),
            'average_salary' => $query->whereMonth('disbursement_date', now()->month)->avg('amount')
        ];
    }

    /**
     * Get all departments
     */
    private function getAllDepartments()
    {
        try {
            return Department::with(['employee', 'company'])->get()->map(function($dept) {
                return [
                    'id' => $dept->id,
                    'name' => $dept->department_name ?? $dept->department ?? 'Unknown Department',
                    'head' => $dept->employee ? $dept->employee->firstname . ' ' . $dept->employee->lastname : 'No Head Assigned',
                    'company' => $dept->company ? $dept->company->name : 'No Company',
                    'employee_count' => Employee::where('department_id', $dept->id)->count()
                ];
            })->toArray();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($companyId = null)
    {
        $activities = [];

        // Recent employee joinings
        $recentJoinings = Employee::when($companyId, function($q) use ($companyId) {
                return $q->where('company_id', $companyId);
            })
            ->where('joining_date', '>=', now()->subDays(7))
            ->with(['department', 'designation'])
            ->get();

        foreach ($recentJoinings as $employee) {
            $activities[] = [
                'type' => 'employee_joined',
                'message' => "{$employee->firstname} {$employee->lastname} joined as " . ($employee->designation ? $employee->designation->designation : 'Employee') . " in " . ($employee->department ? $employee->department->department : 'Unknown') . " department",
                'date' => $employee->joining_date
            ];
        }

        // Recent project completions
        $recentProjects = Project::when($companyId, function($q) use ($companyId) {
                return $q->where('company_id', $companyId);
            })
            ->where('status', 'completed')
            ->where('updated_at', '>=', now()->subDays(7))
            ->get();

        foreach ($recentProjects as $project) {
            $activities[] = [
                'type' => 'project_completed',
                'message' => "Project '{$project->title}' was completed",
                'date' => $project->updated_at
            ];
        }

        return collect($activities)->sortByDesc('date')->take(10)->values();
    }

    /**
     * Get system info (Super Admin only)
     */
    private function getSystemInfo()
    {
        return [
            'total_users' => User::count(),
            'total_companies' => Company::count(),
            'database_size' => $this->getDatabaseSize(),
            'last_backup' => 'Not implemented',
            'system_version' => '1.0.0'
        ];
    }

    /**
     * Get personal employee info
     */
    private function getEmployeeInfo($employee)
    {
        return [
            'id' => $employee->id,
            'name' => trim($employee->firstname . ' ' . $employee->lastname),
            'email' => $employee->email,
            'phone' => $employee->phone,
            'department' => $employee->department ? $employee->department->department : null,
            'designation' => $employee->designation ? $employee->designation->designation : null,
            'joining_date' => $employee->joining_date,
            'basic_salary' => $employee->basic_salary,
            'remaining_leave' => $employee->remaining_leave,
            'total_leave' => $employee->total_leave,
            'employment_type' => $employee->employment_type
        ];
    }

    /**
     * Get personal statistics
     */
    private function getPersonalStats($employee)
    {
        return [
            'total_projects' => $employee->tasks()->distinct('project_id')->count('project_id'),
            'total_tasks' => $employee->tasks()->count(),
            'completed_tasks' => $employee->tasks()->where('status', 'completed')->count(),
            'attendance_this_month' => Attendance::where('employee_id', $employee->id)
                ->whereMonth('date', now()->month)->count(),
            'leaves_taken_this_year' => Leave::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->whereYear('start_date', now()->year)->count()
        ];
    }

    /**
     * Get my projects
     */
    private function getMyProjects($employeeId)
    {
        return Project::whereHas('assignedEmployees', function($q) use ($employeeId) {
            $q->where('employee_id', $employeeId);
        })->with(['client', 'company'])->get()->map(function($project) {
            return [
                'id' => $project->id,
                'title' => $project->title,
                'status' => $project->status,
                'progress' => $project->project_progress,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'client' => $project->client ? $project->client->firstname . ' ' . $project->client->lastname : null
            ];
        });
    }

    /**
     * Get my tasks
     */
    private function getMyTasks($employeeId)
    {
        return Task::whereHas('assignedEmployees', function($q) use ($employeeId) {
            $q->where('employee_id', $employeeId);
        })->with(['project'])->get()->map(function($task) {
            return [
                'id' => $task->id,
                'title' => $task->title,
                'status' => $task->status,
                'progress' => $task->task_progress,
                'priority' => $task->priority,
                'start_date' => $task->start_date,
                'end_date' => $task->end_date,
                'project' => $task->project ? $task->project->title : null
            ];
        });
    }

    /**
     * Get my attendance
     */
    private function getMyAttendance($employeeId)
    {
        return Attendance::where('employee_id', $employeeId)
            ->whereMonth('date', now()->month)
            ->orderBy('date', 'desc')
            ->take(10)
            ->get()
            ->map(function($attendance) {
                return [
                    'date' => $attendance->date,
                    'clock_in' => $attendance->clock_in,
                    'clock_out' => $attendance->clock_out,
                    'total_hours' => $attendance->clock_out ?
                        round((strtotime($attendance->clock_out) - strtotime($attendance->clock_in)) / 3600, 2) : null
                ];
            });
    }

    /**
     * Get my leaves
     */
    private function getMyLeaves($employeeId)
    {
        return Leave::where('employee_id', $employeeId)
            ->orderBy('start_date', 'desc')
            ->take(10)
            ->get()
            ->map(function($leave) {
                return [
                    'id' => $leave->id,
                    'start_date' => $leave->start_date,
                    'end_date' => $leave->end_date,
                    'status' => $leave->status,
                    'reason' => $leave->reason ?? 'Not specified',
                    'days' => $leave->days ?? 1
                ];
            });
    }

    /**
     * Get my salary information
     */
    private function getMySalary($employeeId)
    {
        $latestSalary = SalaryDisbursement::where('employee_id', $employeeId)
            ->orderBy('disbursement_date', 'desc')
            ->first();

        return [
            'latest_disbursement' => $latestSalary ? [
                'amount' => $latestSalary->amount,
                'date' => $latestSalary->disbursement_date,
                'status' => $latestSalary->status
            ] : null,
            'total_this_year' => SalaryDisbursement::where('employee_id', $employeeId)
                ->whereYear('disbursement_date', now()->year)
                ->sum('amount')
        ];
    }

    /**
     * Helper methods
     */
    private function getEmployeesOnLeaveToday($companyId = null)
    {
        $today = now()->format('Y-m-d');

        $query = Leave::where('status', 'approved')
            ->where('start_date', '<=', $today)
            ->where('end_date', '>=', $today);

        if ($companyId) {
            $query->whereHas('employee', function($q) use ($companyId) {
                $q->where('company_id', $companyId);
            });
        }

        return $query->count();
    }

    private function getAbsentToday($companyId = null)
    {
        $today = now()->format('Y-m-d');
        $totalEmployees = Employee::when($companyId, function($q) use ($companyId) {
            return $q->where('company_id', $companyId);
        })->whereNull('leaving_date')->count();

        $presentToday = Attendance::whereDate('date', $today)
            ->when($companyId, function($q) use ($companyId) {
                return $q->whereHas('employee', function($subQ) use ($companyId) {
                    $subQ->where('company_id', $companyId);
                });
            })
            ->whereNotNull('clock_in')->count();

        return $totalEmployees - $presentToday;
    }

    private function getDatabaseSize()
    {
        try {
            $size = DB::select("SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 1) AS 'size_mb' FROM information_schema.tables WHERE table_schema = DATABASE()")[0]->size_mb ?? 0;
            return $size . ' MB';
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    private function getDepartmentInfo($departmentId)
    {
        if (!$departmentId) return null;

        $department = Department::with(['employee', 'company'])->find($departmentId);
        if (!$department) return null;

        return [
            'id' => $department->id,
            'name' => $department->department,
            'head' => $department->employee ? $department->employee->firstname . ' ' . $department->employee->lastname : null,
            'company' => $department->company ? $department->company->name : null,
            'employee_count' => Employee::where('department_id', $departmentId)->count()
        ];
    }

    private function getTeamMembers($departmentId)
    {
        if (!$departmentId) return [];

        return Employee::where('department_id', $departmentId)
            ->with(['designation'])
            ->get()
            ->map(function($employee) {
                return [
                    'id' => $employee->id,
                    'name' => trim($employee->firstname . ' ' . $employee->lastname),
                    'designation' => $employee->designation ? $employee->designation->designation : null,
                    'email' => $employee->email
                ];
            });
    }

    private function getColleagues($departmentId, $excludeEmployeeId)
    {
        if (!$departmentId) return [];

        return Employee::where('department_id', $departmentId)
            ->where('id', '!=', $excludeEmployeeId)
            ->with(['designation'])
            ->take(5)
            ->get()
            ->map(function($employee) {
                return [
                    'name' => trim($employee->firstname . ' ' . $employee->lastname),
                    'designation' => $employee->designation ? $employee->designation->designation : null
                ];
            });
    }

    private function getClientProjectStats($clientId)
    {
        return [
            'total_projects' => Project::where('client_id', $clientId)->count(),
            'active_projects' => Project::where('client_id', $clientId)->where('status', 'in_progress')->count(),
            'completed_projects' => Project::where('client_id', $clientId)->where('status', 'completed')->count()
        ];
    }
}
