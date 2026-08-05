<?php

namespace App\Models\Storage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

/**
 * object_key is the client-visible S3 path; relative_storage_path is the
 * on-disk path relative to buckets/{user_id}/{bucket_name}/ - see Database
 * Storage Strategy in docs/local/prd.md. The full physical path is always
 * derived, never stored.
 */
class StorageObject extends Model
{
    protected $table = 'storage_objects';

    protected $fillable = [
        'uuid',
        'bucket_id',
        'object_key',
        'original_filename',
        'mime_type',
        'size',
        'checksum',
        'relative_storage_path',
        'metadata_json',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'metadata_json' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $object) {
            $object->uuid ??= (string) Str::uuid();
        });
    }

    public function bucket(): BelongsTo
    {
        return $this->belongsTo(Bucket::class, 'bucket_id');
    }

    /**
     * The S3 ETag header - MD5 of the body, quoted per the spec.
     */
    public function etag(): string
    {
        return '"'.$this->checksum.'"';
    }
}
