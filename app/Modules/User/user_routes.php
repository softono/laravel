<?php

use App\Modules\User\Controllers\AccountController;
use App\Modules\User\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['device.uid', 'auth.user'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('account')->group(function () {
        Route::get('update', [AccountController::class, 'update'])->name('account/update');
        Route::post('update-process', [AccountController::class, 'updateProcess'])->name('account/update-process');
        Route::get('password-change', [AccountController::class, 'passwordChange'])->name('account/password-change');
        Route::get('image', [AccountController::class, 'image'])->name('account/image');
        Route::post('image-save', [AccountController::class, 'imageSave'])->name('account/image-save');
        Route::post('delete-image', [AccountController::class, 'deleteImage'])->name('account/delete-image');
        Route::get('two-factor', [AccountController::class, 'twoFactor'])->name('account/two-factor');
        Route::get('passkeys', [AccountController::class, 'passkeys'])->name('account/passkeys');
        Route::get('session', [AccountController::class, 'session'])->name('account/session');
        Route::post('session-list', [AccountController::class, 'sessionList'])->name('account/session-list');
        Route::post('session-logout', [AccountController::class, 'sessionLogout'])->name('account/session-logout');
        Route::get('user-activity', [AccountController::class, 'userActivity'])->name('account/user-activity');
        Route::post('user-activity-list', [AccountController::class, 'userActivityList'])->name('account/user-activity-list');
        Route::post('deactivate', [AccountController::class, 'deactivate'])->name('account/deactivate');
    });
});
