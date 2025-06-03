<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Attendance;
use App\Models\Project;
use App\Models\Task;
use App\Models\Client;
use App\Models\Expense;
use App\Models\Deposit;
use App\Models\Employee;
use App\Models\ExpenseCategory;
use App\Models\Account;
use App\Models\PaymentMethod;
use App\Models\DepositCategory;
use Carbon\Carbon;
use DB;
use App\utils\helpers;
use DataTables;
use App\Models\Holiday;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\BonusAllowance;
use App\Models\SalaryDisbursement;
use App\Notifications\SalaryDisbursed;

class ReportController extends Controller
{

    /* Attendance report */

    public function attendance_report_index(Request $request){

        $user_auth = auth()->user();
		if ($user_auth->can('attendance_report')){

            $employees = Employee::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','username']);

            if ($request->ajax()) {
                $helpers = new helpers();
                $param = array(0 => '=');
                $columns = array(0 => 'employee_id');
                $end_date_default = Carbon::now()->format('Y-m-d');
                $start_date_default = Carbon::now()->subYear()->format('Y-m-d');
                $start_date = empty($request->start_date)?$start_date_default:$request->start_date;
                $end_date = empty($request->end_date)?$end_date_default:$request->end_date;

                // Debug logging
                \Log::info('Attendance Report Filter Debug:', [
                    'request_start_date' => $request->start_date,
                    'request_end_date' => $request->end_date,
                    'processed_start_date' => $start_date,
                    'processed_end_date' => $end_date,
                    'employee_id' => $request->employee_id
                ]);

                // Validate date format
                try {
                    if ($start_date) {
                        Carbon::createFromFormat('Y-m-d', $start_date);
                    }
                    if ($end_date) {
                        Carbon::createFromFormat('Y-m-d', $end_date);
                    }
                } catch (\Exception $e) {
                    \Log::error('Invalid date format in attendance report:', [
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'error' => $e->getMessage()
                    ]);
                    return response()->json(['error' => 'Invalid date format'], 400);
                }

                $attendances = Attendance::where('deleted_at', '=', null)
                ->whereBetween('date', array($start_date, $end_date))
                ->with('company:id,name','employee:id,username')
                ->orderBy('id', 'desc');

                //Multiple Filter
                $attendances_Filtred = $helpers->filter($attendances, $columns, $param, $request)->get();

                \Log::info('Attendance Report Results:', [
                    'total_records' => $attendances_Filtred->count(),
                    'date_range' => [$start_date, $end_date]
                ]);

                return Datatables::of($attendances_Filtred)
                        ->addIndexColumn()
                        ->make(true);
            }

            return view('report.attendance_report',compact('employees'));

        }
        return abort('403', __('You are not authorized'));

    }


    /* Employee report */
    public function employee_report_index(Request $request){

        $user_auth = auth()->user();
		if ($user_auth->can('employee_report')){

            $companies = Company::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','name']);

            if ($request->ajax()) {
                $helpers = new helpers();
                $param = array(0 => '=' , 1=> '=' , 2=> '=');
                $columns = array(0 => 'company_id' , 1 => 'department_id' , 2 => 'designation_id');

                $employees = Employee::where('deleted_at', '=', null)
                ->with('company:id,name','department:id,department','designation:id,designation','office_shift:id,name')
                ->orderBy('id', 'desc');
                //Multiple Filter
                $employees_Filtred = $helpers->filter($employees, $columns, $param, $request)->get();
                return Datatables::of($employees_Filtred)
                        ->addIndexColumn()
                        ->make(true);
            }

            return view('report.employee_report',compact('companies'));

        }
        return abort('403', __('You are not authorized'));
    }


    /* Project report */
    public function project_report_index(Request $request){

        $user_auth = auth()->user();
		if ($user_auth->can('project_report')){

            $clients = Client::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','username']);
            $companies = Company::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','name']);

            if ($request->ajax()) {
                $helpers = new helpers();
                $param = array(0 => 'like' , 1=> '=' , 2=> '=' , 3=> 'like' , 4=> 'like');
                $columns = array(0 => 'title' , 1 => 'client_id' , 2 => 'company_id' , 3 => 'priority' , 4 => 'status');

                $projects = Project::where('deleted_at', '=', null)
                ->with('company:id,name','client:id,username')->orderBy('id', 'desc');
                //Multiple Filter
                $projects_Filtred = $helpers->filter($projects, $columns, $param, $request)->get();

                return Datatables::of($projects_Filtred)
                        ->addIndexColumn()
                        ->make(true);
            }

            return view('report.project_report',compact('clients','companies'));

        }
        return abort('403', __('You are not authorized'));
    }




    /* Task report */
    public function task_report_index(Request $request){

        $user_auth = auth()->user();
		if ($user_auth->can('task_report')){

            $projects = Project::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','title']);
            $companies = Company::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','name']);
            $employees = Employee::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','firstname','lastname']);

            if ($request->ajax()) {
                $helpers = new helpers();
                $param = array(0 => 'like' , 1=> '=' , 2=> '=' , 3=> 'like' , 4=> 'like');
                $columns = array(0 => 'title' , 1 => 'project_id' , 2 => 'company_id' , 3 => 'priority' , 4 => 'status');

                $tasks = Task::with('company:id,name','project:id,title','assignedEmployees:id,firstname,lastname')
                    ->orderBy('id', 'desc');

                // Filter by assigned member (employee_id)
                if ($request->filled('employee_id') && $request->employee_id != '0') {
                    $tasks = $tasks->whereHas('assignedEmployees', function($q) use ($request) {
                        $q->where('employee_id', $request->employee_id);
                    });
                }

                // Filter by date range
                if ($request->filled('start_date')) {
                    $tasks = $tasks->where('start_date', '>=', $request->start_date);
                }
                if ($request->filled('end_date')) {
                    $tasks = $tasks->where('end_date', '<=', $request->end_date);
                }

                //Multiple Filter
                $tasks_Filtred = $helpers->filter($tasks, $columns, $param, $request)->get();

                return Datatables::of($tasks_Filtred)
                        ->addIndexColumn()
                        ->addColumn('member_name', function($task) {
                            if ($task->assignedEmployees && count($task->assignedEmployees)) {
                                return collect($task->assignedEmployees)->map(function($emp) {
                                    return trim($emp->firstname . ' ' . $emp->lastname);
                                })->implode(', ');
                            }
                            return '-';
                        })
                        ->make(true);
            }

            return view('report.task_report',compact('projects','companies', 'employees'));

        }
        return abort('403', __('You are not authorized'));

    }


    /* Expense report */

    public function expense_report_index(Request $request){

        $user_auth = auth()->user();
		if ($user_auth->can('expense_report')){

            $accounts = Account::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','account_name']);
            $categories = ExpenseCategory::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','title']);
            $payment_methods = PaymentMethod::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','title']);

            if ($request->ajax()) {
                $helpers = new helpers();
                $param = array(0=> 'like' , 1=> '=' , 2=> '=' , 3 => '=');
                $columns = array(0 => 'expense_ref' , 1 => 'account_id' , 2 => 'expense_category_id' , 3 => 'payment_method_id');
                $end_date_default = Carbon::now()->format('Y-m-d');
                $start_date_default = Carbon::now()->subYear()->format('Y-m-d');
                $start_date = empty($request->start_date)?$start_date_default:$request->start_date;
                $end_date = empty($request->end_date)?$end_date_default:$request->end_date;

                $expenses = Expense::where('deleted_at', '=', null)
                ->whereBetween('date', array($start_date, $end_date))
                ->with('account:id,account_name','payment_method:id,title','expense_category:id,title')->orderBy('id', 'desc');
                //Multiple Filter
                $expenses_Filtred = $helpers->filter($expenses, $columns, $param, $request)->get();
                return Datatables::of($expenses_Filtred)
                        ->addIndexColumn()
                        ->make(true);
            }

            return view('report.expense_report',compact('accounts','categories','payment_methods'));

        }
        return abort('403', __('You are not authorized'));

    }


