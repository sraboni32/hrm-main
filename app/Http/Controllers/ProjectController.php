<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Task;
use App\Models\Project;
use App\Models\ProjectDiscussion;
use App\Models\ProjectDocument;
use App\Models\ProjectIssue;
use App\Models\EmployeeProject;
use App\Models\Client;
use App\Services\AiTaskGeneratorService;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('project_view')){
            $count_not_started = Project::where('deleted_at', '=', null)
            ->where('status', '=', 'not_started')
            ->count();
            $count_in_progress = Project::where('deleted_at', '=', null)
            ->where('status', '=', 'progress')
            ->count();
            $count_cancelled = Project::where('deleted_at', '=', null)
            ->where('status', '=', 'cancelled')
            ->count();
            $count_completed = Project::where('deleted_at', '=', null)
            ->where('status', '=', 'completed')
            ->count();
            $count_hold = Project::where('deleted_at', '=', null)
            ->where('status', '=', 'hold')
            ->count();

            $clients = \App\Models\Client::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','username']);
            $companies = \App\Models\Company::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','name']);

            $projectsQuery = Project::where('deleted_at', '=', null)
            ->with('company:id,name','client:id,username')
                ->orderBy('id', 'desc');

            if ($request->filled('title')) {
                $projectsQuery->where('title', 'like', '%'.$request->title.'%');
            }
            if ($request->filled('client_id') && $request->client_id != '0') {
                $projectsQuery->where('client_id', $request->client_id);
            }
            if ($request->filled('company_id') && $request->company_id != '0') {
                $projectsQuery->where('company_id', $request->company_id);
            }
            if ($request->filled('status') && $request->status != '0') {
                $projectsQuery->where('status', $request->status);
            }
            if ($request->filled('start_date')) {
                $projectsQuery->where('start_date', '>=', $request->start_date);
            }
            if ($request->filled('end_date')) {
                $projectsQuery->where('end_date', '<=', $request->end_date);
            }
            $projects = $projectsQuery->get();

            return view('project.project_list', compact('projects','count_not_started','count_in_progress','count_cancelled','count_completed','count_hold','clients','companies'));

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
		if ($user_auth->can('project_add')){

            $clients = Client::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','username']);
            $companies = Company::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','name']);
            return view('project.create_project', compact('clients','companies'));

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
		if ($user_auth->can('project_add')){

            $request->validate([
                'title'           => 'required|string|max:255',
                'summary'         => 'required|string|max:255',
                'client'          => 'required',
                'company_id'      => 'required',
                'assigned_to'     => 'required',
                'start_date'      => 'required',
                'end_date'        => 'required',
                'priority'        => 'required',
                'status'          => 'required',
            ]);

            $project  = Project::create([
                'title'            => $request['title'],
                'summary'          => $request['summary'],
                'start_date'       => $request['start_date'],
                'end_date'         => $request['end_date'],
                'company_id'       => $request['company_id'],
                'client_id'        => $request['client'],
                'priority'         => $request['priority'],
                'status'           => $request['status'],
                'project_progress' => $request['project_progress'],
                'description'      => $request['description'],
            ]);

            $project->assignedEmployees()->sync($request['assigned_to']);

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
		if ($user_auth->can('project_details')){

            $project = Project::where('deleted_at', '=', null)->findOrFail($id);
            $discussions = ProjectDiscussion::where('project_id' , $id)
            ->where('deleted_at', '=', null)
            ->with('User:id,username')
            ->orderBy('id', 'desc')
            ->get();

            $issues = ProjectIssue::where('project_id' , $id)
            ->where('deleted_at', '=', null)
            ->orderBy('id', 'desc')
            ->get();

            $documents = ProjectDocument::where('project_id' , $id)
            ->where('deleted_at', '=', null)
            ->orderBy('id', 'desc')
            ->get();

            $tasks = Task::where('project_id' , $id)
            ->where('deleted_at', '=', null)
            ->orderBy('id', 'desc')
            ->get();

            $companies = Company::where('deleted_at', '=', null)
            ->orderBy('id', 'desc')
            ->get(['id','name']);

            return view('project.project_details',
                compact('project','discussions','issues','documents','companies','tasks')
            );

        }
        return abort('403', __('You are not authorized'));

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
		if ($user_auth->can('project_edit')){

            $project = Project::where('deleted_at', '=', null)->findOrFail($id);
            $assigned_employees = EmployeeProject::where('project_id', $id)->pluck('employee_id')->toArray();
            $clients = Client::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','username']);
            $companies = Company::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','name']);
            $employees = Employee::where('company_id' , $project->company_id)->where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','username']);

            return view('project.edit_project', compact('project','companies','clients','employees','assigned_employees'));

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
		if ($user_auth->can('project_edit')){

            $request->validate([
                'title'           => 'required|string|max:255',
                'summary'         => 'required|string|max:255',
                'client'          => 'required',
                'company_id'      => 'required',
                'assigned_to'     => 'required',
                'start_date'      => 'required',
                'end_date'        => 'required',
                'priority'        => 'required',
                'status'          => 'required',
            ]);

            Project::whereId($id)->update([
                'title'            => $request['title'],
                'summary'          => $request['summary'],
                'start_date'       => $request['start_date'],
                'end_date'         => $request['end_date'],
                'company_id'       => $request['company_id'],
                'client_id'        => $request['client'],
                'priority'         => $request['priority'],
                'status'           => $request['status'],
                'project_progress' => $request['project_progress'],
                'description'      => $request['description'],
            ]);

            $project = Project::where('deleted_at', '=', null)->findOrFail($id);
            $project->assignedEmployees()->sync($request['assigned_to']);

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
		if ($user_auth->can('project_delete')){

            Project::whereId($id)->update([
                'deleted_at' => Carbon::now(),
            ]);

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }

      //-------------- Delete by selection  ---------------\\

      public function delete_by_selection(Request $request)
      {
         $user_auth = auth()->user();
         if($user_auth->can('project_delete')){
             $selectedIds = $request->selectedIds;

             foreach ($selectedIds as $project_id) {
                Project::whereId($project_id)->update([
                    'deleted_at' => Carbon::now(),
                ]);
             }
             return response()->json(['success' => true]);
         }
         return abort('403', __('You are not authorized'));
      }


    //-----------Project Details--------------------------------\\

    public function Create_project_discussions(Request $request)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('project_details')){

            $request->validate([
                'message'           => 'required|string',
            ]);

            ProjectDiscussion::create([
                'message'            => $request['message'],
                'user_id'            => Auth::user()->id,
                'project_id'        => $request['project_id'],
            ]);

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }

    public function destroy_project_discussion($id)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('project_details')){

            ProjectDiscussion::whereId($id)->update([
                'deleted_at' => Carbon::now(),
            ]);

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }


    public function Create_project_issues(Request $request)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('project_details')){

            $request->validate([
                'title'         => 'required|string|max:255',
                'comment'       => 'required',
                'attachment'    => 'nullable|file|mimes:pdf,docs,doc,pptx,jpeg,png,jpg,bmp,gif,svg|max:2048',

            ]);


            if ($request->hasFile('attachment')) {

                $image = $request->file('attachment');
                $attachment = time().'.'.$image->extension();
                $image->move(public_path('/assets/images/projects/issues'), $attachment);

            } else {
                $attachment = Null;
            }

            ProjectIssue::create([
                'title'            => $request['title'],
                'project_id'       => $request['project_id'],
                'comment'          => $request['comment'],
                'label'            => $request['label'],
                'status'           => 'pending',
                'attachment'       => $attachment,
            ]);

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }

    public function Update_project_issues(Request $request , $id)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('project_details')){

            $request->validate([
                'title'         => 'required|string|max:255',
                'comment'       => 'required',
                'attachment'    => 'nullable|file|mimes:pdf,docs,doc,pptx,jpeg,png,jpg,bmp,gif,svg|max:2048',

            ]);

            //upload attachment

            $project_issue = ProjectIssue::findOrFail($id);

            $currentAttachment = $project_issue->attachment;

            if ($request->attachment != null) {
                if ($request->attachment != $currentAttachment) {

                    $image = $request->file('attachment');
                    $attachment = time().'.'.$image->extension();
                    $image->move(public_path('/assets/images/projects/issues'), $attachment);
                    $path = public_path() . '/assets/images/projects/issues';
                    $project_issue_attachment = $path . '/' . $currentAttachment;
                    if (file_exists($project_issue_attachment)) {
                            @unlink($project_issue_attachment);
                    }
                } else {
                    $attachment = $currentAttachment;
                }
            }else{
                $attachment = $currentAttachment;
            }

            ProjectIssue::whereId($id)->update([
                'title'            => $request['title'],
                'project_id'       => $request['project_id'],
                'comment'          => $request['comment'],
                'label'            => $request['label'],
                'status'           => $request['status'],
                'attachment'       => $attachment,
            ]);

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }


    public function destroy_project_issues($id)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('project_details')){

            ProjectIssue::whereId($id)->update([
                'deleted_at' => Carbon::now(),
            ]);

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }


    public function Create_project_documents(Request $request)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('project_details')){

            $request->validate([
                'title'         => 'required|string|max:255',
                'attachment'    => 'required|file|mimes:pdf,docs,doc,pptx,jpeg,png,jpg,bmp,gif,svg|max:2048',

            ]);


            if ($request->hasFile('attachment')) {

                $image = $request->file('attachment');
                $attachment = time().'.'.$image->extension();
                $image->move(public_path('/assets/images/projects/documents'), $attachment);

            } else {
                $attachment = Null;
            }

            ProjectDocument::create([
                'title'            => $request['title'],
                'project_id'       => $request['project_id'],
                'description'      => $request['description'],
                'attachment'       => $attachment,
            ]);

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }


    public function destroy_project_documents($id)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('project_details')){

            ProjectDocument::whereId($id)->update([
                'deleted_at' => Carbon::now(),
            ]);

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }

    public function import(Request $request)
    {
        $user_auth = auth()->user();
        if (!$user_auth->can('project_add')) {
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
            $client_name = trim($row[3] ?? '');
            $start_date = trim($row[4] ?? '');
            $end_date = trim($row[5] ?? '');
            $priority = trim($row[6] ?? 'medium'); // Default to medium if not provided
            $status = trim($row[7] ?? '');
            $description = trim($row[8] ?? '');
            $project_progress = trim($row[9] ?? '0'); // Default to 0 if not provided

            $errors = [];

            // Validate required fields
            if (empty($title)) $errors[] = 'Title is required';
            if (empty($summary)) $errors[] = 'Summary is required';
            if (empty($company_name)) $errors[] = 'Company name is required';
            if (empty($client_name)) $errors[] = 'Client username is required';
            if (empty($start_date)) $errors[] = 'Start date is required';
            if (empty($end_date)) $errors[] = 'End date is required';
            if (empty($status)) $errors[] = 'Status is required';

            // Validate field lengths
            if (strlen($title) > 255) $errors[] = 'Title must not exceed 255 characters';
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
            if ($project_progress !== '' && $project_progress !== null) {
                $progressNum = filter_var($project_progress, FILTER_VALIDATE_INT);
                if ($progressNum === false || $progressNum < 0 || $progressNum > 100) {
                    $errors[] = 'Progress must be a number between 0 and 100';
                }
            }

            // Check for duplicate title
            if (!empty($title)) {
                $existingProject = \App\Models\Project::where('title', $title)->where('deleted_at', null)->first();
                if ($existingProject) {
                    $errors[] = 'Project with this title already exists in the database';
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

            // Lookup client by username
            $client = \App\Models\Client::where('username', $client_name)->first();
            if (!$client) {
                $skipped++;
                $skippedRows[] = [
                    'row_number' => $rowNumber,
                    'row_data' => $row,
                    'errors' => ['Client not found: ' . $client_name]
                ];
                continue;
            }

            // Create the project
            try {
                \App\Models\Project::create([
                    'title' => $title,
                    'summary' => $summary,
                    'company_id' => $company->id,
                    'client_id' => $client->id,
                    'start_date' => $start_date,
                    'end_date' => $end_date,
                    'priority' => $priority,
                    'status' => $status,
                    'description' => $description,
                    'project_progress' => $project_progress !== '' ? (int) $project_progress : 0,
                ]);
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
            'message' => "$created projects imported successfully. $skipped rows skipped.",
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
     * Download CSV template for project import
     */
    public function downloadTemplate()
    {
        $user_auth = auth()->user();
        if (!$user_auth->can('project_add') && !$user_auth->can('project_view') && !$user_auth->can('project_view_own')) {
            return response()->json(['success' => false, 'message' => 'Not authorized.'], 403);
        }

        $headers = [
            'Title',
            'Summary',
            'Company Name',
            'Client Username',
            'Start Date',
            'End Date',
            'Priority',
            'Status',
            'Description',
            'Progress'
        ];

        $sampleData = [
            [
                'Website Redesign Project',
                'Complete redesign of company website with modern UI/UX',
                'Tech Corp',
                'john.client',
                '2024-01-15',
                '2024-03-30',
                'high',
                'not_started',
                'Full website redesign including responsive design and SEO optimization',
                '0'
            ],
            [
                'Mobile App Development',
                'Develop cross-platform mobile application',
                'Tech Corp',
                'jane.client',
                '2024-02-01',
                '2024-06-15',
                'urgent',
                'progress',
                'Native mobile app for iOS and Android platforms',
                '25'
            ]
        ];

        $filename = 'project_import_template.csv';

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

    /**
     * Generate and create AI tasks for a project
     */
    public function generateAndCreateAiTasks(Request $request, $projectId)
    {
        \Log::info('AI Task Generation: Method called', ['project_id' => $projectId]);

        $user_auth = auth()->user();
        if (!$user_auth->can('task_add')) {
            \Log::warning('AI Task Generation: User not authorized', ['user_id' => $user_auth->id]);
            return response()->json([
                'success' => false,
                'error' => 'You are not authorized to create tasks'
            ], 403);
        }

        try {
            \Log::info('AI Task Generation: Starting process');
            // Get the project
            $project = Project::where('deleted_at', null)->findOrFail($projectId);

            // Validate request
            $request->validate([
                'task_complexity' => 'nullable|in:low,medium,high',
                'include_testing' => 'nullable|boolean',
                'include_documentation' => 'nullable|boolean',
                'methodology' => 'nullable|in:agile,waterfall,kanban',
                'client_requirements' => 'nullable|string',
                'technical_requirements' => 'nullable|string'
            ]);

            // Prepare project data
            $projectData = [
                'id' => $project->id,
                'title' => $project->title,
                'summary' => $project->summary,
                'description' => $project->description,
                'priority' => $project->priority,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'company_id' => $project->company_id,
                'client_requirements' => $request->input('client_requirements'),
                'technical_requirements' => $request->input('technical_requirements')
            ];

            // Prepare options
            $options = [
                'task_complexity' => $request->input('task_complexity', 'medium'),
                'include_testing' => $request->input('include_testing', true),
                'include_documentation' => $request->input('include_documentation', true),
                'methodology' => $request->input('methodology', 'agile')
            ];

            // Generate tasks using AI
            \Log::info('AI Task Generation: Creating service instance');
            $aiTaskGenerator = new AiTaskGeneratorService();
            \Log::info('AI Task Generation: Service created, calling generateTasksForProject');
            $result = $aiTaskGenerator->generateTasksForProject($projectData, $options);
            \Log::info('AI Task Generation: AI response received', ['success' => $result['success']]);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], 500);
            }

            // Automatically create all generated tasks in database
            $createdTasks = [];
            $errors = [];

            foreach ($result['tasks'] as $index => $taskData) {
                try {
                    $task = Task::create([
                        'title' => $taskData['title'],
                        'summary' => $taskData['summary'],
                        'description' => $taskData['description'] ?? '',
                        'priority' => $taskData['priority'],
                        'estimated_hour' => $taskData['estimated_hour'],
                        'start_date' => $taskData['start_date'],
                        'end_date' => $taskData['end_date'],
                        'status' => 'not_started',
                        'task_progress' => '0',
                        'project_id' => $project->id,
                        'company_id' => $project->company_id,
                        'note' => 'AI Generated Task - ' . ($taskData['milestone'] ?? 'No milestone')
                    ]);

                    $createdTasks[] = $task;

                } catch (\Exception $e) {
                    $errors[] = "Task " . ($index + 1) . " (" . $taskData['title'] . "): " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'created_count' => count($createdTasks),
                'total_generated' => count($result['tasks']),
                'errors' => $errors,
                'metadata' => $result['metadata'],
                'message' => count($createdTasks) . ' tasks created successfully for project: ' . $project->title
            ]);

        } catch (\Exception $e) {
            \Log::error('AI Task Generation: Exception caught', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate and create AI tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate AI tasks without saving (for review)
     */
    public function generateAiTasks(Request $request, $projectId)
    {
        $user_auth = auth()->user();
        if (!$user_auth->can('task_add')) {
            return response()->json([
                'success' => false,
                'error' => 'You are not authorized to create tasks'
            ], 403);
        }

        try {
            \Log::info('AI Task Generation: Generate only method called', ['project_id' => $projectId]);

            // Get the project
            $project = Project::where('deleted_at', null)->findOrFail($projectId);

            // Validate request
            $request->validate([
                'task_complexity' => 'nullable|in:low,medium,high',
                'include_testing' => 'nullable|boolean',
                'include_documentation' => 'nullable|boolean',
                'methodology' => 'nullable|in:agile,waterfall,kanban',
                'client_requirements' => 'nullable|string',
                'technical_requirements' => 'nullable|string'
            ]);

            // Prepare project data
            $projectData = [
                'id' => $project->id,
                'title' => $project->title,
                'summary' => $project->summary,
                'description' => $project->description,
                'priority' => $project->priority,
                'start_date' => $project->start_date,
                'end_date' => $project->end_date,
                'company_id' => $project->company_id,
                'client_requirements' => $request->input('client_requirements'),
                'technical_requirements' => $request->input('technical_requirements')
            ];

            // Prepare options
            $options = [
                'task_complexity' => $request->input('task_complexity', 'medium'),
                'include_testing' => $request->input('include_testing', true),
                'include_documentation' => $request->input('include_documentation', true),
                'methodology' => $request->input('methodology', 'agile')
            ];

            // Generate tasks using AI
            $aiTaskGenerator = new AiTaskGeneratorService();
            $result = $aiTaskGenerator->generateTasksForProject($projectData, $options);

            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error']
                ], 500);
            }

            // Return tasks for review (don't save yet)
            return response()->json([
                'success' => true,
                'tasks' => $result['tasks'],
                'metadata' => $result['metadata'],
                'message' => 'Tasks generated successfully for review'
            ]);

        } catch (\Exception $e) {
            \Log::error('AI Task Generation: Exception in generate method', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to generate AI tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save bulk AI tasks after review
     */
    public function saveBulkAiTasks(Request $request, $projectId)
    {
        $user_auth = auth()->user();
        if (!$user_auth->can('task_add')) {
            return response()->json([
                'success' => false,
                'error' => 'You are not authorized to create tasks'
            ], 403);
        }

        try {
            \Log::info('AI Task Generation: Save bulk method called', ['project_id' => $projectId]);

            // Get the project
            $project = Project::where('deleted_at', null)->findOrFail($projectId);

            // Validate request
            $request->validate([
                'tasks' => 'required|array|min:1',
                'tasks.*.title' => 'required|string|max:192',
                'tasks.*.summary' => 'required|string',
                'tasks.*.description' => 'nullable|string',
                'tasks.*.priority' => 'required|in:urgent,high,medium,low',
                'tasks.*.status' => 'required|in:not_started,progress,completed,cancelled,hold',
                'tasks.*.estimated_hour' => 'nullable|numeric|min:1',
                'tasks.*.start_date' => 'required|date',
                'tasks.*.end_date' => 'required|date|after_or_equal:tasks.*.start_date',
                'tasks.*.task_progress' => 'nullable|numeric|min:0|max:100',
                'tasks.*.note' => 'nullable|string'
            ]);

            $tasksData = $request->input('tasks');
            $createdTasks = [];
            $errors = [];

            foreach ($tasksData as $index => $taskData) {
                try {
                    $task = Task::create([
                        'title' => $taskData['title'],
                        'summary' => $taskData['summary'],
                        'description' => $taskData['description'] ?? '',
                        'priority' => $taskData['priority'],
                        'status' => $taskData['status'],
                        'estimated_hour' => $taskData['estimated_hour'] ?? 8,
                        'start_date' => $taskData['start_date'],
                        'end_date' => $taskData['end_date'],
                        'task_progress' => $taskData['task_progress'] ?? 0,
                        'note' => $taskData['note'] ?? 'AI Generated Task',
                        'project_id' => $project->id,
                        'company_id' => $project->company_id
                    ]);

                    $createdTasks[] = $task;

                } catch (\Exception $e) {
                    $errors[] = "Task " . ($index + 1) . " (" . $taskData['title'] . "): " . $e->getMessage();
                }
            }

            return response()->json([
                'success' => true,
                'created_count' => count($createdTasks),
                'total_submitted' => count($tasksData),
                'errors' => $errors,
                'message' => count($createdTasks) . ' tasks created successfully for project: ' . $project->title
            ]);

        } catch (\Exception $e) {
            \Log::error('AI Task Generation: Exception in save bulk method', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'success' => false,
                'error' => 'Failed to save bulk AI tasks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show AI task generation form
     */
    public function showAiTaskGenerator($projectId)
    {
        $user_auth = auth()->user();
        if (!$user_auth->can('task_add')) {
            return abort(403, 'You are not authorized');
        }

        $project = Project::where('deleted_at', null)->findOrFail($projectId);

        return view('project.ai_task_generator', compact('project'));
    }
}
