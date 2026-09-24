# Files and uploads

Mirrors Next's `server/lib/file`. Everything goes through `App\Services\FileStorageService`; `General::uploadFile()`, `getFileUrl()` and `deleteFile()` are thin wrappers so views keep calling `$general`.

## Upload types (`config/files.php`)

| Type | Folder | Visibility |
|---|---|---|
| `profile`, `logo`, `blog`, `content`, `email`, `temp` | `<type>/` | public |
| `documents` | `documents/` | private |

Add a type by adding a row to `files.types`; nothing else is needed.

## Disks

| Env | Meaning | Default |
|---|---|---|
| `FILESYSTEM_DISK` | disk for **public** types: `general` (`public/upload`), `public`, or `s3` | `general` |
| `FILESYSTEM_PRIVATE_DISK` | disk for **private** types: `local` (`storage/app/private`) or `s3` | `local` |
| `AWS_*`, `AWS_URL`, `AWS_ENDPOINT` | the `s3` disk (`league/flysystem-aws-s3-v3`) | |

- **Public file URL**: `disk->url(dir/file)`. On a local disk `getFileUrl()` falls back to the "no image" placeholder when the file is missing; on S3 it does not check (one network call per URL is not worth it).
- **Private file URL**: local disk → `GET /file?p=<base64 path>` (`auth.user`, traversal-guarded, `Cache-Control: private, no-store`); S3 → a presigned URL valid for `files.presigned_ttl` seconds.
- `General::getFileUrl($file, $type, $subDir = '', $processing = '')` returns the URL; never build one by hand.

## Uploading

```php
$result = $this->general->uploadFile($request->file('image'), 'blog', 'date');
// $result['data']: file_name (relative to the type's folder), file_type, size, name, extension
```

- The stored **extension comes from the file's content** (`guessExtension()`), never from the client name; content that maps to no extension is rejected ("Unsupported file type").
- Validate with `$general->fileRules('image'|'pdf'|'doc'|'all', $sizeKb)`. Laravel's `mimes` checks content, and **SVG is deliberately not accepted** (it can carry scripts).
- Private uploads are stored with `private` visibility, public ones with `public`.

## imgproxy

Set `IMGPROXY_ENABLED=true`, `IMGPROXY_KEY`, `IMGPROXY_SALT` (hex) and `IMGPROXY_URL`. Public image URLs (`jpg jpeg png gif webp bmp avif`) are then signed exactly like Next's `imageCacheUrl` (`App\Helpers\ImgProxy`); pass imgproxy processing options as the fourth argument of `getFileUrl()` (e.g. `rs:fill:64:64`). Other files and misconfigured keys fall back to the plain URL. The CSP `img-src` includes the imgproxy origin automatically.
