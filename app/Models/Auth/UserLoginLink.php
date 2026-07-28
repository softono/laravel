<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Magic login link / second-device approval flow.
 */
class UserLoginLink extends Model
{
    use HasUuids;

    protected $table = 'user_login_links';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'purpose',
        'email',
        'user_id',
        'poll_token_hash',
        'link_token_hash',
        'code',
        'status',
        'device_name',
        'location',
        'ip',
        'remember',
        'trust_device',
        'tfa_handle',
        'expires_at',
        'approved_at',
    ];

    protected $hidden = [
        'poll_token_hash',
        'link_token_hash',
    ];

    protected function casts(): array
    {
        return [
            'remember' => 'boolean',
            'trust_device' => 'boolean',
            'expires_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
