<?php

namespace App\Modules\Admin\Services;

use App\Helpers\General;
use App\Models\Auth\User;
use App\Repositories\Auth\UserRepository;

/** DataTables payload for the user and admin-account lists. */
class AccountListService
{
    public function __construct(
        protected UserRepository $users,
        protected General $general,
    ) {}

    /**
     * @param  string  $role  USER or ADMIN
     * @param  string  $routeBase  route-name prefix of the module, e.g. "admin/user"
     */
    public function datatable(User $viewer, array $post, string $role, string $routeBase): array
    {
        $result = $this->users->datatable($post, [$role], $viewer->id);

        $countries = config('countries');

        $result['data'] = $result['data']->map(function ($row) use ($viewer, $countries, $routeBase) {
            $row->first_name = trim($row->first_name.' '.$row->last_name);
            $row->country = $countries[$row->country] ?? ($row->country ?? '');
            $row->status = view('modules.admin.partials.status-badge', ['status' => $row->status])->render();
            $row->created_at = $this->general->dateFormat($row->created_at);
            $row->action = view('modules.admin.partials.row-actions', [
                'base' => $routeBase,
                'id' => $row->id,
                'viewer' => $viewer,
            ])->render();

            return $row;
        })->all();

        return $result;
    }
}
