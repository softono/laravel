<?php

use App\Modules\Admin\Admins\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

// Admin prefix and auth.admin are applied by the loader in bootstrap/app.php.
Route::prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin/admin');
    Route::post('list', [AdminController::class, 'list'])->name('admin/admin/list');
    Route::get('create', [AdminController::class, 'create'])->name('admin/admin/create');
    Route::get('update', [AdminController::class, 'update'])->name('admin/admin/update');
    Route::post('save', [AdminController::class, 'save'])->name('admin/admin/save');
    Route::get('view', [AdminController::class, 'view'])->name('admin/admin/view');
    Route::post('delete', [AdminController::class, 'delete'])->name('admin/admin/delete');
    Route::post('change-status', [AdminController::class, 'changeStatus'])->name('admin/admin/change-status');
});
