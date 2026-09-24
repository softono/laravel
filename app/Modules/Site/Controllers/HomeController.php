<?php

namespace App\Modules\Site\Controllers;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return view('modules.site.index');
    }
}
