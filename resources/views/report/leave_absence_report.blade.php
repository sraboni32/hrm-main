@extends('layouts.master')
@section('main-content')
@section('page-css')
<link rel="stylesheet" href="{{asset('assets/styles/vendor/datatables.min.css')}}">
@endsection

<div class="breadcrumb">
    <h1>Leave & Absence Report</h1>
    <ul>
        <li><a href="/report/leave-absence-report">Leave & Absence</a></li>
    </ul>
</div>
<div class="separator-breadcrumb border-top"></div>
<div class="row">
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-header bg-transparent">
                <form method="GET" action="" class="form-inline">
                    <input type="text" name="search" class="form-control mr-2" placeholder="Search Employee" value="{{ request('search') }}">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="leaveAbsenceTable" class="display table">
                        <thead>
                            <tr>
                                <th>Employee Name</th>
                                <th>Leave Type</th>
                                <th>From Date</th>
                                <th>To Date</th>
                                <th>Total Days</th>
                                <th>Status</th>
                                <th>Balance Days Left</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $row)
                            <tr>
                                <td>{{ $row['employee_name'] }}</td>
                                <td>{{ $row['leave_type'] }}</td>
                                <td>{{ $row['from_date'] }}</td>
                                <td>{{ $row['to_date'] }}</td>
                                <td>{{ $row['total_days'] }}</td>
                                <td>{{ ucfirst($row['status']) }}</td>
                                <td>{{ $row['balance_days_left'] }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {{ $data->links() }}
                    </div>
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
    $(function () {
        $('#leaveAbsenceTable').DataTable({
            dom: "<'row'<'col-sm-12 col-md-7'lB><'col-sm-12 col-md-5 p-0'f>>rtip",
            buttons: [
                {
                    extend: 'collection',
                    text: 'EXPORT',
                    buttons: [
                        'csv','excel', 'pdf', 'print'
                    ]
                }
            ]
        });
    });
</script>
@endsection 