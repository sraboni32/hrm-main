@extends('layouts.master')
@section('main-content')
@section('page-css')
<link rel="stylesheet" href="{{asset('assets/styles/vendor/datatables.min.css')}}">
@endsection

@php $user = auth()->user(); @endphp

<div class="breadcrumb">
    <h1>Monthly Salary Disbursement Report</h1>
    <ul>
        <li><a href="/report/monthly-salary-disbursement-report">Salary Disbursement</a></li>
    </ul>
</div>
<div class="separator-breadcrumb border-top"></div>
<div class="row">
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-header bg-transparent">
                <div class="d-flex justify-content-between align-items-center">
                    <form action="{{ route('report.monthly_salary_disbursement_report') }}" method="GET" class="d-flex gap-2">
                        <input type="month" name="month" class="form-control" value="{{ $month }}" onchange="this.form.submit()">
                        <select name="employee_id" class="form-control">
                            <option value="">All Employees</option>
                            @if(is_array($employees) && count($employees))
                            @foreach($employees as $employee)
                                    @if(is_array($employee) && isset($employee['id']))
                                        <option value="{{ $employee['id'] }}" {{ $employee_id == $employee['id'] ? 'selected' : '' }}>
                                {{ $employee['name'] }}
                            </option>
                                    @endif
                            @endforeach
                            @else
                                <option value="">No employees found</option>
                            @endif
                        </select>
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </form>
                    @can('salary_disbursement_report')
                    <button type="button" class="btn btn-success" onclick="sendForReview()">
                        Send for Review
                    </button>
                    @endcan
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table id="salary_disbursement_table" class="display table">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="select_all"></th>
                                <th>Employee Name</th>
                                <th>Basic Salary</th>
                                <th>Adjustments (+/-)</th>
                                <th>Leave Deductions</th>
                                <th>Bonus/Allowance</th>
                                <th>Gross Salary</th>
                                <th>Net Payable</th>
                                <th>Status</th>
                                <th>Review Info</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($data as $row)
                            <tr>
                                <td><input type="checkbox" class="employee_checkbox" value="{{ $row['employee_id'] }}"></td>
                                <td>{{ $row['employee_name'] }}</td>
                                <td>{{ number_format($row['basic_salary'], 2) }}</td>
                                <td>
                                    @if(in_array($row['status'], ['approved', 'paid']))
                                        <span class="form-control-plaintext">{{ number_format($row['adjustments'], 2) }}</span>
                                    @else
                                        <input type="number" step="0.01" class="form-control adjustment-input"
                                               value="{{ $row['adjustments'] }}"
                                               data-employee-id="{{ $row['employee_id'] }}"
                                               data-field="adjustments"
                                               onchange="saveInlineEdit(this)"
                                               title="Click to edit adjustment amount">
                                    @endif
                                </td>
                                <td>
                                    @if(in_array($row['status'], ['approved', 'paid']))
                                        <span class="form-control-plaintext">{{ number_format($row['leave_deductions'], 2) }}</span>
                                    @else
                                        <input type="number" step="0.01" class="form-control leave-deduction-input"
                                               value="{{ $row['leave_deductions'] }}"
                                               data-employee-id="{{ $row['employee_id'] }}"
                                               data-field="leave_deductions"
                                               onchange="saveInlineEdit(this)"
                                               title="Click to edit leave deduction amount">
                                    @endif
                                </td>
                                <td>
                                    @if(in_array($row['status'], ['approved', 'paid']))
                                        <span class="form-control-plaintext">{{ number_format($row['bonus_allowance'], 2) }}</span>
                                    @else
                                        <input type="number" step="0.01" class="form-control bonus-allowance-input"
                                               value="{{ $row['bonus_allowance'] }}"
                                               data-employee-id="{{ $row['employee_id'] }}"
                                               data-field="bonus_allowance"
                                               onchange="saveInlineEdit(this)"
                                               title="Click to edit bonus/allowance amount">
                                    @endif
                                </td>
                                <td>{{ number_format($row['gross_salary'], 2) }}</td>
                                <td>{{ number_format($row['net_payable'], 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ $row['status'] == 'pending' ? 'warning' :
                                        ($row['status'] == 'sent_for_review' ? 'info' :
                                        ($row['status'] == 'reviewed' ? 'info' :
                                        ($row['status'] == 'feedback' ? 'danger' :
                                        ($row['status'] == 'updated' ? 'warning' :
                                        ($row['status'] == 'approved' ? 'success' :
                                        ($row['status'] == 'paid' ? 'primary' : 'secondary')))))) }}">
                                        {{ ucfirst(str_replace('_', ' ', $row['status'])) }}
                                    </span>
                                    <div class="mt-1 small">
                                        @if($row['status'] == 'pending')
                                            <span class="text-muted">Waiting for review</span>
                                        @elseif($row['status'] == 'sent_for_review')
                                            <span class="text-info">Sent for employee review</span>
                                        @elseif($row['status'] == 'reviewed')
                                            <span class="text-info">Reviewed by employee, waiting for approval</span>
                                        @elseif($row['status'] == 'feedback')
                                            <span class="text-warning">Employee has concerns - requires admin attention</span>
                                        @elseif($row['status'] == 'updated')
                                            <span class="text-warning">Feedback addressed - please edit amounts and resubmit</span>
                                        @elseif($row['status'] == 'approved')
                                            <span class="text-success">Approved, waiting for payment</span>
                                        @elseif($row['status'] == 'paid')
                                            <span class="text-primary">Payment completed</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($row['reviewed_by'])
                                    <small>
                                        <strong>Reviewed by:</strong> {{ $row['reviewer_name'] }}<br>
                                        <strong>On:</strong> {{ $row['reviewed_at'] }}<br>
                                        @if($row['feedback'])
                                        <strong>Feedback:</strong> {{ $row['feedback'] }}<br>
                                        @endif
                                    </small>
                                    @endif

                                    @if($row['approved_by'])
                                    <small class="text-success">
                                        <strong>Approved by:</strong> {{ $row['approver_name'] ?: 'User ID: ' . $row['approved_by'] }}<br>
                                        <strong>On:</strong> {{ $row['approved_at'] }}<br>
                                    </small>
                                    @endif

                                    @if($row['paid_by'])
                                    <small class="text-primary">
                                        <strong>Paid by:</strong> {{ $row['paid_by_name'] ?: 'User ID: ' . $row['paid_by'] }}<br>
                                        <strong>On:</strong> {{ $row['paid_at'] }}<br>
                                    </small>
                                    @endif

                                    @if($row['admin_response'])
                                    <small class="text-warning">
                                        <strong>Admin Response by:</strong> {{ $row['admin_response_by_name'] ?: 'User ID: ' . $row['admin_response_by'] }}<br>
                                        <strong>On:</strong> {{ $row['admin_response_at'] }}<br>
                                        <strong>Response:</strong> {{ $row['admin_response'] }}<br>
                                    </small>
                                    @endif
                                </td>
                                <td>
                                    {{-- Employee Review Button --}}
                                    @if($row['can_review'] && isset($row['employee_id']) && $user && $user->employee && $user->employee->id == $row['employee_id'])
                                        <button type="button" class="btn btn-sm btn-info" onclick="showReviewModal({{ $row['id'] }})">
                                            Review
                                        </button>
                                    @endif

                                    {{-- Admin Approve Button - Show for reviewed status --}}
                                    @if($row['status'] === 'reviewed')
                                        <button type="button" class="btn btn-sm btn-success" onclick="approveDisbursement({{ $row['id'] }})">
                                            Approve
                                        </button>
                                    @endif

                                    {{-- Admin Feedback Buttons - Show for feedback status --}}
                                    @if($row['status'] === 'feedback')
                                        <button type="button" class="btn btn-sm btn-info"
                                                data-feedback="{{ $row['feedback'] }}"
                                                data-feedback-date="{{ $row['feedback_at'] }}"
                                                onclick="viewFeedback({{ $row['id'] }}, this)">
                                            <i class="fa fa-eye"></i> View Feedback
                                        </button>
                                        <button type="button" class="btn btn-sm btn-warning" onclick="showAddressFeedbackModal({{ $row['id'] }})">
                                            <i class="fa fa-reply"></i> Address Feedback
                                        </button>
                                    @endif

                                    {{-- Admin Updated Status Buttons - Show for updated status --}}
                                    @if($row['status'] === 'updated')
                                        <button type="button" class="btn btn-sm btn-warning" onclick='showEditModal(@json($row))'>
                                            <i class="fa fa-edit"></i> Edit Amounts
                                        </button>
                                        <button type="button" class="btn btn-sm btn-info" onclick="resubmitForReview({{ $row['id'] }})">
                                            <i class="fa fa-paper-plane"></i> Resubmit for Review
                                        </button>
                                    @endif

                                    {{-- Admin Mark as Paid Button - Show for approved status --}}
                                    @if($row['status'] === 'approved')
                                        <button type="button" class="btn btn-sm btn-primary" onclick="showMarkAsPaidModal({{ $row['id'] }})">
                                            Mark as Paid
                                        </button>
                                    @endif

                                    {{-- Edit Button - Show for other statuses --}}
                                    @if($user && $user->can('salary_disbursement_report') && !in_array($row['status'], ['updated', 'approved', 'paid']))
                                        <button type="button" class="btn btn-sm btn-warning" onclick='showEditModal(@json($row))'>
                                            Edit
                                        </button>
                                    @endif
                                </td>
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

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Review Salary Disbursement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="reviewForm">
                    <input type="hidden" name="disbursement_id" id="review_disbursement_id">
                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" class="form-control" required>
                            <option value="reviewed">Approve</option>
                            <option value="feedback">Provide Feedback</option>
                        </select>
                    </div>
                    <div class="form-group" id="feedbackGroup" style="display: none;">
                        <label>Feedback</label>
                        <textarea name="feedback" class="form-control" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitReview()">Submit Review</button>
            </div>
        </div>
    </div>
</div>

<!-- Mark as Paid Modal -->
<div class="modal fade" id="markAsPaidModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mark as Paid</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="markAsPaidForm">
                    <input type="hidden" name="disbursement_id" id="paid_disbursement_id">
                    <div class="form-group">
                        <label>Payment Date</label>
                        <input type="date" name="payment_date" class="form-control" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitMarkAsPaid()">Mark as Paid</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Salary Disbursement</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editForm">
                    <input type="hidden" name="disbursement_id" id="edit_disbursement_id">
                    <div class="form-group">
                        <label>Basic Salary</label>
                        <input type="number" step="0.01" name="basic_salary" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Adjustments</label>
                        <input type="number" step="0.01" name="adjustments" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Leave Deductions</label>
                        <input type="number" step="0.01" name="leave_deductions" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Bonus/Allowance</label>
                        <input type="number" step="0.01" name="bonus_allowance" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Gross Salary <small class="text-muted">(Auto-calculated)</small></label>
                        <input type="number" step="0.01" name="gross_salary" class="form-control" readonly style="background-color: #f8f9fa;">
                    </div>
                    <div class="form-group">
                        <label>Net Payable <small class="text-muted">(Auto-calculated)</small></label>
                        <input type="number" step="0.01" name="net_payable" class="form-control" readonly style="background-color: #f8f9fa;">
                    </div>
                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="submitEdit()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- View Feedback Modal -->
<div class="modal fade" id="viewFeedbackModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Employee Feedback</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label><strong>Employee Feedback:</strong></label>
                    <div id="feedbackContent" class="border p-3 bg-light"></div>
                </div>
                <div class="form-group">
                    <label><strong>Submitted On:</strong></label>
                    <div id="feedbackDate"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Address Feedback Modal -->
<div class="modal fade" id="addressFeedbackModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Address Employee Feedback</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addressFeedbackForm">
                    <input type="hidden" name="disbursement_id" id="address_disbursement_id">

                    <div class="form-group">
                        <label><strong>Admin Response:</strong></label>
                        <textarea name="admin_response" class="form-control" rows="4" placeholder="Provide your response to the employee's feedback..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label><strong>Action to Take:</strong></label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="action" value="approve" id="actionApprove">
                            <label class="form-check-label" for="actionApprove">
                                <strong>Approve as-is</strong> - No changes needed, approve the disbursement
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="action" value="edit" id="actionEdit">
                            <label class="form-check-label" for="actionEdit">
                                <strong>Edit disbursement</strong> - Modify amounts and resubmit for review
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="action" value="resubmit" id="actionResubmit">
                            <label class="form-check-label" for="actionResubmit">
                                <strong>Send back for re-review</strong> - No changes, but employee should review again
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitAddressFeedback()">Submit Response</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-css')
<style>
    /* Inline editing styles */
    .adjustment-input, .leave-deduction-input, .bonus-allowance-input {
        border: 1px solid #ddd;
        transition: all 0.3s ease;
    }

    .adjustment-input:hover, .leave-deduction-input:hover, .bonus-allowance-input:hover {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .adjustment-input:focus, .leave-deduction-input:focus, .bonus-allowance-input:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    /* Visual feedback for saving states */
    .border-warning {
        border-color: #ffc107 !important;
        box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25) !important;
    }

    .border-success {
        border-color: #28a745 !important;
        box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25) !important;
    }

    .border-danger {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
    }

    /* Disabled state for approved/paid disbursements */
    .form-control-plaintext {
        padding: 0.375rem 0.75rem;
        margin-bottom: 0;
        font-size: 1rem;
        line-height: 1.5;
        color: #495057;
        background-color: transparent;
        border: solid transparent;
        border-width: 1px 0;
    }
