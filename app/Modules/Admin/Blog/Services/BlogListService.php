<?php

namespace App\Modules\Admin\Blog\Services;

use App\Constants\BlogCategory;
use App\Helpers\General;
use App\Repositories\BlogRepository;

class BlogListService
{
    public function __construct(
        protected BlogRepository $blogs,
        protected General $general,
    ) {}

    public function datatable(array $post): array
    {
        $result = $this->blogs->datatable($post);
        $viewer = auth()->user();

        $result['data'] = $result['data']->map(function ($row) use ($viewer) {
            $row->category = BlogCategory::label($row->category);
            $row->status = view('modules.admin.partials.status-badge', ['status' => $row->status])->render();
            $row->created_at = $this->general->dateFormat($row->created_at);
            $row->action = view('modules.admin.partials.row-actions', ['base' => 'admin/blog', 'id' => $row->id, 'viewer' => $viewer])->render();

            return $row;
        })->all();

        return $result;
    }
}
