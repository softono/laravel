<?php

use App\Http\Controllers\Api\Storage\BucketController;
use App\Http\Controllers\Api\Storage\ObjectController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| S3-compatible REST API
|--------------------------------------------------------------------------
|
| See "S3 Compatible APIs" in docs/local/prd.md. Deliberately registered
| at the host root (no /api prefix) for compatibility with vanilla S3
| clients - see bootstrap/app.php for why this file is required last and
| docs/local/api.md for the resulting list of reserved bucket names
| (any single path segment already claimed by routes/web.php or
| routes/auth.php - "login", "admin", "dashboard", etc.).
|
| Every route here already sits behind ['storage.auth', 'throttle:storage-api']
| (applied by bootstrap/app.php), so controllers only deal with ownership/
| visibility, never raw credential checks.
*/

Route::get('/', [BucketController::class, 'index']);
Route::put('/{bucket}', [BucketController::class, 'store'])->where('bucket', '[A-Za-z0-9.-]+');
Route::delete('/{bucket}', [BucketController::class, 'destroy'])->where('bucket', '[A-Za-z0-9.-]+');

Route::get('/{bucket}', [ObjectController::class, 'index'])->where('bucket', '[A-Za-z0-9.-]+');

// {object} intentionally matches '.*' - S3 object keys are themselves
// '/'-delimited virtual paths (see Object Management > Folder support).
Route::put('/{bucket}/{object}', [ObjectController::class, 'store'])
    ->where(['bucket' => '[A-Za-z0-9.-]+', 'object' => '.*']);
Route::get('/{bucket}/{object}', [ObjectController::class, 'show'])
    ->where(['bucket' => '[A-Za-z0-9.-]+', 'object' => '.*']);
Route::match(['head'], '/{bucket}/{object}', [ObjectController::class, 'head'])
    ->where(['bucket' => '[A-Za-z0-9.-]+', 'object' => '.*']);
Route::delete('/{bucket}/{object}', [ObjectController::class, 'destroy'])
    ->where(['bucket' => '[A-Za-z0-9.-]+', 'object' => '.*']);
