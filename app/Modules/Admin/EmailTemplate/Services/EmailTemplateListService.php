<?php

namespace App\Modules\Admin\EmailTemplate\Services;

use App\Helpers\General;
use App\Repositories\EmailTemplateRepository;

class EmailTemplateListService
{
    public function __construct(
        protected EmailTemplateRepository $templates,
        protected General $general,
    ) {}

    public function datatable(array $post): array
    {
        $result = $this->templates->datatable($post);
        $viewer = auth()->user();

        $result['data'] = $result['data']->map(function ($row) use ($viewer) {
            $row->updated_at = $this->general->dateFormat($row->updated_at);
            $row->action = view('modules.admin.email-template.partials.row-actions', ['row' => $row, 'viewer' => $viewer])->render();

            return $row;
        })->all();

        return $result;
    }
}
