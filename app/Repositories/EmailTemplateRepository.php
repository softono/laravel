<?php

namespace App\Repositories;

use App\Helpers\Pagination;
use App\Models\EmailTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EmailTemplateRepository
{
    public function __construct(protected Pagination $pagination) {}

    public function findById(int|string $id): ?EmailTemplate
    {
        return EmailTemplate::find($id);
    }

    public function findByKey(string $key): ?EmailTemplate
    {
        return EmailTemplate::where('key', $key)->first();
    }

    public function update(EmailTemplate $template, array $data): bool
    {
        return $template->update($data);
    }

    /**
     * @return array{recordsTotal: int, recordsFiltered: int, draw: int|string, data: Collection}
     */
    public function datatable(array $post): array
    {
        $query = DB::table('email_templates')->select('id', 'key', 'title', 'subject', 'updated_at');

        $search = trim($post['search']['value'] ?? '');
        if (strlen($search) > 2) {
            $query->where('title', 'like', '%'.$search.'%');
        }

        return $this->pagination->getDataTable($query, $post);
    }
}
