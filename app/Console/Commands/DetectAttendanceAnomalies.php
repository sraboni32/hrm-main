<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Notifications\AttendanceAnomaly;
use App\Models\Holiday;

class DetectAttendanceAnomalies extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:detect-anomalies';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Detect missed punch-in and absence anomalies and send notifications.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $today = Carbon::now()->format('Y-m-d');
        $employees = Employee::where('deleted_at', null)->get();

        foreach ($employees as $employee) {
            // Get office shift and weekend days
            $officeShift = $employee->office_shift;
            $weekendDays = [];
            if ($officeShift && $officeShift->weekend_days) {
                $weekendDays = collect(explode(',', $officeShift->weekend_days))->filter(fn($d) => $d !== '')->map('intval')->toArray();
            }
            $todayWeekday = Carbon::now()->dayOfWeek; // 0=Sunday, 6=Saturday
            // Skip if today is a weekend for this employee
            if (in_array($todayWeekday, $weekendDays)) {
                continue;
            }
            // Check if today is a workday for this employee
            // (skip if on leave, holiday, or off day)
            $hasAttendance = Attendance::where('employee_id', $employee->id)
                ->where('date', $today)
                ->exists();

            // Check if on leave
            $onLeave = $employee->leave()
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->exists();

            // Check if today is a holiday for the employee's company
            $isHoliday = Holiday::where('company_id', $employee->company_id)
                ->where('deleted_at', null)
                ->where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->exists();

            if (!$hasAttendance && !$onLeave && !$isHoliday) {
                if ($employee->user) {
                    $employee->user->notify(new AttendanceAnomaly($employee, $today, 'Missed Punch-In/Absence'));
                }
            }
        }
        $this->info('Attendance anomaly detection completed.');
        return 0;
    }
}
