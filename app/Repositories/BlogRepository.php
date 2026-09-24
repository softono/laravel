<?php

namespace App\Repositories;

use App\Constants\UserStatus;
use App\Helpers\Pagination;
use App\Models\Blog;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BlogRepository
{
    public function __construct(protected Pagination $pagination) {}

    public function findById(int|string $id): ?Blog
    {
        return Blog::find($id);
    }

    public function findActiveBySlug(string $slug): ?Blog
    {
        return Blog::where('slug', $slug)->where('status', UserStatus::ACTIVE)->first();
    }

    public function create(array $data): Blog
    {
        return Blog::create($data);
    }

    public function update(Blog $blog, array $data): bool
    {
        return $blog->update($data);
    }

    public function delete(Blog $blog): ?bool
    {
        return $blog->delete();
    }

    /** Active posts, newest first, filtered by a title/excerpt search and an optional category. */
    public function paginatePublic(?string $search, ?string $category, int $perPage = 12): LengthAwarePaginator
    {
        return Blog::where('status', UserStatus::ACTIVE)
            ->when($category, fn ($q) => $q->where('category', $category))
            ->when($search, fn ($q) => $q->where(fn ($q) => $q
                ->where('title', 'like', '%'.$search.'%')
                ->orWhere('excerpt', 'like', '%'.$search.'%')))
            ->orderByDesc('created_at')
            ->select('id', 'slug', 'title', 'excerpt', 'category', 'image', 'created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return array{recordsTotal: int, recordsFiltered: int, draw: int|string, data: Collection}
     */
    public function datatable(array $post): array
    {
        $query = DB::table('blogs')->select('id', 'slug', 'title', 'category', 'status', 'created_at');

        $search = trim($post['search']['value'] ?? '');
        if (strlen($search) > 2) {
            $like = '%'.$search.'%';
            $query->where(fn ($q) => $q->where('title', 'like', $like)->orWhere('slug', 'like', $like));
        }

        return $this->pagination->getDataTable($query, $post);
    }
}
