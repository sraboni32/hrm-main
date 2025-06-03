<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth')->except('RedirectToLogin');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        //
    }

    public function RedirectToLogin()
    {
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->role_users_id == 1) {
                return redirect('/dashboard/admin');
            } elseif ($user->role_users_id == 2) {
                return redirect('/dashboard/employee');
            } elseif ($user->role_users_id == 3) {
                return redirect('/dashboard/client');
            }
            // fallback
            return redirect('/dashboard/admin');
        } else {
            return view('home');
        }
    }
}