    /* Deposit report */

    public function deposit_report_index(Request $request){

        $user_auth = auth()->user();
		if ($user_auth->can('deposit_report')){

            $accounts = Account::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','account_name']);
            $categories = DepositCategory::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','title']);
            $payment_methods = PaymentMethod::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','title']);

            if ($request->ajax()) {
                $helpers = new helpers();
                $param = array(0=> 'like' , 1=> '=' , 2=> '=' , 3 => '=');
                $columns = array(0 => 'deposit_ref' , 1 => 'account_id' , 2 => 'deposit_category_id' , 3 => 'payment_method_id');

                $end_date_default = Carbon::now()->format('Y-m-d');
                $start_date_default = Carbon::now()->subYear()->format('Y-m-d');
                $start_date = empty($request->start_date)?$start_date_default:$request->start_date;
                $end_date = empty($request->end_date)?$end_date_default:$request->end_date;

                $deposits = Deposit::where('deleted_at', '=', null)
                ->whereBetween('date', array($start_date, $end_date))
                ->with('account:id,account_name','payment_method:id,title','deposit_category:id,title')->orderBy('id', 'desc');
                //Multiple Filter
                $deposits_Filtred = $helpers->filter($deposits, $columns, $param, $request)->get();
                return Datatables::of($deposits_Filtred)
                        ->addIndexColumn()
                        ->make(true);
            }

            return view('report.deposit_report',compact('accounts','categories','payment_methods'));

        }
        return abort('403', __('You are not authorized'));
    }


    public function fetchDepartment(Request $request){

        $value = $request->get('value');
        $dependent  = $request->get('dependent');
        $data = Department::where('company_id' ,$value)->where('deleted_at', '=', null)->groupBy('department')->get();
        $output = '';

        foreach ($data as $row)
        {
            $output .= '<option value=' . $row->id . '>' . $row->$dependent . '</option>';
        }

        return $output;
    }


    public function fetchDesignation(Request $request){
        $value = $request->get('value');
        $designation_name  = $request->get('designation_name');
        $data = Designation::where('department_id' ,$value)->where('deleted_at', '=', null)->groupBy('designation')->get();
        $output = '';

        foreach ($data as $row)
        {
            $output .= '<option value=' . $row->id . '>' . $row->$designation_name . '</option>';
        }

        return $output;
    }

