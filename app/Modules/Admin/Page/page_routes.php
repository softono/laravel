<?php

use App\Modules\Admin\Page\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Admin prefix and auth.admin are applied by the loader in bootstrap/app.php.
Route::get('pages', [PageController::class, 'index'])->name('admin/page');

Route::prefix('page')->group(function () {
    Route::post('list', [PageController::class, 'list'])->name('admin/page/list');
    Route::get('update', [PageController::class, 'update'])->name('admin/page/update');
    Route::post('save', [PageController::class, 'save'])->name('admin/page/save');
    Route::post('save-image', [PageController::class, 'saveImage'])->name('admin/page/save-image');
});
