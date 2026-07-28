<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserActivity;
use App\Models\UserAuth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Display the admin dashboard index page.
     *
     * @return View
     */
    public function index()
    {
        return view('admin/admin/index');
    }

    /**
     * List the admins based on the request data.
     *
     * @return JsonResponse
     */
    public function list(Request $request)
    {

        return response()->json((new User)->listAdmin($request->all()));
    }

    /**
     * Show the form for creating a new admin.
     *
     * @return View
     */
    public function create()
    {
        $countries = DB::table('country')->get();
        $model = new User;

        return view('admin/admin/create', compact('model', 'countries'));
    }

    /**
     * Show the form for updating an existing admin.
     *
     * @return RedirectResponse|View
     */
    public function update(Request $request)
    {
        $model = User::find($request->input('id'));
        if (! $model) {
            return redirect('admin/admin')->withError('error', 'No data found');
        }
        $countries = DB::table('country')->get();

        return view('admin/admin/update', compact('model', 'countries'));
    }

    /**
     * Save a new admin or update an existing admin.
     *
     * @return JsonResponse
     */
    public function save(Request $request)
    {
        return response()->json((new User)->storeAdmin($request->all()));
    }

    /**
     * Delete an existing admin.
     *
     * @return JsonResponse|RedirectResponse
     */
    public function delete(Request $request)
    {
        $model = User::find($request->input('id'));
        if (! $model) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }
        $model->delete();

        return response()->json(['status' => 1, 'message' => 'Data deleted successfully.', 'next' => 'table_refresh']);
    }

    /**
     * View details of a specific admin.
     *
     * @return View
     */
    public function view(Request $request)
    {
        $id = $request->input('id');
        $logData = UserActivity::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        $deviceData = UserAuth::where('user_id', $id)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();
        $model = User::where('id', $id)->first();

        return view('admin/admin/view', compact('model', 'logData', 'deviceData'));
    }
}
