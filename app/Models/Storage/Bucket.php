<?php

namespace App\Models\Storage;

use App\Models\Auth\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A bucket belongs directly to a User (Bucket Admin, role USER) - there is
 * no separate Account/tenant concept. See Tenancy Model in docs/local/prd.md.
 */
class Bucket extends Model
{
    protected $table = 'storage_buckets';

    protected $fillable = [
        'uuid',
        'name',
        'visibility',
        'user_id',
        'storage_quota',
    ];

    protected function casts(): array
    {
        return [
            'storage_quota' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $bucket) {
            $bucket->uuid ??= (string) Str::uuid();
        });
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function objects(): HasMany
    {
        return $this->hasMany(StorageObject::class, 'bucket_id');
    }
}
