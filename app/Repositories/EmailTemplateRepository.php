<?php

namespace App\Repositories;

use App\Helpers\Pagination;
use App\Models\EmailTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmailTemplateRepository
{
    public function findById($id): ?EmailTemplate
    {
        return EmailTemplate::find($id);
    }

    public function findByKey(string $key): ?EmailTemplate
    {
        return EmailTemplate::where('key', $key)->first();
    }

    public function listAdmin(array $postData): array
    {
        $query = DB::table('email_templates');

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

    protected function generateActionLinks($row, $sessionUser): string
    {
        $actionLinks = '';

        if ($sessionUser && $sessionUser->hasPermission('admin/email-template/update')) {
            $actionLinks .= '<a href="admin/email-template/update?id='.$row->id.'" class="btn btn-icon pjax" title="Update"><i class="bx bxs-edit icon-base"></i></a>';
        }

        if ($sessionUser && $sessionUser->hasPermission('admin/email-template/view')) {
            $actionLinks .= '<a target="_blank" href="admin/email-template/view?id='.$row->id.'" class="btn btn-icon pjax" title="View"><i class="bx bxs-show icon-base"></i></a>&nbsp;';
        }

        return '<div class="d-flex align-items-center">'.$actionLinks.'</div>';
    }

    public function store(array $postData): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ];
        $validator = Validator::make($postData, $rules);
        if ($validator->fails()) {
            return [
                'status' => 0,
                'message' => $validator->errors()->first(),
            ];
        }

        $model = EmailTemplate::find($postData['id'] ?? null);

        if (! $model) {
            return [
                'status' => 0,
                'message' => 'Page not found.',
            ];
        }

        $model->title = $postData['title'];
        $model->subject = $postData['subject'];
        $model->body = $postData['body'];
        $model->save();

        return [
            'status' => 1,
            'message' => 'Email Template saved successfully',
            'next' => 'load',
            'url' => 'admin/email-template',
        ];
    }

    public function getEmailTemplate($key, $data = []): array
    {
        $template = $this->findByKey($key);
        $model = new EmailTemplate;

        return $model->parseTemplate($template, $data);
    }
}
