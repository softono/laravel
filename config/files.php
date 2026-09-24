<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Upload types
    |--------------------------------------------------------------------------
    |
    | Same idea as Next's FILE_PATH / FILE_DIR: every upload type has a folder
    | and a visibility. `public` files are served by URL from FILESYSTEM_DISK;
    | `private` files live on FILESYSTEM_PRIVATE_DISK and are only reachable
    | through GET /file (local disk) or a presigned URL (S3).
    |
    */
    'types' => [
        'profile' => ['dir' => 'profile/', 'visibility' => 'public'],
        'email' => ['dir' => 'email/', 'visibility' => 'public'],
        'logo' => ['dir' => 'logo/', 'visibility' => 'public'],
        'content' => ['dir' => 'content/', 'visibility' => 'public'],
        'blog' => ['dir' => 'blog/', 'visibility' => 'public'],
        'documents' => ['dir' => 'documents/', 'visibility' => 'private'],
        'temp' => ['dir' => 'temp/', 'visibility' => 'public'],
    ],

    'public_disk' => env('FILESYSTEM_DISK', 'general'),
    'private_disk' => env('FILESYSTEM_PRIVATE_DISK', 'local'),

    // Lifetime of a presigned S3 URL for a private file.
    'presigned_ttl' => 3600,

    /*
    |--------------------------------------------------------------------------
    | imgproxy
    |--------------------------------------------------------------------------
    |
    | Signed image URLs, same scheme as Next's `imageCacheUrl`. Key and salt are
    | hex strings, as imgproxy expects.
    |
    */
    'imgproxy' => [
        'enabled' => (bool) env('IMGPROXY_ENABLED', false),
        'key' => env('IMGPROXY_KEY', ''),
        'salt' => env('IMGPROXY_SALT', ''),
        'url' => env('IMGPROXY_URL', ''),
    ],

];