</style>
@endsection

@section('page-js')
<script src="{{asset('assets/js/vendor/datatables.min.js')}}"></script>
<script src="{{asset('assets/js/datatables.script.js')}}"></script>
<script>
    $(function () {
        $('#salary_disbursement_table').DataTable({
            dom: "<'row'<'col-sm-12 col-md-7'lB><'col-sm-12 col-md-5 p-0'f>>rtip",
            buttons: [
                {
                    extend: 'collection',
                    text: 'EXPORT',
                    buttons: [
                        {
                            text: 'CSV (Enhanced)',
                            action: function (e, dt, button, config) {
                                exportSalaryReport('csv');
                            }
                        },
                        {
                            text: 'PDF (Professional)',
                            action: function (e, dt, button, config) {
                                exportSalaryReport('pdf');
                            }
                        },
                        {
                            extend: 'csv',
                            text: 'CSV (Basic)',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7, 8] // Exclude checkbox, review info, and actions columns
                            }
                        },
                        {
                            extend: 'excel',
                            text: 'Excel (Basic)',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7, 8]
                            }
                        },
                        {
                            extend: 'pdf',
                            text: 'PDF (Basic)',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7, 8]
                            }
                        },
                        {
                            extend: 'print',
                            text: 'Print',
                            exportOptions: {
                                columns: [1, 2, 3, 4, 5, 6, 7, 8]
                            }
                        }
                    ]
                }
            ]
        });

        // Select/Deselect all checkboxes
        $('#select_all').on('click', function() {
            $('.employee_checkbox').prop('checked', this.checked);
        });

        // Show/hide feedback field based on status selection
        $('select[name="status"]').change(function() {
            if ($(this).val() === 'feedback') {
                $('#feedbackGroup').show();
            } else {
                $('#feedbackGroup').hide();
            }
        });
    });

    // Save inline edits for adjustments, leave deductions, and bonus allowances
    function saveInlineEdit(inputElement) {
        const $input = $(inputElement);
        const employeeId = $input.data('employee-id');
        const field = $input.data('field');
        const value = $input.val() || 0;
        const month = $('input[name="month"]').val();

        // Add visual feedback
        $input.addClass('border-warning');
        $input.prop('disabled', true);

        $.ajax({
            url: '{{ route("salary-disbursement.update-inline") }}',
            method: 'POST',
            data: {
                employee_id: employeeId,
                month: month,
                field: field,
                value: value,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    // Update the gross salary and net payable in the UI
                    if (response.updated_disbursement) {
                        const $row = $input.closest('tr');
                        $row.find('td:nth-child(8)').text(parseFloat(response.updated_disbursement.gross_salary).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        $row.find('td:nth-child(9)').text(parseFloat(response.updated_disbursement.net_payable).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                    }

                    // Success feedback
                    $input.removeClass('border-warning').addClass('border-success');
                    setTimeout(function() {
                        $input.removeClass('border-success');
                    }, 2000);
                } else {
                    alert(response.message || 'Failed to save changes');
                    $input.removeClass('border-warning').addClass('border-danger');
                }
            },
            error: function(xhr) {
                alert('Failed to save changes: ' + (xhr.responseJSON?.message || 'Unknown error'));
                $input.removeClass('border-warning').addClass('border-danger');
            },
            complete: function() {
                $input.prop('disabled', false);
                setTimeout(function() {
                    $input.removeClass('border-danger');
                }, 3000);
            }
        });
    }

    function sendForReview() {
        const selectedEmployees = [];
        const adjustments = {};
        const leaveDeductions = {};
        const bonusAllowances = {};

        $('#salary_disbursement_table tbody tr').each(function() {
            const checkbox = $(this).find('input[type="checkbox"]');
            if (checkbox.length && checkbox.is(':checked')) {
                const employeeId = checkbox.val();
                selectedEmployees.push(employeeId);
                adjustments[employeeId] = $(this).find('.adjustment-input').val() || 0;
                leaveDeductions[employeeId] = $(this).find('.leave-deduction-input').val() || 0;
                bonusAllowances[employeeId] = $(this).find('.bonus-allowance-input').val() || 0;
            }
        });

        if (selectedEmployees.length === 0) {
            alert('Please select at least one employee');
            return;
        }

        $.ajax({
            url: '{{ route("salary-disbursement.send-for-review") }}',
            method: 'POST',
            data: {
                month: $('input[name="month"]').val(),
                employee_ids: selectedEmployees,
                adjustments: adjustments,
                leave_deductions: leaveDeductions,
                bonus_allowance: bonusAllowances,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    alert('Sent for review!');
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr) {
                alert('Failed to send for review');
            }
        });
    }

    function showReviewModal(disbursementId) {
        $('#review_disbursement_id').val(disbursementId);
        $('#reviewModal').modal('show');
    }

    function submitReview() {
        const formData = new FormData($('#reviewForm')[0]);
        formData.append('_token', '{{ csrf_token() }}');

        $.ajax({
            url: '/salary-disbursement/' + formData.get('disbursement_id') + '/review',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.message);
                }
            },
            error: function(xhr) {
                alert('Failed to submit review');
            }
        });
    }

    function approveDisbursement(disbursementId) {
        if (!confirm('Are you sure you want to approve this salary report? This will send a notification to the employee.')) return;
        $.ajax({
            url: '/salary-disbursement/' + disbursementId + '/approve',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    alert('Salary report approved successfully. A notification has been sent to the employee.');
                    location.reload();
                } else {
                    alert(response.message || 'Failed to approve.');
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Failed to approve.');
            }
        });
    }

    function showMarkAsPaidModal(disbursementId) {
        $('#paid_disbursement_id').val(disbursementId);
        $('#markAsPaidModal').modal('show');
    }

    function submitMarkAsPaid() {
        var disbursementId = $('#paid_disbursement_id').val();
        var paymentDate = $('#markAsPaidForm input[name="payment_date"]').val();
        if (!paymentDate) {
            alert('Please select a payment date.');
            return;
        }
        $.ajax({
            url: '/salary-disbursement/' + disbursementId + '/mark-as-paid',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                payment_date: paymentDate
            },
            success: function(response) {
                if (response.success) {
                    $('#markAsPaidModal').modal('hide');
                    alert('Salary has been marked as paid. A notification has been sent to the employee.');
                    location.reload();
                } else {
                    alert(response.message || 'Failed to mark as paid.');
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Failed to mark as paid.');
            }
        });
    }

    function showEditModal(row) {
        var data = typeof row === 'string' ? JSON.parse(row) : row;
        $('#edit_disbursement_id').val(data.id);
        $('#editForm [name="basic_salary"]').val(data.basic_salary);
        $('#editForm [name="adjustments"]').val(data.adjustments);
        $('#editForm [name="leave_deductions"]').val(data.leave_deductions);
        $('#editForm [name="bonus_allowance"]').val(data.bonus_allowance);
        $('#editForm [name="gross_salary"]').val(data.gross_salary);
        $('#editForm [name="net_payable"]').val(data.net_payable);
        $('#editForm [name="notes"]').val(data.notes || '');

        // Add event listeners for auto-calculation
        $('#editForm [name="basic_salary"], #editForm [name="adjustments"], #editForm [name="leave_deductions"], #editForm [name="bonus_allowance"]').off('input').on('input', function() {
            calculateModalTotals();
        });

        $('#editModal').modal('show');
    }

    // Calculate totals in edit modal
    function calculateModalTotals() {
        var basicSalary = parseFloat($('#editForm [name="basic_salary"]').val()) || 0;
        var adjustments = parseFloat($('#editForm [name="adjustments"]').val()) || 0;
        var leaveDeductions = parseFloat($('#editForm [name="leave_deductions"]').val()) || 0;
        var bonusAllowance = parseFloat($('#editForm [name="bonus_allowance"]').val()) || 0;

        var grossSalary = basicSalary + adjustments + bonusAllowance - leaveDeductions;
        var netPayable = grossSalary; // Add any additional calculations here if needed

        $('#editForm [name="gross_salary"]').val(grossSalary.toFixed(2));
        $('#editForm [name="net_payable"]').val(netPayable.toFixed(2));
    }

    function submitEdit() {
        var id = $('#edit_disbursement_id').val();
        if (!id) {
            alert('Disbursement ID is missing.');
            return;
        }

        // Get form data
        var formData = $('#editForm').serialize();

        $.ajax({
            url: '/salary-disbursement/' + id,
            method: 'POST',
            data: formData + '&_method=PUT&_token={{ csrf_token() }}',
            success: function(response) {
                if (response.success) {
                    // Update the table row with new values
                    updateTableRow(id, {
                        basic_salary: $('#editForm [name="basic_salary"]').val(),
                        adjustments: $('#editForm [name="adjustments"]').val(),
                        leave_deductions: $('#editForm [name="leave_deductions"]').val(),
                        bonus_allowance: $('#editForm [name="bonus_allowance"]').val(),
                        gross_salary: $('#editForm [name="gross_salary"]').val(),
                        net_payable: $('#editForm [name="net_payable"]').val(),
                        notes: $('#editForm [name="notes"]').val()
                    });

                    // Close modal
                    $('#editModal').modal('hide');

                    // Show success message
                    alert('Disbursement updated successfully!');
                } else {
                    alert(response.message || 'Failed to update record');
                }
            },
            error: function(xhr) {
                alert('Failed to update record: ' + (xhr.responseJSON?.message || 'Unknown error'));
            }
        });
    }

    // Update table row with new values
    function updateTableRow(disbursementId, data) {
        // Find the row with the disbursement ID
        var $row = null;
        $('#salary_disbursement_table tbody tr').each(function() {
            var $editButton = $(this).find('button[onclick*="showEditModal"]');
            if ($editButton.length > 0) {
                var onclickAttr = $editButton.attr('onclick');
                if (onclickAttr && onclickAttr.includes('"id":' + disbursementId)) {
                    $row = $(this);
                    return false; // Break the loop
                }
            }
        });

        if ($row) {
            // Update the values in the table
            $row.find('td:nth-child(3)').text(parseFloat(data.basic_salary).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $row.find('.adjustment-input').val(data.adjustments);
            $row.find('.leave-deduction-input').val(data.leave_deductions);
            $row.find('.bonus-allowance-input').val(data.bonus_allowance);
            $row.find('td:nth-child(8)').text(parseFloat(data.gross_salary).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            $row.find('td:nth-child(9)').text(parseFloat(data.net_payable).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        }
    }

    // View feedback modal
    function viewFeedback(disbursementId, buttonElement) {
        var feedback = $(buttonElement).data('feedback') || 'No feedback provided';
        var feedbackDate = $(buttonElement).data('feedback-date') || 'Not available';

        $('#feedbackContent').text(feedback);
        $('#feedbackDate').text(feedbackDate);
        $('#viewFeedbackModal').modal('show');
    }

    // Show address feedback modal
    function showAddressFeedbackModal(disbursementId) {
        $('#address_disbursement_id').val(disbursementId);
        $('#addressFeedbackForm')[0].reset();
        $('#addressFeedbackModal').modal('show');
    }

    // Submit address feedback
    function submitAddressFeedback() {
        var disbursementId = $('#address_disbursement_id').val();
        var adminResponse = $('#addressFeedbackForm [name="admin_response"]').val();
        var action = $('#addressFeedbackForm [name="action"]:checked').val();

        if (!adminResponse.trim()) {
            alert('Please provide an admin response.');
            return;
        }

        if (!action) {
            alert('Please select an action to take.');
            return;
        }

        $.ajax({
            url: '/salary-disbursement/' + disbursementId + '/address-feedback',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                admin_response: adminResponse,
                action: action
            },
            success: function(response) {
                if (response.success) {
                    $('#addressFeedbackModal').modal('hide');
                    alert(response.message);
                    location.reload();
                } else {
                    alert(response.message || 'Failed to address feedback.');
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Failed to address feedback.');
            }
        });
    }

    // Resubmit for review
    function resubmitForReview(disbursementId) {
        if (!confirm('Are you sure you want to resubmit this disbursement for employee review?')) return;

        $.ajax({
            url: '/salary-disbursement/' + disbursementId + '/resubmit-for-review',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    alert('Disbursement resubmitted for employee review successfully.');
                    location.reload();
                } else {
                    alert(response.message || 'Failed to resubmit for review.');
                }
            },
            error: function(xhr) {
                alert(xhr.responseJSON?.message || 'Failed to resubmit for review.');
            }
        });
    }

    // Enhanced Export Function for Salary Disbursement
    function exportSalaryReport(format) {
        var month = $('input[name="month"]').val();
        var employee_id = $('select[name="employee_id"]').val() || '';

        var params = new URLSearchParams({
            format: format,
            month: month,
            employee_id: employee_id
        });

        window.open('{{ route("export.salary_disbursement_report") }}?' + params.toString(), '_blank');
    }
</script>
@endsection