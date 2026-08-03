<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * Class Setting
 *
 * Represents application settings stored in the database.
 * Provides methods to retrieve, update, and cache settings.
 */
class Setting extends Model
{
    /**
     * @var string The table associated with the model.
     */
    protected $table = 'settings';

    /**
     * @var string The primary key associated with the table.
     */
    protected $primaryKey = 'id';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = ['key', 'value', 'type', 'group', 'created_at', 'updated_at'];

    /**
     * @var bool Indicates if the model should be timestamped.
     */
    public $timestamps = false;
}
