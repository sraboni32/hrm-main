<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Company;
use App\Models\Task;
use App\Models\TaskDiscussion;
use App\Models\TaskDocument;
use App\Models\TaskLink;
use App\Models\Employee;
use App\Models\EmployeeTask;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskUpdated;
use App\Notifications\TaskCommented;
use App\Notifications\TaskCompleted;

class TaskController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user_auth = auth()->user();
        if ($user_auth->can('task_view_own')) {
            // Show only tasks assigned to this user
            $tasks = Task::whereHas('assignedEmployees', function($q) use ($user_auth) {
                    $q->where('employee_id', $user_auth->id);
                })
                ->with('company:id,name','project:id,title','assignedEmployees:id,firstname,lastname')
                ->orderBy('id', 'desc');

            // Apply filters
            if ($request->filled('title')) {
                $tasks->where('title', 'like', '%' . $request->title . '%');
            }
            if ($request->filled('project_id') && $request->project_id != '0') {
                $tasks->where('project_id', $request->project_id);
            }
            if ($request->filled('company_id') && $request->company_id != '0') {
                $tasks->where('company_id', $request->company_id);
            }
            if ($request->filled('status') && $request->status != '0') {
                $tasks->where('status', $request->status);
            }
            if ($request->filled('priority') && $request->priority != '0') {
                $tasks->where('priority', $request->priority);
            }
            if ($request->filled('employee_id') && $request->employee_id != '0') {
                $tasks->whereHas('assignedEmployees', function($q) use ($request) {
                    $q->where('employee_id', $request->employee_id);
                });
            }
            if ($request->filled('start_date')) {
                $tasks->where('start_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $tasks->where('end_date', '<=', $request->end_date);
            }

            $tasks = $tasks->get();
        } elseif ($user_auth->can('task_view')) {
            // Show all tasks
            $tasks = Task::with('company:id,name','project:id,title','assignedEmployees:id,firstname,lastname')
                ->orderBy('id', 'desc');

            // Apply filters
            if ($request->filled('title')) {
                $tasks->where('title', 'like', '%' . $request->title . '%');
            }
            if ($request->filled('project_id') && $request->project_id != '0') {
                $tasks->where('project_id', $request->project_id);
            }
            if ($request->filled('company_id') && $request->company_id != '0') {
                $tasks->where('company_id', $request->company_id);
            }
            if ($request->filled('status') && $request->status != '0') {
                $tasks->where('status', $request->status);
            }
            if ($request->filled('priority') && $request->priority != '0') {
                $tasks->where('priority', $request->priority);
            }
            if ($request->filled('employee_id') && $request->employee_id != '0') {
                $tasks->whereHas('assignedEmployees', function($q) use ($request) {
                    $q->where('employee_id', $request->employee_id);
                });
            }
            if ($request->filled('start_date')) {
                $tasks->where('start_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $tasks->where('end_date', '<=', $request->end_date);
            }

            $tasks = $tasks->get();
        } else {
            return abort('403', __('You are not authorized'));
        }

        $count_not_started = $tasks->where('status', 'not_started')->count();
        $count_in_progress = $tasks->where('status', 'progress')->count();
        $count_cancelled = $tasks->where('status', 'cancelled')->count();
        $count_completed = $tasks->where('status', 'completed')->count();

        // Get data for filters
        $projects = Project::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','title']);
        $companies = Company::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','name']);
        $employees = Employee::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','firstname','lastname']);

        return view('task.task_list', compact(
            'tasks',
            'count_not_started',
            'count_in_progress',
            'count_cancelled',
            'count_completed',
            'projects',
            'companies',
            'employees'
        ));
    }

    public function tasks_kanban()
    {
        $user_auth = auth()->user();
        if ($user_auth->can('task_view_own')) {
            // Show only tasks assigned to this user
            $tasks_not_started = Task::where('status', '=', 'not_started')
                ->whereHas('assignedEmployees', function($q) use ($user_auth) {
                    $q->where('employee_id', $user_auth->id);
                })
                ->with('project:id,title')
                ->orderBy('id', 'desc')
                ->get();

            $tasks_in_progress = Task::where('status', '=', 'progress')
                ->whereHas('assignedEmployees', function($q) use ($user_auth) {
                    $q->where('employee_id', $user_auth->id);
                })
                ->with('project:id,title')
                ->orderBy('id', 'desc')
                ->get();

            $tasks_cancelled = Task::where('status', '=', 'cancelled')
                ->whereHas('assignedEmployees', function($q) use ($user_auth) {
                    $q->where('employee_id', $user_auth->id);
                })
                ->with('project:id,title')
                ->orderBy('id', 'desc')
                ->get();

            $tasks_completed = Task::where('status', '=', 'completed')
                ->whereHas('assignedEmployees', function($q) use ($user_auth) {
                    $q->where('employee_id', $user_auth->id);
                })
                ->with('project:id,title')
                ->orderBy('id', 'desc')
                ->get();

            $tasks_hold = Task::where('status', '=', 'hold')
                ->whereHas('assignedEmployees', function($q) use ($user_auth) {
                    $q->where('employee_id', $user_auth->id);
                })
                ->with('project:id,title')
                ->orderBy('id', 'desc')
                ->get();

            return view('task.kanban_task', compact('tasks_not_started','tasks_in_progress','tasks_cancelled','tasks_completed','tasks_hold'));
        } elseif ($user_auth->can('task_view')) {
            // Show all tasks for users with task_view permission
            $tasks_not_started = Task::where('status', '=', 'not_started')
                ->with('project:id,title')
                ->orderBy('id', 'desc')
                ->get();

            $tasks_in_progress = Task::where('status', '=', 'progress')
                ->with('project:id,title')
                ->orderBy('id', 'desc')
                ->get();

            $tasks_cancelled = Task::where('status', '=', 'cancelled')
                ->with('project:id,title')
                ->orderBy('id', 'desc')
                ->get();

            $tasks_completed = Task::where('status', '=', 'completed')
                ->with('project:id,title')
                ->orderBy('id', 'desc')
                ->get();

            $tasks_hold = Task::where('status', '=', 'hold')
                ->with('project:id,title')
                ->orderBy('id', 'desc')
                ->get();

            return view('task.kanban_task', compact('tasks_not_started','tasks_in_progress','tasks_cancelled','tasks_completed','tasks_hold'));
        }
        return abort('403', __('You are not authorized'));
    }

    public function task_change_status(Request $request)
    {
        $user_auth = auth()->user();
        if ($user_auth->can('kanban_task')) {
            $task = Task::where('deleted_at', '=', null)->findOrFail($request->task_id);
            $wasCompleted = $task->status === 'completed';

            Task::whereId($request->task_id)->update([
                'status' => $request['status'],
                'completed_at' => $request['status'] === 'completed' ? Carbon::now() : null,
            ]);

            // If status changed to completed, notify all assigned employees and the assigner
            if (!$wasCompleted && $request['status'] === 'completed') {
                $employees = $task->assignedEmployees;
                foreach ($employees as $employee) {
                    if ($employee->user) {
                        $employee->user->notify(new TaskCompleted($task, $user_auth));
                    }
                }
                if ($task->created_by && (!$employees->contains('user_id', $task->created_by))) {
                    $assigner = \App\Models\User::find($task->created_by);
                    if ($assigner) {
                        $assigner->notify(new TaskCompleted($task, $user_auth));
                    }
                }
            }

            return response()->json(['success' => true]);
        }
        return abort('403', __('You are not authorized'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user_auth = auth()->user();
		if ($user_auth->can('task_add')){

            $projects = Project::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','title']);
            $companies = Company::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','name']);

            return view('task.create_task', compact('projects','companies'));

        }
        return abort('403', __('You are not authorized'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('task_add')){

            $request->validate([
                'title'           => 'required|string|max:255',
                'summary'         => 'required|string|max:255',
                'project_id'      => 'required',
                'start_date'      => 'required',
                'end_date'        => 'required',
                'status'          => 'required',
                'company_id'      => 'required',
                'priority'        => 'required',
            ]);

            $task = Task::create([
                'title'            => $request['title'],
                'summary'          => $request['summary'],
                'start_date'       => $request['start_date'],
                'end_date'         => $request['end_date'],
                'project_id'       => $request['project_id'],
                'company_id'       => $request['company_id'],
                'status'           => $request['status'],
                'priority'         => $request['priority'],
                'task_progress'    => $request['task_progress'],
                'description'      => $request['description'],
            ]);

            $assignedIds = $user_auth->role_users_id == 2 ? [$user_auth->id] : $request['assigned_to'];
            $task->assignedEmployees()->sync($assignedIds);

            // Send notification to each assigned employee
            $employees = \App\Models\Employee::whereIn('id', $assignedIds)->get();
            foreach ($employees as $employee) {
                if ($employee->user) {
                    $employee->user->notify(new TaskAssigned($task));
                }
            }

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user_auth = auth()->user();
            $task = Task::where('deleted_at', '=', null)->findOrFail($id);

        if ($user_auth->can('task_view')) {
            // Admin/manager: can view any task
        } elseif ($user_auth->can('task_view_own')) {
            // Employee: can only view if assigned
            if (!$task->assignedEmployees->contains('id', $user_auth->id)) {
                return abort(403, __('You are not authorized'));
            }
        } else {
            return abort(403, __('You are not authorized'));
        }

        $discussions = TaskDiscussion::where('task_id', $id)->with('User:id,username')->orderBy('id', 'desc')->get();
        $documents = TaskDocument::where('task_id', $id)->orderBy('id', 'desc')->get();
        $links = TaskLink::where('task_id', $id)->orderBy('id', 'desc')->get();

        return view('task.task_details', compact('task', 'discussions', 'documents', 'links'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('task_edit')){

            $task = Task::where('deleted_at', '=', null)->findOrFail($id);
            $assigned_employees = EmployeeTask::where('task_id', $id)->pluck('employee_id')->toArray();
            $projects = Project::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','title']);
            $companies = Company::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','name']);
            $employees = Employee::where('company_id' , $task->company_id)->where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','username']);

            return view('task.edit_task', compact('task','projects','companies','employees','assigned_employees'));

        }
        return abort('403', __('You are not authorized'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('task_edit')){

            $request->validate([
                'title'           => 'required|string|max:255',
                'summary'         => 'required|string|max:255',
                'project_id'      => 'required',
                'start_date'      => 'required',
                'end_date'        => 'required',
                'status'          => 'required',
                'company_id'      => 'required',
                'priority'          => 'required',
            ]);

            $oldTask = Task::where('deleted_at', '=', null)->findOrFail($id);
            $wasCompleted = $oldTask->status === 'completed';

            Task::whereId($id)->update([
                'title'            => $request['title'],
                'summary'          => $request['summary'],
                'start_date'       => $request['start_date'],
                'end_date'         => $request['end_date'],
                'project_id'       => $request['project_id'],
                'company_id'       => $request['company_id'],
                'status'           => $request['status'],
                'priority'         => $request['priority'],
                'task_progress'    => $request['task_progress'],
                'description'      => $request['description'],
            ]);

            $task = Task::where('deleted_at', '=', null)->findOrFail($id);
            $task->assignedEmployees()->sync($request['assigned_to']);

            // Notify all assigned employees about the update
            $employees = $task->assignedEmployees;
            foreach ($employees as $employee) {
                if ($employee->user) {
                    $employee->user->notify(new TaskUpdated($task));
                }
            }

            // If status changed to completed, notify all assigned employees and the assigner
            if (!$wasCompleted && $request['status'] === 'completed') {
                foreach ($employees as $employee) {
                    if ($employee->user) {
                        $employee->user->notify(new TaskCompleted($task, $user_auth));
                    }
                }
                // Notify the assigner/creator if not already in assigned employees
                if ($task->created_by && (!$employees->contains('user_id', $task->created_by))) {
                    $assigner = \App\Models\User::find($task->created_by);
                    if ($assigner) {
                        $assigner->notify(new TaskCompleted($task, $user_auth));
                    }
                }
            }

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('task_delete')){

            $task = Task::findOrFail($id);
            $task->delete();

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }

      //-------------- Delete by selection  ---------------\\

      public function delete_by_selection(Request $request)
      {
         $user_auth = auth()->user();
         if($user_auth->can('task_delete')){
             $selectedIds = $request->selectedIds;

             foreach ($selectedIds as $task_id) {
                $task = Task::findOrFail($task_id);
                $task->delete();
             }
             return response()->json(['success' => true]);
         }
         return abort('403', __('You are not authorized'));
      }


    //---------------------Task Details -----------------------------\\

    public function Create_task_discussions(Request $request)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('task_details')){

            $request->validate([
                'message'           => 'required|string',
            ]);

            $discussion = TaskDiscussion::create([
                'message'            => $request['message'],
                'user_id'            => Auth::user()->id,
                'task_id'           => $request['task_id'],
            ]);

            // Notify all assigned employees except the commenter
            $task = \App\Models\Task::find($request['task_id']);
            $assignedEmployees = $task->assignedEmployees()->get();
            foreach ($assignedEmployees as $employee) {
                if ($employee->user && $employee->user->id != $user_auth->id) {
                    $employee->user->notify(new TaskCommented($task, $request['message'], $user_auth));
                }
            }

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }

    public function destroy_task_discussion($id)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('task_details')){

            $discussion = TaskDiscussion::findOrFail($id);
            $discussion->delete();

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }

    public function Create_task_documents(Request $request)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('task_details')){

            $request->validate([
                'title'         => 'required|string|max:255',
                'attachment'    => 'required|file|mimes:pdf,docs,doc,pptx,jpeg,png,jpg,bmp,gif,svg|max:2048',

            ]);


            if ($request->hasFile('attachment')) {

                $image = $request->file('attachment');
                $attachment = time().'.'.$image->extension();
                $image->move(public_path('/assets/images/tasks/documents'), $attachment);

            } else {
                $attachment = Null;
            }

            TaskDocument::create([
                'title'            => $request['title'],
                'task_id'          => $request['task_id'],
                'description'      => $request['description'],
                'attachment'       => $attachment,
            ]);

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }


    public function destroy_task_documents($id)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('task_details')){

            $document = TaskDocument::findOrFail($id);
            $document->delete();

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }


    public function update_task_status(Request $request, $id)
    {
        $user_auth = auth()->user();
        if ($user_auth->can('task_edit')) {
            $request->validate([
                'status'          => 'required',
            ]);

            $task = Task::where('deleted_at', '=', null)->findOrFail($id);
            $wasCompleted = $task->status === 'completed';

            Task::whereId($id)->update([
                'status'           => $request['status'],
                'completed_at'     => $request['status'] === 'completed' ? Carbon::now() : null,
            ]);

            // If status changed to completed, notify all assigned employees and the assigner
            if (!$wasCompleted && $request['status'] === 'completed') {
                $employees = $task->assignedEmployees;
                foreach ($employees as $employee) {
                    if ($employee->user) {
                        $employee->user->notify(new TaskCompleted($task, $user_auth));
                    }
                }
                if ($task->created_by && (!$employees->contains('user_id', $task->created_by))) {
                    $assigner = \App\Models\User::find($task->created_by);
                    if ($assigner) {
                        $assigner->notify(new TaskCompleted($task, $user_auth));
                    }
                }
            }

            return response()->json(['success' => true]);
        }
        return abort('403', __('You are not authorized'));
    }

    public function import(Request $request)
    {
        $user_auth = auth()->user();
        if (!$user_auth->can('task_add')) {
            return response()->json(['success' => false, 'message' => 'Not authorized.'], 403);
        }

        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('import_file');
        $handle = fopen($file->getRealPath(), 'r');

        if (!$handle) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to read the CSV file. Please check the file format.',
                'errors' => ['File could not be opened for reading.']
            ]);
        }

        $header = fgetcsv($handle);
        $created = 0;
        $skipped = 0;
        $skippedRows = [];
        $rowNumber = 1; // Start from 1 (header row)

        // Define valid values for validation
        $validStatuses = ['not_started', 'progress', 'cancelled', 'hold', 'completed'];
        $validPriorities = ['urgent', 'high', 'medium', 'low'];

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            // Skip empty rows
            if (empty(array_filter($row))) {
                continue;
            }

            // Map columns by index
            $title = trim($row[0] ?? '');
            $summary = trim($row[1] ?? '');
            $company_name = trim($row[2] ?? '');
            $project_name = trim($row[3] ?? '');
            $assigned_to = trim($row[4] ?? '');
            $start_date = trim($row[5] ?? '');
            $end_date = trim($row[6] ?? '');
            $status = trim($row[7] ?? '');
            $progress = trim($row[8] ?? '');
            $priority = trim($row[9] ?? 'medium'); // Default to medium if not provided

            $errors = [];

            // Validate required fields
            if (empty($title)) $errors[] = 'Title is required';
            if (empty($summary)) $errors[] = 'Summary is required';
            if (empty($company_name)) $errors[] = 'Company name is required';
            if (empty($project_name)) $errors[] = 'Project name is required';
            if (empty($assigned_to)) $errors[] = 'Assigned employee is required';
            if (empty($start_date)) $errors[] = 'Start date is required';
            if (empty($end_date)) $errors[] = 'End date is required';
            if (empty($status)) $errors[] = 'Status is required';
            if ($progress === '' || $progress === null) $errors[] = 'Progress is required';

            // Validate field lengths
            if (strlen($title) > 192) $errors[] = 'Title must not exceed 192 characters';
            if (strlen($summary) > 255) $errors[] = 'Summary must not exceed 255 characters';

            // Validate date formats
            if (!empty($start_date) && !$this->isValidDate($start_date)) {
                $errors[] = 'Start date must be in YYYY-MM-DD format';
            }
            if (!empty($end_date) && !$this->isValidDate($end_date)) {
                $errors[] = 'End date must be in YYYY-MM-DD format';
            }

            // Validate date logic
            if (!empty($start_date) && !empty($end_date) && $this->isValidDate($start_date) && $this->isValidDate($end_date)) {
                if (strtotime($end_date) < strtotime($start_date)) {
                    $errors[] = 'End date must be after or equal to start date';
                }
            }

            // Validate status
            if (!empty($status) && !in_array($status, $validStatuses)) {
                $errors[] = 'Status must be one of: ' . implode(', ', $validStatuses);
            }

            // Validate priority
            if (!empty($priority) && !in_array($priority, $validPriorities)) {
                $errors[] = 'Priority must be one of: ' . implode(', ', $validPriorities);
            }

            // Validate progress
            if ($progress !== '' && $progress !== null) {
                $progressNum = filter_var($progress, FILTER_VALIDATE_INT);
                if ($progressNum === false || $progressNum < 0 || $progressNum > 100) {
                    $errors[] = 'Progress must be a number between 0 and 100';
                }
            }

            // Check for duplicate title
            if (!empty($title)) {
                $existingTask = \App\Models\Task::where('title', $title)->where('deleted_at', null)->first();
                if ($existingTask) {
                    $errors[] = 'Task with this title already exists in the database';
                }
            }

            if (count($errors) > 0) {
                $skipped++;
                $skippedRows[] = [
                    'row_number' => $rowNumber,
                    'row_data' => $row,
                    'errors' => $errors
                ];
                continue;
            }

            // Lookup company by name
            $company = \App\Models\Company::where('name', $company_name)->first();
            if (!$company) {
                $skipped++;
                $skippedRows[] = [
                    'row_number' => $rowNumber,
                    'row_data' => $row,
                    'errors' => ['Company not found: ' . $company_name]
                ];
                continue;
            }

            // Lookup project by name and company
            $project = \App\Models\Project::where('title', $project_name)->where('company_id', $company->id)->first();
            if (!$project) {
                $skipped++;
                $skippedRows[] = [
                    'row_number' => $rowNumber,
                    'row_data' => $row,
                    'errors' => ['Project not found: ' . $project_name . ' for company ' . $company_name]
                ];
                continue;
            }

            // Lookup assigned employees by username (support multiple employees separated by commas)
            $employeeUsernames = array_map('trim', explode(',', $assigned_to));
            $employeeIds = [];
            $notFoundEmployees = [];

            foreach ($employeeUsernames as $username) {
                if (empty($username)) continue;

                $employee = \App\Models\Employee::where('username', $username)->first();
                if ($employee) {
                    $employeeIds[] = $employee->id;
                } else {
                    $notFoundEmployees[] = $username;
                }
            }

            // Check if we have any valid employees
            if (empty($employeeIds)) {
                $skipped++;
                $errorMessages = ['No valid employees found'];
                if (!empty($notFoundEmployees)) {
                    $errorMessages[] = 'Employees not found: ' . implode(', ', $notFoundEmployees);
                }
                $skippedRows[] = [
                    'row_number' => $rowNumber,
                    'row_data' => $row,
                    'errors' => $errorMessages
                ];
                continue;
            }

            // Create the task
            try {
                $task = new \App\Models\Task();
                $task->title = $title;
                $task->summary = $summary;
                $task->company_id = $company->id;
                $task->project_id = $project->id;
                $task->start_date = $start_date;
                $task->end_date = $end_date;
                $task->status = $status;
                $task->priority = $priority;
                $task->task_progress = $progress !== '' ? (int) $progress : 0;
                $task->save();

                // Attach all valid employees
                $task->assignedEmployees()->sync($employeeIds);

                // Add warning if some employees were not found
                if (!empty($notFoundEmployees)) {
                    // Note: Task was created but with warnings about missing employees
                    $skippedRows[] = [
                        'row_number' => $rowNumber,
                        'row_data' => $row,
                        'errors' => ['Warning: Task created but some employees not found: ' . implode(', ', $notFoundEmployees)]
                    ];
                }

                $created++;
            } catch (\Exception $e) {
                $skipped++;
                $skippedRows[] = [
                    'row_number' => $rowNumber,
                    'row_data' => $row,
                    'errors' => ['Database error: ' . $e->getMessage()]
                ];
            }
        }

        fclose($handle);

        return response()->json([
            'success' => $created > 0 || $skipped == 0,
            'message' => "$created tasks imported successfully. $skipped rows skipped.",
            'created' => $created,
            'skipped' => $skipped,
            'total_rows' => $rowNumber - 1,
            'skippedRows' => $skippedRows
        ]);
    }

    /**
     * Validate date format (YYYY-MM-DD)
     */
    private function isValidDate($date)
    {
        $d = \DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Download CSV template for task import
     */
    public function downloadTemplate()
    {
        $user_auth = auth()->user();
        if (!$user_auth->can('task_add') && !$user_auth->can('task_view') && !$user_auth->can('task_view_own')) {
            return response()->json(['success' => false, 'message' => 'Not authorized.'], 403);
        }

        $headers = [
            'Title',
            'Summary',
            'Company Name',
            'Project Name',
            'Assigned Employee(s)',
            'Start Date',
            'End Date',
            'Status',
            'Progress',
            'Priority'
        ];

        $sampleData = [
            [
                'Create user authentication system',
                'Implement login and registration functionality',
                'Tech Corp',
                'Website Redesign',
                'john.doe',
                '2024-01-15',
                '2024-01-30',
                'not_started',
                '0',
                'high'
            ],
            [
                'Design database schema',
                'Create tables for user management',
                'Tech Corp',
                'Website Redesign',
                'jane.smith, bob.wilson',
                '2024-01-16',
                '2024-01-25',
                'progress',
                '25',
                'medium'
            ]
        ];

        $filename = 'task_import_template.csv';

        $callback = function() use ($headers, $sampleData) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($sampleData as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    //---------------------Task Links -----------------------------\\

    public function Create_task_links(Request $request)
    {
        $user_auth = auth()->user();
        if ($user_auth->can('task_details')) {

            $request->validate([
                'title'         => 'required|string|max:255',
                'url'           => 'required|url|max:2048',
            ]);

            TaskLink::create([
                'title'            => $request['title'],
                'url'              => $request['url'],
                'description'      => $request['description'],
                'task_id'          => $request['task_id'],
            ]);

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }

    public function destroy_task_links($id)
    {
        $user_auth = auth()->user();
        if ($user_auth->can('task_details')) {

            TaskLink::whereId($id)->delete();

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }

}
