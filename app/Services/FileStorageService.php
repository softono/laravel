<?php

namespace App\Services;

use App\Helpers\ApiResult;
use App\Helpers\ImgProxy;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Where uploads go and how they are addressed. Public types live on
 * `files.public_disk` and are linked by URL (through imgproxy when enabled);
 * private types live on `files.private_disk` and are linked through GET /file
 * (local disk) or a presigned URL (S3). See config/files.php.
 */
class FileStorageService
{
    public function isPrivate(string $type): bool
    {
        return (config("files.types.$type.visibility") ?? 'public') === 'private';
    }

    public function directory(string $type): string
    {
        return config("files.types.$type.dir") ?? config('files.types.temp.dir');
    }

    public function disk(string $type): Filesystem
    {
        return Storage::disk($this->diskName($type));
    }

    /** URL for a stored file, or '' when there is no file name. */
    public function url(?string $file, string $type = 'profile', string $subDir = '', string $processing = ''): string
    {
        if (! $file) {
            return '';
        }

        $path = $this->directory($type).($subDir !== '' ? trim($subDir, '/').'/' : '').$file;

        if ($this->isPrivate($type)) {
            return $this->privateUrl($path);
        }

        return ImgProxy::url($this->disk($type)->url($path), $processing);
    }

    /** Whether the public file can be linked; remote disks are trusted rather than queried once per URL. */
    public function exists(string $file, string $type): bool
    {
        if (config('filesystems.disks.'.$this->diskName($type).'.driver') !== 'local') {
            return true;
        }

        return $this->disk($type)->exists($this->directory($type).$file);
    }

    /**
     * @return array{http_status: int, status: int, message: string, data: array<string, mixed>} `data`: file_name, file_type, size, name, extension
     */
    public function store(UploadedFile $file, string $type, string $subDir = '', string $name = ''): array
    {
        // The extension comes from the file's content, never the client-supplied name.
        $extension = $file->guessExtension();

        if (! $extension) {
            return ApiResult::failure('Unsupported file type');
        }

        $subDir = $subDir === 'date' ? date('Y/m') : trim($subDir, '/');
        $dir = $this->directory($type).($subDir !== '' ? $subDir.'/' : '');
        $name = match ($name) {
            '' => Str::random(32).'.'.$extension,
            'same' => $file->getClientOriginalName(),
            default => $name,
        };

        try {
            $stored = $this->disk($type)->putFileAs($dir, $file, $name, $this->isPrivate($type) ? 'private' : 'public');
        } catch (\Throwable $e) {
            report($e);

            return ApiResult::failure('File upload failed');
        }

        if (! $stored) {
            return ApiResult::failure('File upload failed');
        }

        return ApiResult::success('File uploaded successfully', [
            'file_name' => ltrim(substr($stored, strlen($this->directory($type))), '/'),
            'file_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'name' => $file->getClientOriginalName(),
            'extension' => $extension,
        ]);
    }

    public function delete(?string $file, string $type): void
    {
        if ($file) {
            $this->disk($type)->delete($this->directory($type).$file);
        }
    }

    protected function privateUrl(string $path): string
    {
        if (config('filesystems.disks.'.config('files.private_disk').'.driver') === 's3') {
            return Storage::disk(config('files.private_disk'))->temporaryUrl($path, now()->addSeconds((int) config('files.presigned_ttl')));
        }

        return route('file', ['p' => base64_encode($path)]);
    }

    protected function diskName(string $type): string
    {
        return $this->isPrivate($type) ? config('files.private_disk') : config('files.public_disk');
    }
}
