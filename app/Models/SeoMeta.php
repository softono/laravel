<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoMeta extends Model
{
    protected $table = 'seos';

    public $timestamps = false;

    protected $fillable = [
        'type',
        'url',
        'title',
        'meta_title',
        'keyword',
        'meta_keyword',
        'description',
        'meta_description',
        'image',
        'canonical',
        'last_modified',
        'change_frequency',
        'priority',
        'status',
        'sitemap_enable',
    ];
}
