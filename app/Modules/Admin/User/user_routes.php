<?php

use App\Modules\Admin\User\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Admin prefix and auth.admin are applied by the loader in bootstrap/app.php.
Route::prefix('user')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('admin/user');
    Route::post('list', [UserController::class, 'list'])->name('admin/user/list');
    Route::get('create', [UserController::class, 'create'])->name('admin/user/create');
    Route::get('update', [UserController::class, 'update'])->name('admin/user/update');
    Route::post('save', [UserController::class, 'save'])->name('admin/user/save');
    Route::get('view', [UserController::class, 'view'])->name('admin/user/view');
    Route::post('delete', [UserController::class, 'delete'])->name('admin/user/delete');
    Route::post('change-status', [UserController::class, 'changeStatus'])->name('admin/user/change-status');
    Route::post('mail', [UserController::class, 'sendMail'])->name('admin/user/mail');
});
