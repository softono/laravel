<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Port of src/server/models/user-passkey.ts (WebAuthn credentials).
 * Next has `created_at` nullable with no default and no `updated_at` at
 * all, so timestamps are disabled entirely here (matching the Next model
 * which never auto-manages either column).
 */
class UserPasskey extends Model
{
    use HasUuids;

    protected $table = 'user_passkeys';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'public_key',
        'user_id',
        'credential_id',
        'counter',
        'device_type',
        'backed_up',
        'transports',
        'created_at',
        'aaguid',
    ];

    protected $hidden = [
        'public_key',
    ];

    protected function casts(): array
    {
        return [
            'counter' => 'integer',
            'backed_up' => 'boolean',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
