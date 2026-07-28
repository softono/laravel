<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cookie signing key
    |--------------------------------------------------------------------------
    |
    | Deliberately separate from APP_KEY: this key signs the auth cookies
    | (tfa/webauthn/oauth challenge handles) the same way the Next app's
    | ENCRYPTION_KEY does, so cookie signing stays decoupled from Laravel's
    | own cipher/key rotation. Must be >= 32 bytes.
    |
    */
    'encryption_key' => env('ENCRYPTION_KEY'),

    /*
    |--------------------------------------------------------------------------
    | App UID (cookie name prefix)
    |--------------------------------------------------------------------------
    */
    'app_uid' => env('APP_UID', 'app'),

    /*
    |--------------------------------------------------------------------------
    | Session
    |--------------------------------------------------------------------------
    */
    'session_ttl_days' => [
        'remember' => 30,
        'default' => 1,
    ],
    'session_cache_ttl' => 300,   // seconds
    'session_update_age' => 86400, // seconds; sliding refresh threshold

    /*
    |--------------------------------------------------------------------------
    | OTP (email verification / password reset / signin / 2FA email code)
    |--------------------------------------------------------------------------
    */
    'otp_expire_sec' => env('OTP_EXPIRE_SEC', 600),
    'otp_max_attempts' => 5,

    /*
    |--------------------------------------------------------------------------
    | Magic login link
    |--------------------------------------------------------------------------
    */
    'login_link_expire_sec' => env('LOGIN_LINK_EXPIRE_SEC', 300),

    /*
    |--------------------------------------------------------------------------
    | 2FA
    |--------------------------------------------------------------------------
    */
    'tfa_ttl' => 600,
    'tfa_max_attempts' => 5,
    'backup_code_count' => 10,
    // otplib (Next) defaults to window 0 (current 30s step only). We use a
    // deliberately wider window for usability - documented divergence.
    'totp_window' => 1,

    /*
    |--------------------------------------------------------------------------
    | WebAuthn / Passkeys
    |--------------------------------------------------------------------------
    */
    'webauthn_ttl' => 300,

    /*
    |--------------------------------------------------------------------------
    | Trusted devices
    |--------------------------------------------------------------------------
    */
    'trust_days' => 30,
    'device_cookie_days' => 365,

];
