<?php

use App\Modules\Admin\Activity\Controllers\ActivityController;
use Illuminate\Support\Facades\Route;

// Admin prefix and auth.admin are applied by the loader in bootstrap/app.php.
Route::prefix('activity')->group(function () {
    Route::get('/', [ActivityController::class, 'index'])->name('admin/activity');
    Route::post('list', [ActivityController::class, 'list'])->name('admin/activity/list');
});
