<?php

return [
    'app_uid' => env('APP_UID', 'laravel'),
    'app_name' => 'Laravel',
    'app_favicon' => 'favicon.png',
    'app_logo' => 'logo.png',

    'date_format' => 'Y-m-d',
    'date_time_format' => 'Y-m-d h:i A',
    'token_expire_time' => 600,
    'device_expire_days' => 90,
    'login_max_attempt' => env('LOGIN_MAX_ATTEMPT', 5),
    'login_ban_time' => env('LOGIN_BAN_TIME', 60),

    'login_redirect_url' => 'dashboard',
    'admin_login_redirect_url' => 'admin/dashboard',

    'timezone_enabled' => true,
    'user_email_verify' => true,
    'google_recaptcha' => true,
    'google_recaptcha_secret_key' => '',
    'google_recaptcha_public_key' => '',
    'save_user_device' => 1,
    'show_user_device' => 1,
    'save_user_log' => 1,
    'show_user_log' => 1,
    'cookie_consent' => 1,
    'dark_mode' => 1,
    'breadcrumb' => 1,
];
