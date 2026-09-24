<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSession extends Model
{
    use HasUuids;

    protected $table = 'user_sessions';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'expires_at',
        'token',
        'ip_address',
        'user_agent',
        'user_id',
        'device_uid',
        'remember',
    ];

    protected $hidden = [
        'token',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'remember' => 'boolean',
        ];
    }

    /**
     * "Remember me" sessions live 30 days, ordinary ones a day (sliding), so an expiry
     * beyond the ordinary lifetime marks a remembered session.
     */
    public function isRemembered(): bool
    {
        return $this->expires_at->gt(now()->addSeconds(config('auth_next.session_ttl_days.default') * 86400 + 3600));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
