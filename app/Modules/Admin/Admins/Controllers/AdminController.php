<?php

namespace App\Modules\Admin\Admins\Controllers;

use App\Constants\UserRole;
use App\Helpers\Response;
use App\Modules\Admin\Admins\Requests\SaveAdminRequest;
use App\Modules\Admin\Controllers\Controller;
use App\Modules\Admin\Services\AccountListService;
use App\Modules\Admin\Services\AccountManagementService;
use App\Modules\Auth\Services\LoginAttemptService;
use App\Repositories\Auth\UserActivityRepository;
use App\Repositories\Auth\UserRepository;
use App\Repositories\Auth\UserSessionRepository;
use Illuminate\Http\Request;

/** Manage admin accounts (role ADMIN) and their permissions. */
class AdminController extends Controller
{
    public function __construct(
        protected AccountManagementService $accounts,
        protected AccountListService $list,
        protected UserRepository $users,
        protected UserSessionRepository $sessions,
        protected UserActivityRepository $activities,
        protected LoginAttemptService $attempts,
    ) {
        parent::__construct();
    }

    public function index()
    {
        return view('modules.admin.admins.index');
    }

    public function list(Request $request)
    {
        return Response::sendResult($this->list->datatable(auth()->user(), $request->all(), UserRole::ADMIN, 'admin/admin'));
    }

    public function create()
    {
        return view('modules.admin.admins.create', ['countries' => config('countries')]);
    }

    public function update(Request $request)
    {
        $model = $this->users->findByIdAndRole((string) $request->input('id'), UserRole::ADMIN);

        if (! $model) {
            return redirect()->route('admin/admin')->with('error', 'No data found');
        }

        return view('modules.admin.admins.update', ['model' => $model, 'countries' => config('countries')]);
    }

    public function save(SaveAdminRequest $request)
    {
        $data = $request->validated();

        $result = $request->filled('id')
            ? $this->accounts->update($request, auth()->user(), UserRole::ADMIN, $request->string('id'), $data)
            : $this->accounts->create($request, auth()->user(), UserRole::ADMIN, $data);

        return Response::sendResult($result);
    }

    public function view(Request $request)
    {
        $model = $this->users->findByIdAndRole((string) $request->input('id'), UserRole::ADMIN);

        if (! $model) {
            return redirect()->route('admin/admin')->with('error', 'No data found');
        }

        return view('modules.admin.admins.view', [
            'model' => $model,
            'loginLocked' => $this->attempts->isLocked($model->email),
            'sessions' => $this->sessions->latestForUser($model->id),
            'activities' => $this->activities->getByUserId($model->id, 10),
        ]);
    }

    public function delete(Request $request)
    {
        $result = $this->accounts->delete($request, auth()->user(), UserRole::ADMIN, (string) $request->input('id'));

        return Response::sendResult($result);
    }

    public function changeStatus(Request $request)
    {
        $result = $this->accounts->toggleStatus($request, auth()->user(), UserRole::ADMIN, (string) $request->input('id'));

        return Response::sendResult($result);
    }
}
