<?php

namespace App\Repositories\Auth;

use App\Helpers\Pagination;
use App\Models\Auth\UserSession;
use DateTimeInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserSessionRepository
{
    public function __construct(
        protected Pagination $pagination,
    ) {}

    public function findByToken(string $token): ?UserSession
    {
        return UserSession::where('token', $token)->first();
    }

    public function findForUser(string $userId, string $id): ?UserSession
    {
        return UserSession::where('user_id', $userId)->where('id', $id)->first();
    }

    public function findById(string $id): ?UserSession
    {
        return UserSession::find($id);
    }

    public function create(array $data): UserSession
    {
        return UserSession::create($data);
    }

    public function touch(UserSession $session, DateTimeInterface $expiresAt): bool
    {
        return $session->update(['expires_at' => $expiresAt]);
    }

    public function revoke(UserSession $session): ?bool
    {
        return $session->delete();
    }

    public function revokeAllForUser(string $userId): int
    {
        return UserSession::where('user_id', $userId)->delete();
    }

    /** @return Collection<int, UserSession> */
    public function latestForUser(string $userId, int $limit = 10): Collection
    {
        return UserSession::where('user_id', $userId)->orderByDesc('updated_at')->limit($limit)->get();
    }

    /**
     * DataTables page of one user's sessions.
     *
     * @return array{recordsTotal: int, recordsFiltered: int, draw: int|string, data: Collection}
     */
    public function datatableForUser(string $userId, array $post): array
    {
        return $this->pagination->getDataTable($this->listQuery()->where('user_sessions.user_id', $userId), $post);
    }

    /**
     * DataTables page of every user's sessions, searchable by name, email and IP.
     *
     * @return array{recordsTotal: int, recordsFiltered: int, draw: int|string, data: Collection}
     */
    public function datatableAll(array $post): array
    {
        $query = $this->listQuery();

        $search = trim($post['search']['value'] ?? '');
        if (strlen($search) > 2) {
            $like = '%'.$search.'%';
            $query->where(fn ($q) => $q
                ->whereRaw("concat(users.first_name, ' ', users.last_name) like ?", [$like])
                ->orWhere('users.email', 'like', $like)
                ->orWhere('user_sessions.ip_address', 'like', $like));
        }

        return $this->pagination->getDataTable($query, $post);
    }

    /** Column aliases match the field names the DataTables views declare. */
    protected function listQuery()
    {
        return DB::table('user_sessions')
            ->join('users', 'users.id', '=', 'user_sessions.user_id')
            ->select([
                'user_sessions.id as id',
                'user_sessions.user_agent as client',
                'user_sessions.ip_address as ip',
                'user_sessions.updated_at as last_activity',
                'users.first_name',
                'users.last_name',
                'users.email',
            ]);
    }
}
