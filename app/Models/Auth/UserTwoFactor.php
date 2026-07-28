<?php

namespace App\Models\Auth;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * No timestamps at all - this table only ever holds current 2FA state.
 */
class UserTwoFactor extends Model
{
    use HasUuids;

    protected $table = 'user_two_factors';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'secret',
        'backup_codes',
        'user_id',
        'verified',
    ];

    protected $hidden = [
        'secret',
        'backup_codes',
    ];

    protected function casts(): array
    {
        return [
            'verified' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
