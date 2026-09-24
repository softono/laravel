<?php

namespace App\Repositories\Auth;

use App\Helpers\Pagination;
use App\Models\Auth\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UserRepository
{
    public function __construct(
        protected Pagination $pagination,
    ) {}

    public function findById(string $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', strtolower(trim($email)))->first();
    }

    public function findByPhone(string $phone): ?User
    {
        return User::where('phone', $phone)->first();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function delete(User $user): ?bool
    {
        return $user->delete();
    }

    /**
     * DataTables page of users with the given roles, searchable by name, email and phone.
     *
     * @param  string[]  $roles
     * @return array{recordsTotal: int, recordsFiltered: int, draw: int|string, data: Collection}
     */
    public function datatable(array $post, array $roles, ?string $exceptId = null): array
    {
        $query = DB::table('users')->whereIn('role', $roles);

        if ($exceptId) {
            $query->where('id', '!=', $exceptId);
        }

        $search = trim($post['search']['value'] ?? '');
        if (strlen($search) > 2) {
            $like = '%'.$search.'%';
            $query->where(fn ($q) => $q
                ->whereRaw("concat(first_name, ' ', last_name) like ?", [$like])
                ->orWhere('email', 'like', $like)
                ->orWhere('phone', 'like', $like));
        }

        return $this->pagination->getDataTable($query, $post);
    }

    public function countByRole(string $role, ?string $status = null): int
    {
        return User::where('role', $role)
            ->when($status, fn ($q) => $q->where('status', $status))
            ->count();
    }

    /**
     * Sign-ups per day (`Y-m-d`) or month (`Y-m`) since $since, oldest first.
     *
     * @return Collection<int, object{label: string, count: int}>
     */
    public function signupsSince(string $role, \DateTimeInterface $since, string $format): Collection
    {
        return User::where('role', $role)
            ->where('created_at', '>=', $since)
            ->selectRaw('date_format(created_at, ?) as label, count(*) as count', [$format])
            ->groupBy('label')
            ->orderBy('label')
            ->get();
    }
}
