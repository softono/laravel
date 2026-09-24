<?php

namespace App\Modules\User\Services;

use App\Constants\UserActivity;
use App\Helpers\ClientInfo;
use App\Helpers\General;
use App\Models\Auth\User;
use App\Repositories\Auth\UserActivityRepository;

/** Activity-log DataTables payloads for the account area and the admin panel. */
class ActivityListService
{
    public function __construct(
        protected UserActivityRepository $activities,
        protected General $general,
    ) {}

    public function forUser(User $user, array $post): array
    {
        return $this->present($this->activities->datatableForUser($user->id, $post));
    }

    public function all(array $post): array
    {
        return $this->present($this->activities->datatableAll($post));
    }

    protected function present(array $result): array
    {
        $result['data'] = $result['data']->map(function ($row) {
            $row->type = UserActivity::label($row->type ?? '');
            $row->client = ClientInfo::deviceNameFor($row->client);
            $row->created_at = $this->general->dateFormat($row->created_at);
            if (property_exists($row, 'first_name')) {
                $row->first_name = trim($row->first_name.' '.$row->last_name);
            }

            return $row;
        })->all();

        return $result;
    }
}
