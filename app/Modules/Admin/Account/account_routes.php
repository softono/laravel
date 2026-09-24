<?php

use App\Modules\Admin\Account\Controllers\AccountController;
use App\Modules\User\Controllers\EmailChangeController;
use Illuminate\Support\Facades\Route;

// Admin prefix and auth.admin are applied by the loader in bootstrap/app.php.
Route::prefix('account')->group(function () {
    Route::get('update', [AccountController::class, 'update'])->name('admin/account/update');
    Route::post('update-process', [AccountController::class, 'updateProcess'])->name('admin/account/update-process');
    Route::get('password-change', [AccountController::class, 'passwordChange'])->name('admin/account/password-change');
    Route::get('image', [AccountController::class, 'image'])->name('admin/account/image');
    Route::post('image-save', [AccountController::class, 'imageSave'])->name('admin/account/image-save');
    Route::post('delete-image', [AccountController::class, 'deleteImage'])->name('admin/account/delete-image');
    Route::get('two-factor', [AccountController::class, 'twoFactor'])->name('admin/account/two-factor');
    Route::get('passkeys', [AccountController::class, 'passkeys'])->name('admin/account/passkeys');
    Route::get('session', [AccountController::class, 'session'])->name('admin/account/session');
    Route::post('session-list', [AccountController::class, 'sessionList'])->name('admin/account/session-list');
    Route::post('session-logout', [AccountController::class, 'sessionLogout'])->name('admin/account/session-logout');
    Route::get('user-activity', [AccountController::class, 'userActivity'])->name('admin/account/user-activity');
    Route::post('user-activity-list', [AccountController::class, 'userActivityList'])->name('admin/account/user-activity-list');
    Route::post('deactivate', [AccountController::class, 'deactivate'])->name('admin/account/deactivate');
    Route::post('email/start', [EmailChangeController::class, 'start'])->middleware('auth.throttle:email_change')->name('admin/account/email/start');
    Route::post('email/resend', [EmailChangeController::class, 'resend'])->middleware('auth.throttle:email_change')->name('admin/account/email/resend');
    Route::post('email/verify-new', [EmailChangeController::class, 'verifyNew'])->middleware('auth.throttle:email_change')->name('admin/account/email/verify-new');
    Route::post('email/send-otp', [EmailChangeController::class, 'sendOtp'])->middleware('auth.throttle:email_change')->name('admin/account/email/send-otp');
    Route::post('email/verify', [EmailChangeController::class, 'verify'])->middleware('auth.throttle:email_change')->name('admin/account/email/verify');
});
