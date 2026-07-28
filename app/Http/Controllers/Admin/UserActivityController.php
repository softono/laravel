<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller; // Fixed namespace for the base Controller
use App\Models\UserActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class AccountActivityController
 *
 * This controller handles the log management in the admin panel.
 */
class UserActivityController extends Controller
{
    /**
     * Display the log index view.
     *
     * @return View
     */
    public function index()
    {
        return view('admin.user_activity.index'); // Use dot notation for view paths
    }

    /**
     * List logs for admin.
     *
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $logs = (new UserActivity)->listAdmin($request->all());

        return response()->json($logs);
    }
}
