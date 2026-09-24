<?php

namespace App\Modules\File\Services;

use App\Helpers\ApiResult;
use Illuminate\Support\Facades\Storage;

/**
 * Resolves a path inside the private disk (`storage/app/private`), refusing
 * anything that would escape it. The path arrives base64-encoded, as in Next's
 * `GET /api/file?p=`.
 */
class PrivateFileService
{
    /** `data.path`: absolute path of the requested file; a failure (404) when it is outside the disk, missing or not a file. */
    public function resolve(string $encodedPath): array
    {
        $relative = base64_decode($encodedPath, true);

        if ($relative === false || $relative === '' || str_contains($relative, "\0")) {
            return $this->notFound();
        }

        // Flysystem throws on traversal; answer 404 like any other miss instead of a 500.
        if (in_array('..', preg_split('#[\\/]+#', $relative), true)) {
            return $this->notFound();
        }

        $root = realpath(Storage::disk('local')->path(''));
        $resolved = realpath(Storage::disk('local')->path($relative));

        if ($root === false || $resolved === false) {
            return $this->notFound();
        }

        // Compare with a trailing separator so `/private-x` is not mistaken for `/private`.
        if (! str_starts_with($resolved, $root.DIRECTORY_SEPARATOR) || ! is_file($resolved)) {
            return $this->notFound();
        }

        return ApiResult::success('', ['path' => $resolved]);
    }

    protected function notFound(): array
    {
        return ApiResult::failure('File not found', [], 404);
    }
}
