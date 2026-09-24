<?php

use App\Http\Controllers\Account\AccountSecurityController;
use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\Auth\PageController as AdminAuthPageController;
use Illuminate\Support\Facades\Route;

// Temporary home of the routes that move in Phase 3 (admin auth) and Phase 4 (account pages).

Route::middleware(['device.uid', 'auth.user'])->group(function () {
    Route::get('/account/two-factor', [AccountSecurityController::class, 'twoFactor'])->name('account/two-factor');
    Route::get('/account/passkeys', [AccountSecurityController::class, 'passkeys'])->name('account/passkeys');
});

// --- Admin pages (guests only - redirect to /admin/dashboard if already signed in as an admin) ---
Route::prefix('admin')->middleware(['device.uid', 'admin.guest.redirect'])->group(function () {
    Route::get('/auth/login', [AdminLoginController::class, 'show'])->name('admin/auth/login');
    Route::get('/auth/password-forgot', [AdminAuthPageController::class, 'showForgot'])->name('admin/auth/password-forgot');
    Route::get('/auth/reset-password', [AdminAuthPageController::class, 'showReset'])->name('admin/auth/reset-password');
    Route::get('/auth/verify', [AdminAuthPageController::class, 'showVerify'])->name('admin/auth/verify');
    Route::get('/auth/verify-account', [AdminAuthPageController::class, 'showVerifyAccount'])->name('admin/auth/verify-account');
});

Route::get('/admin/auth/logout', [AdminLoginController::class, 'logout'])->middleware('device.uid')->name('admin/auth/logout');

Route::post('/admin/auth/login', [AdminLoginController::class, 'login'])
    ->middleware(['device.uid', 'auth.throttle:admin_login']);
