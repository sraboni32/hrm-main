@extends('layouts.master')
@section('main-content')
<div class="breadcrumb">
    <h1>Job Vacancies</h1>
    <ul>
        <li><a href="{{ route('job_vacancies.admin.create') }}" class="btn btn-primary">Add New Job Vacancy</a></li>
    </ul>
</div>
<div class="separator-breadcrumb border-top"></div>

<div class="row mb-3">
    <div class="col-md-6">
        <a href="{{ route('job_vacancies.admin.export') }}" class="btn btn-success">Export Job Vacancies</a>
    </div>
    <div class="col-md-6">
        <form action="{{ route('job_vacancies.admin.import') }}" method="POST" enctype="multipart/form-data" class="form-inline float-right">
            @csrf
            <input type="file" name="import_file" class="form-control mr-2" required accept=".csv">
            <button type="submit" class="btn btn-primary">Import Job Vacancies</button>
        </form>
    </div>
</div>

<form id="bulk-delete-form" action="{{ route('job_vacancies.admin.delete_by_selection') }}" method="POST">
    @csrf
    <button type="submit" class="btn btn-danger mb-2" id="bulk-delete-btn">Delete Selected</button>
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead>
                <tr>
                    <th><input type="checkbox" id="select-all"></th>
                    <th scope="col">#</th>
                    <th scope="col">Title</th>
                    <th scope="col">Company</th>
                    <th scope="col">Status</th>
                    <th scope="col">Created By</th>
                    <th scope="col">Created At</th>
                    <th scope="col" class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($job_vacancies as $key => $job)
                    <tr>
                        <td><input type="checkbox" name="ids[]" value="{{ $job->id }}" class="row-checkbox"></td>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $job->title }}</td>
                        <td>{{ $job->company->name }}</td>
                        <td>
                            <span class="badge {{ $job->status ? 'badge-success' : 'badge-danger' }}">
                                {{ $job->status ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td>{{ $job->creator->username }}</td>
                        <td>{{ $job->created_at->format('Y-m-d H:i') }}</td>
                        <td class="text-center">
                            <a href="{{ route('job_vacancies.admin.edit', $job->id) }}" class="btn btn-info btn-sm">
                                <i class="i-Edit"></i> Edit
                            </a>
                            <!-- Single delete form: now uses POST method for individual row delete -->
                            <form action="{{ route('job_vacancies.admin.destroy.post', $job->id) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this job vacancy?')">
                                    <i class="i-Delete"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</form>
<!-- Bulk delete form above uses POST only. Single row delete forms use DELETE. -->
<script>
    document.getElementById('select-all').addEventListener('change', function() {
        let checked = this.checked;
        document.querySelectorAll('.row-checkbox').forEach(cb => cb.checked = checked);
    });

    document.getElementById('bulk-delete-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        const selected = Array.from(document.querySelectorAll('.row-checkbox:checked'));
        if (selected.length === 0) {
            alert('Please select at least one job vacancy to delete.');
            return;
        }
        if (!confirm('Are you sure you want to delete selected job vacancies?')) {
            return;
        }
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
            if (data.success) {
                // Remove deleted rows from the table
                selected.forEach(cb => {
                    cb.closest('tr').remove();
                });
                alert(data.message);
            } else {
                alert(data.message || 'Failed to delete selected job vacancies.');
            }
        })
        .catch(() => {
            alert('An error occurred while deleting.');
        });
    });
</script>
@endsection 