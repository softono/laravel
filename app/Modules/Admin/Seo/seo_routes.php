<?php

use App\Modules\Admin\Seo\Controllers\SeoController;
use Illuminate\Support\Facades\Route;

// Admin prefix and auth.admin are applied by the loader in bootstrap/app.php.
Route::prefix('seo')->group(function () {
    Route::get('meta', [SeoController::class, 'index'])->name('admin/seo/meta');
    Route::post('list', [SeoController::class, 'list'])->name('admin/seo/list');
    Route::get('create', [SeoController::class, 'create'])->name('admin/seo/create');
    Route::get('update', [SeoController::class, 'update'])->name('admin/seo/update');
    Route::post('save', [SeoController::class, 'save'])->name('admin/seo/save');
    Route::post('delete', [SeoController::class, 'delete'])->name('admin/seo/delete');
    Route::post('sitemap-update', [SeoController::class, 'sitemapUpdate'])->name('admin/seo/sitemap-update');
});
