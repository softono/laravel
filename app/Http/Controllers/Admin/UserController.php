<?php

namespace App\Http\Controllers\Admin;

use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Repositories\Auth\UserRepository;
use App\Services\Admin\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Manages Bucket Admin (role USER, see App\Constants\UserRole) accounts -
 * the "existing User Management" feature from docs/local/prd.md, scoped
 * to the tenancy model's bucket-owning role.
 */
class UserController extends Controller
{
    public function __construct(
        protected UserManagementService $userManagement,
        protected UserRepository $users,
    ) {
        parent::__construct();
    }

    /**
     * @return View
     */
    public function index()
    {
        return view('admin/user/index');
    }

    /**
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        return response()->json($this->userManagement->list([UserRole::USER], $request->all(), 'admin/user'));
    }

    /**
     * @return View
     */
    public function create()
    {
        return view('admin/user/create');
    }

    /**
     * @return View|RedirectResponse
     */
    public function update(Request $request)
    {
        $model = $this->users->findById($request->input('id'));

        if (! $model || $model->role !== UserRole::USER) {
            return redirect()->route('admin/user')->with('error', 'No data found');
        }

        return view('admin/user/update', compact('model'));
    }

    /**
     * @return JsonResponse
     */
    public function save(Request $request)
    {
        return response()->json($this->userManagement->store($request->all(), UserRole::USER));
    }

    /**
     * @return View|RedirectResponse
     */
    public function view(Request $request)
    {
        $model = $this->users->findById($request->input('id'));

        if (! $model || $model->role !== UserRole::USER) {
            return redirect()->route('admin/user')->with('error', 'No data found');
        }

        $activities = $model->activities()->orderByDesc('created_at')->limit(10)->get();
        $devices = $model->devices()->orderByDesc('created_at')->limit(10)->get();

        return view('admin/user/view', compact('model', 'activities', 'devices'));
    }

    /**
     * @return JsonResponse
     */
    public function delete(Request $request)
    {
        $model = $this->users->findById($request->input('id'));
        if (! $model) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }

        return response()->json($this->userManagement->delete($model));
    }

    /**
     * @return JsonResponse
     */
    public function changeStatus(Request $request)
    {
        $model = $this->users->findById($request->input('id'));
        if (! $model) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }

        return response()->json($this->userManagement->toggleStatus($model));
    }
}
