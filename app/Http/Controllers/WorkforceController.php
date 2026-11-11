<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class WorkforceController extends Controller
{
    public function index(): View
    {
        return view('workforce.index');
    }
}
