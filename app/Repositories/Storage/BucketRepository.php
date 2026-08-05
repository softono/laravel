<?php

namespace App\Repositories\Storage;

use App\Constants\UserRole;
use App\Helpers\Pagination;
use App\Models\Storage\Bucket;
use Illuminate\Support\Facades\DB;

class BucketRepository
{
    public function findById(int $id): ?Bucket
    {
        return Bucket::find($id);
    }

    public function findByUuid(string $uuid): ?Bucket
    {
        return Bucket::where('uuid', $uuid)->first();
    }

    /**
     * Bucket names are globally unique (like real S3) - see the
     * storage_buckets migration. $userId is kept as a defence-in-depth
     * ownership check for authenticated (non-public) operations.
     */
    public function findByName(string $userId, string $name): ?Bucket
    {
        return Bucket::where('user_id', $userId)->where('name', $name)->first();
    }

    public function findByNameGlobal(string $name): ?Bucket
    {
        return Bucket::where('name', $name)->first();
    }

    public function listForUser(string $userId)
    {
        return Bucket::where('user_id', $userId)->orderByDesc('created_at')->get();
    }

    public function create(array $data): Bucket
    {
        return Bucket::create($data);
    }

    public function delete(Bucket $bucket): ?bool
    {
        return $bucket->delete();
    }

    public function countForUser(string $userId): int
    {
        return Bucket::where('user_id', $userId)->count();
    }

    public function countAll(): int
    {
        return Bucket::count();
    }

    /** Admin-panel datatable listing across all users (read-only support view). */
    public function listAllForAdmin(array $postData): array
    {
        $query = DB::table('storage_buckets')
            ->join('users', 'users.id', '=', 'storage_buckets.user_id')
            ->select('storage_buckets.*', 'users.email as owner_email', 'users.first_name as owner_first_name', 'users.last_name as owner_last_name');

        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $query->where(function ($q) use ($searchText) {
                $q->where('storage_buckets.name', 'like', '%'.$searchText.'%')
                    ->orWhere('users.email', 'like', '%'.$searchText.'%');
            });
        }

        return (new Pagination)->getDataTable($query, $postData);
    }

    /**
     * Per-user storage breakdown for the Super Admin dashboard - every
     * Bucket Admin (role USER), including those with zero buckets yet.
     */
    public function storageBreakdownByUser()
    {
        return DB::table('users')
            ->where('users.role', UserRole::USER)
            ->leftJoin('storage_buckets', 'storage_buckets.user_id', '=', 'users.id')
            ->leftJoin('storage_objects', 'storage_objects.bucket_id', '=', 'storage_buckets.id')
            ->select(
                'users.id as user_id',
                'users.email',
                'users.first_name',
                'users.last_name',
                DB::raw('COUNT(DISTINCT storage_buckets.id) as bucket_count'),
                DB::raw('COUNT(storage_objects.id) as object_count'),
                DB::raw('COALESCE(SUM(storage_objects.size), 0) as storage_used')
            )
            ->groupBy('users.id', 'users.email', 'users.first_name', 'users.last_name')
            ->get();
    }
}
