<?php

namespace App\Repositories;

use App\Helpers\Pagination;
use App\Models\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PageRepository
{
    public function __construct(protected Pagination $pagination) {}

    public function findById(int|string $id): ?Page
    {
        return Page::find($id);
    }

    public function findBySlug(string $slug): ?Page
    {
        return Page::where('slug', $slug)->first();
    }

    public function update(Page $page, array $data): bool
    {
        return $page->update($data);
    }

    /**
     * @return array{recordsTotal: int, recordsFiltered: int, draw: int|string, data: Collection}
     */
    public function datatable(array $post): array
    {
        $query = DB::table('pages')->select('id', 'slug', 'title', 'status', 'updated_at');

        $search = trim($post['search']['value'] ?? '');
        if (strlen($search) > 2) {
            $query->where('title', 'like', '%'.$search.'%');
        }

        return $this->pagination->getDataTable($query, $post);
    }
}
