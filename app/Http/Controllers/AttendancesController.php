<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\Company;
use App\Models\Employee;
use App\Models\Holiday;
use Carbon\Carbon;
use DateTime;
use Exception;
use DB;
use App\utils\helpers;
use DataTables;
use Illuminate\Support\Facades\Http;
use App\Notifications\AttendanceCorrected;



class AttendancesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user_auth = auth()->user();
        $employee_id = $request->get('employee_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $allEmployees = Employee::where('deleted_at', null)->get(['id', 'username']);

        $query = Attendance::where('deleted_at', '=', null);

        if ($employee_id) {
            $query->where('employee_id', $employee_id);
        } elseif ($user_auth->role_users_id != 1) {
            $query->where('employee_id', $user_auth->id);
        }

        if ($start_date) {
            $query->where('date', '>=', $start_date);
        }
        if ($end_date) {
            $query->where('date', '<=', $end_date);
        }

        $attendances = $query->orderBy('id', 'desc')->get();

        // Summary logic
        $employeeSummaries = [];
        $grouped = $attendances->groupBy('employee_id');
        foreach ($grouped as $employee_id => $records) {
            $totalWorkMinutes = 0;
            $dates = [];
            foreach ($records as $attendance) {
                if ($attendance->total_work) {
                    list($h, $m) = explode(':', $attendance->total_work);
                    $totalWorkMinutes += ((int)$h) * 60 + ((int)$m);
                }
                $dates[] = $attendance->date;
            }
            $employee = $records->first()->employee;
            $employeeSummaries[] = [
                'employee' => $employee ? $employee->username : 'Unknown',
                'total_work' => sprintf('%02d:%02d', intdiv($totalWorkMinutes, 60), $totalWorkMinutes % 60),
                'date_start' => $dates ? min($dates) : '',
                'date_end' => $dates ? max($dates) : '',
            ];
        }

        return view('attendance.attendance_list', compact('attendances', 'employeeSummaries', 'allEmployees', 'employee_id', 'start_date', 'end_date'));
    }




    public function daily_attendance(Request $request)
    {
        $user_auth = auth()->user();
        $day_now = Carbon::now()->format('Y-m-d');
        $day_in_now = strtolower(Carbon::now()->format('l')) . '_in';

        if ($request->ajax()) {
		    if ($user_auth->can('attendance_view')){
                if ($user_auth->role_users_id == 1){
                    $employee = Employee::with(['office_shift','attendance' => function ($query) use ($day_now)
                    {
                        $query->where('date' , $day_now);
                    },
                    'office_shift',
                    'company:id,name',
                    'leave' => function ($query) use ($day_now)
                    {
                        $query->where('start_date' ,'<=', $day_now)->where('end_date' ,'>=', $day_now);
                    }]
                    )
                    ->select('id','company_id','username','office_shift_id')
                    ->where('joining_date' ,'<=', $day_now)
                    ->where('leaving_date' , NULL)
                    ->where('deleted_at' , NULL)
                    ->get();

                }else{

                    $employee = Employee::with(['office_shift','attendance' => function ($query) use ($day_now)
                    {
                        $query->where('date' , $day_now);
                    },
                    'office_shift',
                    'company:id,name',
                    'leave' => function ($query) use ($day_now)
                    {
                        $query->where('start_date' ,'<=', $day_now)->where('end_date' ,'>=', $day_now);
                    }]
                    )
                    ->select('id','company_id','username','office_shift_id')
                    ->where('id' ,'=', $user_auth->id)
                    ->where('joining_date' ,'<=', $day_now)
                    ->where('leaving_date' , NULL)
                    ->where('deleted_at' , NULL)
                    ->get();
                }

                $holidays = Holiday::select('id','company_id','start_date','end_date')
                ->where('start_date' ,'<=', $day_now)
                ->where('end_date' ,'>=', $day_now)
                ->where('deleted_at' , NULL)
                ->first();

            return datatables()->of($employee)
                ->setRowId(function($employee)
                {
                    return $employee->id;
                })
                ->addColumn('username' , function($employee)
                {
                    return $employee->username;
                })
                ->addColumn('company' , function($employee)
                {
                    return $employee->company->name;
                })
                ->addColumn('date' , function($employee) use($day_now)
                {
                    if($employee->attendance->isEmpty()){
                        return Carbon::parse($day_now)->format('Y-m-d');
                    }else{
                        $attendace_row = $employee->attendance->first();
                        return $attendace_row->date;
                    }
                })
                ->addColumn('status' , function($employee) use($holidays , $day_in_now)
                {
                    if($employee->attendance->isEmpty()){
                        
                        if(is_null($employee->office_shift->$day_in_now ?? null || ($employee->office_shift->day_in_now == '')))
                        {
                            return 'Off Day' ;
                        }
                        if($holidays)
                        {
                            if($employee->company_id == $holidays->company_id)
                            {
                                return 'Holiday';
                            }
                        }
                        if($employee->leave->isEmpty())
                        {
                            return 'Absent';
                        }

                    return 'On leave';
                    }else
                    {
                        $attendace_row = $employee->attendance->first();
                        return $attendace_row->status;
                    }
                    })
                ->addColumn('clock_in' , function($employee)
                {
                    if($employee->attendance->isEmpty())
                    {
                        return '---';
                    }else
                    {
                        $attendace_row = $employee->attendance->first();
                        return $attendace_row->clock_in;
                    }
                })
                ->addColumn('clock_out' , function($employee)
                {
                    if($employee->attendance->isEmpty())
                    {
                        return '---';
                    }else
                    {
                        $attendace_row = $employee->attendance->last();
                        return $attendace_row->clock_out;
                    }
                })
                ->addColumn('late_time' , function($employee)
                {
                    if($employee->attendance->isEmpty())
                    {
                        return '---';
                    }else
                    {
                        $attendace_row = $employee->attendance->first();
                        return $attendace_row->late_time;
                    }
                })
                ->addColumn('depart_early' , function($employee)
                {
                    if($employee->attendance->isEmpty())
                    {
                        return '---';
                    }else
                    {
                        $attendace_row = $employee->attendance->first();
                        return $attendace_row->depart_early;
                    }
                })
                ->addColumn('overtime' , function($employee)
                {
                    if($employee->attendance->isEmpty())
                    {
                        return '---';
                    }else
                    {
                        $total = 0;
                        foreach($employee->attendance as $attendance_row)
                        {
                            sscanf($attendance_row->overtime, '%d:%d' , $hour , $min);
                            $total += $hour *60 + $min;
                        }
                        if($h = floor($total / 60))
                        {
                            $total %= 60;
                        }
                        return sprintf('%02d:%02d', $h, $total);
                    }
                })


                ->addColumn('total_work' , function($employee)
                {
                    if($employee->attendance->isEmpty())
                    {
                         return '---';
                    }else
                        {
                        $total = 0;
                        foreach($employee->attendance as $attendance_row)
                            {
                                sscanf($attendance_row->total_work, '%d:%d' , $hour , $min);
                                $total += $hour * 60 + $min;
                            }
                        if($h = floor($total / 60))
                        {
                             $total %= 60;
                        }
                        return sprintf('%02d:%02d', $h, $total);
                    }
                })

                ->addColumn('total_rest' , function($employee)
                {
                    if($employee->attendance->isEmpty())
                    {
                        return '---';
                    }else
                    {
                        $total = 0;
                        foreach($employee->attendance as $attendance_row)
                        {
                            sscanf($attendance_row->total_rest, '%d:%d' , $hour , $min);
                            $total += $hour * 60 + $min;
                        }
                        if($h = floor($total / 60))
                        {
                            $total %= 60;
                        }
                        return sprintf('%02d:%02d', $h, $total);
                    }
                })
            ->rawColumns(['action'])
            ->make(true);

            
            }else{
                return abort('403', __('You are not authorized'));
            }
        }
        return view('attendance.attendance_daily');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user_auth = auth()->user();
		if ($user_auth->can('attendance_add')){

            $companies = Company::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','name']);
            return response()->json([
                'companies'       => $companies,
            ]);

        }
        return abort('403', __('You are not authorized'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('attendance_add')){
                
            $this->validate($request, [
                'company_id'      => 'required',
                'employee_id'      => 'required',
                'date'           => 'required',
                'clock_in'      => 'required',
                'clock_out'      => 'required',
                'mode'           => 'required|in:office,remote',
            ]);


            $employee_id  = $request->employee_id;
            $date  = $request->date;
            $company_id  = $request->company_id;
            $clock_in  = $request->clock_in;
            $clock_out  = $request->clock_out;

            try{
                $clock_in  = new DateTime($clock_in);
                $clock_out  = new DateTime($clock_out);
            }catch(Exception $e){
                return $e;
            }

            
            $employee = Employee::with('office_shift')->findOrFail($employee_id);
            $office_shift = $employee->office_shift;
            $data = [];
            if ($office_shift && $office_shift->is_flexible) {
                // Flexible shift: allow any time, skip late/early/overtime
                $data['employee_id'] = $employee_id;
                $data['company_id'] = $company_id;
                $data['date'] = $date;
                $data['clock_in'] = $clock_in->format('H:i');
                $data['clock_out'] = $clock_out->format('H:i');
                $data['status'] = 'present';
                $work_duration = $clock_in->diff($clock_out)->format('%H:%I');
                $data['total_work'] = $work_duration;
                $data['depart_early'] = '00:00';
                $data['late_time'] = '00:00';
                $data['overtime'] = '00:00';
                $data['clock_in_out'] = 0;
                $data['clock_in_ip'] = '';
                $data['clock_out_ip'] = '';
                $data['mode'] = $request->mode;
                Attendance::create($data);
                // Discord notification logic (unchanged)
                \Log::info('About to send Discord notification', [
                    'url' => env('DISCORD_BOT_URL', 'http://localhost:3001').'/notify-punch-in',
                    'username' => $employee->username ?? ($employee->first_name ?? 'Employee'),
                    'punchTime' => $data['clock_in'],
                    'secret' => env('ATTENDANCE_SECRET', 'yourStrongSecretHere'),
                    'channelId' => '1341382328680972360',
                ]);
                try {
                    $response = Http::post(env('DISCORD_BOT_URL', 'http://localhost:3001').'/notify-punch-in', [
                        'username'  => $employee->username ?? ($employee->first_name ?? 'Employee'),
                        'punchTime' => $data['clock_in'],
                        'secret'    => env('ATTENDANCE_SECRET', 'yourStrongSecretHere'),
                        'channelId' => '1341382328680972360',
                    ]);
                    \Log::info('Discord notification response', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Discord notification failed: '.$e->getMessage());
                }
                return response()->json(['success' => true]);
            }

            $day_now = Carbon::parse($request->date)->format('l');
            $day_in_now = strtolower($day_now) . '_in';
            $day_out_now = strtolower($day_now) . '_out';

            $shift_in = $employee->office_shift->$day_in_now;
            $shift_out = $employee->office_shift->$day_out_now;

            if($shift_in ==null){
                $data['employee_id'] = $employee_id;
                $data['company_id'] = $company_id;
                $data['date'] = $date;
                $data['clock_in'] = $clock_in->format('H:i');
                $data['clock_out'] = $clock_out->format('H:i');
                $data['status'] = 'present';

                $work_duration = $clock_in->diff($clock_out)->format('%H:%I');
                $data['total_work'] = $work_duration;
                $data['depart_early'] = '00:00';
                $data['late_time'] = '00:00';
                $data['overtime'] = '00:00';
                $data['clock_in_out'] = 0;
                $data['mode'] = $request->mode;

            }

            try{
                $shift_in  = new DateTime(substr($shift_in, 0, -2));
                $shift_out  = new DateTime(substr($shift_out, 0, -2));
            }catch(Exception $e){
                return $e;
            }

            $data['employee_id'] = $employee_id;
            $data['date'] = $date;

            if($clock_in > $shift_in){
                $time_diff = $shift_in->diff($clock_in)->format('%H:%I');
                $data['clock_in'] = $clock_in->format('H:i');
                $data['late_time'] = $time_diff;
            }else{
                $data['clock_in'] = $shift_in->format('H:i');
                $data['late_time'] = '00:00';
            }


            if($clock_out < $shift_out){
                $time_diff = $shift_out->diff($clock_out)->format('%H:%I');
                $data['clock_out'] = $clock_out->format('H:i');
                $data['depart_early'] = $time_diff;

            }elseif($clock_out > $shift_out){
                $time_diff = $shift_out->diff($clock_out)->format('%H:%I');
                $data['clock_out'] = $clock_out->format('H:i');
                $data['overtime'] = $time_diff;
                $data['depart_early'] = '00:00';
            }else{
                $data['clock_out'] = $shift_out->format('H:i');
                $data['overtime'] = '00:00';
                $data['depart_early'] = '00:00';
            }

            $data['status'] = 'present';
            $work_duration = $clock_in->diff($clock_out)->format('%H:%I');
            $data['total_work'] = $work_duration;
            $data['clock_in_out'] = 0;
            $data['mode'] = $request->mode;


            Attendance::create($data);

            // Send Discord notification for punch in
            \Log::info('About to send Discord notification', [
                'url' => env('DISCORD_BOT_URL', 'http://localhost:3001').'/notify-punch-in',
                'username' => $employee->username ?? ($employee->first_name ?? 'Employee'),
                'punchTime' => $data['clock_in'],
                'secret' => env('ATTENDANCE_SECRET', 'yourStrongSecretHere'),
                'channelId' => '1341382328680972360',
            ]);
            try {
                $response = Http::post(env('DISCORD_BOT_URL', 'http://localhost:3001').'/notify-punch-in', [
                    'username'  => $employee->username ?? ($employee->first_name ?? 'Employee'),
                    'punchTime' => $data['clock_in'],
                    'secret'    => env('ATTENDANCE_SECRET', 'yourStrongSecretHere'),
                    'channelId' => '1341382328680972360',
                ]);
                \Log::info('Discord notification response', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
            } catch (\Exception $e) {
                \Log::error('Discord notification failed: '.$e->getMessage());
            }

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('attendance_edit')){

            $companies = Company::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','name']);
            return response()->json([
                'companies'       => $companies,
            ]);

        }
        return abort('403', __('You are not authorized'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        \Log::info('Attendance update request', ['request' => $request->all(), 'attendance_id' => $id]);
        $user_auth = auth()->user();
		if ($user_auth->can('attendance_edit')){

            $this->validate($request, [
                'company_id'      => 'required',
                'employee_id'      => 'required',
                'date'           => 'required',
                'clock_in'      => 'required',
                'clock_out'      => 'required',
                'mode'           => 'required|in:office,remote',
            ]);

            $employee_id  = $request->employee_id;
            $date  = $request->date;
            $clock_in  = $request->clock_in;
            $clock_out  = $request->clock_out;

            try{
                $clock_in  = new DateTime($clock_in);
                $clock_out  = new DateTime($clock_out);
            }catch(Exception $e){
                \Log::error('Attendance update DateTime error', ['error' => $e->getMessage()]);
                return $e;
            }

            $day_now = Carbon::parse($request->date)->format('l');
        
            $employee = Employee::with('office_shift')->findOrFail($employee_id);
            
            $day_in_now = strtolower($day_now) . '_in';
            $day_out_now = strtolower($day_now) . '_out';

            $shift_in = $employee->office_shift->$day_in_now;
            $shift_out = $employee->office_shift->$day_out_now;

            \Log::info('Office shift info', [
                'employee_id' => $employee_id,
                'office_shift_id' => $employee->office_shift->id ?? null,
                'is_flexible' => $employee->office_shift->is_flexible ?? null
            ]);

            if ($employee->office_shift) {
                $data['employee_id'] = $employee_id;
                $data['date'] = $date;
                $data['clock_in'] = $clock_in->format('H:i');
                $data['clock_out'] = $clock_out->format('H:i');
                $data['status'] = 'present';
                $work_duration = $clock_in->diff($clock_out)->format('%H:%I');
                $data['total_work'] = $work_duration;
                $data['depart_early'] = '00:00';
                $data['late_time'] = '00:00';
                $data['overtime'] = '00:00';
                $data['clock_in_out'] = 0;
                $data['mode'] = $request->mode;
                \Log::info('Attendance update data (flexible shift)', ['data' => $data]);
                $result = Attendance::find($id)->update($data);
                \Log::info('Attendance update result (flexible shift)', ['result' => $result]);

                // After updating attendance, notify the employee
                $attendance = Attendance::find($id);
                $employee = $attendance ? $attendance->employee : null;
                if ($employee && $employee->user) {
                    $correctionDetails = 'Clock In: ' . $attendance->clock_in . ', Clock Out: ' . $attendance->clock_out;
                    $employee->user->notify(new AttendanceCorrected($attendance, $user_auth, $correctionDetails));
                }

                return response()->json(['success' => true]);
            } elseif($shift_in == null) {
                $data['employee_id'] = $employee_id;
                $data['date'] = $date;
                $data['clock_in'] = $clock_in->format('H:i');
                $data['clock_out'] = $clock_out->format('H:i');
                $data['status'] = 'present';
                $work_duration = $clock_in->diff($clock_out)->format('%H:%I');
                $data['total_work'] = $work_duration;
                $data['depart_early'] = '00:00';
                $data['late_time'] = '00:00';
                $data['overtime'] = '00:00';
                $data['clock_in_out'] = 0;
                $data['mode'] = $request->mode;
                \Log::info('Attendance update data (no shift)', ['data' => $data]);
                $result = Attendance::find($id)->update($data);
                \Log::info('Attendance update result (no shift)', ['result' => $result]);

                // After updating attendance, notify the employee
                $attendance = Attendance::find($id);
                $employee = $attendance ? $attendance->employee : null;
                if ($employee && $employee->user) {
                    $correctionDetails = 'Clock In: ' . $attendance->clock_in . ', Clock Out: ' . $attendance->clock_out;
                    $employee->user->notify(new AttendanceCorrected($attendance, $user_auth, $correctionDetails));
                }

                return response()->json(['success' => true]);
            }

            try{
                $shift_in  = new DateTime($shift_in);
                $shift_out  = new DateTime($shift_out);
            }catch(Exception $e){
                return $e;
            }

            $data['employee_id'] = $employee_id;
            $data['date'] = $date;

            if($clock_in > $shift_in){
                $time_diff = $shift_in->diff($clock_in)->format('%H:%I');
                $data['clock_in'] = $clock_in->format('H:i');
                $data['late_time'] = $time_diff;
            }else{
                $data['clock_in'] = $shift_in->format('H:i');
                $data['late_time'] = '00:00';
            }


            if($clock_out < $shift_out){
                $time_diff = $shift_out->diff($clock_out)->format('%H:%I');
                $data['clock_out'] = $clock_out->format('H:i');
                $data['depart_early'] = $time_diff;

            }elseif($clock_out > $shift_out){
                $time_diff = $shift_out->diff($clock_out)->format('%H:%I');
                $data['clock_out'] = $clock_out->format('H:i');
                $data['overtime'] = $time_diff;
                $data['depart_early'] = '00:00';
            }else{
                $data['clock_out'] = $shift_out->format('H:i');
                $data['overtime'] = '00:00';
                $data['depart_early'] = '00:00';
            }

            $data['status'] = 'present';
            $work_duration = $clock_in->diff($clock_out)->format('%H:%I');
            $data['total_work'] = $work_duration;
            $data['clock_in_out'] = 0;
            $data['mode'] = $request->mode;


            \Log::info('Attendance update data (with shift)', ['data' => $data]);
            $result = Attendance::find($id)->update($data);
            \Log::info('Attendance update result (with shift)', ['result' => $result]);

            // After updating attendance, notify the employee
            $attendance = Attendance::find($id);
            $employee = $attendance ? $attendance->employee : null;
            if ($employee && $employee->user) {
                $correctionDetails = 'Clock In: ' . $attendance->clock_in . ', Clock Out: ' . $attendance->clock_out;
                $employee->user->notify(new AttendanceCorrected($attendance, $user_auth, $correctionDetails));
            }

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user_auth = auth()->user();
		if ($user_auth->can('attendance_delete')){

            Attendance::whereId($id)->update([
                'deleted_at' => Carbon::now(),
            ]);

            return response()->json(['success' => true]);

        }
        return abort('403', __('You are not authorized'));
    }

     //-------------- Delete by selection  ---------------\\

     public function delete_by_selection(Request $request)
     {
        $user_auth = auth()->user();
        if($user_auth->can('attendance_delete')){
            $selectedIds = $request->selectedIds;
    
            foreach ($selectedIds as $attendance_id) {
                Attendance::whereId($attendance_id)->update([
                    'deleted_at' => Carbon::now(),
                ]);
            }
            return response()->json(['success' => true]);
        }
        return abort('403', __('You are not authorized'));
     }
}
