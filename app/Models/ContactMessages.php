<?php

namespace App\Models;

use App\Helpers\Pagination;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Page
 *
 * Model for the `page` table.
 * Handles listing pages for admin with search and pagination.
 */
class ContactMessages extends Model
{
    /**
     * The name of the table associated with the model.
     *
     * @var string
     */
    protected $table = 'contact_messages';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The table (mirrored from Next) has created_at only.
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = ['user_id', 'to_user', 'subject', 'message', 'created_at'];

    /**
     * Retrieves paginated list of pages for admin with search capability.
     *
     * @param  array  $postData  The data passed for pagination and search.
     * @return array The paginated and formatted list of pages.
     */
}
