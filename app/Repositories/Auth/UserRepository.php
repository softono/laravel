<?php

namespace App\Repositories\Auth;

use App\Helpers\Pagination;
use App\Models\Auth\User;
use Illuminate\Support\Facades\DB;

class UserRepository
{
    public function findById(string $id): ?User
    {
        return User::find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return User::where('email', strtolower(trim($email)))->first();
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
     * Admin-panel datatable listing, scoped to one or more roles.
     *
     * @param  string[]  $roles
     */
    public function listByRoles(array $roles, array $postData): array
    {
        $query = DB::table('users')->whereIn('role', $roles);

        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $query->where(function ($q) use ($searchText) {
                $q->where('first_name', 'like', '%'.$searchText.'%')
                    ->orWhere('last_name', 'like', '%'.$searchText.'%')
                    ->orWhere('email', 'like', '%'.$searchText.'%');
            });
        }

        return (new Pagination)->getDataTable($query, $postData);
    }

    public function countByRoles(array $roles): int
    {
        return User::whereIn('role', $roles)->count();
    }
}