    /**
     * KPI Summary Report for Employees
     */
    public function kpi_summary_report_index(Request $request)
    {
        if (!auth()->user()->can('kpi_report')) {
            abort(403, 'Unauthorized');
        }

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        // Set default date range to this week (Monday to today) if not provided
        if (empty($start_date) || empty($end_date)) {
            $today = Carbon::today();
            $monday = $today->copy()->startOfWeek(Carbon::MONDAY);
            $start_date = $start_date ?: $monday->format('Y-m-d');
            $end_date = $end_date ?: $today->format('Y-m-d');
        }
        $search = $request->input('search');
        $perPage = 15;
        $page = $request->input('page', 1);

        $employees = Employee::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','firstname','lastname']);

        $employeeQuery = Employee::with([
            'RoleUser:id,name',
            'attendance' => function($query) use ($start_date, $end_date) {
                if ($start_date) $query->where('date', '>=', $start_date);
                if ($end_date) $query->where('date', '<=', $end_date);
            },
            'tasks' => function($query) use ($start_date, $end_date) {
                // Don't filter by date here, we'll do that in the collection
            },
            'office_shift',
        ])->where('deleted_at', '=', null);

        // Filter by employee_id (member name)
        if ($request->filled('employee_id') && $request->employee_id != '0') {
            $employeeQuery->where('id', $request->employee_id);
        }

        if ($search) {
            $employeeQuery->where(function($q) use ($search) {
                $q->where('firstname', 'like', "%$search%")
                  ->orWhere('lastname', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('username', 'like', "%$search%") ;
            });
        }

        $employees = $employeeQuery->get();

        // Fetch holidays for all companies in the date range
        $holidaysByCompany = Holiday::where('deleted_at', '=', null)
            ->where(function($q) use ($start_date, $end_date) {
                $q->where(function($q2) use ($start_date, $end_date) {
                    $q2->where('start_date', '<=', $end_date)
                        ->where('end_date', '>=', $start_date);
                });
            })
            ->get()
            ->groupBy('company_id');

        $data = $employees->map(function($employee) use ($start_date, $end_date, $holidaysByCompany) {
            $officeMinutes = 0;
            $remoteMinutes = 0;
            foreach ($employee->attendance as $att) {
                if (empty($att->total_work)) continue;
                [$h, $m] = array_pad(explode(':', $att->total_work), 2, 0);
                $minutes = ((int)$h) * 60 + ((int)$m);
                if ($att->mode === 'office') {
                    $officeMinutes += $minutes;
                } elseif ($att->mode === 'remote') {
                    $remoteMinutes += $minutes;
                }
            }
            $totalLoggedHours = round(($officeMinutes + $remoteMinutes) / 60, 2);
            $officeHours = round($officeMinutes / 60, 2);
            $remoteHours = round($remoteMinutes / 60, 2);

            $tasks = $employee->tasks;

            // Debug information
            \Log::info("Employee: " . $employee->firstname . ' ' . $employee->lastname);
            \Log::info("Timeline: " . $start_date . " to " . $end_date);
            \Log::info("All tasks count: " . $tasks->count());

            // Debug all tasks
            foreach ($tasks as $task) {
                \Log::info("Task: " . $task->title .
                          " (status: " . $task->status .
                          ", completed_at: " . $task->completed_at .
                          ", start_date: " . $task->start_date .
                          ", end_date: " . $task->end_date . ")");
            }

            // Count completed tasks within timeline
            $completedTasksInTimeline = $employee->tasks->filter(function($task) use ($start_date, $end_date) {
                \Log::info("Checking task for completion: {$task->title}", [
                    'status' => $task->status,
                    'start_date' => $task->start_date,
                    'end_date' => $task->end_date
                ]);

                $isCompleted = $task->status === 'completed';
                $isInTimeline = Carbon::parse($task->start_date)->between($start_date, $end_date) ||
                               Carbon::parse($task->end_date)->between($start_date, $end_date);

                \Log::info("Task completion check results", [
                    'is_completed' => $isCompleted,
                    'is_in_timeline' => $isInTimeline,
                    'is_valid' => $isCompleted && $isInTimeline
                ]);

                return $isCompleted && $isInTimeline;
            })->count();

            // Count incomplete tasks until timeline end
            $incompleteTasksUntilTimeline = $employee->tasks->filter(function($task) use ($end_date) {
                \Log::info("Checking task for incomplete status: {$task->title}", [
                    'status' => $task->status,
                    'start_date' => $task->start_date,
                    'end_date' => $task->end_date
                ]);

                $isIncomplete = !in_array($task->status, ['completed', 'cancelled']);
                $isBeforeTimelineEnd = Carbon::parse($task->start_date)->lte($end_date);

                \Log::info("Task incomplete check results", [
                    'is_incomplete' => $isIncomplete,
                    'is_before_timeline_end' => $isBeforeTimelineEnd,
                    'is_valid' => $isIncomplete && $isBeforeTimelineEnd
                ]);

                return $isIncomplete && $isBeforeTimelineEnd;
            })->count();

            // Calculate total tasks
            $totalTasks = $incompleteTasksUntilTimeline + $completedTasksInTimeline;

            // Count tasks completed on time
            $tasksOnTime = $employee->tasks->filter(function($task) use ($start_date, $end_date) {
                \Log::info("Checking task for on-time completion: {$task->title}", [
                    'status' => $task->status,
                    'start_date' => $task->start_date,
                    'end_date' => $task->end_date
                ]);

                $isCompleted = $task->status === 'completed';
                $isInTimeline = Carbon::parse($task->start_date)->between($start_date, $end_date) ||
                               Carbon::parse($task->end_date)->between($start_date, $end_date);
                $isOnTime = $isCompleted && $isInTimeline && Carbon::parse($task->end_date)->gte(Carbon::parse($task->start_date));

                \Log::info("Task on-time check results", [
                    'is_completed' => $isCompleted,
                    'is_in_timeline' => $isInTimeline,
                    'is_on_time' => $isOnTime,
                    'is_valid' => $isOnTime
                ]);

                return $isOnTime;
            })->count();

            \Log::info("Completed tasks in timeline: " . $completedTasksInTimeline);

            \Log::info("Incomplete tasks until timeline: " . $incompleteTasksUntilTimeline);

            \Log::info("Total tasks: " . $totalTasks);

            // Tasks completed is only the ones within timeline
            $tasksCompleted = $completedTasksInTimeline;

            $timeliness = $totalTasks > 0 ? round(($tasksOnTime / $totalTasks) * 100, 2) : 0;
            \Log::info("Timeliness percentage: " . $timeliness . "%");
            \Log::info("----------------------------------------");
            $qualityScore = $tasks->avg('quality_score') ?? 0;

            // Fetch approved leaves for this employee in the date range
            $leaves = $employee->leave->filter(function($leave) use ($start_date, $end_date) {
                return $leave->start_date <= $end_date && $leave->end_date >= $start_date;
            });
            // Helper to check if a date is on leave
            $isOnLeave = function($date) use ($leaves) {
                foreach ($leaves as $leave) {
                    if ($date->betweenIncluded(\Carbon\Carbon::parse($leave->start_date), \Carbon\Carbon::parse($leave->end_date))) {
                        return true;
                    }
                }
                return false;
            };

            // Calculate expected hours from office shift for the date range
            $expected = 0;
            $shift = $employee->office_shift;
            if ($shift && $start_date && $end_date) {
                $start = \Carbon\Carbon::parse($start_date);
                $end = \Carbon\Carbon::parse($end_date);
                $weekendDays = collect(explode(',', $shift->weekend_days))->filter(fn($d) => $d !== '')->map('intval')->toArray();
                // Get holidays for this employee's company
                $companyHolidays = $holidaysByCompany[$employee->company_id] ?? collect();
                $isHoliday = function($date) use ($companyHolidays) {
                    foreach ($companyHolidays as $holiday) {
                        if ($date->betweenIncluded(\Carbon\Carbon::parse($holiday->start_date), \Carbon\Carbon::parse($holiday->end_date))) {
                            return true;
                        }
                    }
                    return false;
                };
                if ($shift->is_flexible && $shift->expected_hours) {
                    // expected_hours is daily, so multiply by number of non-weekend, non-holiday, non-leave days in range
                    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                        if (!in_array($date->dayOfWeek, $weekendDays) && !$isHoliday($date) && !$isOnLeave($date)) {
                            if ($shift->half_day_of_week !== null && $date->dayOfWeek == $shift->half_day_of_week && $shift->half_day_expected_hours) {
                                $expected += $shift->half_day_expected_hours;
                            } else {
                                $expected += $shift->expected_hours;
                            }
                        }
                    }
                } else {
                    // For each date in range, get weekday, fetch in/out, sum hours, skip weekends, holidays, and leave
                    $dayMap = [
                        0 => ['sunday_in', 'sunday_out'],
                        1 => ['monday_in', 'monday_out'],
                        2 => ['tuesday_in', 'tuesday_out'],
                        3 => ['wednesday_in', 'wednesday_out'],
                        4 => ['thursday_in', 'thursday_out'],
                        5 => ['friday_in', 'friday_out'],
                        6 => ['saturday_in', 'saturday_out'],
                    ];
                    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                        $weekday = $date->dayOfWeek;
                        if (in_array($weekday, $weekendDays) || $isHoliday($date) || $isOnLeave($date)) continue;
                        if ($shift->half_day_of_week !== null && $weekday == $shift->half_day_of_week && $shift->half_day_expected_hours) {
                            $expected += $shift->half_day_expected_hours;
                        } else {
                            [$in, $out] = $dayMap[$weekday];
                            $inTime = $shift->$in;
                            $outTime = $shift->$out;
                            if ($inTime && $outTime) {
                                $inParts = explode(':', $inTime);
                                $outParts = explode(':', $outTime);
                                if (count($inParts) === 2 && count($outParts) === 2) {
                                    $inMinutes = ((int)$inParts[0]) * 60 + ((int)$inParts[1]);
                                    $outMinutes = ((int)$outParts[0]) * 60 + ((int)$outParts[1]);
                                    $diff = $outMinutes - $inMinutes;
                                    if ($diff > 0) {
                                        $expected += $diff / 60;
                                    }
                                }
                            }
                        }
                    }
                }
            }
            $lackExtra = $totalLoggedHours - $expected;

            $finalRating = round((0.6 * $qualityScore) + (0.4 * ($totalLoggedHours && $expected ? min($totalLoggedHours / $expected, 1) * 100 : 0)), 2);

            return [
                'name' => $employee->firstname . ' ' . $employee->lastname,
                'role' => optional($employee->RoleUser)->name,
                'total_logged_hours' => round($totalLoggedHours, 2),
                'expected_hours' => round($expected, 2),
                'mode' => [
                    'office' => $officeHours,
                    'remote' => $remoteHours,
                ],
                'total_task_count' => $totalTasks,
                'tasks_completed' => $tasksCompleted,
                'lack_extra_time' => round($lackExtra, 2),
                'total_leave_left' => $employee->remaining_leave,
                'quality_score' => round($qualityScore, 2),
                'final_rating' => $finalRating,
            ];
        });

        // Pagination
        $total = $data->count();
        $pagedData = $data->slice(($page - 1) * $perPage, $perPage)->values();
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator($pagedData, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('report.kpi_summary_report', [
            'data' => $paginator,
            'search' => $search,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'employees' => $employees,
        ]);
    }

    /**
     * Export KPI Summary Report
     */
    public function export_kpi_summary_report(Request $request)
    {
        if (!auth()->user()->can('kpi_report')) {
            abort(403, 'Unauthorized');
        }

        $start_date = $request->input('start_date');
        $end_date = $request->input('end_date');
        $employee_id = $request->input('employee_id');
        $format = $request->get('format', 'csv');

        // Set default date range to this week if not provided
        if (empty($start_date) || empty($end_date)) {
            $today = Carbon::today();
            $monday = $today->copy()->startOfWeek(Carbon::MONDAY);
            $start_date = $start_date ?: $monday->format('Y-m-d');
            $end_date = $end_date ?: $today->format('Y-m-d');
        }

        // Get KPI data (same logic as the report)
        $data = $this->getKpiSummaryData($start_date, $end_date, $employee_id);

        if ($format === 'pdf') {
            return $this->exportKpiSummaryPDF($data, $start_date, $end_date);
        } else {
            return $this->exportKpiSummaryCSV($data, $start_date, $end_date);
        }
    }

