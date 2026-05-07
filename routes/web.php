<?php

use Illuminate\Support\Facades\Route;

/* User routes =========================================================================== */

Route::get('cron/schedule/run', function () {
    $artisan = new \Illuminate\Support\Facades\Artisan();
    $artisan::call("schedule:run");
    return $artisan::output();
})->name('cron/schedule/run');

Route::group(['middleware' => ['web']], function () {
    Route::get('/', '\App\Http\Controllers\FrontController@index')->name('home');
    Route::get('page/{slug}', '\App\Http\Controllers\FrontController@page')->name('page');
    Route::get('contact', '\App\Http\Controllers\FrontController@contact')->name('contact');
    Route::post('contact-process', '\App\Http\Controllers\FrontController@contactProcess')->name('contact-process');

    Route::get('register', '\App\Http\Controllers\SiteController@register')->name('register');
    Route::post('site/register-process', '\App\Http\Controllers\SiteController@registerProcess')->name('site/register-process');
    Route::get('site/verify-account', '\App\Http\Controllers\SiteController@verifyAccount')->name('site/verify-account');
    Route::post('site/verify-account-process', '\App\Http\Controllers\SiteController@verifyAccountProcess')->name('site/verify-account-process');
    Route::get('site/password-forgot', '\App\Http\Controllers\SiteController@passwordForgot')->name('site/password-forgot');
    Route::post('site/password-forgot-process', '\App\Http\Controllers\SiteController@passwordForgotProcess')->name('site/password-forgot-process');

    Route::get('login', '\App\Http\Controllers\AuthController@login')->name('login');
    Route::post('auth/login-process', '\App\Http\Controllers\AuthController@loginProcess')->name('auth/login-process');
    Route::get('logout', '\App\Http\Controllers\AuthController@logout')->name('logout');
    Route::get('auth/login-otp', '\App\Http\Controllers\AuthController@loginOtp')->name('auth/login-otp');
    Route::post('auth/login-otp-process', '\App\Http\Controllers\AuthController@loginOtpProcess')->name('auth/login-otp-process');
    Route::get('auth/verify', '\App\Http\Controllers\AuthController@verify')->name('auth/verify');
    Route::post('auth/verify-process', '\App\Http\Controllers\AuthController@verifyProcess')->name('auth/verify-process');
    Route::post('auth/resend-otp', '\App\Http\Controllers\AuthController@resendOTP')->name('auth/resend-otp');

    Route::get('oauth/login/{type}', '\App\Http\Controllers\AuthController@socialLogin')->name('oauth/login');
    Route::get('oauth/callback/{type}', '\App\Http\Controllers\AuthController@socialLoginCallback')->name('oauth/callback');
});


Route::group(['middleware' => ['web', 'user']], function () {
    Route::get('dashboard', '\App\Http\Controllers\SiteController@dashboard')->name('dashboard');
    Route::get('/get-qr-modal', '\App\Http\Controllers\AuthController@getTotpModel')->name('get-qr-modal');
    Route::get('otp.verify', '\App\Http\Controllers\AuthController@verifyOtpModal')->name('otp.verify');
    Route::post('otp.confirm', '\App\Http\Controllers\AuthController@optVerifyProcess')->name('otp.confirm');
    Route::get('backup-code', '\App\Http\Controllers\AuthController@backupCode')->name('backup-code');
    Route::post('backup-codes-regenerate', '\App\Http\Controllers\AuthController@regenerateBackupProcess')->name('backup-codes-regenerate');
    Route::get('/copy-secret-key', '\App\Http\Controllers\AuthController@getTotpModel')->name('copy.secret.key');
    Route::post('/remove-totp', '\App\Http\Controllers\AuthController@removeTotp')->name('remove-totp');

    Route::get('account/update', '\App\Http\Controllers\AccountController@update')->name('account/update');
    Route::post('account/update-process', '\App\Http\Controllers\AccountController@updateProcess')->name('account/update-process');
    Route::get('account/image', '\App\Http\Controllers\AccountController@image')->name('account/image');
    Route::post('account/image-save', '\App\Http\Controllers\AccountController@imageSave')->name('account/image-save');
    Route::post('account/image-delete', '\App\Http\Controllers\AccountController@imageDelete')->name('account/image-delete');
    Route::get('account/password-change', '\App\Http\Controllers\AccountController@passwordChange')->name('account/password-change');
    Route::post('account/password-change-process', '\App\Http\Controllers\AccountController@passwordChangeProcess')->name('account/password-change-process');
    Route::get('account/tfa', '\App\Http\Controllers\AccountController@tfa')->name('account/tfa');
    Route::post('account/tfa-status-change', '\App\Http\Controllers\AccountController@tfaStatusChange')->name('account/tfa-status-change');
    Route::post('account/revoke-all', '\App\Http\Controllers\AccountController@revokeAll')->name('account/revoke-all');

    Route::get('account/device', '\App\Http\Controllers\AccountController@device')->name('account/device');
    Route::post('account/device-list', '\App\Http\Controllers\AccountController@deviceList')->name('account/device-list');
    Route::post('account/device-logout', '\App\Http\Controllers\AccountController@deviceLogout')->name('account/device-logout');

    Route::get('account/user-activity', '\App\Http\Controllers\AccountController@userActivity')->name('account/user-activity');
    Route::post('account/user-activity-list', '\App\Http\Controllers\AccountController@userActivityList')->name('account/user-activity-list');
    Route::post('account/deactivate', '\App\Http\Controllers\Admin\AccountController@accountDeactivate')->name('account/deactivate');
});


