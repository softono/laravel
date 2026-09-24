<?php

use App\Modules\Admin\Blog\Controllers\BlogController;
use Illuminate\Support\Facades\Route;

// Admin prefix and auth.admin are applied by the loader in bootstrap/app.php.
Route::prefix('blog')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('admin/blog');
    Route::post('list', [BlogController::class, 'list'])->name('admin/blog/list');
    Route::get('create', [BlogController::class, 'create'])->name('admin/blog/create');
    Route::get('update', [BlogController::class, 'update'])->name('admin/blog/update');
    Route::post('save', [BlogController::class, 'save'])->name('admin/blog/save');
    Route::post('delete', [BlogController::class, 'delete'])->name('admin/blog/delete');
    Route::post('save-image', [BlogController::class, 'saveImage'])->name('admin/blog/save-image');
});
