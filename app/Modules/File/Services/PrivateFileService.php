<?php

namespace App\Modules\File\Services;

use Illuminate\Support\Facades\Storage;

/**
 * Resolves a path inside the private disk (`storage/app/private`), refusing
 * anything that would escape it. The path arrives base64-encoded, as in Next's
 * `GET /api/file?p=`.
 */
class PrivateFileService
{
    /** Absolute path of the requested file, or null when it is outside the disk, missing or not a file. */
    public function resolve(string $encodedPath): ?string
    {
        $relative = base64_decode($encodedPath, true);

        if ($relative === false || $relative === '' || str_contains($relative, "\0")) {
            return null;
        }

        // Flysystem throws on traversal; answer 404 like any other miss instead of a 500.
        if (in_array('..', preg_split('#[\\/]+#', $relative), true)) {
            return null;
        }

        $root = realpath(Storage::disk('local')->path(''));
        $resolved = realpath(Storage::disk('local')->path($relative));

        if ($root === false || $resolved === false) {
            return null;
        }

        // Compare with a trailing separator so `/private-x` is not mistaken for `/private`.
        if (! str_starts_with($resolved, $root.DIRECTORY_SEPARATOR) || ! is_file($resolved)) {
            return null;
        }

        return $resolved;
    }
}
