<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\BonusAllowance;
use App\Notifications\BonusAllowanceGranted;
use Illuminate\Support\Facades\DB;

class BonusAllowanceController extends Controller
{
    // List all bonus/allowance records
    public function index()
    {
        $bonuses = BonusAllowance::with('employee')->orderBy('created_at', 'desc')->paginate(20);
        $employees = \App\Models\Employee::where('deleted_at', null)->get(['id', 'firstname', 'lastname']);
        return view('hrm.bonus_allowance.index', compact('bonuses', 'employees'));
    }

    // Show form to create a new bonus/allowance
    public function create()
    {
        $employees = Employee::where('deleted_at', null)->get();
        return view('hrm.bonus_allowance.create', compact('employees'));
    }

    // Store a bonus/allowance for an individual
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric',
            'type' => 'required|in:fixed,percentage',
            'description' => 'nullable|string',
        ]);

        $bonus = BonusAllowance::create([
            'employee_id' => $request->employee_id,
            'amount' => $request->amount,
            'type' => $request->type,
            'description' => $request->description,
        ]);

        // Send notification to the employee
        $employee = Employee::find($request->employee_id);
        if ($employee && $employee->user) {
            $employee->user->notify(new BonusAllowanceGranted($bonus, auth()->user()));
        }

        return redirect()->route('hrm.bonus_allowance.index')->with('success', 'Bonus/Allowance added successfully.');
    }

    // Store bonus/allowance for all or selected employees (bulk)
    public function bulkStore(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array',
            'employee_ids.*' => 'exists:employees,id',
            'amount' => 'required|numeric',
            'type' => 'required|in:fixed,percentage',
            'description' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->employee_ids as $empId) {
                $bonus = BonusAllowance::create([
                    'employee_id' => $empId,
                    'amount' => $request->amount,
                    'type' => $request->type,
                    'description' => $request->description,
                ]);

                // Send notification to each employee
                $employee = Employee::find($empId);
                if ($employee && $employee->user) {
                    $employee->user->notify(new BonusAllowanceGranted($bonus, auth()->user()));
                }
            }
        });

        return redirect()->route('hrm.bonus_allowance.index')->with('success', 'Bonuses/Allowances added successfully.');
    }
} 