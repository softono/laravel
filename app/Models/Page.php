<?php

namespace App\Models;

use App\Helpers\Pagination;
use App\Helpers\General;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Class Page
 *
 * Model for the `page` table.
 * Handles listing pages for admin with search and pagination.
 *
 * @package App\Models
 */
class Page extends Model
{
    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'pages';

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
    protected $fillable = ['slug', 'title', 'body', 'meta_title', 'meta_description', 'status', 'created_at', 'updated_at'];

    /**
     * Retrieves paginated list of pages for admin with search capability.
     *
     * @param array $postData The data passed for pagination and search.
     * @return array The paginated and formatted list of pages.
     */
    public function listAdmin(array $postData): array
    {
        $query = DB::table($this->table);

        // Apply search filter if search text is provided and is more than 2 characters long
        $searchText = $postData['search']['value'] ?? '';
        if (strlen($searchText) > 2) {
            $query->where('title', 'like', '%' . $searchText . '%');
        }

        // Retrieve paginated result using custom Pagination helper
        $result = (new Pagination())->getDataTable($query, $postData);
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
     * @param object $row The row data.
     * @param \Illuminate\Contracts\Auth\Authenticatable|null $sessionUser The authenticated user.
     * @return string The generated HTML action links.
     */
    protected function generateActionLinks(object $row, $sessionUser): string
    {
        $actionLinks = '';

        // Add update link if user has permission
        if ($sessionUser && $sessionUser->hasPermission('admin/page/update')) {
            $actionLinks .= sprintf(
                '<a href="admin/page/update?id=%d" class="btn btn-icon pjax" title="Update"><i class="bx bxs-edit icon-base"></i></a>',
                $row->id
            );
        }
        // Add view link if user has permission
        if ($sessionUser && $sessionUser->hasPermission('page/')) {
            $actionLinks .= sprintf(
                '<a target="_blank" href="page/%s" class="btn btn-icon pjax" title="View"><i class="bx bxs-show icon-base"></i></a>&nbsp;',
                htmlspecialchars($row->slug, ENT_QUOTES, 'UTF-8')
            );
        }

        return '<div class="d-flex align-items-center">' . $actionLinks . '</div>';
    }

    /**
     * Stores or updates a page record based on provided data.
     *
     * @param array $postData The data for creating or updating a page.
     * @return array The status and message of the operation.
     */
    public function store(array $postData): array
    {
        $general = new General();

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

        $model = self::find($postData['id']);

        if (!$model) {
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
            'message' => 'Pages  saved successfully' ?? 'Pages Updated Successfully',
            'next' => 'load',
            'url' => 'admin/pages',
        ];
    }
}