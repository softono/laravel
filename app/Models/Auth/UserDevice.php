<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Trusted devices for 2FA ("skip 2FA on this device for 30 days").
 */
class UserDevice extends Model
{
    use HasUuids;

    protected $table = 'user_devices';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'device_uid',
        'ip_address',
        'user_agent',
        'trusted_at',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'trusted_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
