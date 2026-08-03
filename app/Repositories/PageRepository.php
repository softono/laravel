<?php

namespace App\Repositories;

use App\Helpers\General;
use App\Helpers\Pagination;
use App\Models\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PageRepository
{
    public function findById($id): ?Page
    {
        return Page::find($id);
    }

    public function findBySlug(string $slug): ?Page
    {
        return Page::where('slug', $slug)->first();
    }

    public function listAdmin(array $postData): array
    {
        $query = DB::table('pages');

        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $query->where('title', 'like', '%'.$searchText.'%');
        }

        $result = (new Pagination)->getDataTable($query, $postData);
        $sessionUser = auth()->user();

        foreach ($result['data'] as $key => $row) {
            $result['data'][$key]->action = $this->generateActionLinks($row, $sessionUser);
        }

        return $result;
    }

    protected function generateActionLinks(object $row, $sessionUser): string
    {
        $actionLinks = '';

        if ($sessionUser && $sessionUser->hasPermission('admin/page/update')) {
            $actionLinks .= sprintf(
                '<a href="admin/page/update?id=%d" class="btn btn-icon pjax" title="Update"><i class="bx bxs-edit icon-base"></i></a>',
                $row->id
            );
        }
        if ($sessionUser && $sessionUser->hasPermission('page/')) {
            $actionLinks .= sprintf(
                '<a target="_blank" href="page/%s" class="btn btn-icon pjax" title="View"><i class="bx bxs-show icon-base"></i></a>&nbsp;',
                htmlspecialchars($row->slug, ENT_QUOTES, 'UTF-8')
            );
        }

        return '<div class="d-flex align-items-center">'.$actionLinks.'</div>';
    }

    public function store(array $postData): array
    {
        $general = new General;

        $validator = Validator::make($postData, [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return [
                'status' => 0,
                'message' => $validator->errors()->first(),
            ];
        }

        $model = Page::find($postData['id'] ?? null);

        if (! $model) {
            return [
                'status' => 0,
                'message' => 'Page not found.',
            ];
        }

        $model->title = $postData['title'];
        $model->body = $postData['body'];
        $model->slug = $general->slugify($model->title);
        $model->save();

        return [
            'status' => 1,
            'message' => 'Pages saved successfully',
            'next' => 'load',
            'url' => 'admin/pages',
        ];
    }
}
