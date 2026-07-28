<?php

use App\Models\Auth\User;

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'web'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
        'web' => [
            // Next-parity session cookie, not Laravel's own session-based
            // auth. Registered by App\Providers\AppServiceProvider::boot().
            'driver' => 'session_token',
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env('AUTH_MODEL', User::class),
        ],

        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

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