    /**
     * Get KPI Summary Data
     */
    private function getKpiSummaryData(string $start_date, string $end_date, ?string $employee_id = null)
    {
        $employees = Employee::where('deleted_at', null)->with(['company', 'department']);

        if ($employee_id) {
            $employees->where('id', $employee_id);
        }

        $employees = $employees->get();
        $data = collect();

        foreach ($employees as $employee) {
            // Calculate KPI metrics for the employee
            $attendanceCount = \App\Models\Attendance::where('employee_id', $employee->id)
                ->whereBetween('date', [$start_date, $end_date])
                ->count();

            $tasksCompleted = \App\Models\Task::where('assigned_to', $employee->id)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->where('status', 'completed')
                ->count();

            $totalTasks = \App\Models\Task::where('assigned_to', $employee->id)
                ->whereBetween('created_at', [$start_date, $end_date])
                ->count();

            $projectsInvolved = \App\Models\EmployeeProject::where('employee_id', $employee->id)
                ->whereHas('project', function($q) use ($start_date, $end_date) {
                    $q->whereBetween('created_at', [$start_date, $end_date]);
                })
                ->count();

            $data->push([
                'employee_name' => $employee->firstname . ' ' . $employee->lastname,
                'company' => $employee->company?->name ?? '---',
                'department' => $employee->department?->department ?? '---',
                'attendance_days' => $attendanceCount,
                'tasks_completed' => $tasksCompleted,
                'total_tasks' => $totalTasks,
                'task_completion_rate' => $totalTasks > 0 ? round(($tasksCompleted / $totalTasks) * 100, 2) : 0,
                'projects_involved' => $projectsInvolved,
                'performance_score' => $this->calculatePerformanceScore($attendanceCount, $tasksCompleted, $totalTasks, $projectsInvolved)
            ]);
        }

        return $data;
    }

    /**
     * Calculate Performance Score
     */
    private function calculatePerformanceScore(int $attendance, int $completed, int $total, int $projects): float
    {
        $attendanceScore = min($attendance * 2, 40); // Max 40 points for attendance
        $taskScore = $total > 0 ? ($completed / $total) * 40 : 0; // Max 40 points for task completion
        $projectScore = min($projects * 5, 20); // Max 20 points for project involvement

        return round($attendanceScore + $taskScore + $projectScore, 2);
    }

    /**
     * Export KPI Summary as CSV
     */
    private function exportKpiSummaryCSV($data, string $start_date, string $end_date): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'kpi_summary_report_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ];

        $columns = [
            'Employee Name',
            'Company',
            'Department',
            'Attendance Days',
            'Tasks Completed',
            'Total Tasks',
            'Task Completion Rate (%)',
            'Projects Involved',
            'Performance Score'
        ];

