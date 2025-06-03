@extends('layouts.master')
@section('main-content')
@section('page-css')
<link rel="stylesheet" href="{{asset('assets/styles/vendor/datatables.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/styles/vendor/datepicker.min.css')}}">


@endsection

<div class="breadcrumb">
    <h1>{{ __('translate.Attendances') }}</h1>
    <ul>
        <li><a href="/report/attendance">{{ __('translate.Attendance_Report') }}</a></li>
        <li>{{ __('translate.Attendances') }}</li>
    </ul>
</div>

<div class="separator-breadcrumb border-top"></div>

<div class="row" id="section_Attendance_report">
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-header text-right bg-transparent">

                <a class="btn btn-primary btn-md m-1" id="Show_Modal_Filter"><i class="i-Filter-2 text-white mr-2"></i>
                    {{ __('translate.Filter') }}</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="ul-contact-list" class="display table Attendance_datatable">
                        <thead>
                            <tr>
                                <th>{{ __('translate.Employee') }}</th>
                                <th>{{ __('translate.Company') }}</th>
                                <th>{{ __('translate.Date') }}</th>
                                <th>{{ __('translate.Time_In') }}</th>
                                <th>{{ __('translate.Time_Out') }}</th>
                                <th>{{ __('translate.Time_Late') }}</th>
                                <th>{{ __('translate.Depart_early') }}</th>
                                <th>{{ __('translate.Overtime') }}</th>
                                <th>{{ __('translate.Work_Duration') }}</th>
                                <th>{{ __('translate.Rest_Duration') }}</th>
                                <th>{{ __('translate.Status') }}</th>
                            </tr>
                        </thead>


                    </table>
                </div>
            </div>
        </div>
        <!-- Modal Filter Attendance -->
        <div class="modal fade" id="Filter_Attendance_Modal" tabindex="-1" role="dialog"
            aria-labelledby="Filter_Attendance_Modal" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ __('translate.Filter') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">

                        <form method="POST" id="Filter_Attendance_report">
                            @csrf
                            <div class="row">

                                <div class="col-md-4">
                                    <label for="employee_id" class="ul-form__label">{{ __('translate.Employee') }}
                                    </label>
                                    <select name="employee_id" id="employee_id" class="form-control">
                                        <option value="0">{{ __('translate.All') }}</option>
                                        @foreach ($employees as $employee)
                                        <option value="{{$employee->id}}">{{$employee->username}}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label for="start_date" class="ul-form__label">{{ __('translate.From_Date') }}
                                    </label>
                                    <input type="text" class="form-control date" name="start_date" id="start_date"
                                        placeholder="{{ __('translate.From_Date') }}" value="">
                                </div>

                                <div class="col-md-4">
                                    <label for="end_date" class="ul-form__label">{{ __('translate.To_Date') }} </label>
                                    <input type="text" class="form-control date" name="end_date" id="end_date"
                                        placeholder="{{ __('translate.To_Date') }}" value="">
                                </div>


                            </div>

                            <div class="row mt-3">

                                <div class="col-md-6">
                                    <button type="submit" class="btn btn-outline-success">
                                        {{ __('translate.Filter') }}
                                    </button>

                                    <button id="Clear_Form" class="btn btn-outline-danger">
                                        {{ __('translate.Clear') }}
                                    </button>
                                </div>

                            </div>


                        </form>

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
<script src="{{asset('assets/js/datepicker.min.js')}}"></script>

<script type="text/javascript">
    $(function () {
      "use strict";

      $(document).ready(function () {

            // Initialize datepickers
            $("#start_date,#end_date").datepicker({
                format: 'yyyy-mm-dd',
                changeMonth: true,
                changeYear: true,
                autoclose: true,
                todayHighlight: true,
                orientation: 'bottom auto'
            });

            // Set default dates
            var today = new Date();
            var oneYearAgo = new Date();
            oneYearAgo.setFullYear(today.getFullYear() - 1);

            $("#end_date").datepicker("setDate", today);
            $("#start_date").datepicker("setDate", oneYearAgo);

            // Debug: Log initial values
            console.log('Initial start_date:', $("#start_date").val());
            console.log('Initial end_date:', $("#end_date").val());
        });


        // init datatable.
        Attendance_datatable();
        function Attendance_datatable(start_date = '', end_date = '', employee_id = ''){
            console.log('Initializing DataTable with params:', {start_date, end_date, employee_id});

            var dataTable = $('#ul-contact-list').DataTable({
                processing: true,
                responsive: true,
                serverSide: true,
                autoWidth: false,
                pageLength: 10,
                "order": [[ 0, "desc" ]],
                ajax: {
                    url: "{{ route('attendance_report_index') }}",
                    data: {
                        start_date: start_date === null ? '' : start_date,
                        end_date: end_date === null ? '' : end_date,
                        employee_id: employee_id == '0' ? '' : employee_id,
                        "_token": "{{ csrf_token()}}"
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTable AJAX Error:', {xhr, error, thrown});
                        alert('Error loading attendance data. Please check the console for details.');
                    }
                },

                columns: [
                    {data: 'employee.username', name: 'Employee'},
                    {data: 'company.name', name: 'Company'},
                    {data: 'date', name: 'Date'},
                    {data: 'clock_in', name: 'Time In'},
                    {data: 'clock_out', name: 'Time Out'},
                    {data: 'late_time', name: 'Time Late'},

                    {data: 'depart_early', name: 'Depart early'},
                    {data: 'overtime', name: 'Overtime'},
                    {data: 'total_work', name: 'Work Duration'},
                    {data: 'total_rest', name: 'Rest Duration'},
                    {data: 'status', name: 'Status'},
                ],
                dom: "<'row'<'col-sm-12 col-md-7'lB><'col-sm-12 col-md-5 p-0'f>>rtip",
                oLanguage:
                    {
                    sLengthMenu: "_MENU_",
                    sSearch: '',
                    sSearchPlaceholder: "Search..."
                },
                buttons: [
                {
                    extend: 'collection',
                    text: 'EXPORT',
                    buttons: [
                        'csv','excel', 'pdf', 'print'
                    ]
                }]
            });
        }

         // Clear Filter

         $('#Clear_Form').on('click' , function (e) {
            e.preventDefault();

            // Reset dates to default (today and one year ago)
            var today = new Date();
            var oneYearAgo = new Date();
            oneYearAgo.setFullYear(today.getFullYear() - 1);

            $("#end_date").datepicker("setDate", today);
            $("#start_date").datepicker("setDate", oneYearAgo);
            $('#employee_id').val('0');

            console.log('Cleared - start_date:', $("#start_date").val());
            console.log('Cleared - end_date:', $("#end_date").val());

            // Destroy existing DataTable and reinitialize with cleared filters
            $('#ul-contact-list').DataTable().destroy();
            Attendance_datatable('', '', '');
        });

         // Show Modal Filter

        $('#Show_Modal_Filter').on('click' , function (e) {
            $('#Filter_Attendance_Modal').modal('show');
        });


         // Submit Filter

        $('#Filter_Attendance_report').on('submit' , function (e) {
            e.preventDefault();
            var start_date = $('#start_date').val();
            var end_date = $('#end_date').val();
            var employee_id = $('#employee_id').val();

            // Validate dates
            if (start_date && end_date && start_date > end_date) {
                alert('Start date cannot be greater than end date');
                return false;
            }

            console.log('Filtering with:', {start_date, end_date, employee_id}); // Debug log

            $('#ul-contact-list').DataTable().destroy();
            Attendance_datatable(start_date, end_date, employee_id);

            $('#Filter_Attendance_Modal').modal('hide');
        });

    });
</script>


@endsection