<?php

use App\Modules\Admin\Auth\Controllers\LoginController;
use App\Modules\Admin\Auth\Controllers\PageController;
use Illuminate\Support\Facades\Route;

// Loaded under the `admin` prefix by bootstrap/app.php, without `auth.admin`: these are the guest routes.
Route::middleware(['device.uid', 'admin.guest.redirect'])->group(function () {
    Route::get('/auth/login', [LoginController::class, 'show'])->name('admin/auth/login');
    Route::get('/auth/password-forgot', [PageController::class, 'showForgot'])->name('admin/auth/password-forgot');
    Route::get('/auth/reset-password', [PageController::class, 'showReset'])->name('admin/auth/reset-password');
    Route::get('/auth/verify', [PageController::class, 'showVerify'])->name('admin/auth/verify');
    Route::get('/auth/verify-account', [PageController::class, 'showVerifyAccount'])->name('admin/auth/verify-account');
});

Route::get('/auth/logout', [LoginController::class, 'logout'])->middleware('device.uid')->name('admin/auth/logout');

Route::post('/auth/login', [LoginController::class, 'login'])->middleware(['device.uid', 'auth.throttle:admin_login']);
