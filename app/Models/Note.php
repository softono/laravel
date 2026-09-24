<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $table = 'notes';

    public $timestamps = false;

    protected $fillable = ['user_id', 'title', 'note'];
}
