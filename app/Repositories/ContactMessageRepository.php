<?php

namespace App\Repositories;

use App\Helpers\Pagination;
use App\Models\ContactMessages;
use Illuminate\Support\Facades\DB;

class ContactMessageRepository
{
    public function findById($id): ?ContactMessages
    {
        return ContactMessages::find($id);
    }

    public function create(array $data): ContactMessages
    {
        return ContactMessages::create($data);
    }

    public function listAdmin(array $postData): array
    {
        $query = DB::table('contact_messages');

        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $query->where('subject', 'like', '%'.$searchText.'%')
                ->orWhere('message', 'like', '%'.$searchText.'%');
        }

        return (new Pagination)->getDataTable($query, $postData);
    }
}
