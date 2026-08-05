<?php

namespace App\Services\Admin;

use App\Constants\UserStatus;
use App\Models\Auth\User;
use App\Repositories\Auth\UserAccountRepository;
use App\Repositories\Auth\UserRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Backs both Admin\UserController (role USER = Bucket Admin, per the
 * Tenancy Model - see docs/local/prd.md) and Admin\AdminController
 * (roles ADMIN / SUPER_ADMIN). Business logic lives here per the
 * Architecture rule (controllers thin, repositories DB-only).
 */
class UserManagementService
{
    public function __construct(
        protected UserRepository $users,
        protected UserAccountRepository $userAccounts,
    ) {}

    /**
     * @param  string[]  $roles
     * @param  string  $routePrefix  'admin/user' or 'admin/admin' - selects which
     *                               CRUD routes the action column links to.
     */
    public function list(array $roles, array $postData, string $routePrefix = 'admin/user'): array
    {
        $result = $this->users->listByRoles($roles, $postData);
        $sessionUser = auth()->user();

        foreach ($result['data'] as $row) {
            $row->status_label = $row->status === UserStatus::ACTIVE ? 'Active' : 'Inactive';
            $row->action = $this->actionLinks($row, $sessionUser, $routePrefix);
        }

        return $result;
    }

    protected function actionLinks(object $row, $sessionUser, string $routePrefix): string
    {
        $links = '';

        if ($sessionUser && $sessionUser->hasPermission($routePrefix.'/view')) {
            $links .= sprintf(
                '<a href="%s/view?id=%s" class="btn btn-icon pjax" title="View"><i class="bx bxs-show icon-base"></i></a>',
                $routePrefix,
                $row->id
            );
        }
        if ($sessionUser && $sessionUser->hasPermission($routePrefix.'/update')) {
            $links .= sprintf(
                '<a href="%s/update?id=%s" class="btn btn-icon pjax" title="Update"><i class="bx bxs-edit icon-base"></i></a>',
                $routePrefix,
                $row->id
            );
        }
        if ($sessionUser && $sessionUser->hasPermission($routePrefix.'/delete')) {
            $links .= sprintf(
                '<button onclick="app.confirmAction(this);" data-action="%s/delete" data-id="%s" class="btn btn-icon" title="Delete"><i class="bx bxs-trash icon-base"></i></button>',
                $routePrefix,
                $row->id
            );
        }

        return '<div class="d-flex align-items-center">'.$links.'</div>';
    }

    /**
     * @return array{status: int, message: string, next?: string, url?: string}
     */
    public function store(array $postData, string $defaultRole): array
    {
        $id = $postData['id'] ?? null;
        $model = $id ? $this->users->findById($id) : null;

        $validator = Validator::make($postData, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,'.($model?->id ?? 'NULL').',id',
            'password' => $model ? 'nullable|string|min:6' : 'required|string|min:6',
            'phone' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:255',
            'status' => 'required|in:'.UserStatus::ACTIVE.','.UserStatus::INACTIVE,
        ]);

        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }

        $data = [
            'first_name' => $postData['first_name'],
            'last_name' => $postData['last_name'],
            'email' => strtolower(trim($postData['email'])),
            'phone' => $postData['phone'] ?? null,
            'country' => $postData['country'] ?? null,
            'status' => $postData['status'],
        ];

        // Only the admin/admin form (roles ADMIN/SUPER_ADMIN) submits a
        // permission checkbox tree - Bucket Admins have no such concept.
        if (isset($postData['permission'])) {
            $data['permission'] = implode(',', (array) $postData['permission']);
        }

        if ($model) {
            $this->users->update($model, $data);
        } else {
            $data['role'] = $postData['role'] ?? $defaultRole;
            $data['email_verified'] = true;
            $data['timezone'] = 'UTC';
            $model = $this->users->create($data);
        }

        if (! empty($postData['password'])) {
            $account = $this->userAccounts->findCredentialAccount($model->id);
            if ($account) {
                $account->update(['password' => Hash::make($postData['password'])]);
            } else {
                $this->userAccounts->create([
                    'user_id' => $model->id,
                    'account_id' => $model->id,
                    'provider_id' => 'credential',
                    'password' => Hash::make($postData['password']),
                ]);
            }
        }

        return [
            'status' => 1,
            'message' => 'User saved successfully.',
            'next' => 'load',
        ];
    }

    public function delete(User $user): array
    {
        $this->users->delete($user);

        return ['status' => 1, 'message' => 'User deleted successfully.', 'next' => 'table_refresh'];
    }

    public function toggleStatus(User $user): array
    {
        $user->update([
            'status' => $user->status === UserStatus::ACTIVE ? UserStatus::INACTIVE : UserStatus::ACTIVE,
        ]);

        return ['status' => 1, 'message' => 'User status updated successfully.', 'next' => 'refresh'];
    }
}