        $callback = function () use ($data, $columns, $start_date, $end_date): void {
            $file = fopen('php://output', 'w');

            // Add header information
            fputcsv($file, ['KPI Summary Report']);
            fputcsv($file, ['Period: ' . $start_date . ' to ' . $end_date]);
            fputcsv($file, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
            fputcsv($file, ['Generated by: ' . auth()->user()->username ?? 'System']);
            fputcsv($file, ['Total Employees: ' . $data->count()]);
            fputcsv($file, []); // Empty row

            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row['employee_name'],
                    $row['company'],
                    $row['department'],
                    $row['attendance_days'],
                    $row['tasks_completed'],
                    $row['total_tasks'],
                    number_format($row['task_completion_rate'], 2),
                    $row['projects_involved'],
                    number_format($row['performance_score'], 2)
                ]);
            }

            // Add summary statistics
            fputcsv($file, []); // Empty row
            fputcsv($file, ['SUMMARY STATISTICS']);
            fputcsv($file, ['Average Performance Score', number_format($data->avg('performance_score'), 2)]);
            fputcsv($file, ['Average Task Completion Rate', number_format($data->avg('task_completion_rate'), 2) . '%']);
            fputcsv($file, ['Total Tasks Completed', $data->sum('tasks_completed')]);
            fputcsv($file, ['Total Projects Involved', $data->sum('projects_involved')]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export KPI Summary as PDF
     */
    private function exportKpiSummaryPDF($data, string $start_date, string $end_date): \Illuminate\Http\Response
    {
        $company = \App\Models\Company::first();

        // Calculate summary statistics
        $avgPerformanceScore = $data->avg('performance_score');
        $avgTaskCompletionRate = $data->avg('task_completion_rate');
        $totalTasksCompleted = $data->sum('tasks_completed');
        $totalProjectsInvolved = $data->sum('projects_involved');
        $topPerformer = $data->sortByDesc('performance_score')->first();

        $pdfData = [
            'data' => $data,
            'company' => $company,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'period' => $start_date . ' to ' . $end_date,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => auth()->user()->username ?? 'System',
            'total_employees' => $data->count(),
            'summary' => [
                'avg_performance_score' => round($avgPerformanceScore, 2),
                'avg_task_completion_rate' => round($avgTaskCompletionRate, 2),
                'total_tasks_completed' => $totalTasksCompleted,
                'total_projects_involved' => $totalProjectsInvolved,
                'top_performer' => $topPerformer ? $topPerformer['employee_name'] : 'N/A',
                'top_performer_score' => $topPerformer ? $topPerformer['performance_score'] : 0
            ]
        ];

        $pdf = \PDF::loadView('exports.kpi_summary_pdf', $pdfData);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('kpi_summary_report_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Monthly Salary Disbursement Report
     */
    public function monthly_salary_disbursement_report(Request $request)
    {
        if (!auth()->user()->can('salary_disbursement_report')) {
            abort(403, 'Unauthorized');
        }

        $month = $request->input('month', now()->format('Y-m'));
        $employee_id = $request->input('employee_id');
        $perPage = 15;
        $page = $request->input('page', 1);

        $start_date = $month . '-01';
        $end_date = \Carbon\Carbon::parse($start_date)->endOfMonth()->format('Y-m-d');

        // Get all employees for the dropdown
        $allEmployees = \App\Models\Employee::where('deleted_at', '=', null)
            ->orderBy('firstname')
            ->get()
            ->map(function($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->firstname . ' ' . $employee->lastname
                ];
            })
            ->values()
            ->toArray();

        $employeeQuery = \App\Models\Employee::with(['salaryDisbursements' => function($q) use ($month) {
            $q->where('month', $month)->with(['reviewer', 'approver', 'approver.employee', 'paidBy', 'paidBy.employee', 'adminResponder', 'adminResponder.employee']);
        }, 'office_shift', 'attendance' => function($q) use ($start_date, $end_date) {
            $q->whereBetween('date', [$start_date, $end_date]);
        }, 'leave' => function($q) use ($start_date, $end_date) {
            $q->where('status', 'approved')
              ->where(function($q2) use ($start_date, $end_date) {
                $q2->where('start_date', '<=', $end_date)
                   ->where('end_date', '>=', $start_date);
            });
        }])->where('deleted_at', '=', null);

        if ($employee_id) {
            $employeeQuery->where('id', $employee_id);
        }

        $employees = $employeeQuery->get();

        // Fetch holidays for all companies in the date range
        $holidaysByCompany = Holiday::where('deleted_at', '=', null)
            ->where(function($q) use ($start_date, $end_date) {
                $q->where(function($q2) use ($start_date, $end_date) {
                    $q2->where('start_date', '<=', $end_date)
                        ->where('end_date', '>=', $start_date);
                });
            })
            ->get()
            ->groupBy('company_id');

        // Get all leave types and their paid/unpaid status
        $leaveTypes = LeaveType::all()->keyBy('id');

        $data = $employees->map(function($employee) use ($month, $start_date, $end_date, $holidaysByCompany, $leaveTypes) {
            $disb = $employee->salaryDisbursements->first();
            $basic_salary = $disb ? $disb->basic_salary : $employee->basic_salary;
            $hourly_rate = $employee->hourly_rate ?? 0;

            // Calculate bonus/allowance for this employee for the month
            $bonus_allowance = BonusAllowance::where('employee_id', $employee->id)
                ->whereYear('created_at', '=', substr($month, 0, 4))
                ->whereMonth('created_at', '=', substr($month, 5, 2))
                ->get()
                ->sum(function($bonus) use ($basic_salary) {
                    if ($bonus->type === 'percentage') {
                        return ($basic_salary * $bonus->amount) / 100;
                    } else {
                        return $bonus->amount;
                    }
                });

            // Calculate expected hours (excluding weekends, holidays, approved leave)
            $shift = $employee->office_shift;
            $expected_hours = 0;
            $expected_days = 0;
            if ($shift) {
                $start = \Carbon\Carbon::parse($start_date);
                $end = \Carbon\Carbon::parse($end_date);
                $weekendDays = collect(explode(',', $shift->weekend_days))->filter(fn($d) => $d !== '')->map('intval')->toArray();
                $companyHolidays = $holidaysByCompany[$employee->company_id] ?? collect();
                $isHoliday = function($date) use ($companyHolidays) {
                    foreach ($companyHolidays as $holiday) {
                        if ($date->betweenIncluded(\Carbon\Carbon::parse($holiday->start_date), \Carbon\Carbon::parse($holiday->end_date))) {
                            return true;
                        }
                    }
                    return false;
                };
                // Build a set of leave dates for this employee in the month (approved only)
                $leaveDates = collect();
                foreach ($employee->leave as $leave) {
                    $leaveStart = \Carbon\Carbon::parse(max($leave->start_date, $start_date));
                    $leaveEnd = \Carbon\Carbon::parse(min($leave->end_date, $end_date));
                    for ($d = $leaveStart->copy(); $d->lte($leaveEnd); $d->addDay()) {
                        $leaveDates->push($d->format('Y-m-d'));
                    }
                }
                if ($shift->is_flexible && $shift->expected_hours) {
                    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                        if (
                            !in_array($date->dayOfWeek, $weekendDays) &&
                            !$isHoliday($date) &&
                            !$leaveDates->contains($date->format('Y-m-d'))
                        ) {
                            $expected_days++;
                            $expected_hours += $shift->expected_hours;
                        }
                    }
                } else {
                    $dayMap = [
                        0 => ['sunday_in', 'sunday_out'],
                        1 => ['monday_in', 'monday_out'],
                        2 => ['tuesday_in', 'tuesday_out'],
                        3 => ['wednesday_in', 'wednesday_out'],
                        4 => ['thursday_in', 'thursday_out'],
                        5 => ['friday_in', 'friday_out'],
                        6 => ['saturday_in', 'saturday_out'],
                    ];
                    for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
                        $weekday = $date->dayOfWeek;
                        if (
                            in_array($weekday, $weekendDays) ||
                            $isHoliday($date) ||
                            $leaveDates->contains($date->format('Y-m-d'))
                        ) continue;
                        [$in, $out] = $dayMap[$weekday];
                        $inTime = $shift->$in;
                        $outTime = $shift->$out;
                        if ($inTime && $outTime) {
                            $inParts = explode(':', $inTime);
                            $outParts = explode(':', $outTime);
                            if (count($inParts) === 2 && count($outParts) === 2) {
                                $inMinutes = ((int)$inParts[0]) * 60 + ((int)$inParts[1]);
                                $outMinutes = ((int)$outParts[0]) * 60 + ((int)$outParts[1]);
                                $diff = $outMinutes - $inMinutes;
                                if ($diff > 0) {
                                    $expected_days++;
                                    $expected_hours += $diff / 60;
                                }
                            }
                        }
                    }
                }
            }

            // Calculate total logged hours from attendance
            $totalMinutes = 0;
            foreach ($employee->attendance as $att) {
                if (empty($att->total_work)) continue;
                [$h, $m] = array_pad(explode(':', $att->total_work), 2, 0);
                $minutes = ((int)$h) * 60 + ((int)$m);
                $totalMinutes += $minutes;
            }
            $total_logged_hours = round($totalMinutes / 60, 2);

            // Calculate adjustments using the same logic as leave_deductions, but preserve sign
            $adjustments = 0;
            if ($expected_hours > 0) {
                $hourly_salary = $basic_salary / $expected_hours;
                $adjustments = round(($total_logged_hours - $expected_hours) * $hourly_salary, 2);
            }

            // Calculate leave deduction
            $leave_deductions = 0;

            $gross_salary = $basic_salary + $adjustments + $bonus_allowance;
            $net_payable = $gross_salary - $leave_deductions;

            return [
                'id' => $disb ? $disb->id : null,
                'employee_id' => $employee->id,
                'employee_name' => $employee->firstname . ' ' . $employee->lastname,
                'basic_salary' => $basic_salary,
                'adjustments' => $adjustments,
                'leave_deductions' => $leave_deductions,
                'bonus_allowance' => $bonus_allowance,
                'gross_salary' => $gross_salary,
                'net_payable' => $net_payable,
                'paid' => $disb ? ($disb->paid ? 'Yes' : 'No') : 'No',
                'payment_date' => $disb ? ($disb->payment_date ? $disb->payment_date->format('Y-m-d') : '') : '',
                'status' => $disb ? $disb->status : 'pending',
                'reviewed_by' => $disb ? $disb->reviewed_by : null,
                'reviewer_name' => $disb && $disb->reviewer ? ($disb->reviewer->firstname . ' ' . $disb->reviewer->lastname) : null,
                'reviewed_at' => $disb && $disb->reviewed_at ? $disb->reviewed_at->format('Y-m-d H:i:s') : null,
                'approved_by' => $disb ? $disb->approved_by : null,
                'approver_name' => $disb && $disb->approver ?
                    ($disb->approver->employee ?
                        ($disb->approver->employee->firstname . ' ' . $disb->approver->employee->lastname) :
                        $disb->approver->username
                    ) : null,
                'approved_at' => $disb && $disb->approved_at ? $disb->approved_at->format('Y-m-d H:i:s') : null,
                'paid_by' => $disb ? $disb->paid_by : null,
                'paid_by_name' => $disb && $disb->paidBy ?
                    ($disb->paidBy->employee ?
                        ($disb->paidBy->employee->firstname . ' ' . $disb->paidBy->employee->lastname) :
                        $disb->paidBy->username
                    ) : null,
                'paid_at' => $disb && $disb->paid_at ? $disb->paid_at->format('Y-m-d H:i:s') : null,
                'feedback' => $disb ? $disb->feedback : null,
                'feedback_at' => $disb && $disb->feedback_at ? $disb->feedback_at->format('Y-m-d H:i:s') : null,
                'admin_response' => $disb ? $disb->admin_response : null,
                'admin_response_by' => $disb ? $disb->admin_response_by : null,
                'admin_response_by_name' => $disb && $disb->adminResponder ?
                    ($disb->adminResponder->employee ?
                        ($disb->adminResponder->employee->firstname . ' ' . $disb->adminResponder->employee->lastname) :
                        $disb->adminResponder->username
                    ) : null,
                'admin_response_at' => $disb && $disb->admin_response_at ? $disb->admin_response_at->format('Y-m-d H:i:s') : null,
                'can_review' => auth()->user()->can('review_salary_disbursement'),
                'can_approve' => auth()->user()->can('approve_salary_disbursement'),
                'can_mark_paid' => auth()->user()->can('mark_salary_paid'),
            ];
        });

        // Pagination
        $total = $data->count();
        $pagedData = $data->slice(($page - 1) * $perPage, $perPage)->values();
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator($pagedData, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('report.monthly_salary_disbursement_report', [
            'data' => $paginator,
            'month' => $month,
            'employees' => $allEmployees,
            'employee_id' => $employee_id,
            'user' => auth()->user(),
        ]);
    }

    /**
     * Export Employee Report
     */
    public function export_employee_report(Request $request)
    {
        $user_auth = auth()->user();
        if (!$user_auth->can('employee_report')) {
            return abort('403', __('You are not authorized'));
        }

        $helpers = new helpers();
        $param = array(0 => '=', 1 => '=', 2 => '=');
        $columns = array(0 => 'company_id', 1 => 'department_id', 2 => 'designation_id');

        $employees = Employee::where('deleted_at', '=', null)
            ->with('company:id,name', 'department:id,department', 'designation:id,designation', 'office_shift:id,name')
            ->orderBy('id', 'desc');

        // Apply filters
        $employees_filtered = $helpers->filter($employees, $columns, $param, $request)->get();

        $format = $request->get('format', 'csv');

        if ($format === 'pdf') {
            return $this->exportEmployeeReportPDF($employees_filtered);
        } else {
            return $this->exportEmployeeReportCSV($employees_filtered);
        }
    }

    /**
     * Export Employee Report as CSV
     */
    private function exportEmployeeReportCSV($employees): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'employee_report_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ];

        $columns = [
            'Employee ID',
            'Full Name',
            'Email',
            'Phone',
            'Employment Type',
            'Company',
            'Department',
            'Designation',
            'Office Shift',
            'Joining Date',
            'Status'
        ];

        $callback = function () use ($employees, $columns): void {
            $file = fopen('php://output', 'w');

            // Add header information
            fputcsv($file, ['Employee Report']);
            fputcsv($file, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
            fputcsv($file, ['Generated by: ' . auth()->user()->username]);
            fputcsv($file, ['Total Employees: ' . $employees->count()]);
            fputcsv($file, []); // Empty row

            fputcsv($file, $columns);

            foreach ($employees as $employee) {
                fputcsv($file, [
                    $employee->id,
                    trim(($employee->firstname ?? '') . ' ' . ($employee->lastname ?? '')),
                    $employee->email ?? '',
                    $employee->phone ?? '',
                    $employee->employment_type ?? '',
                    $employee->company?->name ?? '',
                    $employee->department?->department ?? '',
                    $employee->designation?->designation ?? '',
                    $employee->office_shift?->name ?? '',
                    $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('Y-m-d') : '',
                    $employee->is_active ? 'Active' : 'Inactive'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Employee Report as PDF
     */
    private function exportEmployeeReportPDF($employees): \Illuminate\Http\Response
    {
        $company = \App\Models\Company::first();

        // Calculate additional statistics
        $activeEmployees = $employees->where('is_active', 1)->count();
        $inactiveEmployees = $employees->where('is_active', 0)->count();
        $companiesCount = $employees->groupBy('company_id')->count();
        $departmentsCount = $employees->groupBy('department_id')->count();

        $data = [
            'employees' => $employees,
            'company' => $company,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => auth()->user()->username ?? 'System',
            'total_employees' => $employees->count(),
            'statistics' => [
                'active_employees' => $activeEmployees,
                'inactive_employees' => $inactiveEmployees,
                'companies_count' => $companiesCount,
                'departments_count' => $departmentsCount,
                'activity_rate' => $employees->count() > 0 ? round(($activeEmployees / $employees->count()) * 100, 2) : 0
            ]
        ];

        $pdf = \PDF::loadView('exports.employee_report_pdf', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('employee_report_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Export Monthly Salary Disbursement Report
     */
    public function export_salary_disbursement_report(Request $request)
    {
        if (!auth()->user()->can('salary_disbursement_report')) {
            abort(403, 'Unauthorized');
        }

        $month = $request->input('month', now()->format('Y-m'));
        $employee_id = $request->input('employee_id');
        $format = $request->get('format', 'csv');

        $start_date = $month . '-01';
        $end_date = \Carbon\Carbon::parse($start_date)->endOfMonth()->format('Y-m-d');

        // Get salary disbursement data (same logic as the report)
        $data = $this->getSalaryDisbursementData($month, $employee_id, $start_date, $end_date);

        if ($format === 'pdf') {
            return $this->exportSalaryDisbursementPDF($data, $month);
        } else {
            return $this->exportSalaryDisbursementCSV($data, $month);
        }
    }

    /**
     * Get salary disbursement data for export
     */
    private function getSalaryDisbursementData($month, $employee_id, $start_date, $end_date)
    {
        $employees = \App\Models\Employee::where('deleted_at', '=', null);

        if ($employee_id) {
            $employees->where('id', $employee_id);
        }

        $employees = $employees->orderBy('firstname')->get();

        $data = collect();
        foreach ($employees as $employee) {
            // Get existing disbursement or create default
            $disbursement = \App\Models\SalaryDisbursement::where('employee_id', $employee->id)
                ->where('month', $month)
                ->first();

            $basic_salary = $employee->basic_salary ?? 0;
            $adjustments = $disbursement ? $disbursement->adjustments : 0;
            $leave_deductions = $disbursement ? $disbursement->leave_deductions : 0;
            $bonus_allowance = $disbursement ? $disbursement->bonus_allowance : 0;
            $gross_salary = $basic_salary + $adjustments + $bonus_allowance - $leave_deductions;
            $net_payable = $gross_salary;

            $data->push([
                'employee_name' => $employee->firstname . ' ' . $employee->lastname,
                'employee_id' => $employee->id,
                'basic_salary' => $basic_salary,
                'adjustments' => $adjustments,
                'leave_deductions' => $leave_deductions,
                'bonus_allowance' => $bonus_allowance,
                'gross_salary' => $gross_salary,
                'net_payable' => $net_payable,
                'status' => $disbursement ? $disbursement->status : 'pending',
                'reviewed_at' => $disbursement ? $disbursement->reviewed_at : null,
                'approved_at' => $disbursement ? $disbursement->approved_at : null,
                'paid_at' => $disbursement ? $disbursement->paid_at : null,
            ]);
        }

        return $data;
    }

    /**
     * Export Salary Disbursement as CSV
     */
    private function exportSalaryDisbursementCSV($data, string $month): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'salary_disbursement_' . $month . '_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ];

        $columns = [
            'Employee Name',
            'Basic Salary',
            'Adjustments',
            'Leave Deductions',
            'Bonus/Allowance',
            'Gross Salary',
            'Net Payable',
            'Status',
            'Reviewed Date',
            'Approved Date',
            'Paid Date'
        ];

        $callback = function () use ($data, $columns, $month): void {
            $file = fopen('php://output', 'w');

            // Add header information
            fputcsv($file, ['Monthly Salary Disbursement Report']);
            fputcsv($file, ['Month: ' . \Carbon\Carbon::parse($month . '-01')->format('F Y')]);
            fputcsv($file, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
            fputcsv($file, ['Generated by: ' . auth()->user()->username ?? 'System']);
            fputcsv($file, ['Total Employees: ' . $data->count()]);
            fputcsv($file, []); // Empty row

            fputcsv($file, $columns);

            $total_basic = 0.0;
            $total_adjustments = 0.0;
            $total_deductions = 0.0;
            $total_bonus = 0.0;
            $total_gross = 0.0;
            $total_net = 0.0;

            foreach ($data as $row) {
                $basicSalary = (float)($row['basic_salary'] ?? 0);
                $adjustments = (float)($row['adjustments'] ?? 0);
                $deductions = (float)($row['leave_deductions'] ?? 0);
                $bonus = (float)($row['bonus_allowance'] ?? 0);
                $gross = (float)($row['gross_salary'] ?? 0);
                $net = (float)($row['net_payable'] ?? 0);

                fputcsv($file, [
                    $row['employee_name'] ?? 'Unknown',
                    number_format($basicSalary, 2),
                    number_format($adjustments, 2),
                    number_format($deductions, 2),
                    number_format($bonus, 2),
                    number_format($gross, 2),
                    number_format($net, 2),
                    ucfirst(str_replace('_', ' ', $row['status'] ?? 'pending')),
                    $row['reviewed_at'] ? \Carbon\Carbon::parse($row['reviewed_at'])->format('Y-m-d') : '---',
                    $row['approved_at'] ? \Carbon\Carbon::parse($row['approved_at'])->format('Y-m-d') : '---',
                    $row['paid_at'] ? \Carbon\Carbon::parse($row['paid_at'])->format('Y-m-d') : '---'
                ]);

                $total_basic += $basicSalary;
                $total_adjustments += $adjustments;
                $total_deductions += $deductions;
                $total_bonus += $bonus;
                $total_gross += $gross;
                $total_net += $net;
            }

            // Add totals row
            fputcsv($file, []); // Empty row
            fputcsv($file, [
                'TOTALS',
                number_format($total_basic, 2),
                number_format($total_adjustments, 2),
                number_format($total_deductions, 2),
                number_format($total_bonus, 2),
                number_format($total_gross, 2),
                number_format($total_net, 2),
                '', '', '', ''
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Salary Disbursement as PDF
     */
    private function exportSalaryDisbursementPDF($data, string $month): \Illuminate\Http\Response
    {
        $company = \App\Models\Company::first();

        // Calculate totals with proper type casting
        $totals = [
            'basic_salary' => (float)$data->sum('basic_salary'),
            'adjustments' => (float)$data->sum('adjustments'),
            'leave_deductions' => (float)$data->sum('leave_deductions'),
            'bonus_allowance' => (float)$data->sum('bonus_allowance'),
            'gross_salary' => (float)$data->sum('gross_salary'),
            'net_payable' => (float)$data->sum('net_payable'),
        ];

        // Calculate additional statistics
        $approvedCount = $data->where('status', 'approved')->count();
        $paidCount = $data->where('status', 'paid')->count();
        $pendingCount = $data->where('status', 'pending')->count();

        $pdfData = [
            'data' => $data,
            'company' => $company,
            'month' => $month,
            'month_name' => \Carbon\Carbon::parse($month . '-01')->format('F Y'),
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => auth()->user()->username ?? 'System',
            'totals' => $totals,
            'total_employees' => $data->count(),
            'statistics' => [
                'approved_count' => $approvedCount,
                'paid_count' => $paidCount,
                'pending_count' => $pendingCount,
                'completion_rate' => $data->count() > 0 ? round(($paidCount / $data->count()) * 100, 2) : 0
            ]
        ];

        $pdf = \PDF::loadView('exports.salary_disbursement_pdf', $pdfData);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('salary_disbursement_' . $month . '_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Export Attendance Report
     */
    public function export_attendance_report(Request $request)
    {
        $user_auth = auth()->user();
        $employee_id = $request->get('employee_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $format = $request->get('format', 'csv');

        $query = \App\Models\Attendance::where('deleted_at', '=', null)
            ->with('employee:id,firstname,lastname,username');

        if ($employee_id) {
            $query->where('employee_id', $employee_id);
        } elseif ($user_auth->role_users_id != 1) {
            $query->where('employee_id', $user_auth->id);
        }

        if ($start_date) {
            $query->where('date', '>=', $start_date);
        }
        if ($end_date) {
            $query->where('date', '<=', $end_date);
        }

        $attendances = $query->orderBy('date', 'desc')->get();

        if ($format === 'pdf') {
            return $this->exportAttendanceReportPDF($attendances, $start_date, $end_date);
        } else {
            return $this->exportAttendanceReportCSV($attendances, $start_date, $end_date);
        }
    }

    /**
     * Export Attendance Report as CSV
     */
    private function exportAttendanceReportCSV($attendances, ?string $start_date, ?string $end_date): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'attendance_report_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ];

        $columns = [
            'Employee Name',
            'Date',
            'Clock In',
            'Clock Out',
            'Break Time',
            'Total Work',
            'Total Rest',
            'Status'
        ];

        $callback = function () use ($attendances, $columns, $start_date, $end_date): void {
            $file = fopen('php://output', 'w');

            // Add header information
            fputcsv($file, ['Attendance Report']);
            if ($start_date && $end_date) {
                fputcsv($file, ['Period: ' . $start_date . ' to ' . $end_date]);
            }
            fputcsv($file, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
            fputcsv($file, ['Generated by: ' . auth()->user()->username]);
            fputcsv($file, []); // Empty row

            fputcsv($file, $columns);

            foreach ($attendances as $attendance) {
                fputcsv($file, [
                    $attendance->employee ? $attendance->employee->firstname . ' ' . $attendance->employee->lastname : 'Unknown',
                    $attendance->date,
                    $attendance->clock_in ?? '---',
                    $attendance->clock_out ?? '---',
                    $attendance->break_time ?? '00:00',
                    $attendance->total_work ?? '00:00',
                    $attendance->total_rest ?? '00:00',
                    $attendance->status ?? 'Present'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Attendance Report as PDF
     */
    private function exportAttendanceReportPDF($attendances, ?string $start_date, ?string $end_date): \Illuminate\Http\Response
    {
        $company = \App\Models\Company::first();

        // Calculate summary statistics
        $totalDays = $attendances->count();
        $presentDays = $attendances->where('status', '!=', 'Absent')->count();
        $absentDays = $attendances->where('status', 'Absent')->count();
        $lateDays = $attendances->where('status', 'Late')->count();

        // Calculate total work hours
        $totalWorkMinutes = 0;
        foreach ($attendances as $attendance) {
            if ($attendance->total_work) {
                [$hours, $minutes] = explode(':', $attendance->total_work);
                $totalWorkMinutes += ((int)$hours) * 60 + ((int)$minutes);
            }
        }

        $totalWorkHours = sprintf('%02d:%02d', intdiv($totalWorkMinutes, 60), $totalWorkMinutes % 60);

        $data = [
            'attendances' => $attendances,
            'company' => $company,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'period' => $start_date && $end_date ? $start_date . ' to ' . $end_date : 'All Records',
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => auth()->user()->username,
            'summary' => [
                'total_days' => $totalDays,
                'present_days' => $presentDays,
                'absent_days' => $absentDays,
                'late_days' => $lateDays,
                'total_work_hours' => $totalWorkHours,
                'attendance_rate' => $totalDays > 0 ? round(($presentDays / $totalDays) * 100, 2) : 0
            ]
        ];

        $pdf = \PDF::loadView('exports.attendance_report_pdf', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('attendance_report_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Leave & Absence Report
     */
    public function leave_absence_report(Request $request)
    {
        if (!auth()->user()->can('leave_absence_report')) {
            abort(403, 'Unauthorized');
        }

        $search = $request->input('search');
        $perPage = 15;
        $page = $request->input('page', 1);

        $leaveQuery = \App\Models\Leave::with(['employee', 'leave_type'])
            ->where('deleted_at', '=', null);

        if ($search) {
            $leaveQuery->whereHas('employee', function($q) use ($search) {
                $q->where('firstname', 'like', "%$search%")
                  ->orWhere('lastname', 'like', "%$search%")
                  ->orWhere('username', 'like', "%$search%") ;
            });
        }

        $leaves = $leaveQuery->orderBy('id', 'desc')->get();

        $data = $leaves->map(function($leave) {
            $employee = $leave->employee;
            $leaveType = $leave->leave_type;
            $from = \Carbon\Carbon::parse($leave->start_date);
            $to = \Carbon\Carbon::parse($leave->end_date);
            $total_days = $from->diffInDays($to) + 1;
            $balance = $employee ? $employee->remaining_leave : null;
            return [
                'employee_name' => $employee ? ($employee->firstname . ' ' . $employee->lastname) : '',
                'leave_type' => $leaveType ? $leaveType->title : '',
                'from_date' => $from->format('Y-m-d'),
                'to_date' => $to->format('Y-m-d'),
                'total_days' => $total_days,
                'status' => $leave->status,
                'balance_days_left' => $balance,
            ];
        });

        $total = $data->count();
        $pagedData = $data->slice(($page - 1) * $perPage, $perPage)->values();
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator($pagedData, $total, $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        return view('report.leave_absence_report', [
            'data' => $paginator,
            'search' => $search,
        ]);
    }

    /**
     * Export Leave & Absence Report
     */
    public function export_leave_absence_report(Request $request)
    {
        if (!auth()->user()->can('leave_absence_report')) {
            abort(403, 'Unauthorized');
        }

        $search = $request->input('search');
        $format = $request->get('format', 'csv');

        $leaveQuery = \App\Models\Leave::with(['employee', 'leave_type'])
            ->where('deleted_at', '=', null);

        if ($search) {
            $leaveQuery->whereHas('employee', function($q) use ($search) {
                $q->where('firstname', 'like', "%$search%")
                  ->orWhere('lastname', 'like', "%$search%")
                  ->orWhere('username', 'like', "%$search%");
            });
        }

        $leaves = $leaveQuery->orderBy('id', 'desc')->get();

        $data = $leaves->map(function($leave) {
            $employee = $leave->employee;
            $leaveType = $leave->leave_type;
            $from = \Carbon\Carbon::parse($leave->start_date);
            $to = \Carbon\Carbon::parse($leave->end_date);
            $total_days = $from->diffInDays($to) + 1;
            $balance = $employee ? $employee->remaining_leave : null;
            return [
                'employee_name' => $employee ? ($employee->firstname . ' ' . $employee->lastname) : 'Unknown',
                'leave_type' => $leaveType ? $leaveType->title : 'Unknown',
                'from_date' => $from->format('Y-m-d'),
                'to_date' => $to->format('Y-m-d'),
                'total_days' => $total_days,
                'status' => $leave->status,
                'balance_days_left' => $balance,
                'reason' => $leave->reason ?? '',
                'applied_date' => $leave->created_at ? $leave->created_at->format('Y-m-d') : '',
            ];
        });

        if ($format === 'pdf') {
            return $this->exportLeaveAbsencePDF($data, $search);
        } else {
            return $this->exportLeaveAbsenceCSV($data, $search);
        }
    }

    /**
     * Export Leave & Absence as CSV
     */
    private function exportLeaveAbsenceCSV($data, ?string $search = null): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = 'leave_absence_report_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=$filename",
        ];

        $columns = [
            'Employee Name',
            'Leave Type',
            'From Date',
            'To Date',
            'Total Days',
            'Status',
            'Balance Days Left',
            'Reason',
            'Applied Date'
        ];

        $callback = function () use ($data, $columns, $search): void {
            $file = fopen('php://output', 'w');

            // Add header information
            fputcsv($file, ['Leave & Absence Report']);
            if ($search) {
                fputcsv($file, ['Search Filter: ' . $search]);
            }
            fputcsv($file, ['Generated: ' . now()->format('Y-m-d H:i:s')]);
            fputcsv($file, ['Generated by: ' . auth()->user()->username ?? 'System']);
            fputcsv($file, ['Total Records: ' . $data->count()]);
            fputcsv($file, []); // Empty row

            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, [
                    $row['employee_name'],
                    $row['leave_type'],
                    $row['from_date'],
                    $row['to_date'],
                    $row['total_days'],
                    ucfirst($row['status']),
                    $row['balance_days_left'] ?? '---',
                    $row['reason'],
                    $row['applied_date']
                ]);
            }

            // Add summary statistics
            fputcsv($file, []); // Empty row
            fputcsv($file, ['SUMMARY STATISTICS']);
            fputcsv($file, ['Total Leave Days Requested', $data->sum('total_days')]);
            fputcsv($file, ['Approved Leaves', $data->where('status', 'approved')->count()]);
            fputcsv($file, ['Pending Leaves', $data->where('status', 'pending')->count()]);
            fputcsv($file, ['Rejected Leaves', $data->where('status', 'rejected')->count()]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export Leave & Absence as PDF
     */
    private function exportLeaveAbsencePDF($data, ?string $search = null): \Illuminate\Http\Response
    {
        $company = \App\Models\Company::first();

        // Calculate summary statistics
        $totalDaysRequested = $data->sum('total_days');
        $approvedCount = $data->where('status', 'approved')->count();
        $pendingCount = $data->where('status', 'pending')->count();
        $rejectedCount = $data->where('status', 'rejected')->count();
        $leaveTypes = $data->groupBy('leave_type')->map->count();

        $pdfData = [
            'data' => $data,
            'company' => $company,
            'search' => $search,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => auth()->user()->username ?? 'System',
            'total_records' => $data->count(),
            'summary' => [
                'total_days_requested' => $totalDaysRequested,
                'approved_count' => $approvedCount,
                'pending_count' => $pendingCount,
                'rejected_count' => $rejectedCount,
                'approval_rate' => $data->count() > 0 ? round(($approvedCount / $data->count()) * 100, 2) : 0,
                'leave_types' => $leaveTypes
            ]
        ];

        $pdf = \PDF::loadView('exports.leave_absence_pdf', $pdfData);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->download('leave_absence_report_' . date('Ymd_His') . '.pdf');
    }

    /**
     * Test PDF Export Generation
     */
    public function test_pdf_exports()
    {
        if (!auth()->user()->can('employee_report')) {
            abort(403, 'Unauthorized');
        }

        $company = \App\Models\Company::first();
        $testData = [
            'company' => $company,
            'generated_at' => now()->format('Y-m-d H:i:s'),
            'generated_by' => auth()->user()->username ?? 'System',
            'total_employees' => 10,
            'statistics' => [
                'active_employees' => 8,
                'inactive_employees' => 2,
                'companies_count' => 1,
                'departments_count' => 3,
                'activity_rate' => 80.0
            ]
        ];

        // Test if PDF can be generated
        try {
            $pdf = \PDF::loadView('exports.employee_report_pdf', $testData);
            $pdf->setPaper('A4', 'landscape');

            return response()->json([
                'status' => 'success',
                'message' => 'PDF export system is working correctly',
                'company_name' => $company ? $company->name : 'Onchain Software & Research Limited',
                'templates_available' => [
                    'employee_report_pdf' => file_exists(resource_path('views/exports/employee_report_pdf.blade.php')),
                    'salary_disbursement_pdf' => file_exists(resource_path('views/exports/salary_disbursement_pdf.blade.php')),
                    'attendance_report_pdf' => file_exists(resource_path('views/exports/attendance_report_pdf.blade.php')),
                    'kpi_summary_pdf' => file_exists(resource_path('views/exports/kpi_summary_pdf.blade.php')),
                    'job_vacancies_pdf' => file_exists(resource_path('views/exports/job_vacancies_pdf.blade.php')),
                    'leave_absence_pdf' => file_exists(resource_path('views/exports/leave_absence_pdf.blade.php'))
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'PDF export system error: ' . $e->getMessage(),
                'company_name' => $company ? $company->name : 'Onchain Software & Research Limited'
            ], 500);
        }
    }

    /**
     * Approve salary disbursement (AJAX)
     */
    public function approveSalaryDisbursement(Request $request)
    {
        $request->validate([
            'disbursement_id' => 'required|integer',
        ]);
        $disb = SalaryDisbursement::find($request->disbursement_id);
        if (!$disb) {
            return response()->json(['success' => false, 'message' => 'Salary disbursement not found.']);
        }
        // Only admin can approve
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        $disb->status = 'approved';
        $disb->save();
        // Notify employee
        if ($disb->employee && $disb->employee->user) {
            $disb->employee->user->notify(new \App\Notifications\SalaryDisbursed($disb, 'approved'));
        }
        return response()->json(['success' => true]);
    }

    /**
     * Mark salary as paid (AJAX)
     */
    public function markSalaryAsPaid(Request $request)
    {
        $request->validate([
            'disbursement_id' => 'required|integer',
        ]);
        $disb = SalaryDisbursement::find($request->disbursement_id);
        if (!$disb) {
            return response()->json(['success' => false, 'message' => 'Salary disbursement not found.']);
        }
        // Only admin can mark as paid
        if (!auth()->user()->hasRole('admin')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized.'], 403);
        }
        if ($disb->status !== 'approved') {
            return response()->json(['success' => false, 'message' => 'Salary must be approved before marking as paid.'], 400);
        }
        $disb->status = 'paid';
        $disb->paid = true;
        $disb->payment_date = now();
        $disb->save();
        // Notify employee
        if ($disb->employee && $disb->employee->user) {
            $disb->employee->user->notify(new \App\Notifications\SalaryDisbursed($disb, 'paid'));
        }
        return response()->json(['success' => true, 'payment_date' => $disb->payment_date->format('Y-m-d')]);
    }
}
