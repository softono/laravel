<?php

namespace App\Modules\Admin\Seo\Services;

use App\Helpers\General;
use App\Repositories\SeoMetaRepository;

class SeoListService
{
    public function __construct(
        protected SeoMetaRepository $seo,
        protected General $general,
    ) {}

    public function datatable(array $post): array
    {
        $result = $this->seo->datatable($post);
        $viewer = auth()->user();

        $result['data'] = $result['data']->map(function ($row) use ($viewer) {
            $row->sitemap_enable = view('modules.admin.partials.status-badge', [
                'status' => $row->sitemap_enable ? 'active' : 'inactive',
            ])->render();
            $row->updated_at = $this->general->dateFormat($row->updated_at);
            $row->action = view('modules.admin.partials.row-actions', ['base' => 'admin/seo', 'id' => $row->id, 'viewer' => $viewer])->render();

            return $row;
        })->all();

        return $result;
    }
}
