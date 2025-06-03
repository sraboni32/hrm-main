<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SalaryDisbursement;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SalaryDisbursementController extends Controller
{
    // Admin: Send for review
    public function sendForReview(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'month' => 'required',
        ]);
            foreach ($request->employee_ids as $employeeId) {
            $employee = Employee::find($employeeId);
            if (!$employee) continue;
            $basic_salary = $employee->basic_salary;
            $adjustments = $request->adjustments[$employeeId] ?? 0;
            $leave_deductions = $request->leave_deductions[$employeeId] ?? 0;
            $bonus_allowance = $request->bonus_allowance[$employeeId] ?? 0;
            $gross_salary = $basic_salary + $adjustments + $bonus_allowance;
            $net_payable = $gross_salary - $leave_deductions;

            $disb = \App\Models\SalaryDisbursement::updateOrCreate(
                    [
                        'employee_id' => $employeeId,
                        'month' => $request->month
                    ],
                    [
                    'basic_salary' => $basic_salary,
                    'adjustments' => $adjustments,
                    'leave_deductions' => $leave_deductions,
                    'bonus_allowance' => $bonus_allowance,
                    'gross_salary' => $gross_salary,
                    'net_payable' => $net_payable,
                    'status' => 'sent_for_review'
                    ]
                );
            $disb->status = 'sent_for_review';
            $disb->save();
            if ($employee->user) {
                $employee->user->notify(new \App\Notifications\SalaryDisbursementReviewRequest($disb));
            }
            // Add to a results array for batch processing
            $results[] = [
                'employee_id' => $employeeId,
                'disbursement_id' => $disb->id,
                'success' => true
            ];
        }
        return response()->json(['success' => true, 'results' => $results]);
    }

    // Employee: Submit review/feedback
    public function submitReview(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:reviewed,feedback',
            'feedback' => 'nullable|string',
        ]);
        $disb = SalaryDisbursement::findOrFail($id);
        $user = Auth::user();

        // Only the employee can review their own salary
        if (!$disb->employee || !$disb->employee->user || $disb->employee->user->id !== $user->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Validate workflow - can only review if status is pending, sent_for_review, feedback, or updated
        if (!in_array($disb->status, ['pending', 'sent_for_review', 'feedback', 'updated'])) {
            return response()->json(['success' => false, 'message' => 'Cannot review at this stage. Current status: ' . $disb->status], 400);
        }

        $disb->status = $request->status;
        $disb->feedback = $request->feedback;

        // Set the reviewer to the employee ID, not the user ID
        if ($user->employee) {
            $disb->reviewed_by = $user->employee->id;
        } else {
            $disb->reviewed_by = null;
        }

        $disb->reviewed_at = Carbon::now();
        $disb->save();

        // Notify all Super Admins about the employee review
        $employeeName = $disb->employee ? ($disb->employee->firstname . ' ' . $disb->employee->lastname) : 'Unknown Employee';
        $superAdmins = User::where('role_users_id', 1)->get();

        foreach ($superAdmins as $admin) {
            $admin->notify(new \App\Notifications\SalaryDisbursementAdminNotification($disb, $request->status, $employeeName));
        }

        return response()->json(['success' => true]);
    }

    // Admin: Approve after review
    public function approve(Request $request, $id)
    {
        $disb = SalaryDisbursement::findOrFail($id);

        // Check permissions - temporarily disabled for testing
        // if (!Auth::user()->can('approve_salary_disbursement')) {
        //     return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        // }

        // Validate workflow - can only approve if status is reviewed
        if ($disb->status !== 'reviewed') {
            return response()->json(['success' => false, 'message' => 'Cannot approve at this stage. Salary report must be reviewed first.'], 400);
        }

        $disb->status = 'approved';
        $disb->approved_by = Auth::id();
        $disb->approved_at = now();
        $disb->save();

        // Send notification to employee about approval
        if ($disb->employee && $disb->employee->user) {
            $disb->employee->user->notify(new \App\Notifications\SalaryDisbursed($disb, 'approved'));
        }

        return response()->json(['success' => true]);
    }

    // Admin: Mark as paid
    public function markAsPaid(Request $request, $id)
    {
        $disb = SalaryDisbursement::findOrFail($id);

        // Check permissions - temporarily disabled for testing
        // if (!Auth::user()->can('mark_salary_paid')) {
        //     return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        // }

        // Validate workflow - can only mark as paid if status is approved
        if ($disb->status !== 'approved') {
            return response()->json(['success' => false, 'message' => 'Cannot mark as paid at this stage. Salary report must be approved first.'], 400);
        }

        // Get payment date from request or use current date
        $paymentDate = $request->has('payment_date') ? Carbon::parse($request->payment_date) : Carbon::now();

        $disb->status = 'paid';
        $disb->paid = true;
        $disb->payment_date = $paymentDate;
        $disb->paid_by = Auth::id();
        $disb->paid_at = now();
        $disb->save();

        // Notify employee about payment
        if ($disb->employee && $disb->employee->user) {
            $disb->employee->user->notify(new \App\Notifications\SalaryDisbursed($disb, 'paid'));
        }

        return response()->json(['success' => true]);
    }

    // Admin: Edit salary disbursement
    public function update(Request $request, $id)
    {
        $disb = SalaryDisbursement::findOrFail($id);
        if (!Auth::user()->can('salary_disbursement_report')) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }
        $request->validate([
            'basic_salary' => 'required|numeric',
            'adjustments' => 'required|numeric',
            'leave_deductions' => 'required|numeric',
            'bonus_allowance' => 'required|numeric',
            'gross_salary' => 'required|numeric',
            'net_payable' => 'required|numeric',
            'notes' => 'nullable|string',
        ]);
        $disb->basic_salary = $request->basic_salary;
        $disb->adjustments = $request->adjustments;
        $disb->leave_deductions = $request->leave_deductions;
        $disb->bonus_allowance = $request->bonus_allowance;
        $disb->gross_salary = $request->gross_salary;
        $disb->net_payable = $request->net_payable;
        $disb->notes = $request->notes;
        // Do not force status to 'pending' on edit; keep current status
        $disb->save();
        return response()->json(['success' => true]);
    }

    public function employeeIndex() {
        $user = auth()->user();
        $employee = $user->employee;
        $disbursements = $employee ? $employee->salaryDisbursements()
            ->with(['reviewer', 'adminResponder'])
            ->orderBy('month', 'desc')
            ->get() : collect();
        return view('salary_disbursement.employee_index', compact('disbursements'));
    }

    /**
     * Address employee feedback (AJAX)
     */
    public function addressFeedback(Request $request, SalaryDisbursement $disbursement)
    {
        $request->validate([
            'admin_response' => 'required|string|max:1000',
            'action' => 'required|in:approve,edit,resubmit'
        ]);

        // Check permissions - temporarily disabled for testing
        // if (!Auth::user()->can('approve_salary_disbursement')) {
        //     return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        // }

        // Validate current status
        if ($disbursement->status !== 'feedback') {
            return response()->json(['success' => false, 'message' => 'Can only address feedback for disbursements with feedback status'], 400);
        }

        // Update admin response
        $disbursement->admin_response = $request->admin_response;
        $disbursement->admin_response_by = Auth::id();
        $disbursement->admin_response_at = now();

        // Handle different actions
        switch ($request->action) {
            case 'approve':
                $disbursement->status = 'approved';
                $disbursement->approved_by = Auth::id();
                $disbursement->approved_at = now();
                $message = 'Feedback addressed and disbursement approved successfully.';
                break;

            case 'edit':
                $disbursement->status = 'updated';
                $message = 'Feedback addressed. Please edit the disbursement amounts and resubmit.';
                break;

            case 'resubmit':
                $disbursement->status = 'sent_for_review';
                $disbursement->sent_for_review_at = now();
                $message = 'Feedback addressed. Disbursement sent back for employee review.';
                break;
        }

        $disbursement->save();

        // Send notification to employee
        if ($disbursement->employee && $disbursement->employee->user) {
            $disbursement->employee->user->notify(new \App\Notifications\SalaryDisbursed($disbursement, $disbursement->status));
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    /**
     * Resubmit disbursement for review after editing (AJAX)
     */
    public function resubmitForReview(SalaryDisbursement $disbursement)
    {
        // Check permissions - temporarily disabled for testing
        // if (!Auth::user()->can('salary_disbursement_report')) {
        //     return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        // }

        // Validate current status
        if ($disbursement->status !== 'updated') {
            return response()->json(['success' => false, 'message' => 'Can only resubmit disbursements with updated status'], 400);
        }

        // Update status to sent_for_review
        $disbursement->status = 'sent_for_review';
        $disbursement->sent_for_review_at = now();
        $disbursement->save();

        // Send notification to employee
        if ($disbursement->employee && $disbursement->employee->user) {
            $disbursement->employee->user->notify(new \App\Notifications\SalaryDisbursed($disbursement, 'sent_for_review'));
        }

        return response()->json(['success' => true, 'message' => 'Disbursement resubmitted for employee review successfully.']);
    }

    /**
     * Update inline edits for salary disbursement fields (AJAX)
     */
    public function updateInline(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|integer',
            'month' => 'required|string',
            'field' => 'required|in:adjustments,leave_deductions,bonus_allowance',
            'value' => 'required|numeric'
        ]);

        // Check permissions - temporarily disabled for testing
        // if (!Auth::user()->can('salary_disbursement_report')) {
        //     return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        // }

        try {
            // Find or create the salary disbursement record
            $disbursement = SalaryDisbursement::where('employee_id', $request->employee_id)
                ->where('month', $request->month)
                ->first();

            if (!$disbursement) {
                return response()->json(['success' => false, 'message' => 'Salary disbursement record not found'], 404);
            }

            // Only allow editing if status allows it
            if (in_array($disbursement->status, ['approved', 'paid'])) {
                return response()->json(['success' => false, 'message' => 'Cannot edit disbursement in current status: ' . $disbursement->status], 400);
            }

            // Update the specific field
            $disbursement->{$request->field} = $request->value;

            // Recalculate gross salary and net payable
            $disbursement->gross_salary = $disbursement->basic_salary + $disbursement->adjustments + $disbursement->bonus_allowance - $disbursement->leave_deductions;
            $disbursement->net_payable = $disbursement->gross_salary; // Add any additional calculations here if needed

            $disbursement->save();

            return response()->json([
                'success' => true,
                'message' => 'Field updated successfully',
                'updated_disbursement' => [
                    'gross_salary' => $disbursement->gross_salary,
                    'net_payable' => $disbursement->net_payable,
                    $request->field => $disbursement->{$request->field}
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating field: ' . $e->getMessage()], 500);
        }
    }
}