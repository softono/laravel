<?php

namespace App\Models\Storage;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One access-key/secret-key credential pair. Every credential belonging to
 * a User has full access to all of that User's buckets - there is no
 * per-key, per-bucket permission scoping (IAM Policies are out of scope).
 */
class ApiUser extends Model
{
    protected $table = 'storage_api_users';

    protected $fillable = [
        'user_id',
        'access_key',
        'secret_key',
        'status',
        'last_used_at',
    ];

    protected $hidden = [
        'secret_key',
    ];

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
