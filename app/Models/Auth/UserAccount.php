<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Port of src/server/models/user-account.ts. One row per login provider
 * per user - provider_id is 'credential' (password lives here) or
 * 'google'.
 */
class UserAccount extends Model
{
    use HasUuids;

    protected $table = 'user_accounts';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'account_id',
        'provider_id',
        'user_id',
        'access_token',
        'refresh_token',
        'id_token',
        'access_token_expires_at',
        'refresh_token_expires_at',
        'scope',
        'password',
    ];

    protected $hidden = [
        'password',
        'access_token',
        'refresh_token',
        'id_token',
    ];

    protected function casts(): array
    {
        return [
            'access_token_expires_at' => 'datetime',
            'refresh_token_expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
