<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SalaryDisbursement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'employee_id', 'month', 'basic_salary', 'adjustments', 'leave_deductions',
        'bonus_allowance', 'gross_salary', 'net_payable', 'paid', 'payment_date', 'notes',
        'status', 'employee_feedback', 'admin_notes', 'reviewed_by', 'reviewed_at', 'sent_for_review_at',
        'feedback', 'feedback_at', 'approved_by', 'approved_at', 'paid_by', 'paid_at',
        'admin_response', 'admin_response_by', 'admin_response_at'
    ];

    protected $casts = [
        'basic_salary' => 'double',
        'adjustments' => 'double',
        'leave_deductions' => 'double',
        'bonus_allowance' => 'double',
        'gross_salary' => 'double',
        'net_payable' => 'double',
        'paid' => 'boolean',
        'payment_date' => 'date',
        'reviewed_at' => 'datetime',
        'sent_for_review_at' => 'datetime',
        'feedback_at' => 'datetime',
        'approved_at' => 'datetime',
        'paid_at' => 'datetime',
        'admin_response_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer()
    {
        return $this->belongsTo(Employee::class, 'reviewed_by');
    }

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function paidBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'paid_by');
    }

    public function adminResponder()
    {
        return $this->belongsTo(\App\Models\User::class, 'admin_response_by');
    }
}