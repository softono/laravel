<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    protected $table = 'blogs';

    public $timestamps = false;

    protected $fillable = ['slug', 'title', 'excerpt', 'body', 'category', 'image', 'meta_title', 'meta_description', 'status'];

    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'updated_at' => 'datetime'];
    }
}
