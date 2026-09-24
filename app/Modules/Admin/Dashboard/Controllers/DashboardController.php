<?php

namespace App\Modules\Admin\Dashboard\Controllers;

use App\Helpers\Response;
use App\Modules\Admin\Controllers\Controller;
use App\Modules\Admin\Dashboard\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(protected DashboardService $dashboard)
    {
        parent::__construct();
    }

    public function index()
    {
        return view('modules.admin.dashboard.index', ['counts' => $this->dashboard->counts()['data']]);
    }

    public function chartUser(Request $request)
    {
        $request->validate(['type' => ['nullable', 'in:day,month,year']]);

        return Response::sendResult($this->dashboard->userChart((string) $request->input('type')));
    }
}
