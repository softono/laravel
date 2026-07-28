<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * OTP store for email verification / password reset / signin / 2FA email
 * codes. No FK to users - linked purely by `identifier` = "{purpose}:{email}".
 */
class UserVerification extends Model
{
    use HasUuids;

    protected $table = 'user_verifications';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'identifier',
        'value',
        'expires_at',
        'attempts',
    ];

    protected $hidden = [
        'value',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }
}
