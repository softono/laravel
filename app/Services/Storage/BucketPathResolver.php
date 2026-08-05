<?php

namespace App\Services\Storage;

use App\Models\Storage\Bucket;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;

/**
 * Physical storage is organised by user, bucket and upload date - see
 * Storage Structure in docs/local/prd.md:
 *
 *   {storage_path}/{user_id}/{bucket_name}/YYYY/MM/DD/{random_filename}
 *
 * The DB only ever stores object_key (client-visible S3 path) and
 * relative_storage_path (date/filename, relative to the bucket directory) -
 * never a full physical path - see Database Storage Strategy. The base
 * storage_path itself is admin-configurable (Settings), so it is resolved
 * fresh on every call rather than baked into config/filesystems.php.
 */
class BucketPathResolver
{
    /**
     * A Flysystem-backed local disk rooted at storage/app/{setting.storage_path}.
     */
    public function disk(): Filesystem
    {
        return Storage::build([
            'driver' => 'local',
            'root' => storage_path('app/'.trim(config('setting.storage_path', 'buckets'), '/')),
            'throw' => true,
        ]);
    }

    /**
     * The bucket's own directory, relative to the storage disk root:
     * {user_id}/{bucket_name}
     */
    public function bucketDirectory(Bucket $bucket): string
    {
        return $bucket->user_id.'/'.$bucket->name;
    }

    /**
     * A fresh date-bucketed relative path for a newly uploaded object,
     * preserving the original extension: YYYY/MM/DD/{random}.{ext}
     */
    public function newRelativeStoragePath(string $originalFilename): string
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $randomName = bin2hex(random_bytes(16)).($extension ? '.'.$extension : '');

        return now()->format('Y/m/d').'/'.$randomName;
    }

    /**
     * Full path relative to the storage disk root:
     * {user_id}/{bucket_name}/{relative_storage_path}
     */
    public function fullRelativePath(Bucket $bucket, string $relativeStoragePath): string
    {
        return $this->bucketDirectory($bucket).'/'.$relativeStoragePath;
    }
}
