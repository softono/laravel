<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * `type` stores the App\Constants\UserActivity key, not the display label.
 */
class UserActivity extends Model
{
    use HasUuids;

    protected $table = 'user_activities';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'device_id',
        'type',
        'data',
        'ip',
        'client',
        'location',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
