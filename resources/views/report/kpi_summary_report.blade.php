@extends('layouts.master')

@section('main-content')
<div class="breadcrumb">
    <h1>KPI Summary Report</h1>
    <ul>
        <li><a href="#">Reports</a></li>
        <li>KPI Summary</li>
    </ul>
</div>
<div class="separator-breadcrumb border-top"></div>
<div class="row">
    <div class="col-12">
        <div class="card mt-4">
            <div class="card-header">
                <h3>KPI Summary Report</h3>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <button type="button" class="btn btn-outline-primary btn-sm mr-2" onclick="setQuickRange('week')">This Week</button>
                    <button type="button" class="btn btn-outline-primary btn-sm mr-2" onclick="setQuickRange('month')">This Month</button>
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="setQuickRange('year')">This Year</button>
                </div>
                <form method="GET" class="form-inline mb-3" id="kpiFilterForm">
                    <div class="form-group mr-2">
                        <label for="search" class="mr-2">Search</label>
                        <input type="text" id="search" name="search" class="form-control" placeholder="Name, Email, Username" value="{{ request('search', $search ?? '') }}">
                    </div>
                    <div class="form-group mr-2">
                        <label for="start_date" class="mr-2">Start Date</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date', $start_date ?? (\Carbon\Carbon::now()->startOfWeek(1)->format('Y-m-d'))) }}">
                    </div>
                    <div class="form-group mr-2">
                        <label for="end_date" class="mr-2">End Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date', $end_date ?? (\Carbon\Carbon::now()->format('Y-m-d'))) }}">
                    </div>
                    <div class="form-group mr-2">
                        <label for="employee_id" class="mr-2">Member Name</label>
                        <select id="employee_id" name="employee_id" class="form-control">
                            <option value="0">All</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('employee_id') == $employee->id ? 'selected' : '' }}>{{ $employee->firstname }} {{ $employee->lastname }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Filter</button>
                </form>
                <div class="table-responsive">
                    <table id="kpi-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Member Name</th>
                                <th>Role</th>
                                <th>Total Logged Hours</th>
                                <th>Expected Hours</th>
                                <th>Mode (Office/Remote)</th>
                                <th>Total Task Count</th>
                                <th>Tasks Completed</th>
                                <th>Total Lack/Extra Time</th>
                                <th>Total Leave Left</th>
                                <th>Quality Score (%)</th>
                                <th>Final Rating</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $row)
                                <tr>
                                    <td>{{ $row['name'] }}</td>
                                    <td>{{ $row['role'] }}</td>
                                    <td>{{ $row['total_logged_hours'] }}</td>
                                    <td>{{ $row['expected_hours'] }}</td>
                                    <td>
                                        <div><strong>Office:</strong> {{ $row['mode']['office'] }} hrs</div>
                                        <div><strong>Remote:</strong> {{ $row['mode']['remote'] }} hrs</div>
                                    </td>
                                    <td>{{ $row['total_task_count'] }}</td>
                                    <td>{{ $row['tasks_completed'] }}</td>
                                    <td>{{ $row['lack_extra_time'] }}</td>
                                    <td>{{ $row['total_leave_left'] }}</td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="100" class="form-control kpi-edit quality-score-input" value="{{ $row['quality_score'] }}" style="width:90px;" />
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0" max="100" class="form-control kpi-edit final-rating-input" value="{{ $row['final_rating'] }}" style="width:90px;" />
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $data->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function setQuickRange(range) {
    const today = new Date();
    let start, end;
    if (range === 'week') {
        const day = today.getDay();
        const diffToMonday = today.getDate() - day + (day === 0 ? -6 : 1);
        start = new Date(today.setDate(diffToMonday));
        end = new Date();
    } else if (range === 'month') {
        start = new Date(today.getFullYear(), today.getMonth(), 1);
        end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
    } else if (range === 'year') {
        start = new Date(today.getFullYear(), 0, 1);
        end = new Date(today.getFullYear(), 11, 31);
    }
    document.getElementById('start_date').value = start.toISOString().slice(0, 10);
    document.getElementById('end_date').value = end.toISOString().slice(0, 10);
    document.getElementById('kpiFilterForm').submit();
}
</script>
@endsection

@section('page-js')
<link rel="stylesheet" href="{{ asset('assets/js/vendor/datatables.min.css') }}">
<script src="{{ asset('assets/js/vendor/datatables.min.js') }}"></script>
<script src="{{ asset('assets/js/datatables.script.js') }}"></script>
<script src="{{ asset('assets/js/vendor/jszip.min.js') }}"></script>
<script src="{{ asset('assets/js/vendor/pdfmake.min.js') }}"></script>
<script src="{{ asset('assets/js/vendor/vfs_fonts.js') }}"></script>
<script src="{{ asset('assets/js/vendor/buttons.html5.min.js') }}"></script>
<script src="{{ asset('assets/js/vendor/buttons.print.min.js') }}"></script>

<script>
$(document).ready(function() {
    var table = $('#kpi-table').DataTable({
        dom: "<'row'<'col-sm-12 col-md-7'lB><'col-sm-12 col-md-5 p-0'f>>rtip",
        buttons: [
            {
                extend: 'collection',
                text: 'EXPORT',
                buttons: [
                    {
                        extend: 'csv',
                        exportOptions: {
                            format: {
                                body: function (data, row, column, node) {
                                    var input = $('input', node);
                                    if (input.length) {
                                        return input.val();
                                    }
                                    return $(node).text();
                                }
                            }
                        }
                    },
                    {
                        extend: 'excel',
                        exportOptions: {
                            format: {
                                body: function (data, row, column, node) {
                                    var input = $('input', node);
                                    if (input.length) {
                                        return input.val();
                                    }
                                    return $(node).text();
                                }
                            }
                        }
                    },
                    {
                        extend: 'pdf',
                        exportOptions: {
                            format: {
                                body: function (data, row, column, node) {
                                    var input = $('input', node);
                                    if (input.length) {
                                        return input.val();
                                    }
                                    return $(node).text();
                                }
                            }
                        }
                    },
                    {
                        extend: 'print',
                        exportOptions: {
                            format: {
                                body: function (data, row, column, node) {
                                    var input = $('input', node);
                                    if (input.length) {
                                        return input.val();
                                    }
                                    return $(node).text();
                                }
                            }
                        }
                    }
                ]
            }
        ],
        paging: false, // Laravel handles pagination
        ordering: true,
        searching: false // Laravel handles search
    });
});
</script>
@endsection