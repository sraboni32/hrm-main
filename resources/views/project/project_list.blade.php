@extends('layouts.master')
@section('main-content')
@section('page-css')

<link rel="stylesheet" href="{{asset('assets/styles/vendor/datatables.min.css')}}">

<style>
.status-filter-card {
    transition: all 0.3s ease;
}

.status-filter-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.status-filter-card.clickable {
    user-select: none;
}

.status-filter-card .card-body {
    padding: 1.5rem 1rem;
}

@media (max-width: 768px) {
    .status-filter-card .content p {
        font-size: 0.9rem;
    }

    .status-filter-card .content .text-24 {
        font-size: 1.5rem !important;
    }
}
</style>

@endsection

<div class="breadcrumb">
    <h1>{{ __('translate.Project_List') }}</h1>
    <ul>
        <li>{{ __('translate.Project') }}</li>
        <li>{{ __('translate.Project_List') }}</li>
    </ul>
</div>

<div class="separator-breadcrumb border-top"></div>

<div class="row">
    <div class="col-lg-2 col-md-6 col-sm-6">
        <div class="card card-icon-bg card-icon-bg-secondary o-hidden mb-4 status-filter-card clickable" data-status="all" style="cursor: pointer;">
            <div class="card-body text-center">
                <i class="i-File-Horizontal"></i>
                <div class="content">
                    <p class="text-muted mt-2 mb-0">{{ __('translate.All') }}</p>
                    <p class="text-primary text-24 line-height-1 mb-2">{{$projects->count()}}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-6 col-sm-6">
        <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4 status-filter-card clickable" data-status="completed" style="cursor: pointer;">
            <div class="card-body text-center">
                <i class="i-Yes"></i>
                <div class="content">
                    <p class="text-muted mt-2 mb-0">{{ __('translate.Completed') }}</p>
                    <p class="text-primary text-24 line-height-1 mb-2">{{$count_completed}}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-6 col-sm-6">
        <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4 status-filter-card clickable" data-status="progress" style="cursor: pointer;">
            <div class="card-body text-center">
                <i class="i-Loading-3"></i>
                <div class="content">
                    <p class="text-muted mt-2 mb-0">{{ __('translate.In_Progress') }}</p>
                    <p class="text-primary text-24 line-height-1 mb-2">{{$count_in_progress}}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-6 col-sm-6">
        <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4 status-filter-card clickable" data-status="not_started" style="cursor: pointer;">
            <div class="card-body text-center">
                <i class="i-Pause"></i>
                <div class="content">
                    <p class="text-muted mt-2 mb-0">{{ __('translate.Not_Started') }}</p>
                    <p class="text-primary text-24 line-height-1 mb-2">{{$count_not_started}}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-6 col-sm-6">
        <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4 status-filter-card clickable" data-status="cancelled" style="cursor: pointer;">
            <div class="card-body text-center">
                <i class="i-Close"></i>
                <div class="content">
                    <p class="text-muted mt-2 mb-0">{{ __('translate.Cancelled') }}</p>
                    <p class="text-primary text-24 line-height-1 mb-2">{{$count_cancelled}}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-2 col-md-6 col-sm-6">
        <div class="card card-icon-bg card-icon-bg-primary o-hidden mb-4 status-filter-card clickable" data-status="hold" style="cursor: pointer;">
            <div class="card-body text-center">
                <i class="i-Clock"></i>
                <div class="content">
                    <p class="text-muted mt-2 mb-0">{{ __('translate.On_Hold') }}</p>
                    <p class="text-primary text-24 line-height-1 mb-2">{{$count_hold}}</p>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="row mb-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Import Projects from CSV</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <form id="project-import-form" action="{{ route('projects.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="import_file">Select CSV File:</label>
                                <input type="file" name="import_file" id="import_file" class="form-control" required accept=".csv">
                                <small class="form-text text-muted">Maximum file size: 2MB</small>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-upload"></i> Import Projects
                            </button>
                            <button type="button" class="btn btn-info ml-2" data-toggle="collapse" data-target="#csv-format-info">
                                <i class="fas fa-info-circle"></i> CSV Format Info
                            </button>
                            <a href="{{ route('projects.download_template') }}" class="btn btn-success ml-2">
                                <i class="fas fa-download"></i> Download Template
                            </a>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <div id="import-message"></div>
                    </div>
                </div>

                <!-- CSV Format Information -->
                <div class="collapse mt-3" id="csv-format-info">
                    <div class="card card-body bg-light">
                        <h6><i class="fas fa-file-csv"></i> Required CSV Format:</h6>
                        <p class="mb-2">Your CSV file must contain the following columns in this exact order:</p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Column</th>
                                        <th>Field Name</th>
                                        <th>Required</th>
                                        <th>Format/Values</th>
                                        <th>Example</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>1</td>
                                        <td>Title</td>
                                        <td><span class="badge badge-danger">Yes</span></td>
                                        <td>Max 255 characters, must be unique</td>
                                        <td>Website Redesign Project</td>
                                    </tr>
                                    <tr>
                                        <td>2</td>
                                        <td>Summary</td>
                                        <td><span class="badge badge-danger">Yes</span></td>
                                        <td>Max 255 characters</td>
                                        <td>Complete redesign of company website</td>
                                    </tr>
                                    <tr>
                                        <td>3</td>
                                        <td>Company Name</td>
                                        <td><span class="badge badge-danger">Yes</span></td>
                                        <td>Must exist in system</td>
                                        <td>Tech Corp</td>
                                    </tr>
                                    <tr>
                                        <td>4</td>
                                        <td>Client Username</td>
                                        <td><span class="badge badge-danger">Yes</span></td>
                                        <td>Client username must exist</td>
                                        <td>john.client</td>
                                    </tr>
                                    <tr>
                                        <td>5</td>
                                        <td>Start Date</td>
                                        <td><span class="badge badge-danger">Yes</span></td>
                                        <td>YYYY-MM-DD format</td>
                                        <td>2024-01-15</td>
                                    </tr>
                                    <tr>
                                        <td>6</td>
                                        <td>End Date</td>
                                        <td><span class="badge badge-danger">Yes</span></td>
                                        <td>YYYY-MM-DD format</td>
                                        <td>2024-03-30</td>
                                    </tr>
                                    <tr>
                                        <td>7</td>
                                        <td>Priority</td>
                                        <td><span class="badge badge-warning">Optional</span></td>
                                        <td>urgent, high, medium, low (default: medium)</td>
                                        <td>high</td>
                                    </tr>
                                    <tr>
                                        <td>8</td>
                                        <td>Status</td>
                                        <td><span class="badge badge-danger">Yes</span></td>
                                        <td>not_started, progress, cancelled, hold, completed</td>
                                        <td>not_started</td>
                                    </tr>
                                    <tr>
                                        <td>9</td>
                                        <td>Description</td>
                                        <td><span class="badge badge-warning">Optional</span></td>
                                        <td>Project description</td>
                                        <td>Full website redesign including responsive design</td>
                                    </tr>
                                    <tr>
                                        <td>10</td>
                                        <td>Progress</td>
                                        <td><span class="badge badge-warning">Optional</span></td>
                                        <td>Number 0-100 (default: 0)</td>
                                        <td>25</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-info mt-2">
                            <strong>Sample CSV Header:</strong><br>
                            <code>Title,Summary,Company Name,Client Username,Start Date,End Date,Priority,Status,Description,Progress</code>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row" id="section_Project_list">
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-header bg-transparent">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                    <h5 class="card-title mb-2 mb-md-0">{{ __('translate.Project_List') }}</h5>
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        @can('project_add')
                        <a class="btn btn-primary btn-sm" href="{{route('projects.create')}}">
                            <i class="i-Add mr-1"></i>
                            <span class="d-none d-sm-inline">{{ __('translate.Create') }}</span>
                            <span class="d-inline d-sm-none">Add</span>
                        </a>
                        @endcan
                        @can('project_delete')
                        <a v-if="selectedIds.length > 0" class="btn btn-danger btn-sm" @click="delete_selected()">
                            <i class="i-Close-Window mr-1"></i>
                            <span class="d-none d-sm-inline">{{ __('translate.Delete') }}</span>
                            <span class="d-inline d-sm-none">Delete</span>
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" class="form-inline mb-3" id="projectFilterForm">
                    <div class="form-group mr-2">
                        <label for="title" class="mr-2">Project Title</label>
                        <input type="text" id="title" name="title" class="form-control" placeholder="Project Title" value="{{ request('title') }}">
                    </div>
                    <div class="form-group mr-2">
                        <label for="client_id" class="mr-2">Client</label>
                        <select id="client_id" name="client_id" class="form-control">
                            <option value="0">All</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ request('client_id') == $client->id ? 'selected' : '' }}>{{ $client->username }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mr-2">
                        <label for="company_id" class="mr-2">Company</label>
                        <select id="company_id" name="company_id" class="form-control">
                            <option value="0">All</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mr-2">
                        <label for="status" class="mr-2">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="0">All</option>
                            <option value="not_started" {{ request('status') == 'not_started' ? 'selected' : '' }}>Not Started</option>
                            <option value="progress" {{ request('status') == 'progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            <option value="hold" {{ request('status') == 'hold' ? 'selected' : '' }}>On Hold</option>
                        </select>
                    </div>
                    <div class="form-group mr-2">
                        <label for="start_date" class="mr-2">Start Date</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="form-group mr-2">
                        <label for="end_date" class="mr-2">End Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
                <div class="table-responsive">
                    <table id="project_list_table" class="display table">
                        <thead>
                            <tr>
                                <th></th>
                                <th>
                                    <input type="checkbox" id="select-all-projects" @change="selectAll" v-model="selectAllChecked">
                                    <label for="select-all-projects" class="ml-1">{{ __('translate.Select_All') }}</label>
                                </th>
                                <th>{{ __('translate.Project') }}</th>
                                <th>{{ __('translate.Client') }}</th>
                                <th>{{ __('translate.Company') }}</th>
                                <th>{{ __('translate.Start_Date') }}</th>
                                <th>{{ __('translate.Finish_Date') }}</th>
                                <th>{{ __('translate.Status') }}</th>
                                <th>{{ __('translate.Progress') }}</th>
                                <th>{{ __('translate.Action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $project)
                            <tr :class="{ 'table-info': selectedIds.includes({{ $project->id }}) }">
                                <td></td>
                                <td>
                                    <input type="checkbox"
                                           :id="'project-' + {{ $project->id }}"
                                           @change="selected_row({{ $project->id }})"
                                           :checked="selectedIds.includes({{ $project->id }})"
                                           class="project-checkbox">
                                </td>
                                <td><a href="/projects/{{$project->id}}">{{$project->title}}</a></td>
                                <td>{{$project->client->username}}</td>
                                <td>{{$project->company->name}}</td>
                                <td>{{$project->start_date}}</td>
                                <td>{{$project->end_date}}</td>
                                <td>
                                    @if($project->status == 'completed')
                                    <span class="badge badge-success m-2">{{ __('translate.Completed') }}</span>
                                    @elseif($project->status == 'not_started')
                                    <span class="badge badge-warning m-2">{{ __('translate.Not_Started') }}</span>
                                    @elseif($project->status == 'progress')
                                    <span class="badge badge-primary m-2">{{ __('translate.In_Progress') }}</span>
                                    @elseif($project->status == 'cancelled')
                                    <span class="badge badge-danger m-2">{{ __('translate.Cancelled') }}</span>
                                    @elseif($project->status == 'hold')
                                    <span class="badge badge-secondary m-2">{{ __('translate.On_Hold') }}</span>
                                    @endif
                                </td>
                                <td>{{$project->project_progress}} %</td>

                                <td>
                                    @can('project_details')
                                    <a href="/projects/{{$project->id}}" class="ul-link-action text-info"
                                        data-toggle="tooltip" data-placement="top" title="Show">
                                        <i class="i-Eye"></i>
                                    </a>
                                    @endcan

                                    @can('project_edit')
                                    <a href="/projects/{{$project->id}}/edit" class="ul-link-action text-success"
                                        data-toggle="tooltip" data-placement="top" title="Edit">
                                        <i class="i-Edit"></i>
                                    </a>
                                    @endcan
                                    @can('project_delete')
                                    <a @click="Remove_Project( {{ $project->id}})"
                                        class="ul-link-action text-danger mr-1" data-toggle="tooltip"
                                        data-placement="top" title="Delete">
                                        <i class="i-Close-Window"></i>
                                    </a>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-js')

<script src="{{asset('assets/js/vendor/datatables.min.js')}}"></script>
<script src="{{asset('assets/js/datatables.script.js')}}"></script>

<script>
    var app = new Vue({
        el: '#section_Project_list',
        data: {
            SubmitProcessing:false,
            selectedIds:[],
            selectAllChecked: false,
        },

        methods: {

            //---- Event selected_row
            selected_row(id) {
                //in here you can check what ever condition  before append to array.
                if(this.selectedIds.includes(id)){
                    const index = this.selectedIds.indexOf(id);
                    this.selectedIds.splice(index, 1);
                }else{
                    this.selectedIds.push(id)
                }
                this.updateSelectAllState();
            },

            //---- Select All functionality
            selectAll() {
                if (this.selectAllChecked) {
                    // Select all projects
                    this.selectedIds = [];
                    @foreach($projects as $project)
                        this.selectedIds.push({{ $project->id }});
                    @endforeach
                } else {
                    // Deselect all projects
                    this.selectedIds = [];
                }
            },

            //---- Update Select All checkbox state
            updateSelectAllState() {
                const totalProjects = {{ count($projects) }};
                this.selectAllChecked = this.selectedIds.length === totalProjects && totalProjects > 0;
            },

            //--------------------------------- Remove Project ---------------------------\\
            Remove_Project(id) {

                swal({
                    title: '{{ __('translate.Are_you_sure') }}',
                    text: '{{ __('translate.You_wont_be_able_to_revert_this') }}',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0CC27E',
                    cancelButtonColor: '#FF586B',
                    confirmButtonText: '{{ __('translate.Yes_delete_it') }}',
                    cancelButtonText: '{{ __('translate.No_cancel') }}',
                    confirmButtonClass: 'btn btn-primary mr-5',
                    cancelButtonClass: 'btn btn-danger',
                    buttonsStyling: false
                }).then(function () {
                        axios
                            .delete("/projects/" + id)
                            .then(() => {
                                window.location.href = '/projects';
                                toastr.success('{{ __('translate.Deleted_in_successfully') }}');

                            })
                            .catch(() => {
                                toastr.error('{{ __('translate.There_was_something_wronge') }}');
                            });
                    });
                },

                 //--------------------------------- delete_selected ---------------------------\\
            delete_selected() {
                var self = this;
                swal({
                    title: '{{ __('translate.Are_you_sure') }}',
                    text: '{{ __('translate.You_wont_be_able_to_revert_this') }}',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0CC27E',
                    cancelButtonColor: '#FF586B',
                    confirmButtonText: '{{ __('translate.Yes_delete_it') }}',
                    cancelButtonText: '{{ __('translate.No_cancel') }}',
                    confirmButtonClass: 'btn btn-primary mr-5',
                    cancelButtonClass: 'btn btn-danger',
                    buttonsStyling: false
                }).then(function () {
                        axios
                        .post("/projects/delete/by_selection", {
                            selectedIds: self.selectedIds
                        })
                            .then(() => {
                                window.location.href = '/projects';
                                toastr.success('{{ __('translate.Deleted_in_successfully') }}');

                            })
                            .catch(() => {
                                toastr.error('{{ __('translate.There_was_something_wronge') }}');
                            });
                    });
            },






        },
        //-----------------------------Autoload function-------------------
        created() {
        }

    })

</script>

<script type="text/javascript">
    $(function () {
      "use strict";

        // Initialize project table with mobile configuration
        var projectTable;
        if (window.DataTablesMobileConfig && window.DataTablesMobileConfig.initTable) {
            projectTable = window.DataTablesMobileConfig.initTable('#project_list_table', 'project', {
                order: [[2, 'asc']], // Sort by project name
                columnDefs: [
                    { targets: 0, className: 'control', responsivePriority: 1 },
                    { targets: 1, className: 'select-checkbox', responsivePriority: 2 },
                    { targets: 2, responsivePriority: 1 }, // Project name
                    { targets: 3, responsivePriority: 3 }, // Client
                    { targets: 4, responsivePriority: 10000 }, // Company
                    { targets: 5, responsivePriority: 10001 }, // Start date
                    { targets: 6, responsivePriority: 10002 }, // End date
                    { targets: 7, responsivePriority: 2 }, // Status
                    { targets: 8, responsivePriority: 4 }, // Progress
                    { targets: -1, responsivePriority: 1 }, // Actions
                    { targets: '_all', className: 'align-middle' }
                ]
            });
        } else {
            // Fallback to standard DataTable initialization
            projectTable = $('#project_list_table').DataTable({
                order: [[2, 'asc']], // Sort by project name
                responsive: true,
                columnDefs: [
                    { targets: 0, className: 'control', responsivePriority: 1 },
                    { targets: 1, className: 'select-checkbox', responsivePriority: 2 },
                    { targets: 2, responsivePriority: 1 }, // Project name
                    { targets: 3, responsivePriority: 3 }, // Client
                    { targets: 4, responsivePriority: 10000 }, // Company
                    { targets: 5, responsivePriority: 10001 }, // Start date
                    { targets: 6, responsivePriority: 10002 }, // End date
                    { targets: 7, responsivePriority: 2 }, // Status
                    { targets: 8, responsivePriority: 4 }, // Progress
                    { targets: -1, responsivePriority: 1 }, // Actions
                    { targets: '_all', className: 'align-middle' }
                ]
            });
        }

        // Status filter functionality
        $('.status-filter-card').on('click', function() {
            var status = $(this).data('status');

            // Remove active class from all cards
            $('.status-filter-card').removeClass('card-icon-bg-success').addClass('card-icon-bg-primary');
            $('.status-filter-card[data-status="all"]').removeClass('card-icon-bg-success').addClass('card-icon-bg-secondary');

            // Add active class to clicked card
            if (status === 'all') {
                $(this).removeClass('card-icon-bg-secondary').addClass('card-icon-bg-success');
            } else {
                $(this).removeClass('card-icon-bg-primary').addClass('card-icon-bg-success');
            }

            // Apply filter to DataTable
            if (status === 'all') {
                // Clear the search filter to show all projects
                projectTable.column(7).search('').draw(); // Column 7 is the status column
            } else {
                // Map status values to display text for filtering
                var statusDisplayMap = {
                    'completed': '{{ __('translate.Completed') }}',
                    'progress': '{{ __('translate.In_Progress') }}',
                    'not_started': '{{ __('translate.Not_Started') }}',
                    'cancelled': '{{ __('translate.Cancelled') }}',
                    'hold': '{{ __('translate.On_Hold') }}'
                };

                var searchTerm = statusDisplayMap[status] || status;
                projectTable.column(7).search(searchTerm).draw(); // Column 7 is the status column
            }
        });

        // Set "All" as active by default
        $('.status-filter-card[data-status="all"]').removeClass('card-icon-bg-secondary').addClass('card-icon-bg-success');

    });
</script>

<script>
    document.getElementById('project-import-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        const messageDiv = document.getElementById('import-message');
        const submitBtn = form.querySelector('button[type="submit"]');

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';
        messageDiv.innerHTML = '<div class="alert alert-info mb-0"><i class="fas fa-spinner fa-spin"></i> Processing CSV file...</div>';

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': formData.get('_token'),
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-upload"></i> Import Projects';

            if (data.success) {
                // Show success message with statistics
                let successHtml = `
                    <div class="alert alert-success mb-0">
                        <h6><i class="fas fa-check-circle"></i> Import Completed Successfully!</h6>
                        <p class="mb-0">
                            <strong>${data.created}</strong> projects imported successfully.
                            ${data.skipped > 0 ? `<strong>${data.skipped}</strong> rows skipped.` : ''}
                            Total rows processed: <strong>${data.total_rows}</strong>
                        </p>
                    </div>
                `;

                messageDiv.innerHTML = successHtml;

                // Show detailed error information if there were skipped rows
                if (data.skippedRows && data.skippedRows.length > 0) {
                    showErrorDetails(data.skippedRows, messageDiv);
                }

                // Refresh the page after 3 seconds if all rows were successful
                if (data.skipped === 0) {
                    setTimeout(() => {
                        location.reload();
                    }, 3000);
                }
            } else {
                // Show error message
                let errorHtml = `
                    <div class="alert alert-danger mb-0">
                        <h6><i class="fas fa-exclamation-triangle"></i> Import Failed</h6>
                        <p class="mb-0">${data.message || 'An error occurred during import.'}</p>
                    </div>
                `;

                messageDiv.innerHTML = errorHtml;

                // Show detailed error information
                if (data.skippedRows && data.skippedRows.length > 0) {
                    showErrorDetails(data.skippedRows, messageDiv);
                } else if (data.errors && data.errors.length > 0) {
                    let errorsHtml = '<div class="alert alert-warning mt-2"><ul class="mb-0">';
                    data.errors.forEach(error => {
                        errorsHtml += `<li>${error}</li>`;
                    });
                    errorsHtml += '</ul></div>';
                    messageDiv.innerHTML += errorsHtml;
                }
            }
        })
        .catch(error => {
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-upload"></i> Import Projects';

            messageDiv.innerHTML = `
                <div class="alert alert-danger mb-0">
                    <h6><i class="fas fa-exclamation-triangle"></i> Network Error</h6>
                    <p class="mb-0">An error occurred while uploading the file. Please check your connection and try again.</p>
                </div>
            `;
            console.error('Import error:', error);
        });
    });

    function showErrorDetails(skippedRows, messageDiv) {
        let errorDetailsHtml = `
            <div class="alert alert-warning mt-2">
                <h6><i class="fas fa-exclamation-triangle"></i> Rows with Errors (${skippedRows.length})</h6>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>Row #</th>
                                <th>Data</th>
                                <th>Errors</th>
                            </tr>
                        </thead>
                        <tbody>
        `;

        skippedRows.forEach(function(rowInfo) {
            const rowData = Array.isArray(rowInfo.row_data) ? rowInfo.row_data.join(', ') :
                           (rowInfo.row ? JSON.stringify(rowInfo.row) : 'N/A');
            const errors = Array.isArray(rowInfo.errors) ? rowInfo.errors.join('<br>') :
                          (rowInfo.reason || 'Unknown error');

            errorDetailsHtml += `
                <tr>
                    <td><span class="badge badge-danger">${rowInfo.row_number || 'N/A'}</span></td>
                    <td><small>${rowData}</small></td>
                    <td><small class="text-danger">${errors}</small></td>
                </tr>
            `;
        });

        errorDetailsHtml += `
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Please fix the errors in your CSV file and try importing again.
                        Click "CSV Format Info" above for detailed format requirements.
                    </small>
                </div>
            </div>
        `;

        messageDiv.innerHTML += errorDetailsHtml;
    }
</script>
@endsection