<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OfficeShift;
use App\Models\Company;
use Carbon\Carbon;
use DateTime;

class OfficeShiftController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_auth = auth()->user();
		if ($user_auth->can('office_shift_view')){

            $office_shifts = OfficeShift::where('deleted_at', '=', null)->orderBy('id', 'desc')->get();
            return view('hr.office_shift.office_shift_list', compact('office_shifts'));

        }
        return abort('403', __('You are not authorized'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $user_auth = auth()->user();
		if ($user_auth->can('office_shift_add')){

            $companies = Company::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','name']);
            return response()->json([
                'companies' =>$companies,
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
		if ($user_auth->can('office_shift_add')){

            request()->validate([
                'name'           => 'required|string|max:255',
                'company_id'      => 'required',
            ]);

            $is_flexible = $request->input('is_flexible', false);
            $expected_hours = $is_flexible ? $request->input('expected_hours') : null;

            OfficeShift::create([
                'company_id'     => $request['company_id'],
                'name'           => $request['name'],
                'is_flexible'    => $is_flexible,
                'expected_hours' => $expected_hours,
                'weekend_days'   => $request->input('weekend_days', ''),
                'half_day_of_week' => $request->input('half_day_of_week'),
                'half_day_expected_hours' => $request->input('half_day_expected_hours'),
                'monday_in'      => $is_flexible ? null : ($request['monday_in'] ?? null),
                'monday_out'     => $is_flexible ? null : ($request['monday_out'] ?? null),
                'tuesday_in'     => $is_flexible ? null : ($request['tuesday_in'] ?? null),
                'tuesday_out'    => $is_flexible ? null : ($request['tuesday_out'] ?? null),
                'wednesday_in'   => $is_flexible ? null : ($request['wednesday_in'] ?? null),
                'wednesday_out'  => $is_flexible ? null : ($request['wednesday_out'] ?? null),
                'thursday_in'    => $is_flexible ? null : ($request['thursday_in'] ?? null),
                'thursday_out'   => $is_flexible ? null : ($request['thursday_out'] ?? null),
                'friday_in'      => $is_flexible ? null : ($request['friday_in'] ?? null),
                'friday_out'     => $is_flexible ? null : ($request['friday_out'] ?? null),
                'saturday_in'    => $is_flexible ? null : ($request['saturday_in'] ?? null),
                'saturday_out'   => $is_flexible ? null : ($request['saturday_out'] ?? null),
                'sunday_in'      => $is_flexible ? null : ($request['sunday_in'] ?? null),
                'sunday_out'     => $is_flexible ? null : ($request['sunday_out'] ?? null),
            ]);

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
		if ($user_auth->can('office_shift_edit')){

            $companies = Company::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','name']);
            $office_shift = OfficeShift::findOrFail($id);
            return response()->json([
                'companies' =>$companies,
                'office_shift' => $office_shift,
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
        $user_auth = auth()->user();
		if ($user_auth->can('office_shift_edit')){

            $officeShift = OfficeShift::findOrFail($id);
            $is_flexible = $request->input('is_flexible', false);
            $expected_hours = $is_flexible ? $request->input('expected_hours') : null;
            $officeShift->update([
                'name'           => $request['name'],
                'company_id'     => $request['company_id'],
                'is_flexible'    => $is_flexible,
                'expected_hours' => $expected_hours,
                'weekend_days'   => $request->input('weekend_days', ''),
                'half_day_of_week' => $request->input('half_day_of_week'),
                'half_day_expected_hours' => $request->input('half_day_expected_hours'),
                'monday_in'      => $is_flexible ? null : ($request['monday_in'] ?? null),
                'monday_out'     => $is_flexible ? null : ($request['monday_out'] ?? null),
                'tuesday_in'     => $is_flexible ? null : ($request['tuesday_in'] ?? null),
                'tuesday_out'    => $is_flexible ? null : ($request['tuesday_out'] ?? null),
                'wednesday_in'   => $is_flexible ? null : ($request['wednesday_in'] ?? null),
                'wednesday_out'  => $is_flexible ? null : ($request['wednesday_out'] ?? null),
                'thursday_in'    => $is_flexible ? null : ($request['thursday_in'] ?? null),
                'thursday_out'   => $is_flexible ? null : ($request['thursday_out'] ?? null),
                'friday_in'      => $is_flexible ? null : ($request['friday_in'] ?? null),
                'friday_out'     => $is_flexible ? null : ($request['friday_out'] ?? null),
                'saturday_in'    => $is_flexible ? null : ($request['saturday_in'] ?? null),
                'saturday_out'   => $is_flexible ? null : ($request['saturday_out'] ?? null),
                'sunday_in'      => $is_flexible ? null : ($request['sunday_in'] ?? null),
                'sunday_out'     => $is_flexible ? null : ($request['sunday_out'] ?? null),
            ]);

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
		if ($user_auth->can('office_shift_delete')){


            OfficeShift::whereId($id)->update([
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
        if($user_auth->can('office_shift_delete')){
            $selectedIds = $request->selectedIds;
    
            foreach ($selectedIds as $office_shift_id) {
                OfficeShift::whereId($office_shift_id)->update([
                    'deleted_at' => Carbon::now(),
                ]);
            }
            return response()->json(['success' => true]);
        }
        return abort('403', __('You are not authorized'));
     }
}
