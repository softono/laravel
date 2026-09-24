<?php

namespace App\Modules\User\Controllers;

use App\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('modules.user.dashboard', ['user' => auth()->user()]);
    }
}
