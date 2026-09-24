<?php

namespace App\Modules\Admin\Page\Services;

use App\Helpers\ApiResult;
use App\Helpers\General;
use App\Repositories\PageRepository;

class PageListService
{
    public function __construct(
        protected PageRepository $pages,
        protected General $general,
    ) {}

    public function datatable(array $post): array
    {
        $result = $this->pages->datatable($post);
        $viewer = auth()->user();

        $result['data'] = $result['data']->map(function ($row) use ($viewer) {
            $row->status = view('modules.admin.partials.status-badge', ['status' => $row->status])->render();
            $row->updated_at = $this->general->dateFormat($row->updated_at);
            $row->action = view('modules.admin.page.partials.row-actions', ['row' => $row, 'viewer' => $viewer])->render();

            return $row;
        })->all();

        return ApiResult::success('', $result);
    }
}
