<?php

namespace App\Modules\Admin\Activity\Controllers;

use App\Modules\Admin\Controllers\Controller;
use App\Modules\User\Services\ActivityListService;
use Illuminate\Http\Request;

/** Activity log across all users. */
class ActivityController extends Controller
{
    public function __construct(protected ActivityListService $activities)
    {
        parent::__construct();
    }

    public function index()
    {
        return view('modules.admin.activity.index');
    }

    public function list(Request $request)
    {
        return response()->json($this->activities->all($request->all()));
    }
}
