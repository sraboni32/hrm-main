@extends('layouts.master')
@section('main-content')
<div class="breadcrumb">
    <h1>My Salary Disbursements</h1>
</div>
<div class="separator-breadcrumb border-top"></div>
<div class="row">
    <div class="col-md-12">
        <div class="card text-left">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Month</th>
                                <th>Basic Salary</th>
                                <th>Adjustments</th>
                                <th>Leave Deductions</th>
                                <th>Bonus/Allowance</th>
                                <th>Gross Salary</th>
                                <th>Net Payable</th>
                                <th>Status</th>
                                <th>Feedback</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($disbursements as $disb)
                            <tr>
                                <td>{{ $disb->month }}</td>
                                <td>{{ number_format($disb->basic_salary, 2) }}</td>
                                <td>{{ number_format($disb->adjustments, 2) }}</td>
                                <td>{{ number_format($disb->leave_deductions, 2) }}</td>
                                <td>{{ number_format($disb->bonus_allowance, 2) }}</td>
                                <td>{{ number_format($disb->gross_salary, 2) }}</td>
                                <td>{{ number_format($disb->net_payable, 2) }}</td>
                                <td>
                                    <span class="badge badge-{{ $disb->status == 'pending' ? 'warning' :
                                        ($disb->status == 'sent_for_review' ? 'info' :
                                        ($disb->status == 'reviewed' ? 'info' :
                                        ($disb->status == 'feedback' ? 'danger' :
                                        ($disb->status == 'updated' ? 'warning' :
                                        ($disb->status == 'approved' ? 'success' :
                                        ($disb->status == 'paid' ? 'primary' : 'secondary')))))) }}">
                                        {{ ucfirst(str_replace('_', ' ', $disb->status)) }}
                                    </span>
                                    <div class="mt-1 small">
                                        @if($disb->status == 'pending')
                                            <span class="text-muted">Waiting for review</span>
                                        @elseif($disb->status == 'sent_for_review')
                                            <span class="text-info">Please review your salary report</span>
                                        @elseif($disb->status == 'reviewed')
                                            <span class="text-info">Waiting for admin approval</span>
                                        @elseif($disb->status == 'feedback')
                                            <span class="text-warning">Feedback provided - waiting for admin response</span>
                                        @elseif($disb->status == 'updated')
                                            <span class="text-warning">Admin has updated amounts - please review the changes</span>
                                        @elseif($disb->status == 'approved')
                                            <span class="text-success">Approved, waiting for payment</span>
                                        @elseif($disb->status == 'paid')
                                            <span class="text-primary">Payment completed on {{ $disb->payment_date ? $disb->payment_date->format('Y-m-d') : 'N/A' }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>{{ $disb->feedback }}
                                    @if($disb->reviewed_by && $disb->reviewed_at)
                                        <br>
                                        <small class="text-muted">
                                            Reviewed by: {{ optional(optional($disb->reviewer)->firstname) ? $disb->reviewer->firstname . ' ' . $disb->reviewer->lastname : 'N/A' }}<br>
                                            On: {{ $disb->reviewed_at ? \Carbon\Carbon::parse($disb->reviewed_at)->format('Y-m-d H:i:s') : '' }}
                                        </small>
                                    @endif
                                </td>
                                <td>
                                    @if($disb->status == 'sent_for_review')
                                        <button type="button" class="btn btn-sm btn-info" onclick="showReviewModal({{ $disb->id }}, false)">
                                            Review
                                        </button>
                                    @elseif($disb->status == 'updated')
                                        <button type="button" class="btn btn-sm btn-warning" onclick="showReviewModal({{ $disb->id }}, false)">
                                            <i class="fa fa-edit"></i> Review Updated Amounts
                                        </button>
                                        @if($disb->feedback)
                                            <button type="button" class="btn btn-sm btn-secondary"
                                                    data-feedback="{{ $disb->feedback }}"
                                                    data-feedback-date="{{ $disb->feedback_at }}"
                                                    data-admin-response="{{ $disb->admin_response }}"
                                                    data-admin-response-date="{{ $disb->admin_response_at }}"
                                                    onclick="showViewFeedbackModal(this)">
                                                <i class="fa fa-eye"></i> View Previous Feedback
                                            </button>
                                        @endif
                                    @elseif($disb->status == 'reviewed')
                                        <span class="text-success">Reviewed ✓</span>
                                    @elseif($disb->status == 'approved')
                                        <span class="text-success">Approved ✓</span>
                                    @elseif($disb->status == 'paid')
                                        <span class="text-primary">Paid ✓</span>
                                    @elseif($disb->feedback)
                                        <button type="button" class="btn btn-sm btn-secondary"
                                                data-feedback="{{ $disb->feedback }}"
                                                data-feedback-date="{{ $disb->feedback_at }}"
                                                data-admin-response="{{ $disb->admin_response }}"
                                                data-admin-response-date="{{ $disb->admin_response_at }}"
                                                onclick="showViewFeedbackModal(this)">
                                            View Feedback
                                        </button>
                                    @else
                                        <span class="text-muted">No actions</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="10" class="text-center">No salary disbursements found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@include('report.partials.review_modal')

<!-- View Feedback Modal for Employees -->
<div class="modal fade" id="viewFeedbackModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">My Feedback & Admin Response</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-warning text-white">
                                <h6 class="mb-0">My Feedback</h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label><strong>Feedback:</strong></label>
                                    <div id="employeeFeedbackContent" class="border p-3 bg-light"></div>
                                </div>
                                <div class="form-group">
                                    <label><strong>Submitted On:</strong></label>
                                    <div id="employeeFeedbackDate" class="text-muted"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0">Admin Response</h6>
                            </div>
                            <div class="card-body">
                                <div id="adminResponseSection">
                                    <div class="form-group">
                                        <label><strong>Admin Response:</strong></label>
                                        <div id="adminResponseContent" class="border p-3 bg-light"></div>
                                    </div>
                                    <div class="form-group">
                                        <label><strong>Responded On:</strong></label>
                                        <div id="adminResponseDate" class="text-muted"></div>
                                    </div>
                                </div>
                                <div id="noAdminResponse" class="text-center text-muted" style="display: none;">
                                    <i class="fa fa-clock"></i><br>
                                    Waiting for admin response...
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection
@section('page-js')
<script>
function showReviewModal(disbursementId, readOnly = false) {
    $('#review_disbursement_id').val(disbursementId);
    if (readOnly) {
        $('#reviewForm select[name="status"]').prop('disabled', true);
        $('#reviewForm textarea[name="feedback"]').prop('readonly', true);
        $('#reviewForm button[type="submit"]').hide();
    } else {
        $('#reviewForm select[name="status"]').prop('disabled', false);
        $('#reviewForm textarea[name="feedback"]').prop('readonly', false);
        $('#reviewForm button[type="submit"]').show();
    }
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
$('select[name="status"]').change(function() {
    if ($(this).val() === 'feedback') {
        $('#feedbackGroup').show();
    } else {
        $('#feedbackGroup').hide();
    }
});

// Show view feedback modal for employees
function showViewFeedbackModal(buttonElement) {
    var feedback = $(buttonElement).data('feedback') || 'No feedback provided';
    var feedbackDate = $(buttonElement).data('feedback-date') || 'Not available';
    var adminResponse = $(buttonElement).data('admin-response');
    var adminResponseDate = $(buttonElement).data('admin-response-date');

    // Set employee feedback
    $('#employeeFeedbackContent').text(feedback);
    $('#employeeFeedbackDate').text(feedbackDate);

    // Set admin response
    if (adminResponse && adminResponse.trim()) {
        $('#adminResponseContent').text(adminResponse);
        $('#adminResponseDate').text(adminResponseDate || 'Not available');
        $('#adminResponseSection').show();
        $('#noAdminResponse').hide();
    } else {
        $('#adminResponseSection').hide();
        $('#noAdminResponse').show();
    }

    $('#viewFeedbackModal').modal('show');
}
</script>
@endsection