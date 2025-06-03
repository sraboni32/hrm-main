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
use App\Models\AiDatabaseOperation;
use App\Services\FullDatabaseService;
use App\Services\SqlGeneratorService;
use App\Services\DatabaseValidationAgent;
use App\Services\IntelligentRetryAgent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AiAgentService
{
    private $userId;
    private $userRole;
    private $user;

    public function __construct($userId)
    {
        $this->userId = $userId;
        $this->user = User::with(['employee.department', 'employee.designation'])->find($userId);
        $this->userRole = $this->determineUserRole();
    }

    /**
     * Execute database query based on user's question
     */
    public function executeQuery($question)
    {
        try {
            // For super admin, use enhanced full database access
            if ($this->userRole === 'super_admin') {
                return $this->executeEnhancedQuery($question);
            }

            // For other roles, use existing role-based approach
            $queryType = $this->analyzeQuestion($question);

            Log::info('AI Agent Query', [
                'user_id' => $this->userId,
                'question' => $question,
                'query_type' => $queryType,
                'user_role' => $this->userRole
            ]);

            // Execute appropriate query based on question type and user role
            $result = $this->executeSpecificQuery($queryType, $question);

            return [
                'success' => true,
                'data' => $result,
                'query_type' => $queryType,
                'user_role' => $this->userRole
            ];

        } catch (\Exception $e) {
            Log::error('AI Agent Query Error: ' . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Enhanced query execution for super admin with intelligent agents
     */
    private function executeEnhancedQuery($question)
    {
        try {
            Log::info('Enhanced AI Agent - Using intelligent retry system', [
                'user_id' => $this->userId,
                'question' => $question,
                'user_role' => $this->userRole
            ]);

            // Use intelligent retry agent for enhanced queries
            $retryAgent = new IntelligentRetryAgent($this->userId, $this->userRole);
            $result = $retryAgent->executeWithRetry($question);

            if ($result['success']) {
                return [
                    'success' => true,
                    'data' => $result['data'],
                    'query_type' => 'enhanced_intelligent_agent',
                    'strategy_used' => $result['strategy_used'],
                    'attempts' => $result['attempts'],
                    'user_role' => $this->userRole,
                    'data_source' => 'real_database_verified',
                    'validation_status' => 'passed_all_checks'
                ];
            } else {
                // Enhanced fallback for super admin
                return $this->executeEnhancedFallback($question, $result);
            }

        } catch (\Exception $e) {
            Log::error('Enhanced AI Agent Query Error: ' . $e->getMessage(), [
                'user_id' => $this->userId,
                'question' => $question,
                'user_role' => $this->userRole
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'data' => null,
                'fallback_data' => $this->getFallbackData($question)
            ];
        }
    }

    /**
     * Execute direct database query with full SQL support
     */
    private function executeDirectDatabaseQuery($question)
    {
        try {
            // Get database connection details
            $connection = $this->getDatabaseConnection();

            // Analyze question and generate SQL
            $sqlQuery = $this->generateDirectSQL($question);

            // Validate query for security
            $this->validateDirectSQL($sqlQuery);

            // Execute with full SQL support
            $result = $this->executeRawSQL($sqlQuery, $connection);

            // Log operation for audit
            $this->logDirectDatabaseOperation($question, $sqlQuery, $result);

            return [
                'success' => true,
                'data' => $result,
                'query_type' => $this->detectQueryType($sqlQuery),
                'sql_query' => $sqlQuery,
                'user_role' => $this->userRole,
                'connection_info' => [
                    'database' => $connection['database'],
                    'host' => $connection['host']
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Direct Database Query Error: ' . $e->getMessage(), [
                'user_id' => $this->userId,
                'question' => $question,
                'sql_query' => $sqlQuery ?? 'Not generated'
            ]);

            throw $e;
        }
    }

    /**
     * Get complete database schema information
     */
    private function getDatabaseSchema()
    {
        try {
            $schema = [
                'tables' => [],
                'relationships' => [],
                'key_columns' => []
            ];

            // Get all table names
            $tables = DB::select("SHOW TABLES");
            $databaseName = DB::getDatabaseName();
            $tableKey = "Tables_in_{$databaseName}";

            foreach ($tables as $table) {
                $tableName = $table->$tableKey;

                // Get column information for each table
                $columns = DB::select("DESCRIBE {$tableName}");
                $schema['tables'][$tableName] = [
                    'columns' => $columns,
                    'primary_key' => null,
                    'foreign_keys' => []
                ];

                // Identify primary and foreign keys
                foreach ($columns as $column) {
                    if ($column->Key === 'PRI') {
                        $schema['tables'][$tableName]['primary_key'] = $column->Field;
                    }
                    if (strpos($column->Field, '_id') !== false && $column->Field !== 'id') {
                        $schema['tables'][$tableName]['foreign_keys'][] = $column->Field;
                    }
                }
            }

            return $schema;
        } catch (\Exception $e) {
            Log::error('Error getting database schema: ' . $e->getMessage());
            return $this->getBasicSchema();
        }
    }

    /**
     * Analyze question for SQL generation
     */
    private function analyzeQuestionForSQL($question)
    {
        $question = strtolower($question);

        // Determine query type
        $queryType = 'select'; // default
        if (strpos($question, 'update') !== false || strpos($question, 'modify') !== false || strpos($question, 'change') !== false) {
            $queryType = 'update';
        } elseif (strpos($question, 'insert') !== false || strpos($question, 'add') !== false || strpos($question, 'create') !== false) {
            $queryType = 'insert';
        } elseif (strpos($question, 'delete') !== false || strpos($question, 'remove') !== false) {
            $queryType = 'delete';
        }

        // Identify tables mentioned
        $tables = $this->identifyTablesInQuestion($question);

        // Identify columns/fields mentioned
        $columns = $this->identifyColumnsInQuestion($question);

        // Identify conditions and filters
        $conditions = $this->identifyConditionsInQuestion($question);

        // Identify aggregations
        $aggregations = $this->identifyAggregationsInQuestion($question);

        return [
            'type' => $queryType,
            'tables_involved' => $tables,
            'columns_mentioned' => $columns,
            'conditions' => $conditions,
            'aggregations' => $aggregations,
            'requires_joins' => count($tables) > 1,
            'complexity' => $this->assessQueryComplexity($question, $tables, $conditions, $aggregations)
        ];
    }

    /**
     * Generate SQL query based on analysis
     */
    private function generateSQLQuery($question, $analysis, $schema)
    {
        try {
            $sqlGenerator = new SqlGeneratorService();

            switch ($analysis['type']) {
                case 'select':
                    return $sqlGenerator->generateSelectQuery($question, $analysis, $schema);
                case 'update':
                    return $sqlGenerator->generateUpdateQuery($question, $analysis, $schema);
                case 'insert':
                    return $sqlGenerator->generateInsertQuery($question, $analysis, $schema);
                case 'delete':
                    return $sqlGenerator->generateDeleteQuery($question, $analysis, $schema);
                default:
                    return $sqlGenerator->generateSelectQuery($question, $analysis, $schema);
            }
        } catch (\Exception $e) {
            Log::error('Error generating SQL query: ' . $e->getMessage());
            $sqlGenerator = new SqlGeneratorService();
            return $sqlGenerator->generateFallbackQuery($question, $analysis);
        }
    }

    /**
     * Execute SQL query safely with intelligent fallbacks
     */
    private function executeSQLQuery($sqlQuery, $originalQuestion)
    {
        $attempts = 0;
        $maxAttempts = 3;
        $lastError = null;

        while ($attempts < $maxAttempts) {
            try {
                $attempts++;

                // Validate query before execution
                $this->validateSQLQuery($sqlQuery);

                // Execute query based on type
                if (stripos($sqlQuery, 'SELECT') === 0) {
                    $result = DB::select($sqlQuery);
                    return [
                        'type' => 'select',
                        'data' => $result,
                        'count' => count($result),
                        'query' => $sqlQuery,
                        'attempts' => $attempts,
                        'success' => true
                    ];
                } elseif (stripos($sqlQuery, 'UPDATE') === 0) {
                    $affected = DB::update($sqlQuery);
                    return [
                        'type' => 'update',
                        'affected_rows' => $affected,
                        'message' => "Updated {$affected} record(s)",
                        'query' => $sqlQuery,
                        'attempts' => $attempts,
                        'success' => true
                    ];
                } elseif (stripos($sqlQuery, 'INSERT') === 0) {
                    $result = DB::insert($sqlQuery);
                    return [
                        'type' => 'insert',
                        'success' => $result,
                        'message' => 'Record inserted successfully',
                        'query' => $sqlQuery,
                        'attempts' => $attempts
                    ];
                } elseif (stripos($sqlQuery, 'DELETE') === 0) {
                    $affected = DB::delete($sqlQuery);
                    return [
                        'type' => 'delete',
                        'affected_rows' => $affected,
                        'message' => "Deleted {$affected} record(s)",
                        'query' => $sqlQuery,
                        'attempts' => $attempts,
                        'success' => true
                    ];
                }

            } catch (\Exception $e) {
                $lastError = $e->getMessage();

                Log::warning("SQL Execution Attempt {$attempts} Failed", [
                    'query' => $sqlQuery,
                    'error' => $lastError,
                    'original_question' => $originalQuestion
                ]);

                // Try alternative query approaches
                if ($attempts < $maxAttempts) {
                    $sqlQuery = $this->generateAlternativeQuery($sqlQuery, $originalQuestion, $lastError, $attempts);
                    if (!$sqlQuery) {
                        break; // No more alternatives
                    }
                }
            }
        }

        // All attempts failed, try fallback data approach
        Log::error('All SQL attempts failed, using fallback', [
            'original_question' => $originalQuestion,
            'last_error' => $lastError,
            'attempts' => $attempts
        ]);

        return $this->executeIntelligentFallback($originalQuestion, $lastError);
    }

    /**
     * Analyze question to determine query type
     */
    private function analyzeQuestion($question)
    {
        $question = strtolower($question);

        // Employee count queries
        if (preg_match('/how many.*employees?|total.*employees?|employee.*count/', $question)) {
            return 'employee_count';
        }

        // Attendance queries
        if (preg_match('/attendance|present|absent|checked.*in/', $question)) {
            return 'attendance';
        }

        // Leave queries
        if (preg_match('/leave|vacation|holiday|time.*off/', $question)) {
            return 'leave';
        }

        // Department queries
        if (preg_match('/department|dept/', $question)) {
            return 'department';
        }

        // Project queries
        if (preg_match('/project/', $question)) {
            return 'project';
        }

        // Task queries
        if (preg_match('/task/', $question)) {
            return 'task';
        }

        // Specific name queries
        if (preg_match('/names?|list|show.*all|tell.*me.*about/', $question)) {
            if (preg_match('/project/', $question)) return 'project_names';
            if (preg_match('/employee/', $question)) return 'employee_names';
            if (preg_match('/department/', $question)) return 'department_names';
            if (preg_match('/task/', $question)) return 'task_names';
        }

        // Full database queries (super admin only)
        if (preg_match('/all|complete|full|entire|comprehensive/', $question)) {
            if (preg_match('/employee/', $question)) return 'full_employees';
            if (preg_match('/attendance/', $question)) return 'full_attendance';
            if (preg_match('/leave/', $question)) return 'full_leaves';
            if (preg_match('/project/', $question)) return 'full_projects';
            if (preg_match('/task/', $question)) return 'full_tasks';
            if (preg_match('/department/', $question)) return 'full_departments';
        }

        // System queries
        if (preg_match('/system|overview|summary|dashboard/', $question)) {
            return 'system_overview';
        }

        // Today's data queries
        if (preg_match('/today|current|now/', $question)) {
            if (preg_match('/attendance/', $question)) return 'today_attendance';
        }

        // Detailed queries
        if (preg_match('/details?|information|info/', $question)) {
            if (preg_match('/attendance/', $question)) return 'attendance_details';
            if (preg_match('/leave/', $question)) return 'leave_details';
            if (preg_match('/salary/', $question)) return 'salary_details';
        }

        // Personal queries (for employees)
        if (preg_match('/my|mine|i have|i am/', $question)) {
            return 'personal';
        }

        // Salary queries
        if (preg_match('/salary|pay|compensation/', $question)) {
            return 'salary';
        }

        // General stats
        if (preg_match('/statistics|stats|overview|summary/', $question)) {
            return 'general_stats';
        }

        return 'general';
    }

    /**
     * Execute specific query based on type
     */
    private function executeSpecificQuery($queryType, $question)
    {
        switch ($queryType) {
            case 'employee_count':
                return $this->getEmployeeData();

            case 'attendance':
                return $this->getAttendanceData();

            case 'leave':
                return $this->getLeaveData();

            case 'department':
                return $this->getDepartmentData();

            case 'project':
                return $this->getProjectData();

            case 'task':
                return $this->getTaskData();

            case 'project_names':
                return $this->getProjectNames();

            case 'employee_names':
                return $this->getEmployeeNames();

            case 'department_names':
                return $this->getDepartmentNames();

            case 'task_names':
                return $this->getTaskNames();

            case 'attendance_details':
                return $this->getAttendanceDetails();

            case 'leave_details':
                return $this->getLeaveDetails();

            case 'salary_details':
                return $this->getSalaryDetails();

            case 'full_employees':
                return $this->getFullEmployees();

            case 'full_attendance':
                return $this->getFullAttendance();

            case 'full_leaves':
                return $this->getFullLeaves();

            case 'full_projects':
                return $this->getFullProjects();

            case 'full_tasks':
                return $this->getFullTasks();

            case 'full_departments':
                return $this->getFullDepartments();

            case 'system_overview':
                return $this->getSystemOverview();

            case 'today_attendance':
                return $this->getTodayAttendance();

            case 'personal':
                return $this->getPersonalData();

            case 'salary':
                return $this->getSalaryData();

            case 'general_stats':
                return $this->getGeneralStats();

            default:
                return $this->getGeneralInfo();
        }
    }

    /**
     * Get employee data based on user role
     */
    private function getEmployeeData()
    {
        $data = [];

        if ($this->userRole === 'super_admin') {
            $data['total_employees'] = Employee::count();
            $data['active_employees'] = Employee::whereNull('leaving_date')->count();
            $data['inactive_employees'] = Employee::whereNotNull('leaving_date')->count();
            $data['recent_joinings'] = Employee::where('joining_date', '>=', now()->subDays(30))->count();

            // Get all employee details
            $data['all_employees'] = Employee::with(['department', 'designation', 'company'])
                ->get()
                ->map(function($employee) {
                    return [
                        'id' => $employee->id,
                        'name' => trim($employee->firstname . ' ' . $employee->lastname),
                        'email' => $employee->email,
                        'phone' => $employee->phone,
                        'department' => $employee->department ? $employee->department->department_name : 'No Department',
                        'designation' => $employee->designation ? $employee->designation->designation : 'No Designation',
                        'joining_date' => $employee->joining_date,
                        'leaving_date' => $employee->leaving_date,
                        'status' => $employee->leaving_date ? 'Inactive' : 'Active',
                        'basic_salary' => $employee->basic_salary ?? 0,
                        'remaining_leave' => $employee->remaining_leave ?? 0,
                        'total_leave' => $employee->total_leave ?? 0,
                        'company' => $employee->company ? $employee->company->name : 'No Company'
                    ];
                });

            // Employee names list for easy reference
            $data['employee_names'] = $data['all_employees']->pluck('name')->toArray();

            // By department breakdown with details
            $data['by_department'] = Employee::select('department_id', DB::raw('count(*) as count'))
                ->with('department')
                ->groupBy('department_id')
                ->get()
                ->map(function($item) {
                    return [
                        'department' => $item->department ? $item->department->department_name : 'No Department',
                        'count' => $item->count
                    ];
                });

        } elseif ($this->userRole === 'admin') {
            $companyId = $this->user->employee ? $this->user->employee->company_id : null;
            $departmentId = $this->user->employee ? $this->user->employee->department_id : null;

            if ($departmentId) {
                $data['department_employees'] = Employee::where('department_id', $departmentId)->count();
                $data['department_name'] = $this->user->employee->department->department_name ?? 'Unknown';

                // Get comprehensive department team members details
                $data['team_members'] = Employee::where('department_id', $departmentId)
                    ->with(['designation', 'department', 'company'])
                    ->get()
                    ->map(function($employee) {
                        return [
                            'id' => $employee->id,
                            'name' => trim($employee->firstname . ' ' . $employee->lastname),
                            'email' => $employee->email,
                            'phone' => $employee->phone,
                            'designation' => $employee->designation ? $employee->designation->designation : 'No Designation',
                            'joining_date' => $employee->joining_date,
                            'leaving_date' => $employee->leaving_date,
                            'status' => $employee->leaving_date ? 'Inactive' : 'Active',
                            'basic_salary' => $employee->basic_salary ?? 0,
                            'remaining_leave' => $employee->remaining_leave ?? 0,
                            'total_leave' => $employee->total_leave ?? 0,
                            'employment_type' => $employee->employment_type ?? 'Full-time'
                        ];
                    });

                // Department statistics
                $data['department_stats'] = [
                    'total_members' => $data['team_members']->count(),
                    'active_members' => $data['team_members']->where('status', 'Active')->count(),
                    'inactive_members' => $data['team_members']->where('status', 'Inactive')->count(),
                    'recent_joinings' => $data['team_members']->filter(function($emp) {
                        return $emp['joining_date'] && $emp['joining_date'] >= now()->subDays(30)->format('Y-m-d');
                    })->count(),
                    'average_salary' => $data['team_members']->avg('basic_salary'),
                    'total_leave_balance' => $data['team_members']->sum('remaining_leave')
                ];
            }

            if ($companyId) {
                $data['company_employees'] = Employee::where('company_id', $companyId)->count();

                // Get company-wide employee details for admin
                $data['company_team_members'] = Employee::where('company_id', $companyId)
                    ->with(['designation', 'department'])
                    ->get()
                    ->map(function($employee) {
                        return [
                            'id' => $employee->id,
                            'name' => trim($employee->firstname . ' ' . $employee->lastname),
                            'email' => $employee->email,
                            'department' => $employee->department ? $employee->department->department_name : 'No Department',
                            'designation' => $employee->designation ? $employee->designation->designation : 'No Designation',
                            'joining_date' => $employee->joining_date,
                            'status' => $employee->leaving_date ? 'Inactive' : 'Active',
                            'basic_salary' => $employee->basic_salary ?? 0
                        ];
                    });

                // Company statistics
                $data['company_stats'] = [
                    'total_employees' => $data['company_team_members']->count(),
                    'active_employees' => $data['company_team_members']->where('status', 'Active')->count(),
                    'departments_count' => $data['company_team_members']->pluck('department')->unique()->count(),
                    'average_company_salary' => $data['company_team_members']->avg('basic_salary'),
                    'total_payroll' => $data['company_team_members']->sum('basic_salary')
                ];
            }
        }

        return $data;
    }

    /**
     * Get attendance data
     */
    private function getAttendanceData()
    {
        $today = now()->format('Y-m-d');
        $data = [];

        if ($this->userRole === 'super_admin') {
            // Use enhanced database service for complete attendance data
            $fullDbService = new FullDatabaseService();
            $attendanceSummary = $fullDbService->getTodayAttendanceSummary();

            $data = $attendanceSummary;

            // Add additional summary data
            $data['present_today'] = $attendanceSummary['present_count'];
            $data['total_employees'] = $attendanceSummary['total_employees'];
            $data['absent_today'] = $attendanceSummary['absent_count'];
            $data['late_arrivals'] = $attendanceSummary['late_arrivals'];
            $data['attendance_rate'] = $attendanceSummary['attendance_rate'];

        } elseif ($this->userRole === 'admin') {
            $companyId = $this->user->employee ? $this->user->employee->company_id : null;
            $departmentId = $this->user->employee ? $this->user->employee->department_id : null;

            if ($departmentId) {
                // Department attendance
                $data['department_attendance_today'] = Attendance::whereDate('date', $today)
                    ->whereHas('employee', function($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    })
                    ->with(['employee'])
                    ->get()
                    ->map(function($attendance) {
                        return [
                            'employee_name' => $attendance->employee ?
                                trim($attendance->employee->firstname . ' ' . $attendance->employee->lastname) : 'Unknown',
                            'clock_in' => $attendance->clock_in,
                            'clock_out' => $attendance->clock_out,
                            'total_hours' => $attendance->clock_out ?
                                round((strtotime($attendance->clock_out) - strtotime($attendance->clock_in)) / 3600, 2) : null,
                            'status' => $attendance->clock_out ? 'Complete' : 'In Progress'
                        ];
                    });

                $departmentEmployeeCount = Employee::where('department_id', $departmentId)->whereNull('leaving_date')->count();
                $data['department_attendance_rate'] = $departmentEmployeeCount > 0 ?
                    round(($data['department_attendance_today']->count() / $departmentEmployeeCount) * 100, 2) : 0;
            }

            if ($companyId) {
                // Company attendance
                $data['company_attendance_today'] = Attendance::whereDate('date', $today)
                    ->whereHas('employee', function($q) use ($companyId) {
                        $q->where('company_id', $companyId);
                    })
                    ->count();

                $companyEmployeeCount = Employee::where('company_id', $companyId)->whereNull('leaving_date')->count();
                $data['company_attendance_rate'] = $companyEmployeeCount > 0 ?
                    round(($data['company_attendance_today'] / $companyEmployeeCount) * 100, 2) : 0;
            }

        } elseif ($this->userRole === 'employee') {
            $employeeId = $this->user->employee ? $this->user->employee->id : null;
            if ($employeeId) {
                $data['my_attendance_today'] = Attendance::where('employee_id', $employeeId)
                    ->whereDate('date', $today)->first();
                $data['my_attendance_this_month'] = Attendance::where('employee_id', $employeeId)
                    ->whereMonth('date', now()->month)->count();
            }
        }

        return $data;
    }

    /**
     * Get leave data
     */
    private function getLeaveData()
    {
        $data = [];

        if ($this->userRole === 'super_admin') {
            $data['pending_requests'] = Leave::where('status', 'pending')->count();
            $data['approved_requests'] = Leave::where('status', 'approved')->count();
            $data['rejected_requests'] = Leave::where('status', 'rejected')->count();
            $data['on_leave_today'] = Leave::where('status', 'approved')
                ->where('start_date', '<=', now()->format('Y-m-d'))
                ->where('end_date', '>=', now()->format('Y-m-d'))
                ->count();

        } elseif ($this->userRole === 'admin') {
            $companyId = $this->user->employee ? $this->user->employee->company_id : null;
            $departmentId = $this->user->employee ? $this->user->employee->department_id : null;

            if ($departmentId) {
                // Department leave data
                $data['department_pending_requests'] = Leave::whereHas('employee', function($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                })->where('status', 'pending')->count();

                $data['department_approved_requests'] = Leave::whereHas('employee', function($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                })->where('status', 'approved')->count();

                $data['department_on_leave_today'] = Leave::whereHas('employee', function($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                })
                ->where('status', 'approved')
                ->where('start_date', '<=', now()->format('Y-m-d'))
                ->where('end_date', '>=', now()->format('Y-m-d'))
                ->count();

                // Department leave requests details
                $data['department_leave_requests'] = Leave::whereHas('employee', function($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                })
                ->with(['employee'])
                ->orderBy('start_date', 'desc')
                ->take(20)
                ->get()
                ->map(function($leave) {
                    return [
                        'employee_name' => $leave->employee ?
                            trim($leave->employee->firstname . ' ' . $leave->employee->lastname) : 'Unknown',
                        'start_date' => $leave->start_date,
                        'end_date' => $leave->end_date,
                        'days' => $leave->days ?? 1,
                        'status' => $leave->status,
                        'reason' => $leave->reason ?? 'Not specified',
                        'leave_type' => $leave->leave_type ?? 'General'
                    ];
                });
            }

            if ($companyId) {
                // Company leave data
                $data['company_pending_requests'] = Leave::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->where('status', 'pending')->count();

                $data['company_approved_requests'] = Leave::whereHas('employee', function($q) use ($companyId) {
                    $q->where('company_id', $companyId);
                })->where('status', 'approved')->count();
            }

        } elseif ($this->userRole === 'employee') {
            $employeeId = $this->user->employee ? $this->user->employee->id : null;
            if ($employeeId) {
                $employee = $this->user->employee;
                $data['remaining_leave'] = $employee->remaining_leave ?? 0;
                $data['total_leave'] = $employee->total_leave ?? 0;
                $data['used_leave'] = $data['total_leave'] - $data['remaining_leave'];
                $data['my_pending_requests'] = Leave::where('employee_id', $employeeId)
                    ->where('status', 'pending')->count();
                $data['my_approved_requests'] = Leave::where('employee_id', $employeeId)
                    ->where('status', 'approved')->count();
            }
        }

        return $data;
    }

    /**
     * Get department data
     */
    private function getDepartmentData()
    {
        $data = [];

        if ($this->userRole === 'super_admin') {
            // Use enhanced database service for complete department data
            $fullDbService = new FullDatabaseService();
            $departments = $fullDbService->getAllDepartments();

            $data['total_departments'] = count($departments);
            $data['departments'] = $departments;

            // Add summary for AI context
            $data['department_summary'] = "We have " . count($departments) . " departments: " .
                implode(', ', array_slice(array_column($departments, 'name'), 0, 5)) .
                (count($departments) > 5 ? ' and others' : '') . ".";

        } elseif ($this->userRole === 'employee') {
            $employee = $this->user->employee;
            if ($employee && $employee->department) {
                $data['my_department'] = [
                    'name' => $employee->department->department_name,
                    'head' => $employee->department->employee ?
                        $employee->department->employee->firstname . ' ' . $employee->department->employee->lastname : 'No Head',
                    'colleagues_count' => Employee::where('department_id', $employee->department_id)
                        ->where('id', '!=', $employee->id)->count()
                ];
            }
        }

        return $data;
    }

    /**
     * Get project data
     */
    private function getProjectData()
    {
        $data = [];

        if ($this->userRole === 'super_admin') {
            // Get comprehensive project statistics
            $data['total_projects'] = Project::count();
            $data['active_projects'] = Project::where('status', 'in_progress')->count();
            $data['completed_projects'] = Project::where('status', 'completed')->count();
            $data['pending_projects'] = Project::where('status', 'pending')->count();
            $data['overdue_projects'] = Project::where('end_date', '<', now())
                ->where('status', '!=', 'completed')->count();

            // Get all project details with names
            $data['all_projects'] = Project::with(['client', 'company'])
                ->get()
                ->map(function($project) {
                    return [
                        'id' => $project->id,
                        'title' => $project->title,
                        'status' => $project->status,
                        'progress' => $project->project_progress ?? 0,
                        'start_date' => $project->start_date,
                        'end_date' => $project->end_date,
                        'client' => $project->client ?
                            ($project->client->firstname . ' ' . $project->client->lastname) : 'No Client',
                        'company' => $project->company ? $project->company->name : 'No Company',
                        'description' => $project->description ?? 'No description',
                        'priority' => $project->priority ?? 'Normal',
                        'budget' => $project->budget ?? 0,
                        'created_at' => $project->created_at->format('Y-m-d')
                    ];
                });

            // Group projects by status for better organization
            $data['projects_by_status'] = [
                'in_progress' => $data['all_projects']->where('status', 'in_progress')->values(),
                'completed' => $data['all_projects']->where('status', 'completed')->values(),
                'pending' => $data['all_projects']->where('status', 'pending')->values(),
                'on_hold' => $data['all_projects']->where('status', 'on_hold')->values(),
                'cancelled' => $data['all_projects']->where('status', 'cancelled')->values()
            ];

            // Get project names list for easy reference
            $data['project_names'] = $data['all_projects']->pluck('title')->toArray();

        } elseif ($this->userRole === 'admin') {
            $companyId = $this->user->employee ? $this->user->employee->company_id : null;
            $departmentId = $this->user->employee ? $this->user->employee->department_id : null;

            if ($departmentId) {
                // Department projects (projects with tasks assigned to department employees)
                $data['department_projects'] = Project::whereHas('tasks', function($q) use ($departmentId) {
                    $q->whereHas('assignedEmployees', function($subQ) use ($departmentId) {
                        $subQ->where('department_id', $departmentId);
                    });
                })
                ->with(['client', 'company'])
                ->get()
                ->map(function($project) {
                    return [
                        'id' => $project->id,
                        'title' => $project->title,
                        'status' => $project->status,
                        'progress' => $project->project_progress ?? 0,
                        'start_date' => $project->start_date,
                        'end_date' => $project->end_date,
                        'client' => $project->client ?
                            ($project->client->firstname . ' ' . $project->client->lastname) : 'No Client'
                    ];
                });

                // Department tasks
                $data['department_tasks'] = Task::whereHas('assignedEmployees', function($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                })
                ->with(['project', 'assignedEmployees'])
                ->get()
                ->map(function($task) {
                    $assignedEmployees = $task->assignedEmployees
                        ->where('department_id', $departmentId)
                        ->map(function($emp) {
                            return trim($emp->firstname . ' ' . $emp->lastname);
                        })->toArray();

                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'status' => $task->status,
                        'priority' => $task->priority ?? 'Normal',
                        'progress' => $task->task_progress ?? 0,
                        'end_date' => $task->end_date,
                        'project' => $task->project ? $task->project->title : 'No Project',
                        'assigned_to' => $assignedEmployees
                    ];
                });

                $data['department_project_stats'] = [
                    'total_projects' => $data['department_projects']->count(),
                    'active_projects' => $data['department_projects']->where('status', 'in_progress')->count(),
                    'completed_projects' => $data['department_projects']->where('status', 'completed')->count(),
                    'total_tasks' => $data['department_tasks']->count(),
                    'completed_tasks' => $data['department_tasks']->where('status', 'completed')->count(),
                    'pending_tasks' => $data['department_tasks']->where('status', 'pending')->count()
                ];
            }

            if ($companyId) {
                // Company projects
                $data['company_projects'] = Project::where('company_id', $companyId)
                    ->with(['client'])
                    ->get()
                    ->map(function($project) {
                        return [
                            'title' => $project->title,
                            'status' => $project->status,
                            'progress' => $project->project_progress ?? 0,
                            'client' => $project->client ?
                                ($project->client->firstname . ' ' . $project->client->lastname) : 'No Client'
                        ];
                    });

                $data['company_project_stats'] = [
                    'total_projects' => $data['company_projects']->count(),
                    'active_projects' => $data['company_projects']->where('status', 'in_progress')->count(),
                    'completed_projects' => $data['company_projects']->where('status', 'completed')->count()
                ];
            }

        } elseif ($this->userRole === 'employee') {
            $employeeId = $this->user->employee ? $this->user->employee->id : null;
            if ($employeeId) {
                // Get projects assigned to this employee through tasks
                $data['my_projects'] = Task::where('assigned_to', $employeeId)
                    ->with('project')
                    ->get()
                    ->pluck('project')
                    ->unique('id')
                    ->values()
                    ->map(function($project) {
                        return [
                            'title' => $project->title,
                            'status' => $project->status,
                            'progress' => $project->project_progress ?? 0,
                            'start_date' => $project->start_date,
                            'end_date' => $project->end_date
                        ];
                    });

                $data['my_tasks'] = Task::where('assigned_to', $employeeId)->count();
                $data['my_completed_tasks'] = Task::where('assigned_to', $employeeId)
                    ->where('status', 'completed')->count();
            }
        }

        return $data;
    }

    /**
     * Get personal data (employee only)
     */
    private function getPersonalData()
    {
        if ($this->userRole !== 'employee') {
            return ['error' => 'Personal data only available for employees'];
        }

        $employee = $this->user->employee;
        if (!$employee) {
            return ['error' => 'Employee record not found'];
        }

        return [
            'name' => trim($employee->firstname . ' ' . $employee->lastname),
            'employee_id' => $employee->id,
            'department' => $employee->department ? $employee->department->department_name : 'No Department',
            'designation' => $employee->designation ? $employee->designation->designation : 'No Designation',
            'joining_date' => $employee->joining_date,
            'remaining_leave' => $employee->remaining_leave ?? 0,
            'total_leave' => $employee->total_leave ?? 0,
            'basic_salary' => $employee->basic_salary ?? 0,
            'my_projects_count' => Task::where('assigned_to', $employee->id)
                ->distinct('project_id')->count('project_id'),
            'my_tasks_count' => Task::where('assigned_to', $employee->id)->count(),
            'attendance_this_month' => Attendance::where('employee_id', $employee->id)
                ->whereMonth('date', now()->month)->count()
        ];
    }

    /**
     * Get salary data
     */
    private function getSalaryData()
    {
        $data = [];

        if ($this->userRole === 'super_admin') {
            $data['total_disbursed_this_month'] = SalaryDisbursement::whereMonth('disbursement_date', now()->month)
                ->sum('amount');
            $data['pending_disbursements'] = SalaryDisbursement::where('status', 'pending')->count();
            $data['average_salary'] = SalaryDisbursement::whereMonth('disbursement_date', now()->month)
                ->avg('amount');

        } elseif ($this->userRole === 'employee') {
            $employeeId = $this->user->employee ? $this->user->employee->id : null;
            if ($employeeId) {
                $latestSalary = SalaryDisbursement::where('employee_id', $employeeId)
                    ->orderBy('disbursement_date', 'desc')->first();
                $data['latest_salary'] = $latestSalary ? $latestSalary->amount : 0;
                $data['basic_salary'] = $this->user->employee->basic_salary ?? 0;
            }
        }

        return $data;
    }

    /**
     * Get general statistics
     */
    private function getGeneralStats()
    {
        if ($this->userRole !== 'super_admin') {
            return ['error' => 'General statistics only available for super admin'];
        }

        return [
            'total_employees' => Employee::count(),
            'total_departments' => Department::count(),
            'total_projects' => Project::count(),
            'total_users' => User::count(),
            'present_today' => Attendance::whereDate('date', now()->format('Y-m-d'))
                ->whereNotNull('clock_in')->count(),
            'pending_leaves' => Leave::where('status', 'pending')->count(),
            'active_projects' => Project::where('status', 'in_progress')->count()
        ];
    }

    /**
     * Get general info
     */
    private function getGeneralInfo()
    {
        return [
            'user_role' => $this->userRole,
            'user_name' => $this->user->username,
            'message' => 'I can help you with employee data, attendance, leaves, departments, projects, and more. Please ask a specific question.'
        ];
    }

    /**
     * Get task data
     */
    private function getTaskData()
    {
        $data = [];

        if ($this->userRole === 'super_admin') {
            $data['total_tasks'] = Task::count();
            $data['completed_tasks'] = Task::where('status', 'completed')->count();
            $data['pending_tasks'] = Task::where('status', 'pending')->count();
            $data['in_progress_tasks'] = Task::where('status', 'in_progress')->count();
            $data['overdue_tasks'] = Task::where('end_date', '<', now())
                ->where('status', '!=', 'completed')->count();

            // Get all task details
            $data['all_tasks'] = Task::with(['project', 'assignedEmployees'])
                ->get()
                ->map(function($task) {
                    $assignedEmployees = $task->assignedEmployees->map(function($emp) {
                        return trim($emp->firstname . ' ' . $emp->lastname);
                    })->toArray();

                    return [
                        'id' => $task->id,
                        'title' => $task->title,
                        'description' => $task->description ?? 'No description',
                        'status' => $task->status,
                        'priority' => $task->priority ?? 'Normal',
                        'progress' => $task->task_progress ?? 0,
                        'start_date' => $task->start_date,
                        'end_date' => $task->end_date,
                        'project' => $task->project ? $task->project->title : 'No Project',
                        'assigned_to' => $assignedEmployees,
                        'created_at' => $task->created_at->format('Y-m-d')
                    ];
                });

            // Task names for easy reference
            $data['task_names'] = $data['all_tasks']->pluck('title')->toArray();

            // Tasks by status
            $data['tasks_by_status'] = [
                'completed' => $data['all_tasks']->where('status', 'completed')->values(),
                'pending' => $data['all_tasks']->where('status', 'pending')->values(),
                'in_progress' => $data['all_tasks']->where('status', 'in_progress')->values(),
                'overdue' => $data['all_tasks']->filter(function($task) {
                    return $task['end_date'] < now()->format('Y-m-d') && $task['status'] != 'completed';
                })->values()
            ];

        } elseif ($this->userRole === 'employee') {
            $employeeId = $this->user->employee ? $this->user->employee->id : null;
            if ($employeeId) {
                $data['my_tasks'] = Task::where('assigned_to', $employeeId)
                    ->with('project')
                    ->get()
                    ->map(function($task) {
                        return [
                            'title' => $task->title,
                            'status' => $task->status,
                            'priority' => $task->priority ?? 'Normal',
                            'progress' => $task->task_progress ?? 0,
                            'end_date' => $task->end_date,
                            'project' => $task->project ? $task->project->title : 'No Project'
                        ];
                    });

                $data['my_task_count'] = $data['my_tasks']->count();
                $data['my_completed_tasks'] = $data['my_tasks']->where('status', 'completed')->count();
                $data['my_pending_tasks'] = $data['my_tasks']->where('status', 'pending')->count();
            }
        }

        return $data;
    }

    /**
     * Get task names specifically
     */
    private function getTaskNames()
    {
        if ($this->userRole !== 'super_admin') {
            return ['error' => 'Task names only available for super admin'];
        }

        $tasks = Task::select('id', 'title', 'status', 'priority', 'start_date', 'end_date', 'assigned_to')
            ->with(['project', 'assignedEmployees'])
            ->get()
            ->map(function($task) {
                $assignedEmployees = $task->assignedEmployees->map(function($emp) {
                    return trim($emp->firstname . ' ' . $emp->lastname);
                })->toArray();

                return [
                    'id' => $task->id,
                    'name' => $task->title,
                    'status' => $task->status,
                    'priority' => $task->priority ?? 'Normal',
                    'project' => $task->project ? $task->project->title : 'No Project',
                    'assigned_to' => $assignedEmployees,
                    'end_date' => $task->end_date
                ];
            });

        return [
            'total_tasks' => $tasks->count(),
            'task_list' => $tasks->toArray(),
            'task_names_only' => $tasks->pluck('name')->toArray()
        ];
    }

    /**
     * Get attendance details
     */
    private function getAttendanceDetails()
    {
        if ($this->userRole !== 'super_admin') {
            return ['error' => 'Attendance details only available for super admin'];
        }

        $today = now()->format('Y-m-d');
        $thisMonth = now()->format('Y-m');

        // Today's attendance
        $todayAttendance = Attendance::whereDate('date', $today)
            ->with(['employee'])
            ->get()
            ->map(function($attendance) {
                return [
                    'employee_name' => $attendance->employee ?
                        trim($attendance->employee->firstname . ' ' . $attendance->employee->lastname) : 'Unknown',
                    'clock_in' => $attendance->clock_in,
                    'clock_out' => $attendance->clock_out,
                    'total_hours' => $attendance->clock_out ?
                        round((strtotime($attendance->clock_out) - strtotime($attendance->clock_in)) / 3600, 2) : null,
                    'status' => $attendance->clock_out ? 'Complete' : 'In Progress'
                ];
            });

        // This month's summary
        $monthlyStats = [
            'total_attendance_records' => Attendance::whereMonth('date', now()->month)->count(),
            'average_daily_attendance' => Attendance::whereMonth('date', now()->month)
                ->whereNotNull('clock_in')
                ->groupBy('date')
                ->selectRaw('date, count(*) as daily_count')
                ->get()
                ->avg('daily_count'),
            'late_arrivals_this_month' => Attendance::whereMonth('date', now()->month)
                ->where('clock_in', '>', '09:30:00')->count()
        ];

        return [
            'today_attendance' => $todayAttendance->toArray(),
            'monthly_stats' => $monthlyStats,
            'present_today_count' => $todayAttendance->count(),
            'total_employees' => Employee::whereNull('leaving_date')->count()
        ];
    }

    /**
     * Get leave details
     */
    private function getLeaveDetails()
    {
        if ($this->userRole !== 'super_admin') {
            return ['error' => 'Leave details only available for super admin'];
        }

        // All leave requests
        $allLeaves = Leave::with(['employee'])
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function($leave) {
                return [
                    'employee_name' => $leave->employee ?
                        trim($leave->employee->firstname . ' ' . $leave->employee->lastname) : 'Unknown',
                    'start_date' => $leave->start_date,
                    'end_date' => $leave->end_date,
                    'days' => $leave->days ?? 1,
                    'status' => $leave->status,
                    'reason' => $leave->reason ?? 'Not specified',
                    'leave_type' => $leave->leave_type ?? 'General',
                    'applied_date' => $leave->created_at->format('Y-m-d')
                ];
            });

        // Leave statistics
        $leaveStats = [
            'total_requests' => $allLeaves->count(),
            'pending_requests' => $allLeaves->where('status', 'pending')->count(),
            'approved_requests' => $allLeaves->where('status', 'approved')->count(),
            'rejected_requests' => $allLeaves->where('status', 'rejected')->count(),
            'total_days_requested' => $allLeaves->sum('days'),
            'on_leave_today' => $allLeaves->where('status', 'approved')
                ->filter(function($leave) {
                    $today = now()->format('Y-m-d');
                    return $leave['start_date'] <= $today && $leave['end_date'] >= $today;
                })->count()
        ];

        return [
            'all_leave_requests' => $allLeaves->toArray(),
            'leave_statistics' => $leaveStats,
            'pending_requests_details' => $allLeaves->where('status', 'pending')->values()->toArray()
        ];
    }

    /**
     * Get salary details
     */
    private function getSalaryDetails()
    {
        if ($this->userRole !== 'super_admin') {
            return ['error' => 'Salary details only available for super admin'];
        }

        try {
            // All employee salaries
            $employeeSalaries = Employee::with(['designation', 'department'])
                ->get()
                ->map(function($employee) {
                    return [
                        'employee_name' => trim($employee->firstname . ' ' . $employee->lastname),
                        'basic_salary' => $employee->basic_salary ?? 0,
                        'department' => $employee->department ? $employee->department->department_name : 'No Department',
                        'designation' => $employee->designation ? $employee->designation->designation : 'No Designation',
                        'joining_date' => $employee->joining_date
                    ];
                });

            // Salary statistics
            $salaryStats = [
                'total_employees' => $employeeSalaries->count(),
                'average_salary' => $employeeSalaries->avg('basic_salary'),
                'highest_salary' => $employeeSalaries->max('basic_salary'),
                'lowest_salary' => $employeeSalaries->min('basic_salary'),
                'total_payroll' => $employeeSalaries->sum('basic_salary')
            ];

            return [
                'employee_salaries' => $employeeSalaries->toArray(),
                'salary_statistics' => $salaryStats
            ];

        } catch (\Exception $e) {
            return [
                'error' => 'Salary data unavailable: ' . $e->getMessage(),
                'basic_info' => 'Basic salary information from employee records only'
            ];
        }
    }

    /**
     * Get project names specifically
     */
    private function getProjectNames()
    {
        if ($this->userRole !== 'super_admin') {
            return ['error' => 'Project names only available for super admin'];
        }

        $projects = Project::select('id', 'title', 'status', 'start_date', 'end_date', 'project_progress')
            ->with(['client', 'company'])
            ->get()
            ->map(function($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->title,
                    'status' => $project->status,
                    'progress' => $project->project_progress ?? 0,
                    'start_date' => $project->start_date,
                    'end_date' => $project->end_date,
                    'client' => $project->client ?
                        ($project->client->firstname . ' ' . $project->client->lastname) : 'No Client'
                ];
            });

        return [
            'total_projects' => $projects->count(),
            'project_list' => $projects->toArray(),
            'project_names_only' => $projects->pluck('name')->toArray()
        ];
    }

    /**
     * Get employee names specifically
     */
    private function getEmployeeNames()
    {
        if ($this->userRole !== 'super_admin') {
            return ['error' => 'Employee names only available for super admin'];
        }

        $employees = Employee::select('id', 'firstname', 'lastname', 'email', 'department_id', 'designation_id')
            ->with(['department', 'designation'])
            ->get()
            ->map(function($employee) {
                return [
                    'id' => $employee->id,
                    'name' => trim($employee->firstname . ' ' . $employee->lastname),
                    'email' => $employee->email,
                    'department' => $employee->department ? $employee->department->department_name : 'No Department',
                    'designation' => $employee->designation ? $employee->designation->designation : 'No Designation'
                ];
            });

        return [
            'total_employees' => $employees->count(),
            'employee_list' => $employees->toArray(),
            'employee_names_only' => $employees->pluck('name')->toArray()
        ];
    }

    /**
     * Get department names specifically
     */
    private function getDepartmentNames()
    {
        if ($this->userRole !== 'super_admin') {
            return ['error' => 'Department names only available for super admin'];
        }

        $departments = Department::with(['employee'])
            ->get()
            ->map(function($dept) {
                return [
                    'id' => $dept->id,
                    'name' => $dept->department_name ?? $dept->department,
                    'head' => $dept->employee ?
                        ($dept->employee->firstname . ' ' . $dept->employee->lastname) : 'No Head',
                    'employee_count' => Employee::where('department_id', $dept->id)->count()
                ];
            });

        return [
            'total_departments' => $departments->count(),
            'department_list' => $departments->toArray(),
            'department_names_only' => $departments->pluck('name')->toArray()
        ];
    }

    /**
     * Get full employee data (super admin only)
     */
    private function getFullEmployees()
    {
        if ($this->userRole !== 'super_admin') {
            return ['error' => 'Full employee data only available for super admin'];
        }

        $fullDbService = new FullDatabaseService();
        $employees = $fullDbService->getAllEmployees();

        return [
            'total_employees' => count($employees),
            'all_employees' => $employees,
            'summary' => [
                'active_count' => collect($employees)->where('employment_status', 'Active')->count(),
                'inactive_count' => collect($employees)->where('employment_status', 'Inactive')->count(),
                'departments' => collect($employees)->pluck('department.name')->unique()->values()->toArray(),
                'designations' => collect($employees)->pluck('designation.title')->unique()->values()->toArray()
            ]
        ];
    }

    /**
     * Get full attendance data (super admin only)
     */
    private function getFullAttendance()
    {
        if ($this->userRole !== 'super_admin') {
            return ['error' => 'Full attendance data only available for super admin'];
        }

        $fullDbService = new FullDatabaseService();
        $attendance = $fullDbService->getAllAttendance(200); // Get last 200 records

        return [
            'total_records' => count($attendance),
            'all_attendance' => $attendance,
            'summary' => [
                'complete_shifts' => collect($attendance)->where('status', 'Complete')->count(),
                'in_progress_shifts' => collect($attendance)->where('status', 'In Progress')->count(),
                'late_arrivals' => collect($attendance)->where('is_late', true)->count(),
                'departments_represented' => collect($attendance)->pluck('employee.department')->unique()->values()->toArray()
            ]
        ];
    }

    /**
     * Get today's attendance summary (super admin only)
     */
    private function getTodayAttendance()
    {
        if ($this->userRole !== 'super_admin') {
            return ['error' => 'Today\'s attendance data only available for super admin'];
        }

        $fullDbService = new FullDatabaseService();
        return $fullDbService->getTodayAttendanceSummary();
    }

    /**
     * Get full leave data (super admin only)
     */
    private function getFullLeaves()
    {
        if ($this->userRole !== 'super_admin') {
            return ['error' => 'Full leave data only available for super admin'];
        }

        $fullDbService = new FullDatabaseService();
        $leaves = $fullDbService->getAllLeaveRequests(200); // Get last 200 records

        return [
            'total_requests' => count($leaves),
            'all_leave_requests' => $leaves,
            'summary' => [
                'pending_count' => collect($leaves)->where('status', 'pending')->count(),
                'approved_count' => collect($leaves)->where('status', 'approved')->count(),
                'rejected_count' => collect($leaves)->where('status', 'rejected')->count(),
                'currently_on_leave' => collect($leaves)->where('is_current', true)->count(),
                'total_days_requested' => collect($leaves)->sum('days')
            ]
        ];
    }

    /**
     * Get full project data (super admin only)
     */
    private function getFullProjects()
    {
        if ($this->userRole !== 'super_admin') {
            return ['error' => 'Full project data only available for super admin'];
        }

        $fullDbService = new FullDatabaseService();
        $projects = $fullDbService->getAllProjects();

        return [
            'total_projects' => count($projects),
            'all_projects' => $projects,
            'summary' => [
                'active_count' => collect($projects)->where('status', 'in_progress')->count(),
                'completed_count' => collect($projects)->where('status', 'completed')->count(),
                'pending_count' => collect($projects)->where('status', 'pending')->count(),
                'overdue_count' => collect($projects)->where('is_overdue', true)->count(),
                'total_budget' => collect($projects)->sum('budget'),
                'clients' => collect($projects)->pluck('client.name')->unique()->values()->toArray()
            ]
        ];
    }

    /**
     * Get full task data (super admin only)
     */
    private function getFullTasks()
    {
        if ($this->userRole !== 'super_admin') {
            return ['error' => 'Full task data only available for super admin'];
        }

        $fullDbService = new FullDatabaseService();
        $tasks = $fullDbService->getAllTasks(200); // Get last 200 records

        return [
            'total_tasks' => count($tasks),
            'all_tasks' => $tasks,
            'summary' => [
                'completed_count' => collect($tasks)->where('status', 'completed')->count(),
                'in_progress_count' => collect($tasks)->where('status', 'in_progress')->count(),
                'pending_count' => collect($tasks)->where('status', 'pending')->count(),
                'overdue_count' => collect($tasks)->where('is_overdue', true)->count(),
                'total_assignments' => collect($tasks)->sum('assignment_count'),
                'projects_involved' => collect($tasks)->pluck('project.title')->unique()->values()->toArray()
            ]
        ];
    }

    /**
     * Get full department data (super admin only)
     */
    private function getFullDepartments()
    {
        if ($this->userRole !== 'super_admin') {
            return ['error' => 'Full department data only available for super admin'];
        }

        $fullDbService = new FullDatabaseService();
        $departments = $fullDbService->getAllDepartments();

        return [
            'total_departments' => count($departments),
            'all_departments' => $departments,
            'summary' => [
                'with_head' => collect($departments)->where('head.name', '!=', 'No Head Assigned')->count(),
                'without_head' => collect($departments)->where('head.name', 'No Head Assigned')->count(),
                'total_employees' => collect($departments)->sum('employee_count'),
                'largest_department' => collect($departments)->sortByDesc('employee_count')->first(),
                'smallest_department' => collect($departments)->sortBy('employee_count')->first()
            ]
        ];
    }

    /**
     * Get system overview (super admin only)
     */
    private function getSystemOverview()
    {
        if ($this->userRole !== 'super_admin') {
            return ['error' => 'System overview only available for super admin'];
        }

        $fullDbService = new FullDatabaseService();
        $stats = $fullDbService->getSystemStatistics();

        // Add additional context for better AI responses
        if (!isset($stats['error'])) {
            $stats['summary_text'] = "System is operational with {$stats['employees']['total']} employees across {$stats['departments']['total']} departments, managing {$stats['projects']['total']} projects and {$stats['tasks']['total']} tasks.";
            $stats['attendance_summary'] = "Today's attendance: {$stats['attendance']['present_today']} present out of {$stats['employees']['active']} active employees ({$stats['attendance']['attendance_rate_today']}% rate).";
        }

        return $stats;
    }

    /**
     * Determine user role
     */
    private function determineUserRole()
    {
        if (!$this->user) return 'unknown';

        if ($this->user->role_users_id == 1) {
            return 'super_admin';
        }

        if ($this->user->employee) {
            return 'employee';
        }

        return 'user';
    }

    /**
     * Get complete database schema with all tables and fields
     */
    private function getCompleteTableSchema()
    {
        return [
            // Core User & Employee Tables
            'users' => [
                'fields' => ['id', 'username', 'email', 'email_verified_at', 'avatar', 'status', 'role_users_id', 'password', 'remember_token', 'created_at', 'updated_at', 'deleted_at'],
                'description' => 'System users and authentication',
                'relationships' => ['employees', 'roles']
            ],
            'employees' => [
                'fields' => ['id', 'firstname', 'lastname', 'username', 'email', 'gender', 'phone', 'role_users_id', 'remaining_leave', 'total_leave', 'sick_leave', 'casual_leave', 'birth_date', 'department_id', 'designation_id', 'office_shift_id', 'joining_date', 'leaving_date', 'marital_status', 'employment_type', 'city', 'province', 'zipcode', 'address', 'resume', 'avatar', 'document', 'country', 'company_id', 'facebook', 'skype', 'whatsapp', 'twitter', 'linkedin', 'hourly_rate', 'basic_salary', 'mode', 'expected_hours', 'created_at', 'updated_at', 'deleted_at'],
                'description' => 'Employee personal and professional information',
                'relationships' => ['departments', 'designations', 'companies', 'office_shifts', 'users']
            ],

            // Organizational Structure
            'companies' => [
                'fields' => ['id', 'company_name', 'company_email', 'company_phone', 'company_address', 'company_country', 'company_city', 'company_zipcode', 'company_logo', 'created_at', 'updated_at', 'deleted_at'],
                'description' => 'Company information and details',
                'relationships' => ['employees', 'departments', 'projects']
            ],
            'departments' => [
                'fields' => ['id', 'department_name', 'department', 'department_head', 'company_id', 'created_at', 'updated_at', 'deleted_at'],
                'description' => 'Department structure and hierarchy',
                'relationships' => ['employees', 'companies', 'designations']
            ],
            'designations' => [
                'fields' => ['id', 'designation', 'department_id', 'company_id', 'created_at', 'updated_at', 'deleted_at'],
                'description' => 'Job titles and positions',
                'relationships' => ['employees', 'departments', 'companies']
            ],

            // Project Management
            'projects' => [
                'fields' => ['id', 'title', 'project_name', 'description', 'start_date', 'end_date', 'budget', 'project_progress', 'status', 'client_id', 'company_id', 'created_at', 'updated_at', 'deleted_at'],
                'description' => 'Project information and tracking',
                'relationships' => ['clients', 'companies', 'tasks', 'employees']
            ],
            'tasks' => [
                'fields' => ['id', 'title', 'task_name', 'description', 'start_date', 'end_date', 'priority', 'status', 'project_id', 'assigned_to', 'created_at', 'updated_at', 'deleted_at'],
                'description' => 'Task management and assignment',
                'relationships' => ['projects', 'employees']
            ],
            'clients' => [
                'fields' => ['id', 'firstname', 'lastname', 'username', 'email', 'phone', 'address', 'city', 'province', 'zipcode', 'country', 'avatar', 'company_id', 'created_at', 'updated_at', 'deleted_at'],
                'description' => 'Client information and contacts',
                'relationships' => ['projects', 'companies']
            ],

            // Time & Attendance
            'attendance' => [
                'fields' => ['id', 'employee_id', 'date', 'clock_in', 'clock_out', 'total_hours', 'break_time', 'overtime', 'status', 'mode', 'created_at', 'updated_at'],
                'description' => 'Employee attendance tracking',
                'relationships' => ['employees']
            ],
            'office_shifts' => [
                'fields' => ['id', 'shift_name', 'start_time', 'end_time', 'break_duration', 'is_flexible', 'expected_hours', 'weekend_days', 'half_day', 'company_id', 'created_at', 'updated_at', 'deleted_at'],
                'description' => 'Work shift schedules and timing',
                'relationships' => ['employees', 'companies']
            ],

            // Leave Management
            'leaves' => [
                'fields' => ['id', 'employee_id', 'leave_type_id', 'start_date', 'end_date', 'days', 'reason', 'status', 'approved_by', 'leave_type', 'created_at', 'updated_at', 'deleted_at'],
                'description' => 'Employee leave requests and approvals',
                'relationships' => ['employees', 'leave_types']
            ],
            'leave_types' => [
                'fields' => ['id', 'leave_type', 'days_per_year', 'company_id', 'created_at', 'updated_at', 'deleted_at'],
                'description' => 'Types of leave available',
                'relationships' => ['leaves', 'companies']
            ],

            // Financial Management
            'salary_disbursements' => [
                'fields' => ['id', 'employee_id', 'month', 'year', 'basic_salary', 'allowances', 'deductions', 'net_salary', 'status', 'disbursed_at', 'approved_by', 'admin_response', 'updated_status', 'created_at', 'updated_at'],
                'description' => 'Salary payments and disbursements',
                'relationships' => ['employees']
            ],
            'bonus_allowances' => [
                'fields' => ['id', 'employee_id', 'bonus_type', 'amount', 'month', 'year', 'description', 'status', 'created_at', 'updated_at'],
                'description' => 'Employee bonuses and allowances',
                'relationships' => ['employees']
            ],

            // HR Management
            'awards' => [
                'fields' => ['id', 'employee_id', 'award_type_id', 'gift', 'cash_price', 'month', 'year', 'description', 'company_id', 'created_at', 'updated_at', 'deleted_at'],
                'description' => 'Employee awards and recognition',
                'relationships' => ['employees', 'award_types', 'companies']
            ],
            'complaints' => [
                'fields' => ['id', 'title', 'time', 'date', 'description', 'company_id', 'employee_from', 'employee_against', 'reason', 'created_at', 'updated_at', 'deleted_at'],
                'description' => 'Employee complaints and grievances',
                'relationships' => ['employees', 'companies']
            ],
            'trainings' => [
                'fields' => ['id', 'trainer_id', 'company_id', 'training_skill_id', 'start_date', 'end_date', 'training_cost', 'status', 'description', 'created_at', 'updated_at', 'deleted_at'],
                'description' => 'Employee training programs',
                'relationships' => ['trainers', 'companies', 'training_skills', 'employees']
            ],

            // System & Configuration
            'roles' => [
                'fields' => ['id', 'name', 'guard_name', 'created_at', 'updated_at'],
                'description' => 'User roles and permissions',
                'relationships' => ['users', 'permissions']
            ],
            'permissions' => [
                'fields' => ['id', 'name', 'guard_name', 'created_at', 'updated_at'],
                'description' => 'System permissions',
                'relationships' => ['roles']
            ],
            'settings' => [
                'fields' => ['id', 'currency_id', 'email', 'CompanyName', 'CompanyPhone', 'CompanyAdress', 'footer', 'developed_by', 'logo', 'default_language', 'created_at', 'updated_at', 'deleted_at'],
                'description' => 'System configuration settings',
                'relationships' => ['currencies']
            ],

            // AI System Tables
            'ai_chat_conversations' => [
                'fields' => ['id', 'user_id', 'session_id', 'title', 'is_active', 'created_at', 'updated_at'],
                'description' => 'AI chat conversation sessions',
                'relationships' => ['users', 'ai_chat_messages']
            ],
            'ai_chat_messages' => [
                'fields' => ['id', 'conversation_id', 'type', 'message', 'metadata', 'created_at', 'updated_at'],
                'description' => 'AI chat messages and responses',
                'relationships' => ['ai_chat_conversations']
            ],
            'ai_database_operations' => [
                'fields' => ['id', 'user_id', 'user_role', 'original_question', 'generated_sql', 'operation_type', 'query_analysis', 'result_summary', 'affected_rows', 'result_count', 'success', 'error_message', 'execution_time', 'created_at', 'updated_at'],
                'description' => 'AI database operation audit trail',
                'relationships' => ['users']
            ]
        ];
    }

    /**
     * Identify tables mentioned in the question
     */
    private function identifyTablesInQuestion($question)
    {
        $tables = [];
        $schema = $this->getCompleteTableSchema();

        // Enhanced keyword mapping with more comprehensive coverage
        $tableKeywords = [
            // Core entities
            'employee' => 'employees', 'staff' => 'employees', 'worker' => 'employees', 'personnel' => 'employees',
            'user' => 'users', 'account' => 'users', 'login' => 'users',
            'department' => 'departments', 'dept' => 'departments', 'division' => 'departments',
            'designation' => 'designations', 'position' => 'designations', 'title' => 'designations', 'role' => 'designations',
            'company' => 'companies', 'organization' => 'companies', 'firm' => 'companies',

            // Project management
            'project' => 'projects', 'proj' => 'projects',
            'task' => 'tasks', 'assignment' => 'tasks', 'work' => 'tasks',
            'client' => 'clients', 'customer' => 'clients',

            // Time & attendance
            'attendance' => 'attendance', 'present' => 'attendance', 'absent' => 'attendance', 'clock' => 'attendance',
            'shift' => 'office_shifts', 'schedule' => 'office_shifts', 'timing' => 'office_shifts',

            // Leave management
            'leave' => 'leaves', 'vacation' => 'leaves', 'holiday' => 'leaves', 'off' => 'leaves',
            'leave type' => 'leave_types', 'vacation type' => 'leave_types',

            // Financial
            'salary' => 'salary_disbursements', 'pay' => 'salary_disbursements', 'wage' => 'salary_disbursements', 'payment' => 'salary_disbursements',
            'bonus' => 'bonus_allowances', 'allowance' => 'bonus_allowances', 'incentive' => 'bonus_allowances',

            // HR
            'award' => 'awards', 'recognition' => 'awards', 'achievement' => 'awards',
            'complaint' => 'complaints', 'grievance' => 'complaints', 'issue' => 'complaints',
            'training' => 'trainings', 'course' => 'trainings', 'skill' => 'trainings',

            // AI system
            'chat' => 'ai_chat_conversations', 'conversation' => 'ai_chat_conversations',
            'message' => 'ai_chat_messages', 'response' => 'ai_chat_messages',
            'operation' => 'ai_database_operations', 'query' => 'ai_database_operations', 'audit' => 'ai_database_operations'
        ];

        // Check for direct table name mentions
        foreach ($schema as $tableName => $tableInfo) {
            if (strpos($question, $tableName) !== false) {
                $tables[] = $tableName;
            }
        }

        // Check for keyword matches
        foreach ($tableKeywords as $keyword => $table) {
            if (strpos($question, $keyword) !== false) {
                $tables[] = $table;
            }
        }

        // Context-based table detection
        if (strpos($question, 'who') !== false || strpos($question, 'staff') !== false || strpos($question, 'people') !== false) {
            $tables[] = 'employees';
        }

        if (strpos($question, 'how many') !== false && empty($tables)) {
            $tables[] = 'employees'; // Default for counting queries
        }

        // If no specific tables identified, default to employees for most queries
        if (empty($tables)) {
            $tables[] = 'employees';
        }

        return array_unique($tables);
    }

    /**
     * Identify columns mentioned in the question
     */
    private function identifyColumnsInQuestion($question)
    {
        $columns = [];
        $schema = $this->getCompleteTableSchema();

        // Enhanced column keyword mapping
        $columnKeywords = [
            // Personal information
            'name' => ['firstname', 'lastname', 'username', 'company_name', 'project_name', 'task_name'],
            'first name' => ['firstname'], 'last name' => ['lastname'], 'username' => ['username'],
            'email' => ['email', 'company_email'],
            'phone' => ['phone', 'company_phone'],
            'gender' => ['gender'], 'marital status' => ['marital_status'],

            // Address information
            'address' => ['address', 'company_address'], 'city' => ['city', 'company_city'],
            'province' => ['province'], 'zipcode' => ['zipcode', 'company_zipcode'],
            'country' => ['country', 'company_country'],

            // Employment information
            'salary' => ['basic_salary', 'hourly_rate', 'net_salary'],
            'basic salary' => ['basic_salary'], 'hourly rate' => ['hourly_rate'],
            'employment type' => ['employment_type'], 'mode' => ['mode'],
            'joining date' => ['joining_date'], 'leaving date' => ['leaving_date'],
            'birth date' => ['birth_date'], 'expected hours' => ['expected_hours'],

            // Leave information
            'leave' => ['remaining_leave', 'total_leave', 'sick_leave', 'casual_leave'],
            'sick leave' => ['sick_leave'], 'casual leave' => ['casual_leave'],
            'total leave' => ['total_leave'], 'remaining leave' => ['remaining_leave'],

            // Status and dates
            'status' => ['status'], 'date' => ['date', 'created_at', 'updated_at'],
            'created' => ['created_at'], 'updated' => ['updated_at'],
            'start date' => ['start_date'], 'end date' => ['end_date'],

            // Project and task fields
            'title' => ['title'], 'description' => ['description'],
            'priority' => ['priority'], 'progress' => ['project_progress'],
            'budget' => ['budget'],

            // Attendance fields
            'clock in' => ['clock_in'], 'clock out' => ['clock_out'],
            'total hours' => ['total_hours'], 'break time' => ['break_time'],
            'overtime' => ['overtime'],

            // Financial fields
            'allowances' => ['allowances'], 'deductions' => ['deductions'],
            'bonus' => ['bonus_type', 'amount'], 'amount' => ['amount', 'cash_price'],
            'month' => ['month'], 'year' => ['year'],

            // Organizational fields
            'department' => ['department_id', 'department_name', 'department'],
            'designation' => ['designation_id', 'designation'],
            'company' => ['company_id', 'company_name'],
            'role' => ['role_users_id'],

            // Time fields
            'time' => ['time', 'start_time', 'end_time'],
            'shift' => ['shift_name'], 'break duration' => ['break_duration'],

            // Social media
            'facebook' => ['facebook'], 'twitter' => ['twitter'],
            'linkedin' => ['linkedin'], 'skype' => ['skype'], 'whatsapp' => ['whatsapp']
        ];

        // Check for direct column mentions in any table
        foreach ($schema as $tableName => $tableInfo) {
            foreach ($tableInfo['fields'] as $field) {
                if (strpos($question, $field) !== false) {
                    $columns[] = $field;
                }
            }
        }

        // Check for keyword matches
        foreach ($columnKeywords as $keyword => $cols) {
            if (strpos($question, $keyword) !== false) {
                $columns = array_merge($columns, $cols);
            }
        }

        // Context-based column detection
        if (strpos($question, 'count') !== false || strpos($question, 'how many') !== false) {
            $columns[] = 'id'; // For counting
        }

        if (strpos($question, 'list') !== false || strpos($question, 'show') !== false) {
            // Add common display columns
            $columns = array_merge($columns, ['firstname', 'lastname', 'email']);
        }

        return array_unique($columns);
    }

    /**
     * Identify conditions in the question
     */
    private function identifyConditionsInQuestion($question)
    {
        $conditions = [];

        // Date conditions
        if (preg_match('/(\d{4})/', $question, $matches)) {
            $conditions['year'] = $matches[1];
        }

        if (strpos($question, 'last month') !== false) {
            $conditions['date_range'] = 'last_month';
        } elseif (strpos($question, 'this month') !== false) {
            $conditions['date_range'] = 'this_month';
        } elseif (strpos($question, 'last year') !== false) {
            $conditions['date_range'] = 'last_year';
        } elseif (strpos($question, 'this year') !== false) {
            $conditions['date_range'] = 'this_year';
        }

        // Numeric conditions
        if (preg_match('/more than (\d+)/', $question, $matches)) {
            $conditions['greater_than'] = $matches[1];
        }
        if (preg_match('/less than (\d+)/', $question, $matches)) {
            $conditions['less_than'] = $matches[1];
        }

        return $conditions;
    }

    /**
     * Identify aggregations in the question
     */
    private function identifyAggregationsInQuestion($question)
    {
        $aggregations = [];

        if (strpos($question, 'count') !== false || strpos($question, 'how many') !== false) {
            $aggregations[] = 'COUNT';
        }
        if (strpos($question, 'average') !== false || strpos($question, 'avg') !== false) {
            $aggregations[] = 'AVG';
        }
        if (strpos($question, 'sum') !== false || strpos($question, 'total') !== false) {
            $aggregations[] = 'SUM';
        }
        if (strpos($question, 'maximum') !== false || strpos($question, 'max') !== false || strpos($question, 'highest') !== false) {
            $aggregations[] = 'MAX';
        }
        if (strpos($question, 'minimum') !== false || strpos($question, 'min') !== false || strpos($question, 'lowest') !== false) {
            $aggregations[] = 'MIN';
        }

        return $aggregations;
    }

    /**
     * Assess query complexity
     */
    private function assessQueryComplexity($question, $tables, $conditions, $aggregations)
    {
        $complexity = 'simple';

        if (count($tables) > 2 || count($aggregations) > 1 || count($conditions) > 2) {
            $complexity = 'complex';
        } elseif (count($tables) > 1 || count($aggregations) > 0 || count($conditions) > 1) {
            $complexity = 'medium';
        }

        return $complexity;
    }

    /**
     * Get basic schema when full schema fails
     */
    private function getBasicSchema()
    {
        $sqlGenerator = new SqlGeneratorService();
        return $sqlGenerator->getBasicSchema();
    }

    /**
     * Validate SQL query before execution
     */
    private function validateSQLQuery($sqlQuery)
    {
        // Basic validation to prevent dangerous operations
        $dangerousPatterns = [
            '/DROP\s+TABLE/i',
            '/DROP\s+DATABASE/i',
            '/TRUNCATE/i',
            '/ALTER\s+TABLE.*DROP/i',
            '/DELETE\s+FROM\s+\w+\s*$/i', // DELETE without WHERE
            '/UPDATE\s+\w+\s+SET.*$/i'    // UPDATE without WHERE
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $sqlQuery)) {
                throw new \Exception('Dangerous SQL operation detected and blocked');
            }
        }

        // Check for SQL injection patterns
        $injectionPatterns = [
            '/;\s*(DROP|DELETE|UPDATE|INSERT)/i',
            '/UNION\s+SELECT/i',
            '/--\s*$/m',
            '/\/\*.*\*\//s'
        ];

        foreach ($injectionPatterns as $pattern) {
            if (preg_match($pattern, $sqlQuery)) {
                throw new \Exception('Potential SQL injection detected and blocked');
            }
        }

        return true;
    }

    /**
     * Log database operation for audit trail
     */
    private function logDatabaseOperation($question, $sqlQuery, $result)
    {
        try {
            // Save to audit table
            AiDatabaseOperation::create([
                'user_id' => $this->userId,
                'user_role' => $this->userRole,
                'original_question' => $question,
                'generated_sql' => $sqlQuery,
                'operation_type' => $result['type'] ?? 'unknown',
                'query_analysis' => [
                    'complexity' => 'auto-generated',
                    'tables_involved' => $this->identifyTablesInQuestion(strtolower($question))
                ],
                'result_summary' => [
                    'success' => true,
                    'execution_time' => microtime(true) - (microtime(true) - 0.1) // approximate
                ],
                'affected_rows' => $result['affected_rows'] ?? null,
                'result_count' => $result['count'] ?? null,
                'success' => true,
                'execution_time' => 0.1 // placeholder
            ]);

            // Also log to Laravel logs
            Log::info('AI Database Operation', [
                'user_id' => $this->userId,
                'user_role' => $this->userRole,
                'original_question' => $question,
                'generated_sql' => $sqlQuery,
                'result_type' => $result['type'] ?? 'unknown',
                'affected_rows' => $result['affected_rows'] ?? null,
                'result_count' => $result['count'] ?? null,
                'timestamp' => now()->toISOString()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to log AI database operation: ' . $e->getMessage());
        }
    }

    /**
     * Get fallback data when query fails
     */
    private function getFallbackData($question)
    {
        try {
            // Return basic system stats as fallback
            if ($this->userRole === 'super_admin') {
                return [
                    'fallback_message' => 'Query failed, showing basic system overview',
                    'employees_count' => Employee::count(),
                    'departments_count' => Department::count(),
                    'projects_count' => Project::count(),
                    'tasks_count' => Task::count(),
                    'recent_employees' => Employee::with(['department', 'designation'])
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get()
                        ->map(function($emp) {
                            return [
                                'name' => trim($emp->firstname . ' ' . $emp->lastname),
                                'department' => $emp->department ? $emp->department->department_name : 'No Department',
                                'designation' => $emp->designation ? $emp->designation->designation : 'No Designation'
                            ];
                        })
                ];
            }

            return ['message' => 'Unable to process query, please try again'];
        } catch (\Exception $e) {
            return ['error' => 'System temporarily unavailable'];
        }
    }

    /**
     * Generate alternative query when original fails
     */
    private function generateAlternativeQuery($failedQuery, $originalQuestion, $error, $attempt)
    {
        try {
            $sqlGenerator = new SqlGeneratorService();

            // Analyze the error to determine alternative approach
            if (strpos($error, 'DATE_TRUNC') !== false || strpos($error, 'INTERVAL') !== false) {
                // PostgreSQL functions not supported in MySQL, use MySQL equivalents
                return $this->convertToMySQLDateFunctions($failedQuery);
            }

            if (strpos($error, 'Unknown column') !== false) {
                // Column doesn't exist, try alternative column names
                return $this->fixColumnNames($failedQuery, $error);
            }

            if (strpos($error, 'Unknown table') !== false) {
                // Table doesn't exist, try alternative table names
                return $this->fixTableNames($failedQuery, $error);
            }

            if (strpos($error, 'syntax error') !== false) {
                // Syntax error, simplify the query
                return $this->simplifyQuery($failedQuery, $originalQuestion);
            }

            // If specific error handling didn't work, try a completely different approach
            if ($attempt == 2) {
                return $this->generateSimpleAlternative($originalQuestion);
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error generating alternative query: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Convert PostgreSQL date functions to MySQL equivalents
     */
    private function convertToMySQLDateFunctions($query)
    {
        // Replace PostgreSQL DATE_TRUNC with MySQL equivalents
        $query = preg_replace('/DATE_TRUNC\(\'month\',\s*CURRENT_DATE\)/', 'DATE_FORMAT(CURDATE(), \'%Y-%m-01\')', $query);
        $query = preg_replace('/DATE_TRUNC\(\'year\',\s*CURRENT_DATE\)/', 'DATE_FORMAT(CURDATE(), \'%Y-01-01\')', $query);
        $query = preg_replace('/DATE_TRUNC\(\'week\',\s*CURRENT_DATE\)/', 'DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)', $query);

        // Replace CURRENT_DATE with CURDATE()
        $query = str_replace('CURRENT_DATE', 'CURDATE()', $query);

        // Replace INTERVAL syntax
        $query = preg_replace('/\+\s*INTERVAL\s+\'1\s+month\'/', '+ INTERVAL 1 MONTH', $query);
        $query = preg_replace('/\+\s*INTERVAL\s+\'1\s+year\'/', '+ INTERVAL 1 YEAR', $query);
        $query = preg_replace('/\+\s*INTERVAL\s+\'1\s+week\'/', '+ INTERVAL 1 WEEK', $query);

        return $query;
    }

    /**
     * Fix column names based on error
     */
    private function fixColumnNames($query, $error)
    {
        // Extract column name from error
        if (preg_match('/Unknown column \'([^\']+)\'/', $error, $matches)) {
            $unknownColumn = $matches[1];

            // Common column name mappings
            $columnMappings = [
                'disbursement_date' => 'created_at',
                'department_name' => 'department',
                'project_name' => 'title',
                'task_name' => 'title',
                'employee_name' => 'CONCAT(firstname, " ", lastname)',
                'full_name' => 'CONCAT(firstname, " ", lastname)',
                'name' => 'CONCAT(firstname, " ", lastname)'
            ];

            if (isset($columnMappings[$unknownColumn])) {
                $query = str_replace($unknownColumn, $columnMappings[$unknownColumn], $query);
                return $query;
            }
        }

        return null;
    }

    /**
     * Fix table names based on error
     */
    private function fixTableNames($query, $error)
    {
        // Extract table name from error
        if (preg_match('/Table \'[^\']+\.([^\']+)\' doesn\'t exist/', $error, $matches)) {
            $unknownTable = $matches[1];

            // Common table name mappings
            $tableMappings = [
                'employee' => 'employees',
                'department' => 'departments',
                'project' => 'projects',
                'task' => 'tasks',
                'salary' => 'salary_disbursements',
                'leave' => 'leaves'
            ];

            if (isset($tableMappings[$unknownTable])) {
                $query = str_replace($unknownTable, $tableMappings[$unknownTable], $query);
                return $query;
            }
        }

        return null;
    }

    /**
     * Simplify complex query to basic version
     */
    private function simplifyQuery($query, $originalQuestion)
    {
        // Extract main table from query
        if (preg_match('/FROM\s+(\w+)/', $query, $matches)) {
            $mainTable = $matches[1];

            // Create simple query based on question intent
            if (strpos($originalQuestion, 'count') !== false || strpos($originalQuestion, 'how many') !== false) {
                return "SELECT COUNT(*) as total_count FROM {$mainTable}";
            }

            if (strpos($originalQuestion, 'this month') !== false) {
                return "SELECT * FROM {$mainTable} WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) LIMIT 50";
            }

            if (strpos($originalQuestion, 'today') !== false) {
                return "SELECT * FROM {$mainTable} WHERE DATE(created_at) = CURDATE() LIMIT 50";
            }

            // Default simple query
            return "SELECT * FROM {$mainTable} ORDER BY id DESC LIMIT 20";
        }

        return null;
    }

    /**
     * Generate simple alternative based on question
     */
    private function generateSimpleAlternative($originalQuestion)
    {
        $question = strtolower($originalQuestion);

        // Salary-related queries
        if (strpos($question, 'salary') !== false || strpos($question, 'disbursement') !== false) {
            if (strpos($question, 'this month') !== false) {
                return "SELECT * FROM salary_disbursements WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) ORDER BY created_at DESC LIMIT 50";
            }
            return "SELECT * FROM salary_disbursements ORDER BY created_at DESC LIMIT 20";
        }

        // Employee queries
        if (strpos($question, 'employee') !== false || strpos($question, 'staff') !== false) {
            return "SELECT id, firstname, lastname, email, basic_salary, joining_date FROM employees ORDER BY id DESC LIMIT 20";
        }

        // Project queries
        if (strpos($question, 'project') !== false) {
            return "SELECT id, title, status, start_date, end_date FROM projects ORDER BY id DESC LIMIT 20";
        }

        // Attendance queries
        if (strpos($question, 'attendance') !== false || strpos($question, 'present') !== false) {
            if (strpos($question, 'today') !== false) {
                return "SELECT * FROM attendance WHERE DATE(date) = CURDATE() ORDER BY clock_in DESC LIMIT 50";
            }
            return "SELECT * FROM attendance ORDER BY date DESC, clock_in DESC LIMIT 20";
        }

        // Default fallback
        return "SELECT COUNT(*) as total_employees FROM employees";
    }

    /**
     * Execute intelligent fallback when all SQL attempts fail
     */
    private function executeIntelligentFallback($originalQuestion, $lastError)
    {
        try {
            $question = strtolower($originalQuestion);

            // Use Eloquent models as fallback
            if (strpos($question, 'salary') !== false || strpos($question, 'disbursement') !== false) {
                $data = SalaryDisbursement::with('employee')
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get()
                    ->map(function($salary) {
                        return [
                            'id' => $salary->id,
                            'employee_name' => $salary->employee ?
                                trim($salary->employee->firstname . ' ' . $salary->employee->lastname) : 'Unknown',
                            'month' => $salary->month,
                            'year' => $salary->year,
                            'basic_salary' => $salary->basic_salary,
                            'net_salary' => $salary->net_salary,
                            'status' => $salary->status,
                            'created_at' => $salary->created_at
                        ];
                    });

                return [
                    'type' => 'fallback',
                    'data' => $data,
                    'count' => $data->count(),
                    'message' => 'Retrieved salary information using alternative method',
                    'success' => true
                ];
            }

            if (strpos($question, 'employee') !== false || strpos($question, 'staff') !== false) {
                $data = Employee::with(['department', 'designation'])
                    ->orderBy('created_at', 'desc')
                    ->limit(20)
                    ->get()
                    ->map(function($emp) {
                        return [
                            'id' => $emp->id,
                            'name' => trim($emp->firstname . ' ' . $emp->lastname),
                            'email' => $emp->email,
                            'department' => $emp->department ? $emp->department->department_name : 'No Department',
                            'designation' => $emp->designation ? $emp->designation->designation : 'No Designation',
                            'basic_salary' => $emp->basic_salary,
                            'joining_date' => $emp->joining_date
                        ];
                    });

                return [
                    'type' => 'fallback',
                    'data' => $data,
                    'count' => $data->count(),
                    'message' => 'Retrieved employee information using alternative method',
                    'success' => true
                ];
            }

            // Default system overview
            return [
                'type' => 'fallback',
                'data' => [
                    'total_employees' => Employee::count(),
                    'total_departments' => Department::count(),
                    'total_projects' => Project::count(),
                    'recent_employees' => Employee::orderBy('created_at', 'desc')->limit(5)->get(['firstname', 'lastname', 'email'])
                ],
                'count' => 1,
                'message' => 'Provided system overview as alternative',
                'success' => true
            ];

        } catch (\Exception $e) {
            Log::error('Fallback execution failed: ' . $e->getMessage());

            return [
                'type' => 'error',
                'data' => null,
                'count' => 0,
                'message' => 'Unable to retrieve the requested information at this time',
                'success' => false
            ];
        }
    }

    /**
     * Get direct database connection details
     */
    private function getDatabaseConnection()
    {
        return [
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE'),
            'username' => env('DB_USERNAME'),
            'password' => env('DB_PASSWORD'),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
        ];
    }

    /**
     * Generate direct SQL query with full capabilities
     */
    private function generateDirectSQL($question)
    {
        $question = strtolower(trim($question));

        // Check if user provided direct SQL
        if ($this->isDirectSQLQuery($question)) {
            return $this->extractAndValidateDirectSQL($question);
        }

        // Generate SQL based on natural language
        return $this->generateAdvancedSQL($question);
    }

    /**
     * Check if user provided direct SQL query
     */
    private function isDirectSQLQuery($question)
    {
        $sqlKeywords = ['select', 'insert', 'update', 'delete', 'show', 'describe', 'explain'];
        $firstWord = strtok($question, ' ');
        return in_array($firstWord, $sqlKeywords);
    }

    /**
     * Extract and validate direct SQL from user input
     */
    private function extractAndValidateDirectSQL($question)
    {
        // Remove common prefixes
        $prefixes = ['execute', 'run', 'query:', 'sql:'];
        foreach ($prefixes as $prefix) {
            if (strpos($question, $prefix) === 0) {
                $question = trim(substr($question, strlen($prefix)));
                break;
            }
        }

        // Basic SQL formatting
        $sql = trim($question);
        if (!str_ends_with($sql, ';')) {
            $sql .= ';';
        }

        return $sql;
    }

    /**
     * Generate advanced SQL from natural language
     */
    private function generateAdvancedSQL($question)
    {
        // Enhanced natural language processing
        $schema = $this->getCompleteTableSchema();
        $analysis = $this->analyzeQuestionAdvanced($question);

        // Generate SQL based on analysis
        switch ($analysis['intent']) {
            case 'count':
                return $this->generateCountQuery($analysis, $schema);
            case 'list':
                return $this->generateListQuery($analysis, $schema);
            case 'find':
                return $this->generateFindQuery($analysis, $schema);
            case 'aggregate':
                return $this->generateAggregateQuery($analysis, $schema);
            case 'update':
                return $this->generateUpdateQuery($analysis, $schema);
            case 'insert':
                return $this->generateInsertQuery($analysis, $schema);
            case 'delete':
                return $this->generateDeleteQuery($analysis, $schema);
            default:
                return $this->generateDefaultQuery($analysis, $schema);
        }
    }

    /**
     * Advanced question analysis
     */
    private function analyzeQuestionAdvanced($question)
    {
        $analysis = [
            'intent' => 'list',
            'tables' => [],
            'columns' => [],
            'conditions' => [],
            'aggregations' => [],
            'time_filters' => [],
            'joins_needed' => false
        ];

        // Detect intent
        if (preg_match('/\b(how many|count|total number)\b/i', $question)) {
            $analysis['intent'] = 'count';
        } elseif (preg_match('/\b(find|search|where|filter)\b/i', $question)) {
            $analysis['intent'] = 'find';
        } elseif (preg_match('/\b(average|sum|max|min|avg)\b/i', $question)) {
            $analysis['intent'] = 'aggregate';
        } elseif (preg_match('/\b(update|change|modify|set)\b/i', $question)) {
            $analysis['intent'] = 'update';
        } elseif (preg_match('/\b(add|insert|create|new)\b/i', $question)) {
            $analysis['intent'] = 'insert';
        } elseif (preg_match('/\b(delete|remove|drop)\b/i', $question)) {
            $analysis['intent'] = 'delete';
        }

        // Extract tables
        $analysis['tables'] = $this->identifyTablesInQuestion($question);

        // Extract columns
        $analysis['columns'] = $this->identifyColumnsInQuestion($question);

        // Extract conditions
        $analysis['conditions'] = $this->identifyConditionsInQuestion($question);

        // Extract time filters
        $analysis['time_filters'] = $this->extractTimeFilters($question);

        // Determine if joins are needed
        $analysis['joins_needed'] = count($analysis['tables']) > 1;

        return $analysis;
    }

    /**
     * Execute raw SQL with full database access
     */
    private function executeRawSQL($sqlQuery, $connection)
    {
        try {
            // Create PDO connection for maximum flexibility
            $dsn = "mysql:host={$connection['host']};port={$connection['port']};dbname={$connection['database']};charset={$connection['charset']}";
            $pdo = new \PDO($dsn, $connection['username'], $connection['password'], [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ]);

            $startTime = microtime(true);

            // Execute query
            $stmt = $pdo->prepare($sqlQuery);
            $stmt->execute();

            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2);

            // Handle different query types
            $queryType = $this->detectQueryType($sqlQuery);

            switch ($queryType) {
                case 'SELECT':
                case 'SHOW':
                case 'DESCRIBE':
                case 'EXPLAIN':
                    $data = $stmt->fetchAll();
                    return [
                        'type' => strtolower($queryType),
                        'data' => $data,
                        'count' => count($data),
                        'execution_time' => $executionTime,
                        'columns' => $this->getColumnInfo($stmt)
                    ];

                case 'INSERT':
                    return [
                        'type' => 'insert',
                        'success' => true,
                        'last_insert_id' => $pdo->lastInsertId(),
                        'execution_time' => $executionTime,
                        'message' => 'Record inserted successfully'
                    ];

                case 'UPDATE':
                    $rowCount = $stmt->rowCount();
                    return [
                        'type' => 'update',
                        'affected_rows' => $rowCount,
                        'execution_time' => $executionTime,
                        'message' => "Updated {$rowCount} record(s)"
                    ];

                case 'DELETE':
                    $rowCount = $stmt->rowCount();
                    return [
                        'type' => 'delete',
                        'affected_rows' => $rowCount,
                        'execution_time' => $executionTime,
                        'message' => "Deleted {$rowCount} record(s)"
                    ];

                default:
                    return [
                        'type' => 'other',
                        'success' => true,
                        'execution_time' => $executionTime,
                        'message' => 'Query executed successfully'
                    ];
            }

        } catch (\PDOException $e) {
            throw new \Exception('Database query failed: ' . $e->getMessage());
        }
    }

    /**
     * Detect SQL query type
     */
    private function detectQueryType($sqlQuery)
    {
        $query = trim(strtoupper($sqlQuery));

        if (strpos($query, 'SELECT') === 0) return 'SELECT';
        if (strpos($query, 'INSERT') === 0) return 'INSERT';
        if (strpos($query, 'UPDATE') === 0) return 'UPDATE';
        if (strpos($query, 'DELETE') === 0) return 'DELETE';
        if (strpos($query, 'SHOW') === 0) return 'SHOW';
        if (strpos($query, 'DESCRIBE') === 0) return 'DESCRIBE';
        if (strpos($query, 'EXPLAIN') === 0) return 'EXPLAIN';

        return 'OTHER';
    }

    /**
     * Get column information from statement
     */
    private function getColumnInfo($stmt)
    {
        $columns = [];
        $columnCount = $stmt->columnCount();

        for ($i = 0; $i < $columnCount; $i++) {
            $meta = $stmt->getColumnMeta($i);
            $columns[] = [
                'name' => $meta['name'],
                'type' => $meta['native_type'] ?? 'unknown',
                'length' => $meta['len'] ?? null
            ];
        }

        return $columns;
    }

    /**
     * Validate direct SQL for security
     */
    private function validateDirectSQL($sqlQuery)
    {
        // Enhanced security validation
        $dangerousPatterns = [
            '/DROP\s+(TABLE|DATABASE|INDEX|VIEW)/i',
            '/TRUNCATE\s+TABLE/i',
            '/ALTER\s+TABLE.*DROP/i',
            '/CREATE\s+(USER|ROLE)/i',
            '/GRANT\s+/i',
            '/REVOKE\s+/i',
            '/LOAD\s+DATA/i',
            '/INTO\s+OUTFILE/i',
            '/LOAD_FILE\s*\(/i',
            '/BENCHMARK\s*\(/i',
            '/SLEEP\s*\(/i'
        ];

        foreach ($dangerousPatterns as $pattern) {
            if (preg_match($pattern, $sqlQuery)) {
                throw new \Exception('Dangerous SQL operation detected and blocked for security');
            }
        }

        // Check for SQL injection patterns
        $injectionPatterns = [
            '/;\s*(DROP|DELETE|UPDATE|INSERT)/i',
            '/UNION\s+SELECT/i',
            '/--\s*$/m',
            '/\/\*.*\*\//s',
            '/\'\s*OR\s*\'/i',
            '/\'\s*AND\s*\'/i'
        ];

        foreach ($injectionPatterns as $pattern) {
            if (preg_match($pattern, $sqlQuery)) {
                throw new \Exception('Potential SQL injection detected and blocked');
            }
        }

        return true;
    }

    /**
     * Log direct database operation
     */
    private function logDirectDatabaseOperation($question, $sqlQuery, $result)
    {
        try {
            AiDatabaseOperation::create([
                'user_id' => $this->userId,
                'user_role' => $this->userRole,
                'original_question' => $question,
                'generated_sql' => $sqlQuery,
                'operation_type' => strtolower($result['type'] ?? 'unknown'),
                'query_analysis' => [
                    'method' => 'direct_database_connection',
                    'execution_time' => $result['execution_time'] ?? null,
                    'columns_returned' => $result['columns'] ?? null
                ],
                'result_summary' => [
                    'success' => true,
                    'message' => $result['message'] ?? 'Query executed successfully'
                ],
                'affected_rows' => $result['affected_rows'] ?? null,
                'result_count' => $result['count'] ?? null,
                'success' => true,
                'execution_time' => ($result['execution_time'] ?? 0) / 1000 // Convert to seconds
            ]);

            Log::info('Direct Database Operation', [
                'user_id' => $this->userId,
                'user_role' => $this->userRole,
                'question' => $question,
                'sql_query' => $sqlQuery,
                'execution_time' => $result['execution_time'] ?? null,
                'result_type' => $result['type'] ?? 'unknown'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to log direct database operation: ' . $e->getMessage());
        }
    }

    /**
     * Generate count query
     */
    private function generateCountQuery($analysis, $schema)
    {
        $sqlGenerator = new SqlGeneratorService();
        return $sqlGenerator->generateCountQuery($analysis, $schema);
    }

    /**
     * Generate list query
     */
    private function generateListQuery($analysis, $schema)
    {
        $sqlGenerator = new SqlGeneratorService();
        return $sqlGenerator->generateListQuery($analysis, $schema);
    }

    /**
     * Generate find query
     */
    private function generateFindQuery($analysis, $schema)
    {
        $sqlGenerator = new SqlGeneratorService();
        return $sqlGenerator->generateFindQuery($analysis, $schema);
    }

    /**
     * Generate aggregate query
     */
    private function generateAggregateQuery($analysis, $schema)
    {
        $sqlGenerator = new SqlGeneratorService();
        return $sqlGenerator->generateAggregateQuery($analysis, $schema);
    }

    /**
     * Generate default query
     */
    private function generateDefaultQuery($analysis, $schema)
    {
        $sqlGenerator = new SqlGeneratorService();
        return $sqlGenerator->generateDefaultQuery($analysis, $schema);
    }

    /**
     * Extract time filters from question
     */
    private function extractTimeFilters($question)
    {
        $sqlGenerator = new SqlGeneratorService();
        return $sqlGenerator->extractTimeFilters($question);
    }

    /**
     * Enhanced fallback for super admin when all intelligent strategies fail
     */
    private function executeEnhancedFallback($question, $failedResult)
    {
        try {
            Log::warning('Enhanced AI Agent - Using enhanced fallback', [
                'user_id' => $this->userId,
                'question' => $question,
                'failed_strategies' => $failedResult['strategies_tried'] ?? [],
                'failed_attempts' => $failedResult['attempts'] ?? 0
            ]);

            // Try basic database operations as last resort
            $fallbackData = $this->getBasicDatabaseInfo($question);

            return [
                'success' => true,
                'data' => $fallbackData,
                'query_type' => 'enhanced_fallback',
                'strategy_used' => 'basic_database_info',
                'attempts' => ($failedResult['attempts'] ?? 0) + 1,
                'user_role' => $this->userRole,
                'data_source' => 'basic_database_queries',
                'validation_status' => 'fallback_data',
                'warning' => 'Using fallback data due to query execution issues'
            ];

        } catch (\Exception $e) {
            Log::error('Enhanced fallback also failed: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => 'All query execution strategies failed, including fallback',
                'data' => null,
                'attempts' => ($failedResult['attempts'] ?? 0) + 1,
                'data_source' => 'none'
            ];
        }
    }

    /**
     * Get basic database information as ultimate fallback
     */
    private function getBasicDatabaseInfo($question)
    {
        try {
            // Try very basic queries that should always work
            $basicInfo = [
                'database_status' => 'connected',
                'timestamp' => now()->toISOString()
            ];

            // Try to get basic counts
            try {
                $basicInfo['total_employees'] = DB::table('employees')->count();
            } catch (\Exception $e) {
                $basicInfo['total_employees'] = 'unavailable';
            }

            try {
                $basicInfo['total_departments'] = DB::table('departments')->count();
            } catch (\Exception $e) {
                $basicInfo['total_departments'] = 'unavailable';
            }

            try {
                $basicInfo['total_projects'] = DB::table('projects')->count();
            } catch (\Exception $e) {
                $basicInfo['total_projects'] = 'unavailable';
            }

            return [
                'data' => [$basicInfo],
                'count' => 1,
                'execution_time' => 1,
                'message' => 'Basic database information retrieved as fallback'
            ];

        } catch (\Exception $e) {
            return [
                'data' => [['status' => 'Database connection issues detected']],
                'count' => 1,
                'execution_time' => 0,
                'message' => 'Unable to retrieve database information'
            ];
        }
    }
}
