<?php

use App\Modules\Auth\Controllers\GoogleController;
use App\Modules\Auth\Controllers\LoginController;
use App\Modules\Auth\Controllers\LoginLinkController;
use App\Modules\Auth\Controllers\LoginOtpController;
use App\Modules\Auth\Controllers\PasskeyController;
use App\Modules\Auth\Controllers\PasswordController;
use App\Modules\Auth\Controllers\RegisterController;
use App\Modules\Auth\Controllers\SessionController;
use App\Modules\Auth\Controllers\TfaController;
use App\Modules\Auth\Controllers\VerifyController;
use Illuminate\Support\Facades\Route;

// --- Pages (guests only: signed-in users are redirected to /dashboard) ---
Route::middleware(['device.uid', 'auth.redirect'])->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::get('/password-forgot', [PasswordController::class, 'showForgot'])->name('password-forgot');
    Route::get('/reset-password', [PasswordController::class, 'showReset'])->name('reset-password');
    Route::get('/verify', [VerifyController::class, 'show'])->name('verify');
    Route::get('/verify-account', [VerifyController::class, 'showAccount'])->name('verify-account');
});

Route::get('/logout', [LoginController::class, 'logout'])->middleware('device.uid')->name('logout');

// Not guest-only: the approving device may already have its own session
// (e.g. approving from a phone that's logged in) - approving a sign-in on
// another device shouldn't force-redirect this one to /dashboard.
Route::get('/login/approve', [LoginLinkController::class, 'showApprove'])
    ->middleware('device.uid')->name('login/approve');

// --- Public endpoints ---
Route::middleware('device.uid')->prefix('auth')->group(function () {
    Route::post('/login', [LoginController::class, 'login'])->middleware('auth.throttle:login');
    Route::post('/login-otp', [LoginOtpController::class, 'handle'])->middleware('auth.throttle:login_otp');
    Route::post('/register', [RegisterController::class, 'register'])->middleware(['auth.throttle:register', 'recaptcha']);
    Route::post('/logout', [LoginController::class, 'apiLogout']);
    Route::get('/session', [SessionController::class, 'show']);
    Route::post('/forgot-password', [PasswordController::class, 'forgot'])->middleware(['auth.throttle:forgot_password', 'recaptcha']);
    Route::post('/reset-password', [PasswordController::class, 'reset'])->middleware('auth.throttle:reset_password');
    Route::post('/verify-account', [VerifyController::class, 'verifyAccount'])->middleware('auth.throttle:verify_account');
    Route::post('/otp', [VerifyController::class, 'resend'])->middleware('auth.throttle:otp');

    // 2FA login-time challenge - gated by the signed tfa cookie, not a session.
    Route::get('/tfa/methods', [TfaController::class, 'methods'])->middleware('auth.throttle:tfa');
    Route::post('/tfa/send-otp', [TfaController::class, 'sendOtp'])->middleware('auth.throttle:tfa');
    Route::post('/tfa/send-login-link', [LoginLinkController::class, 'startTfa'])->middleware('auth.throttle:tfa_link');
    Route::post('/tfa/verify', [TfaController::class, 'verify'])->middleware('auth.throttle:tfa');

    // Magic login link - enumeration-safe by design (see LoginLinkService::start).
    Route::post('/login-link', [LoginLinkController::class, 'start'])->middleware('auth.throttle:login_link');
    Route::post('/login-link/poll', [LoginLinkController::class, 'poll'])->middleware('auth.throttle:login_link_poll');
    Route::get('/login-link/approve', [LoginLinkController::class, 'approveInfo'])->middleware('auth.throttle:login_link_approve');
    Route::post('/login-link/approve', [LoginLinkController::class, 'respond'])->middleware('auth.throttle:login_link_approve');

    // Passkey login - discoverable credentials, no email needed.
    Route::post('/passkey/login-options', [PasskeyController::class, 'loginOptions']);
    Route::post('/passkey/login-verify', [PasskeyController::class, 'loginVerify']);

    // Google OAuth - GET redirects; state is Socialite's own CSRF protection, not our rate limiter.
    Route::get('/google', [GoogleController::class, 'redirect']);
    Route::get('/google/callback', [GoogleController::class, 'callback']);
});

// --- Signed-in endpoints ---
Route::middleware(['device.uid', 'auth.user'])->prefix('auth')->group(function () {
    Route::post('/change-password', [PasswordController::class, 'changePassword']);
    Route::post('/set-password', [PasswordController::class, 'setPassword']);
    Route::get('/list-accounts', [SessionController::class, 'accounts']);

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
