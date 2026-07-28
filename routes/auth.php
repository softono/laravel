<?php

use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\Auth\PageController as AdminAuthPageController;
use App\Http\Controllers\Account\AccountSecurityController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LoginLinkController;
use App\Http\Controllers\Auth\PasskeyController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\SessionController;
use App\Http\Controllers\Auth\TfaController;
use App\Http\Controllers\Auth\VerifyController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth routes
|--------------------------------------------------------------------------
|
| This file replaces the auth-related routes previously defined in
| routes/web.php
*/

// --- HTML pages (guests only - redirect to /dashboard if already signed in) ---
Route::middleware(['device.uid', 'guest.redirect'])->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::get('/password-forgot', [PasswordController::class, 'showForgot'])->name('password-forgot');
    Route::get('/reset-password', [PasswordController::class, 'showReset'])->name('reset-password');
    Route::get('/verify', [VerifyController::class, 'show'])->name('verify');
    Route::get('/verify-account', [VerifyController::class, 'showAccount'])->name('verify-account');
});

Route::get('/logout', [LoginController::class, 'logout'])->middleware('device.uid')->name('logout');

// Not guest-only: the approving device may already have its own session
// (e.g. approving from a phone that's logged in) - approving a signin on
// another device shouldn't force-redirect this one to /dashboard.
Route::get('/login/approve', [LoginLinkController::class, 'showApprove'])
    ->middleware('device.uid')->name('login/approve');

Route::middleware(['device.uid', 'auth.session'])->group(function () {
    Route::get('/account/two-factor', [AccountSecurityController::class, 'twoFactor'])->name('account/two-factor');
    Route::get('/account/passkeys', [AccountSecurityController::class, 'passkeys'])->name('account/passkeys');
});

// --- Admin HTML pages (guests only - redirect to /admin/dashboard if already signed in as an admin) ---
Route::prefix('admin')->middleware(['device.uid', 'admin.guest.redirect'])->group(function () {
    Route::get('/auth/login', [AdminLoginController::class, 'show'])->name('admin/auth/login');
    Route::get('/auth/password-forgot', [AdminAuthPageController::class, 'showForgot'])->name('admin/auth/password-forgot');
    Route::get('/auth/reset-password', [AdminAuthPageController::class, 'showReset'])->name('admin/auth/reset-password');
    Route::get('/auth/verify', [AdminAuthPageController::class, 'showVerify'])->name('admin/auth/verify');
    Route::get('/auth/verify-account', [AdminAuthPageController::class, 'showVerifyAccount'])->name('admin/auth/verify-account');
});

Route::get('/admin/auth/logout', [AdminLoginController::class, 'logout'])->middleware('device.uid')->name('admin/auth/logout');

// --- Public JSON endpoints ---
Route::middleware('device.uid')->prefix('api/auth')->group(function () {
    Route::post('/login', [LoginController::class, 'login'])->middleware('auth.throttle:login');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('auth.throttle:register');
    Route::post('/logout', [LoginController::class, 'apiLogout']);
    Route::get('/session', [SessionController::class, 'show']);
    Route::post('/forgot-password', [PasswordController::class, 'forgot'])->middleware('auth.throttle:forgot_password');
    Route::post('/reset-password', [PasswordController::class, 'reset'])->middleware('auth.throttle:reset_password');
    Route::post('/verify-account', [VerifyController::class, 'verifyAccount'])->middleware('auth.throttle:verify_account');
    Route::post('/otp', [VerifyController::class, 'resend'])->middleware('auth.throttle:otp');

    // 2FA login-time challenge - gated by the signed tfa cookie, not a session.
    Route::get('/tfa/methods', [TfaController::class, 'methods'])->middleware('auth.throttle:tfa');
    Route::post('/tfa/send-otp', [TfaController::class, 'sendOtp'])->middleware('auth.throttle:tfa');
    Route::post('/tfa/verify', [TfaController::class, 'verify'])->middleware('auth.throttle:tfa');

    // Magic login link - enumeration-safe by design (see LoginLinkService::start).
    Route::post('/login-link', [LoginLinkController::class, 'start'])->middleware('auth.throttle:login_link');
    Route::post('/login-link/poll', [LoginLinkController::class, 'poll'])->middleware('auth.throttle:login_link_poll');
    Route::get('/login-link/approve', [LoginLinkController::class, 'approveInfo'])->middleware('auth.throttle:login_link_approve');
    Route::post('/login-link/approve', [LoginLinkController::class, 'respond'])->middleware('auth.throttle:login_link_approve');

    // Passkey login - discoverable credentials, no email needed.
    Route::post('/passkey/login-options', [PasskeyController::class, 'loginOptions']);
    Route::post('/passkey/login-verify', [PasskeyController::class, 'loginVerify']);
});

// Google OAuth - GET redirects, outside the throttled JSON block (state is
// Socialite's own CSRF protection, not our rate limiter).
Route::middleware('device.uid')->prefix('api/auth')->group(function () {
    Route::get('/google', [GoogleController::class, 'redirect']);
    Route::get('/google/callback', [GoogleController::class, 'callback']);
});

Route::post('/api/admin/auth/login', [AdminLoginController::class, 'login'])
    ->middleware(['device.uid', 'auth.throttle:admin_login']);

// --- Authenticated JSON endpoints ---
Route::middleware(['device.uid', 'auth.session'])->prefix('api/auth')->group(function () {
    Route::post('/change-password', [PasswordController::class, 'changePassword']);

    Route::get('/2fa/status', [TfaController::class, 'status']);
    Route::post('/2fa/enable', [TfaController::class, 'enable']);
    Route::post('/2fa/verify-setup', [TfaController::class, 'verifySetup']);
    Route::post('/2fa/disable', [TfaController::class, 'disable']);
    Route::post('/2fa/remove-authenticator', [TfaController::class, 'removeAuthenticator']);
    Route::post('/2fa/backup-codes', [TfaController::class, 'regenerateBackupCodes']);

    Route::get('/passkey/list', [PasskeyController::class, 'index']);
    Route::post('/passkey/register-options', [PasskeyController::class, 'registerOptions']);
    Route::post('/passkey/register-verify', [PasskeyController::class, 'registerVerify']);
    Route::post('/passkey/delete', [PasskeyController::class, 'destroy']);
});