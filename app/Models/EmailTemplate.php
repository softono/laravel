<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    protected $table = 'email_templates';

    public $timestamps = false;

    protected $fillable = ['key', 'title', 'subject', 'body', 'params'];
}
