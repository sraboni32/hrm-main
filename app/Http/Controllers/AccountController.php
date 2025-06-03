<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Account;
use Carbon\Carbon;

class AccountController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_auth = auth()->user();
		if ($user_auth->can('account_view')){

            $accounts = Account::where('deleted_at', '=', null)->orderBy('id', 'desc')->get();
            return view('accounting.accounts.account_list', compact('accounts'));
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
		if ($user_auth->can('account_add')){

            return view('accounting.accounts.create_account');
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
		if ($user_auth->can('account_add')){

            $this->validate($request, [
                'account_num'      => 'required|string|max:255',
                'account_name'       => 'required|string|max:255',
                'initial_balance'        => 'required|numeric',
            ]);

            Account::create([
                'account_num'      => $request['account_num'],
                'account_name'       => $request['account_name'],
                'initial_balance'        => $request['initial_balance'],
                'note'        => $request['note'],
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
		if ($user_auth->can('account_edit')){

            $account = Account::where('deleted_at', '=', null)->findOrFail($id);
            return view('accounting.accounts.edit_account', compact('account'));

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
		if ($user_auth->can('account_edit')){

            $this->validate($request, [
                'account_num'      => 'required|string|max:255',
                'account_name'       => 'required|string|max:255',
                'initial_balance'        => 'required|numeric',
            ]);

            Account::whereId($id)->update([
                'account_num'      => $request['account_num'],
                'account_name'       => $request['account_name'],
                'initial_balance'        => $request['initial_balance'],
                'note'        => $request['note'],
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
		if ($user_auth->can('account_delete')){

            Account::whereId($id)->update([
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
           if($user_auth->can('account_delete')){
               $selectedIds = $request->selectedIds;
       
               foreach ($selectedIds as $account_id) {
                    Account::whereId($account_id)->update([
                        'deleted_at' => Carbon::now(),
                    ]);
               }
               return response()->json(['success' => true]);
           }
           return abort('403', __('You are not authorized'));
        }

    public function import(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt',
        ]);
        $file = $request->file('import_file');
        $handle = fopen($file->getRealPath(), 'r');
        $header = fgetcsv($handle);
        $created = 0;
        while (($row = fgetcsv($handle)) !== false) {
            // Map columns: Account Num, Account Name, Initial Balance, Note
            $account_num = $row[0] ?? null;
            $account_name = $row[1] ?? null;
            $initial_balance = $row[2] ?? null;
            $note = $row[3] ?? null;
            if (!$account_num || !$account_name || !$initial_balance) continue;
            \App\Models\Account::create([
                'account_num' => $account_num,
                'account_name' => $account_name,
                'initial_balance' => $initial_balance,
                'note' => $note,
            ]);
            $created++;
        }
        fclose($handle);
        return redirect()->back()->with('success', "$created accounts imported successfully.");
    }

}
