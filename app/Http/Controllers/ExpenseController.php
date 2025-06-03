<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Account;
use App\Models\PaymentMethod;

use Carbon\Carbon;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user_auth = auth()->user();
		if ($user_auth->can('expense_view')){

            $expenses = Expense::where('deleted_at', '=', null)->orderBy('id', 'desc')->get();
            return view('accounting.expense.expense_list',compact('expenses'));

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
		if ($user_auth->can('expense_add')){

            $accounts = Account::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','account_name']);
            $categories = ExpenseCategory::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','title']);
            $payment_methods = PaymentMethod::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','title']);

            return view('accounting.expense.create_expense', compact('accounts','categories','payment_methods'));

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
		if ($user_auth->can('expense_add')){

            \DB::transaction(function () use ($request) {
                $request->validate([
                    'expense_ref'           => 'required|string|max:255',
                    'account_id'            => 'required',
                    'expense_category_id'   => 'required',
                    'amount'                => 'required|numeric',
                    'payment_method_id'     => 'required',
                    'date'                  => 'required',
                    'attachment'           => 'nullable|max:2048',
                ]);

                if ($request->hasFile('attachment')) {

                    $image = $request->file('attachment');
                    $filename = time().'.'.$image->extension();  
                    $image->move(public_path('/assets/images/expenses'), $filename);

                } else {
                    $filename = Null;
                }

                Expense::create([
                    'expense_ref'            => $request['expense_ref'],
                    'account_id'             => $request['account_id'],
                    'expense_category_id'    => $request['expense_category_id'],
                    'amount'                 => $request['amount'],
                    'payment_method_id'      => $request['payment_method_id'],
                    'date'                   => $request['date'],
                    'attachment'            => $filename,
                    'description'            => $request['description'],
                ]);

                $account = Account::findOrFail($request['account_id']);
                $account->update([
                    'initial_balance' => $account->initial_balance - $request['amount'],
                ]);

            }, 10);

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
		if ($user_auth->can('expense_edit')){

            $expense = Expense::where('deleted_at', '=', null)->findOrFail($id);
            $accounts = Account::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','account_name']);
            $categories = ExpenseCategory::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','title']);
            $payment_methods = PaymentMethod::where('deleted_at', '=', null)->orderBy('id', 'desc')->get(['id','title']);

            return view('accounting.expense.edit_expense', compact('expense','accounts','categories','payment_methods'));

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
		if ($user_auth->can('expense_edit')){

            \DB::transaction(function () use ($request, $id) {
                $request->validate([
                    'expense_ref'           => 'required|string|max:255',
                    'account_id'            => 'required',
                    'expense_category_id'   => 'required',
                    'amount'                => 'required|numeric',
                    'payment_method_id'     => 'required',
                    'date'                  => 'required',
                    'attachment'           => 'nullable|max:2048',
                ]);

                $expense = Expense::findOrFail($id);

                $Current_attachment = $expense->attachment;
                if ($request->attachment != 'null') {
                    if ($request->attachment != $Current_attachment) {

                        $image = $request->file('attachment');
                        $filename = time().'.'.$image->extension();  
                        $image->move(public_path('/assets/images/expenses'), $filename);
                        $path = public_path() . '/assets/images/expenses';
                        $attachment = $path . '/' . $Current_attachment;
                        if (file_exists($attachment)) {
                            @unlink($attachment);
                        }
                    } else {
                        $filename = $Current_attachment;
                    }
                }else{
                    $filename = $Current_attachment;
                }

                Expense::whereId($id)->update([
                    'expense_ref'            => $request['expense_ref'],
                    'account_id'             => $request['account_id'],
                    'expense_category_id'    => $request['expense_category_id'],
                    'amount'                 => $request['amount'],
                    'payment_method_id'      => $request['payment_method_id'],
                    'date'                   => $request['date'],
                    'attachment'            => $filename,
                    'description'            => $request['description'],
                ]);

                
                $account = Account::findOrFail($request['account_id']);
                $balance = $account->initial_balance + $expense->amount;
                $account->update([
                    'initial_balance' => $balance - $request['amount'],
                ]);

            }, 10);

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
		if ($user_auth->can('expense_delete')){

            Expense::whereId($id)->update([
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
        if($user_auth->can('expense_delete')){
            $selectedIds = $request->selectedIds;
    
            foreach ($selectedIds as $expense_id) {
                Expense::whereId($expense_id)->update([
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
            // Map columns: Expense Ref, Account ID, Expense Category ID, Amount, Payment Method ID, Date, Attachment, Description
            $expense_ref = $row[0] ?? null;
            $account_id = $row[1] ?? null;
            $expense_category_id = $row[2] ?? null;
            $amount = $row[3] ?? null;
            $payment_method_id = $row[4] ?? null;
            $date = $row[5] ?? null;
            $attachment = $row[6] ?? null;
            $description = $row[7] ?? null;
            if (!$expense_ref || !$account_id || !$expense_category_id || !$amount || !$payment_method_id || !$date) continue;
            \App\Models\Expense::create([
                'expense_ref' => $expense_ref,
                'account_id' => $account_id,
                'expense_category_id' => $expense_category_id,
                'amount' => $amount,
                'payment_method_id' => $payment_method_id,
                'date' => $date,
                'attachment' => $attachment,
                'description' => $description,
            ]);
            $created++;
        }
        fclose($handle);
        return redirect()->back()->with('success', "$created expenses imported successfully.");
    }
}