/* Admin routes =========================================================================== */
Route::group(['prefix' => 'admin', 'middleware' => 'web'], function () {
    Route::get('auth/login', '\App\Http\Controllers\Admin\AuthController@login')->name('admin/auth/login');
    Route::post('auth/login-process', '\App\Http\Controllers\Admin\AuthController@loginProcess')->name('admin/auth/login-process');
    Route::get('auth/logout', '\App\Http\Controllers\Admin\AuthController@logout')->name('admin/auth/logout');

    Route::get('auth/verify', '\App\Http\Controllers\Admin\AuthController@verify')->name('admin/auth/verify');
    Route::post('auth/verify-process', '\App\Http\Controllers\Admin\AuthController@verifyProcess')->name('admin/auth/verify-process');
    Route::post('auth/resend-otp', '\App\Http\Controllers\Admin\AuthController@resendOTP')->name('admin/auth/resend-otp');

    Route::get('site/password-forgot', '\App\Http\Controllers\Admin\SiteController@passwordForgot')->name('admin/site/password-forgot');
    Route::post('site/password-forgot-process', '\App\Http\Controllers\Admin\SiteController@passwordForgotProcess')->name('admin/site/password-forgot-process');
});

Route::group(['prefix' => 'admin', 'middleware' => ['web', 'admin']], function () {
    Route::get('dashboard', '\App\Http\Controllers\Admin\SiteController@dashboard')->name('admin/dashboard');
    Route::post('site/get-chart-user', '\App\Http\Controllers\Admin\SiteController@getChartUser')->name('admin/site/get-chart-user');

    Route::get('account/tfa', '\App\Http\Controllers\Admin\AccountController@tfa')->name('admin/account/tfa');
    Route::post('account/tfa-status-change', '\App\Http\Controllers\Admin\AccountController@tfaStatusChange')->name('admin/account/tfa-status-change');
    Route::post('account/revoke-all', '\App\Http\Controllers\Admin\AccountController@revokeAll')->name('admin/account/revoke-all');

    Route::get('account/update', '\App\Http\Controllers\Admin\AccountController@update')->name('admin/account/update');
    Route::post('account/save', '\App\Http\Controllers\Admin\AccountController@save')->name('admin/account/save');
    Route::get('account/image', '\App\Http\Controllers\Admin\AccountController@image')->name('admin/account/image');
    Route::post('account/image-save', '\App\Http\Controllers\Admin\AccountController@imageSave')->name('admin/account/image-save');
    Route::post('account/image-delete', '\App\Http\Controllers\Admin\AccountController@deleteImage')->name('admin/account/image-delete');
    Route::get('account/password-change', '\App\Http\Controllers\Admin\AccountController@passwordChange')->name('admin/account/password-change');
    Route::post('account/password-change-process', '\App\Http\Controllers\Admin\AccountController@changePasswordProcess')->name('admin/account/password-change-process');

    Route::get('account/device', '\App\Http\Controllers\Admin\AccountController@device')->name('admin/account/device');
    Route::any('account/device-list', '\App\Http\Controllers\Admin\AccountController@deviceList')->name('admin/account/device-list');
    Route::any('account/device-logout', '\App\Http\Controllers\Admin\AccountController@deviceLogout')->name('admin/account/device-logout');

    Route::get('account/user-activity', '\App\Http\Controllers\Admin\AccountController@userActivity')->name('admin/account/user-activity');
    Route::any('account/user-activity-list', '\App\Http\Controllers\Admin\AccountController@userActivityList')->name('admin/account/user-activity-list');
    Route::post('account/deactivate', '\App\Http\Controllers\Admin\AccountController@accountDeactivate')->name('admin/account/deactivate');


    Route::get('user', '\App\Http\Controllers\Admin\UserController@index')->name('admin/user');
    Route::any('user/list', '\App\Http\Controllers\Admin\UserController@list')->name('admin/user/list');
    Route::get('user/create', '\App\Http\Controllers\Admin\UserController@create')->name('admin/user/create');
    Route::get('user/update', '\App\Http\Controllers\Admin\UserController@update')->name('admin/user/update');
    Route::post('user/save', '\App\Http\Controllers\Admin\UserController@save')->name('admin/user/save');
    Route::get('user/view', '\App\Http\Controllers\Admin\UserController@view')->name('admin/user/view');
    Route::post('user/mail', '\App\Http\Controllers\Admin\UserController@sendMail')->name('admin/user/mail');
    Route::post('user/delete', '\App\Http\Controllers\Admin\UserController@delete')->name('admin/user/delete');
    Route::post('user/change_status', '\App\Http\Controllers\Admin\UserController@changeStatus')->name('admin/user/change_status');
    Route::get('user/autologin', '\App\Http\Controllers\Admin\UserController@autoLogin')->name('admin/user/autologin');
    Route::get('user/send-tfa-mail', '\App\Http\Controllers\Admin\UserController@sendTfaMail')->name('admin/user/send-tfa-mail');

    Route::get('admin', '\App\Http\Controllers\Admin\AdminController@index')->name('admin/admin');
    Route::post('admin/list', '\App\Http\Controllers\Admin\AdminController@list')->name('admin/admin/list');
    Route::get('admin/create', '\App\Http\Controllers\Admin\AdminController@create')->name('admin/admin/create');
    Route::get('admin/update', '\App\Http\Controllers\Admin\AdminController@update')->name('admin/admin/update');
    Route::post('admin/save', '\App\Http\Controllers\Admin\AdminController@save')->name('admin/admin/save');
    Route::get('admin/view', '\App\Http\Controllers\Admin\AdminController@view')->name('admin/admin/view');
    Route::post('admin/delete', '\App\Http\Controllers\Admin\AdminController@delete')->name('admin/admin/delete');
    Route::post('admin/status-save', '\App\Http\Controllers\Admin\AdminController@statusSave')->name('admin/admin/status-save');

    Route::get('seo/meta', '\App\Http\Controllers\Admin\SeoController@index')->name('admin/seo/meta');
    Route::post('seo/list', '\App\Http\Controllers\Admin\SeoController@list')->name('admin/seo/list');
    Route::get('seo/create', '\App\Http\Controllers\Admin\SeoController@create')->name('admin/seo/create');
    Route::get('seo/update', '\App\Http\Controllers\Admin\SeoController@update')->name('admin/seo/update');
    Route::post('seo/save', '\App\Http\Controllers\Admin\SeoController@save')->name('admin/seo/save');
    Route::post('seo/delete', '\App\Http\Controllers\Admin\SeoController@delete')->name('admin/seo/delete');
    Route::get('seo/sitemap-update', '\App\Http\Controllers\Admin\SeoController@sitemapUpdate')->name('admin/seo/sitemap-update');

    Route::get('pages', '\App\Http\Controllers\Admin\PageController@index')->name('admin/page');
    Route::post('page/list', '\App\Http\Controllers\Admin\PageController@list')->name('admin/page/list');
    Route::get('page/update', '\App\Http\Controllers\Admin\PageController@update')->name('admin/page/update');
    Route::post('page/save', '\App\Http\Controllers\Admin\PageController@save')->name('admin/page/save');
    Route::post('page/save-image', '\App\Http\Controllers\Admin\PageController@saveImage')->name('admin/page/save-image');

    Route::get('setting/update', '\App\Http\Controllers\Admin\SettingController@update')->name('admin/setting/update');
    Route::post('setting/save', '\App\Http\Controllers\Admin\SettingController@save')->name('admin/setting/save');
    Route::post('setting/save-logo', '\App\Http\Controllers\Admin\SettingController@saveLogo')->name('admin/setting/save-logo');
    Route::get('setting/cache-clear', '\App\Http\Controllers\Admin\SettingController@cacheClear')->name('admin/setting/cache-clear');
    Route::post('setting/mail-process', '\App\Http\Controllers\Admin\SettingController@mailProcess')->name('admin/setting/mail-process');

    Route::get('user-activity', '\App\Http\Controllers\Admin\UserActivityController@index')->name('admin/user-activity');
    Route::post('user-activity/list', '\App\Http\Controllers\Admin\UserActivityController@list')->name('admin/user-activity/list');

    Route::get('device', '\App\Http\Controllers\Admin\DeviceController@index')->name('admin/device');
    Route::post('device/list', '\App\Http\Controllers\Admin\DeviceController@list')->name('admin/device/list');
    Route::post('device/logout', '\App\Http\Controllers\Admin\DeviceController@logout')->name('admin/device/logout');

    Route::get('email-template', '\App\Http\Controllers\Admin\EmailTemplateController@index')->name('admin/email-template');
    Route::get('email-template/list', '\App\Http\Controllers\Admin\EmailTemplateController@list')->name('admin/email-template/list');
    Route::get('email-template/update', '\App\Http\Controllers\Admin\EmailTemplateController@update')->name('admin/email-template/update');
    Route::get('email-template/view', '\App\Http\Controllers\Admin\EmailTemplateController@view')->name('admin/email-template/view');
    Route::post('email-template/save', '\App\Http\Controllers\Admin\EmailTemplateController@save')->name('admin/email-template/save');
    Route::post('email-template/save-file', '\App\Http\Controllers\Admin\EmailTemplateController@saveFile')->name('admin/email-template/save-file');
    Route::get('email-template/create', '\App\Http\Controllers\Admin\EmailTemplateController@create')->name('admin/email-template/create');

    // Route::get('/get-qr-modal',' \App\Http\Controllers\QrcodeControlle@getModel')->name('get/qr/modal');

});

