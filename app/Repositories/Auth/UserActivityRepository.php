<?php

namespace App\Repositories\Auth;

use App\Helpers\Pagination;
use App\Models\Auth\UserActivity;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserActivityRepository
{
    public function __construct(
        protected Pagination $pagination,
    ) {}

    public function create(array $data): UserActivity
    {
        return UserActivity::create($data);
    }

    /** @return Collection<int, UserActivity> */
    public function getByUserId(string $userId, int $limit = 50)
    {
        return UserActivity::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * DataTables page of one user's activity.
     *
     * @return array{recordsTotal: int, recordsFiltered: int, draw: int|string, data: Collection}
     */
    public function datatableForUser(string $userId, array $post): array
    {
        $query = DB::table('user_activities')
            ->select('id', 'type', 'ip', 'client', 'location', 'created_at')
            ->where('user_id', $userId);

        return $this->pagination->getDataTable($query, $post);
    }

    /**
     * DataTables page of every user's activity, searchable by name, email and IP.
     *
     * @return array{recordsTotal: int, recordsFiltered: int, draw: int|string, data: Collection}
     */
    public function datatableAll(array $post): array
    {
        $query = DB::table('user_activities')
            ->join('users', 'users.id', '=', 'user_activities.user_id')
            ->select([
                'user_activities.id as id',
                'user_activities.type',
                'user_activities.ip',
                'user_activities.client',
                'user_activities.device_id as device',
                'user_activities.location',
                'user_activities.created_at',
                'users.first_name',
                'users.last_name',
                'users.email',
            ]);

        $search = trim($post['search']['value'] ?? '');
        if (strlen($search) > 2) {
            $like = '%'.$search.'%';
            $query->where(fn ($q) => $q
                ->whereRaw("concat(users.first_name, ' ', users.last_name) like ?", [$like])
                ->orWhere('users.email', 'like', $like)
                ->orWhere('user_activities.ip', 'like', $like));
        }

        return $this->pagination->getDataTable($query, $post);
    }
}
