<?php

namespace App\Models;

use App\Helpers\Pagination;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class EmailTemplate extends Model
{
    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'email_templates';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['key', 'title', 'subject', 'body', 'params', 'created_at', 'updated_at'];

    /**
     * Retrieves paginated list of email_template for admin with search capability.
     *
     * @param  array  $postData  The data passed for pagination and search.
     * @return array The paginated and formatted list of pages.
     */
    public function listAdmin(array $postData): array
    {

        $query = DB::table($this->table);

        // Apply search filter if search text is provided and is more than 2 characters long
        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $query->where('title', 'like', '%'.$searchText.'%');
        }

        // Retrieve paginated result using custom Pagination helper
        $result = (new Pagination)->getDataTable($query, $postData);
        $sessionUser = auth()->user();

        // Append action links based on permissions
        foreach ($result['data'] as $key => $row) {
            $result['data'][$key]->action = $this->generateActionLinks($row, $sessionUser);
        }

        return $result;
    }

    /**
     * Generates action links based on user permissions for each row.
     *
     * @param  EmailTemplate  $row  The row data.
     * @param  User  $sessionUser  The authenticated user.
     * @return string The generated HTML action links.
     */
    protected function generateActionLinks($row, $sessionUser): string
    {
        $actionLinks = '';

        // Add update link if user has permission
        if ($sessionUser && $sessionUser->hasPermission('admin/email-template/update')) {
            $actionLinks .= '<a href="admin/email-template/update?id='.$row->id.'" class="btn btn-icon pjax" title="Update"><i class="bx bxs-edit icon-base"></i></a>';
        }

        // Add view link if user has permission
        if ($sessionUser && $sessionUser->hasPermission('admin/email-template/view')) {
            $actionLinks .= '<a target="_blank" href="admin/email-template/view?id='.$row->id.'" class="btn btn-icon pjax" title="View"><i class="bx bxs-show icon-base"></i></a>&nbsp;';
        }

        return '<div class="d-flex align-items-center">'.$actionLinks.'</div>';
    }

    /**
     * Stores or updates a page record based on provided data.
     *
     * @param  array  $postData  The data for creating or updating a page.
     * @return array The status and message of the operation.
     */
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

        $model = self::find($postData['id']);

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
            'message' => 'Email Template saved successfully' ?? 'Email Template Updated Successfully',
            'next' => 'load',
            'url' => 'admin/email-template',
        ];
    }

    /**
     * Retrieves a specific email template by its key and parses it with provided data.
     *
     * @param  string  $key  The key of the email template.
     * @param  array  $data  The data to replace in the template.
     * @return array The parsed subject and body of the email.
     */
    public function getEmailTemplate($key, $data = []): array
    {
        $template = $this->where('key', $key)->first();

        return $this->parseTemplate($template, $data);
    }

    /**
     * Parses the email template and replaces placeholders with actual data.
     *
     * @param  object  $template  The email template object.
     * @param  array  $data  The data to replace in the template.
     * @return array The parsed subject and body of the email.
     */
    public function parseTemplate($template, $data = []): array
    {
        if (! $template) {
            return ['subject' => '', 'body' => ''];
        }
        $body = $template->body;
        $subject = $template->subject;
        if ($data) {
            $data['app_name'] = config('app.name');
            foreach ($data as $key => $value) {
                $body = str_replace('{{'.$key.'}}', $value, $body);
                $subject = str_replace('{{'.$key.'}}', $value, $subject);
            }
        }
        // add header footer
        $body = view('email/template', ['subject' => $subject, 'body' => $body])->render();

        return ['subject' => $subject, 'body' => $body];
    }
}
