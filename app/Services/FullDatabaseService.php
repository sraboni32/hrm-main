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
use App\Models\SalaryDisbursement;
use App\Models\Designation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FullDatabaseService
{
    /**
     * Get all employees with complete details
     */
    public function getAllEmployees()
    {
        try {
            return Employee::with([
                'department',
                'designation',
                'company',
                'user'
            ])
            ->get()
            ->map(function($employee) {
                return [
                    'id' => $employee->id,
                    'employee_code' => $employee->employee_code ?? 'N/A',
                    'first_name' => $employee->firstname,
                    'last_name' => $employee->lastname,
                    'full_name' => trim($employee->firstname . ' ' . $employee->lastname),
                    'email' => $employee->email,
                    'phone' => $employee->phone,
                    'address' => $employee->address,
                    'date_of_birth' => $employee->date_of_birth,
                    'gender' => $employee->gender,
                    'joining_date' => $employee->joining_date,
                    'leaving_date' => $employee->leaving_date,
                    'employment_status' => $employee->leaving_date ? 'Inactive' : 'Active',
                    'employment_type' => $employee->employment_type ?? 'Full-time',
                    'basic_salary' => $employee->basic_salary ?? 0,
                    'remaining_leave' => $employee->remaining_leave ?? 0,
                    'total_leave' => $employee->total_leave ?? 0,
                    'used_leave' => ($employee->total_leave ?? 0) - ($employee->remaining_leave ?? 0),
                    'department' => [
                        'id' => $employee->department ? $employee->department->id : null,
                        'name' => $employee->department ? $employee->department->department_name : 'No Department'
                    ],
                    'designation' => [
                        'id' => $employee->designation ? $employee->designation->id : null,
                        'title' => $employee->designation ? $employee->designation->designation : 'No Designation'
                    ],
                    'company' => [
                        'id' => $employee->company ? $employee->company->id : null,
                        'name' => $employee->company ? $employee->company->name : 'No Company'
                    ],
                    'user_account' => $employee->user ? [
                        'id' => $employee->user->id,
                        'username' => $employee->user->username,
                        'role_id' => $employee->user->role_users_id
                    ] : null,
                    'created_at' => $employee->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $employee->updated_at->format('Y-m-d H:i:s')
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('getAllEmployees Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all attendance records with details
     */
    public function getAllAttendance($limit = 100)
    {
        try {
            return Attendance::with(['employee.department'])
                ->orderBy('date', 'desc')
                ->orderBy('clock_in', 'desc')
                ->take($limit)
                ->get()
                ->map(function($attendance) {
                    $clockIn = $attendance->clock_in;
                    $clockOut = $attendance->clock_out;
                    $totalHours = null;
                    $isLate = false;

                    if ($clockIn) {
                        $isLate = $clockIn > '09:30:00';
                        if ($clockOut) {
                            $totalHours = round((strtotime($clockOut) - strtotime($clockIn)) / 3600, 2);
                        }
                    }

                    // Get department name properly
                    $departmentName = 'No Department';
                    if ($attendance->employee && $attendance->employee->department_id) {
                        $department = Department::find($attendance->employee->department_id);
                        $departmentName = $department ? $department->department : 'No Department';
                    }

                    return [
                        'id' => $attendance->id,
                        'date' => $attendance->date,
                        'employee' => [
                            'id' => $attendance->employee ? $attendance->employee->id : null,
                            'name' => $attendance->employee ?
                                trim($attendance->employee->firstname . ' ' . $attendance->employee->lastname) : 'Unknown',
                            'department' => $departmentName,
                            'department_id' => $attendance->employee ? $attendance->employee->department_id : null
                        ],
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'total_hours' => $totalHours,
                        'is_late' => $isLate,
                        'status' => $clockOut ? 'Complete' : ($clockIn ? 'In Progress' : 'Not Started'),
                        'created_at' => $attendance->created_at->format('Y-m-d H:i:s')
                    ];
                })->toArray();
        } catch (\Exception $e) {
            Log::error('getAllAttendance Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get today's attendance summary
     */
    public function getTodayAttendanceSummary()
    {
        try {
            $today = now()->format('Y-m-d');

            $todayAttendance = Attendance::whereDate('date', $today)
                ->with(['employee.department'])
                ->get();

            // If no attendance today, get recent attendance records
            if ($todayAttendance->isEmpty()) {
                $recentAttendance = Attendance::with(['employee.department'])
                    ->orderBy('date', 'desc')
                    ->take(20)
                    ->get();

                $totalEmployees = Employee::whereNull('leaving_date')->count();

                return [
                    'date' => $today,
                    'total_employees' => $totalEmployees,
                    'present_count' => 0,
                    'absent_count' => $totalEmployees,
                    'attendance_rate' => 0,
                    'late_arrivals' => 0,
                    'completed_shifts' => 0,
                    'in_progress_shifts' => 0,
                    'message' => 'No attendance records for today. Showing recent attendance records.',
                    'recent_attendance_records' => $recentAttendance->map(function($attendance) {
                        // Get department name properly
                        $departmentName = 'No Department';
                        if ($attendance->employee && $attendance->employee->department_id) {
                            $department = Department::find($attendance->employee->department_id);
                            $departmentName = $department ? $department->department : 'No Department';
                        }

                        return [
                            'date' => $attendance->date,
                            'employee_name' => $attendance->employee ?
                                trim($attendance->employee->firstname . ' ' . $attendance->employee->lastname) : 'Unknown',
                            'department' => $departmentName,
                            'department_id' => $attendance->employee ? $attendance->employee->department_id : null,
                            'clock_in' => $attendance->clock_in,
                            'clock_out' => $attendance->clock_out,
                            'is_late' => $attendance->clock_in && $attendance->clock_in > '09:30:00',
                            'status' => $attendance->clock_out ? 'Complete' : ($attendance->clock_in ? 'In Progress' : 'Not Started')
                        ];
                    })->toArray()
                ];
            }

            $totalEmployees = Employee::whereNull('leaving_date')->count();
            $presentCount = $todayAttendance->whereNotNull('clock_in')->count();
            $lateCount = $todayAttendance->where('clock_in', '>', '09:30:00')->count();
            $completedCount = $todayAttendance->whereNotNull('clock_out')->count();

            return [
                'date' => $today,
                'total_employees' => $totalEmployees,
                'present_count' => $presentCount,
                'absent_count' => $totalEmployees - $presentCount,
                'attendance_rate' => $totalEmployees > 0 ? round(($presentCount / $totalEmployees) * 100, 2) : 0,
                'late_arrivals' => $lateCount,
                'completed_shifts' => $completedCount,
                'in_progress_shifts' => $presentCount - $completedCount,
                'attendance_details' => $todayAttendance->map(function($attendance) {
                    // Get department name properly
                    $departmentName = 'No Department';
                    if ($attendance->employee && $attendance->employee->department_id) {
                        $department = Department::find($attendance->employee->department_id);
                        $departmentName = $department ? $department->department : 'No Department';
                    }

                    return [
                        'employee_name' => $attendance->employee ?
                            trim($attendance->employee->firstname . ' ' . $attendance->employee->lastname) : 'Unknown',
                        'department' => $departmentName,
                        'department_id' => $attendance->employee ? $attendance->employee->department_id : null,
                        'clock_in' => $attendance->clock_in,
                        'clock_out' => $attendance->clock_out,
                        'is_late' => $attendance->clock_in && $attendance->clock_in > '09:30:00',
                        'status' => $attendance->clock_out ? 'Complete' : ($attendance->clock_in ? 'In Progress' : 'Not Started')
                    ];
                })->toArray()
            ];
        } catch (\Exception $e) {
            Log::error('getTodayAttendanceSummary Error: ' . $e->getMessage());
            return [
                'error' => 'Failed to retrieve attendance data: ' . $e->getMessage(),
                'fallback_data' => [
                    'total_employees' => Employee::whereNull('leaving_date')->count(),
                    'message' => 'Attendance data temporarily unavailable'
                ]
            ];
        }
    }

    /**
     * Get all leave requests with details
     */
    public function getAllLeaveRequests($limit = 100)
    {
        try {
            return Leave::with(['employee.department'])
                ->orderBy('created_at', 'desc')
                ->take($limit)
                ->get()
                ->map(function($leave) {
                    return [
                        'id' => $leave->id,
                        'employee' => [
                            'id' => $leave->employee ? $leave->employee->id : null,
                            'name' => $leave->employee ?
                                trim($leave->employee->firstname . ' ' . $leave->employee->lastname) : 'Unknown',
                            'department' => $leave->employee && $leave->employee->department ?
                                $leave->employee->department->department_name : 'No Department'
                        ],
                        'start_date' => $leave->start_date,
                        'end_date' => $leave->end_date,
                        'days' => $leave->days ?? 1,
                        'leave_type' => $leave->leave_type ?? 'General',
                        'reason' => $leave->reason ?? 'Not specified',
                        'status' => $leave->status,
                        'applied_date' => $leave->created_at->format('Y-m-d'),
                        'approved_date' => $leave->updated_at->format('Y-m-d'),
                        'is_current' => $leave->status === 'approved' &&
                            $leave->start_date <= now()->format('Y-m-d') &&
                            $leave->end_date >= now()->format('Y-m-d')
                    ];
                })->toArray();
        } catch (\Exception $e) {
            Log::error('getAllLeaveRequests Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all departments with complete details
     */
    public function getAllDepartments()
    {
        try {
            return Department::with(['employee', 'company'])
                ->get()
                ->map(function($dept) {
                    $employeeCount = Employee::where('department_id', $dept->id)->count();
                    $activeEmployeeCount = Employee::where('department_id', $dept->id)
                        ->whereNull('leaving_date')->count();

                    return [
                        'id' => $dept->id,
                        'name' => $dept->department ?? 'Unknown Department',
                        'description' => $dept->description ?? 'No description',
                        'head' => [
                            'id' => $dept->employee ? $dept->employee->id : null,
                            'name' => $dept->employee ?
                                trim($dept->employee->firstname . ' ' . $dept->employee->lastname) : 'No Head Assigned'
                        ],
                        'company' => [
                            'id' => $dept->company ? $dept->company->id : null,
                            'name' => $dept->company ? $dept->company->name : 'No Company'
                        ],
                        'employee_count' => $employeeCount,
                        'active_employee_count' => $activeEmployeeCount,
                        'inactive_employee_count' => $employeeCount - $activeEmployeeCount,
                        'created_at' => $dept->created_at->format('Y-m-d H:i:s')
                    ];
                })->toArray();
        } catch (\Exception $e) {
            Log::error('getAllDepartments Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all projects with complete details
     */
    public function getAllProjects()
    {
        try {
            return Project::with(['client', 'company', 'tasks.assignedEmployees'])
                ->get()
                ->map(function($project) {
                    $taskCount = $project->tasks ? $project->tasks->count() : 0;
                    $completedTasks = $project->tasks ? $project->tasks->where('status', 'completed')->count() : 0;

                    return [
                        'id' => $project->id,
                        'title' => $project->title,
                        'description' => $project->description ?? 'No description',
                        'status' => $project->status,
                        'priority' => $project->priority ?? 'Normal',
                        'progress' => $project->project_progress ?? 0,
                        'start_date' => $project->start_date,
                        'end_date' => $project->end_date,
                        'budget' => $project->budget ?? 0,
                        'client' => [
                            'id' => $project->client ? $project->client->id : null,
                            'name' => $project->client ?
                                trim($project->client->firstname . ' ' . $project->client->lastname) : 'No Client'
                        ],
                        'company' => [
                            'id' => $project->company ? $project->company->id : null,
                            'name' => $project->company ? $project->company->name : 'No Company'
                        ],
                        'task_summary' => [
                            'total_tasks' => $taskCount,
                            'completed_tasks' => $completedTasks,
                            'pending_tasks' => $taskCount - $completedTasks,
                            'completion_rate' => $taskCount > 0 ? round(($completedTasks / $taskCount) * 100, 2) : 0
                        ],
                        'is_overdue' => $project->end_date < now()->format('Y-m-d') && $project->status !== 'completed',
                        'created_at' => $project->created_at->format('Y-m-d H:i:s')
                    ];
                })->toArray();
        } catch (\Exception $e) {
            Log::error('getAllProjects Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all tasks with complete details
     */
    public function getAllTasks($limit = 100)
    {
        try {
            return Task::with(['project', 'assignedEmployees.department'])
                ->orderBy('created_at', 'desc')
                ->take($limit)
                ->get()
                ->map(function($task) {
                    $assignedEmployees = $task->assignedEmployees ?
                        $task->assignedEmployees->map(function($emp) {
                            return [
                                'id' => $emp->id,
                                'name' => trim($emp->firstname . ' ' . $emp->lastname),
                                'department' => $emp->department ? $emp->department->department_name : 'No Department'
                            ];
                        })->toArray() : [];

                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description ?? 'No description',
                        'status' => $task->status,
                        'priority' => $task->priority ?? 'Normal',
                        'progress' => $task->task_progress ?? 0,
                        'start_date' => $task->start_date,
                        'end_date' => $task->end_date,
                        'project' => [
                            'id' => $task->project ? $task->project->id : null,
                            'title' => $task->project ? $task->project->title : 'No Project'
                        ],
                        'assigned_employees' => $assignedEmployees,
                        'assignment_count' => count($assignedEmployees),
                        'is_overdue' => $task->end_date < now()->format('Y-m-d') && $task->status !== 'completed',
                        'created_at' => $task->created_at->format('Y-m-d H:i:s')
                    ];
                })->toArray();
        } catch (\Exception $e) {
            Log::error('getAllTasks Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get comprehensive system statistics
     */
    public function getSystemStatistics()
    {
        try {
            $today = now()->format('Y-m-d');

            $stats = [
                'system_date' => $today,
                'employees' => [
                    'total' => Employee::count(),
                    'active' => Employee::whereNull('leaving_date')->count(),
                    'inactive' => Employee::whereNotNull('leaving_date')->count(),
                    'recent_joinings' => Employee::where('joining_date', '>=', now()->subDays(30))->count()
                ],
                'attendance' => [
                    'present_today' => Attendance::whereDate('date', $today)->whereNotNull('clock_in')->count(),
                    'late_today' => Attendance::whereDate('date', $today)->where('clock_in', '>', '09:30:00')->count(),
                    'total_records_this_month' => Attendance::whereMonth('date', now()->month)->count(),
                    'attendance_rate_today' => 0
                ],
                'leaves' => [
                    'pending_requests' => Leave::where('status', 'pending')->count(),
                    'approved_requests' => Leave::where('status', 'approved')->count(),
                    'rejected_requests' => Leave::where('status', 'rejected')->count(),
                    'on_leave_today' => Leave::where('status', 'approved')
                        ->where('start_date', '<=', $today)
                        ->where('end_date', '>=', $today)->count()
                ],
                'projects' => [
                    'total' => Project::count(),
                    'active' => Project::where('status', 'in_progress')->count(),
                    'completed' => Project::where('status', 'completed')->count(),
                    'pending' => Project::where('status', 'pending')->count(),
                    'overdue' => Project::where('end_date', '<', $today)->where('status', '!=', 'completed')->count()
                ],
                'tasks' => [
                    'total' => Task::count(),
                    'completed' => Task::where('status', 'completed')->count(),
                    'in_progress' => Task::where('status', 'in_progress')->count(),
                    'pending' => Task::where('status', 'pending')->count(),
                    'overdue' => Task::where('end_date', '<', $today)->where('status', '!=', 'completed')->count()
                ],
                'departments' => [
                    'total' => Department::count(),
                    'with_head' => Department::whereNotNull('department_head')->count(),
                    'without_head' => Department::whereNull('department_head')->count()
                ],
                'users' => [
                    'total' => User::count(),
                    'super_admins' => User::where('role_users_id', 1)->count(),
                    'employees' => User::whereHas('employee')->count()
                ]
            ];

            // Calculate attendance rate
            $totalActiveEmployees = $stats['employees']['active'];
            if ($totalActiveEmployees > 0) {
                $stats['attendance']['attendance_rate_today'] = round(
                    ($stats['attendance']['present_today'] / $totalActiveEmployees) * 100, 2
                );
            }

            Log::info('System Statistics Generated', $stats);
            return $stats;

        } catch (\Exception $e) {
            Log::error('getSystemStatistics Error: ' . $e->getMessage());
            return [
                'error' => 'Failed to retrieve system statistics: ' . $e->getMessage(),
                'fallback_data' => [
                    'employees' => ['total' => Employee::count()],
                    'projects' => ['total' => Project::count()],
                    'tasks' => ['total' => Task::count()],
                    'departments' => ['total' => Department::count()]
                ]
            ];
        }
    }
}
