@extends('layouts.master')

@section('main-content')
<div class="breadcrumb">
    <h1>{{ __('AI Task Generator') }}</h1>
    <ul>
        <li><a href="{{ route('projects.index') }}">{{ __('Projects') }}</a></li>
        <li><a href="{{ route('projects.show', $project->id) }}">{{ $project->title }}</a></li>
        <li>{{ __('AI Task Generator') }}</li>
    </ul>
</div>

<div class="separator-breadcrumb border-top"></div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">{{ __('Generate AI Tasks for') }}: {{ $project->title }}</h4>
            </div>
            <div class="card-body">
                <!-- Project Information -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> {{ __('Project Information') }}</h5>
                            <p><strong>{{ __('Title') }}:</strong> {{ $project->title }}</p>
                            <p><strong>{{ __('Summary') }}:</strong> {{ $project->summary }}</p>
                            <p><strong>{{ __('Priority') }}:</strong>
                                <span class="badge badge-{{ $project->priority == 'high' ? 'danger' : ($project->priority == 'medium' ? 'warning' : 'success') }}">
                                    {{ ucfirst($project->priority) }}
                                </span>
                            </p>
                            <p><strong>{{ __('Duration') }}:</strong> {{ $project->start_date }} to {{ $project->end_date }}</p>
                            @if($project->description)
                                <p><strong>{{ __('Description') }}:</strong> {{ $project->description }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- AI Task Generation Form -->
                <form id="aiTaskForm">
                    @csrf
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="task_complexity">{{ __('Task Complexity') }}</label>
                                <select class="form-control" id="task_complexity" name="task_complexity">
                                    <option value="low">{{ __('Low - Simple tasks with basic requirements') }}</option>
                                    <option value="medium" selected>{{ __('Medium - Standard complexity tasks') }}</option>
                                    <option value="high">{{ __('High - Complex tasks requiring detailed planning') }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="methodology">{{ __('Project Methodology') }}</label>
                                <select class="form-control" id="methodology" name="methodology">
                                    <option value="agile" selected>{{ __('Agile') }}</option>
                                    <option value="waterfall">{{ __('Waterfall') }}</option>
                                    <option value="kanban">{{ __('Kanban') }}</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_testing" name="include_testing" checked>
                                    <label class="form-check-label" for="include_testing">
                                        {{ __('Include Testing Tasks') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="include_documentation" name="include_documentation" checked>
                                    <label class="form-check-label" for="include_documentation">
                                        {{ __('Include Documentation Tasks') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="client_requirements">{{ __('Additional Client Requirements') }}</label>
                                <textarea class="form-control" id="client_requirements" name="client_requirements" rows="3"
                                    placeholder="{{ __('Describe any specific client requirements or features...') }}"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="technical_requirements">{{ __('Technical Requirements') }}</label>
                                <textarea class="form-control" id="technical_requirements" name="technical_requirements" rows="3"
                                    placeholder="{{ __('Specify technical stack, frameworks, integrations, etc...') }}"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <button type="button" id="generateAndCreateTasksBtn" class="btn btn-success btn-lg">
                                <i class="fas fa-magic"></i> {{ __('Generate & Create All Tasks') }}
                            </button>
                            <a href="{{ route('projects.show', $project->id) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> {{ __('Back to Project') }}
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Success Message Section -->
<div class="row" id="successSection" style="display: none;">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="alert alert-success" id="successMessage">
                    <h4><i class="fas fa-check-circle"></i> {{ __('Tasks Created Successfully!') }}</h4>
                    <p id="successDetails"></p>
                    <a href="{{ route('projects.show', $project->id) }}" class="btn btn-primary">
                        <i class="fas fa-eye"></i> {{ __('View Project Tasks') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">{{ __('Loading...') }}</span>
                </div>
                <p class="mt-3">{{ __('AI is generating tasks for your project...') }}</p>
                <small class="text-muted">{{ __('This may take a few moments') }}</small>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-js')
<script>
const projectId = {{ $project->id }};

$(document).ready(function() {
    // Generate and create tasks button click
    $('#generateAndCreateTasksBtn').click(function() {
        generateAndCreateTasks();
    });
});

function generateAndCreateTasks() {
    const formData = new FormData($('#aiTaskForm')[0]);

    // Show confirmation dialog
    if (!confirm('This will generate and create all AI tasks for this project. Are you sure you want to continue?')) {
        return;
    }

    $('#loadingModal').modal('show');
    $('#generateAndCreateTasksBtn').prop('disabled', true);

    $.ajax({
        url: `/projects/${projectId}/ai-tasks/generate-and-create`,
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#loadingModal').modal('hide');
            $('#generateAndCreateTasksBtn').prop('disabled', false);

            if (response.success) {
                // Show success message
                const successDetails = `
                    <strong>${response.created_count}</strong> tasks created successfully out of <strong>${response.total_generated}</strong> generated tasks.
                    <br><br>
                    <strong>Project:</strong> {{ $project->title }}
                    <br>
                    <strong>Total Estimated Hours:</strong> ${response.metadata?.estimated_duration?.total_hours || 'N/A'} hours
                    <br>
                    <strong>Estimated Duration:</strong> ${response.metadata?.estimated_duration?.estimated_days || 'N/A'} days
                `;

                $('#successDetails').html(successDetails);
                $('#successSection').show();

                // Hide the form
                $('#aiTaskForm').parent().parent().hide();

                // Show success notification
                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message);
                } else {
                    alert('Success: ' + response.message);
                }

            } else {
                showErrorMessage(response.error || 'Failed to generate and create tasks');
            }
        },
        error: function(xhr) {
            $('#loadingModal').modal('hide');
            $('#generateAndCreateTasksBtn').prop('disabled', false);

            const errorMessage = xhr.responseJSON?.error || 'An error occurred while generating and creating tasks';
            showErrorMessage(errorMessage);
        }
    });
}

function showErrorMessage(message) {
    if (typeof toastr !== 'undefined') {
        toastr.error(message);
    } else {
        alert('Error: ' + message);
    }
}
</script>
@endsection
