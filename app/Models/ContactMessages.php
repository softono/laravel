<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessages extends Model
{
    protected $table = 'contact_messages';

    /** The table has created_at only, filled by the database default. */
    public $timestamps = false;

    protected $fillable = ['user_id', 'to_user', 'subject', 'message'];
}
