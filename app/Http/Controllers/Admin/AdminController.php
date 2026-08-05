<?php

namespace App\Http\Controllers\Admin;

use App\Constants\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Repositories\Auth\UserRepository;
use App\Services\Admin\UserManagementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Manages Admin-panel operator accounts (roles ADMIN / SUPER_ADMIN).
 * SUPER_ADMIN itself is only ever seeded, never created through this UI.
 */
class AdminController extends Controller
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
        return view('admin/admin/index');
    }

    /**
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        return response()->json($this->userManagement->list(UserRole::ADMIN_ROLES, $request->all(), 'admin/admin'));
    }

    /**
     * @return View
     */
    public function create()
    {
        $model = new User;

        return view('admin/admin/create', compact('model'));
    }

    /**
     * @return View|RedirectResponse
     */
    public function update(Request $request)
    {
        $model = $this->users->findById($request->input('id'));
        if (! $model || ! $model->isAdmin()) {
            return redirect()->route('admin/admin')->with('error', 'No data found');
        }

        return view('admin/admin/update', compact('model'));
    }

    /**
     * @return JsonResponse
     */
    public function save(Request $request)
    {
        return response()->json($this->userManagement->store($request->all(), UserRole::ADMIN));
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
     * @return View|RedirectResponse
     */
    public function view(Request $request)
    {
        $model = $this->users->findById($request->input('id'));
        if (! $model || ! $model->isAdmin()) {
            return redirect()->route('admin/admin')->with('error', 'No data found');
        }

        $activities = $model->activities()->orderByDesc('created_at')->limit(10)->get();
        $devices = $model->devices()->orderByDesc('created_at')->limit(10)->get();

        return view('admin/admin/view', compact('model', 'activities', 'devices'));
    }

    /**
     * @return JsonResponse
     */
    public function statusSave(Request $request)
    {
        $model = $this->users->findById($request->input('id'));
        if (! $model) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }

        return response()->json($this->userManagement->toggleStatus($model));
    }
}
