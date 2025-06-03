<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestingController extends Controller
{
    //
    public function show()
    {
        return view('testing'); // This assumes you will create a view named 'newpage.blade.php'
    }
}
