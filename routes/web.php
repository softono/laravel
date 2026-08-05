<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/* User routes =========================================================================== */

Route::get('cron/schedule/run', function () {
    $artisan = new Artisan;
    $artisan::call('schedule:run');

    return $artisan::output();
})->name('cron/schedule/run');

// No public-facing marketing site (removed per docs/local/prd.md - this
// project is REST API + Admin Panel + Storage Engine only). The host root
// ('/') is reserved for the S3-compatible API's "List buckets" endpoint
// (see routes/storage_api.php) - register, login, logout, password-forgot,
// verify-account, login-otp, verify (2FA), login-link, passkeys and Google
// OAuth all live in routes/auth.php.

Route::group(['middleware' => ['web', 'auth.user']], function () {
    Route::get('dashboard', '\App\Http\Controllers\SiteController@dashboard')->name('dashboard');

    // Bucket Admin self-service - see "Bucket Admin Panel" in docs/local/prd.md.
    // These top-level path segments (buckets, objects, api-keys) are reserved -
    // the S3 API (routes/storage_api.php) never sees them, since Laravel
    // matches this 'web' group's static routes first. See docs/local/api.md.
    Route::get('buckets', '\App\Http\Controllers\BucketController@index')->name('buckets');
    Route::post('buckets/list', '\App\Http\Controllers\BucketController@list')->name('buckets/list');
    Route::get('buckets/create', '\App\Http\Controllers\BucketController@create')->name('buckets/create');
    Route::get('buckets/update', '\App\Http\Controllers\BucketController@update')->name('buckets/update');
    Route::post('buckets/save', '\App\Http\Controllers\BucketController@save')->name('buckets/save');
    Route::post('buckets/delete', '\App\Http\Controllers\BucketController@delete')->name('buckets/delete');
    Route::get('buckets/view', '\App\Http\Controllers\BucketController@view')->name('buckets/view');

    Route::get('objects', '\App\Http\Controllers\ObjectBrowserController@index')->name('objects');
    Route::post('objects/list', '\App\Http\Controllers\ObjectBrowserController@list')->name('objects/list');
    Route::post('objects/upload', '\App\Http\Controllers\ObjectBrowserController@upload')->name('objects/upload');
    Route::get('objects/download', '\App\Http\Controllers\ObjectBrowserController@download')->name('objects/download');
    Route::post('objects/delete', '\App\Http\Controllers\ObjectBrowserController@destroy')->name('objects/delete');
    Route::post('objects/copy', '\App\Http\Controllers\ObjectBrowserController@copy')->name('objects/copy');
    Route::get('objects/metadata', '\App\Http\Controllers\ObjectBrowserController@metadata')->name('objects/metadata');

    Route::get('api-keys', '\App\Http\Controllers\ApiKeyController@index')->name('api-keys');
    Route::post('api-keys/create', '\App\Http\Controllers\ApiKeyController@store')->name('api-keys/create');
    Route::post('api-keys/regenerate', '\App\Http\Controllers\ApiKeyController@regenerate')->name('api-keys/regenerate');
    Route::post('api-keys/toggle-status', '\App\Http\Controllers\ApiKeyController@toggleStatus')->name('api-keys/toggle-status');
    Route::post('api-keys/delete', '\App\Http\Controllers\ApiKeyController@destroy')->name('api-keys/delete');

    Route::get('account/update', '\App\Http\Controllers\Account\AccountController@update')->name('account/update');
    Route::post('account/update-process', '\App\Http\Controllers\Account\AccountController@updateProcess')->name('account/update-process');
    Route::get('account/password-change', '\App\Http\Controllers\Account\AccountController@passwordChange')->name('account/password-change');
    Route::post('account/password-change-process', '\App\Http\Controllers\Account\AccountController@passwordChangeProcess')->name('account/password-change-process');
    Route::get('account/image', '\App\Http\Controllers\Account\AccountController@image')->name('account/image');
    Route::post('account/image-save', '\App\Http\Controllers\Account\AccountController@imagesave')->name('account/image-save');
    Route::post('account/delete-image', '\App\Http\Controllers\Account\AccountController@deleteImage')->name('account/delete-image');
    Route::get('account/tfa', '\App\Http\Controllers\Account\AccountController@tfa')->name('account/tfa');
    Route::get('account/passkeys', '\App\Http\Controllers\Account\AccountSecurityController@passkeys')->name('account/passkeys');
    Route::post('account/tfa-status-change', '\App\Http\Controllers\Account\AccountController@tfaStatusChange')->name('account/tfa-status-change');
    Route::post('account/revoke-all', '\App\Http\Controllers\Account\AccountController@revokeAll')->name('account/revoke-all');
    Route::get('account/device', '\App\Http\Controllers\Account\AccountController@device')->name('account/device');
    Route::get('account/session', '\App\Http\Controllers\Account\AccountController@device')->name('account/session');
    Route::post('account/device-list', '\App\Http\Controllers\Account\AccountController@deviceList')->name('account/device-list');
    Route::post('account/device-logout', '\App\Http\Controllers\Account\AccountController@deviceLogout')->name('account/device-logout');
    Route::get('account/user-activity', '\App\Http\Controllers\Account\AccountController@userActivity')->name('account/user-activity');
    Route::post('account/user-activity-list', '\App\Http\Controllers\Account\AccountController@userActivityList')->name('account/user-activity-list');
    Route::post('account/deactivate', '\App\Http\Controllers\Account\AccountController@accountDeactivate')->name('account/deactivate');
});

/* Admin routes =========================================================================== */
// admin/auth/*, admin/site/password-forgot(-process) removed here - admin
// auth lives in routes/auth.php (Admin\Auth\{LoginController,PageController}).

Route::group(['prefix' => 'admin', 'middleware' => ['web', 'auth.admin']], function () {
    Route::get('dashboard', '\App\Http\Controllers\Admin\SiteController@dashboard')->name('admin/dashboard');

    Route::get('account/update', '\App\Http\Controllers\Admin\Account\AccountController@update')->name('admin/account/update');
    Route::post('account/save', '\App\Http\Controllers\Admin\Account\AccountController@save')->name('admin/account/save');
    Route::get('account/password-change', '\App\Http\Controllers\Admin\Account\AccountController@passwordChange')->name('admin/account/password-change');
    Route::post('account/change-password-process', '\App\Http\Controllers\Admin\Account\AccountController@changePasswordProcess')->name('admin/account/change-password-process');
    Route::get('account/image', '\App\Http\Controllers\Admin\Account\AccountController@image')->name('admin/account/image');
    Route::post('account/image-save', '\App\Http\Controllers\Admin\Account\AccountController@imagesave')->name('admin/account/image-save');
    Route::post('account/delete-image', '\App\Http\Controllers\Admin\Account\AccountController@deleteImage')->name('admin/account/delete-image');
    Route::get('account/tfa', '\App\Http\Controllers\Admin\Account\AccountController@tfa')->name('admin/account/tfa');
    Route::post('account/tfa-status-change', '\App\Http\Controllers\Admin\Account\AccountController@tfaStatusChange')->name('admin/account/tfa-status-change');
    Route::post('account/revoke-all', '\App\Http\Controllers\Admin\Account\AccountController@revokeAll')->name('admin/account/revoke-all');
    Route::get('account/device', '\App\Http\Controllers\Admin\Account\AccountController@device')->name('admin/account/device');
    Route::post('account/device-list', '\App\Http\Controllers\Admin\Account\AccountController@deviceList')->name('admin/account/device-list');
    Route::post('account/device-logout', '\App\Http\Controllers\Admin\Account\AccountController@deviceLogout')->name('admin/account/device-logout');
    Route::get('account/user-activity', '\App\Http\Controllers\Admin\Account\AccountController@userActivity')->name('admin/account/user-activity');
    Route::post('account/user-activity-list', '\App\Http\Controllers\Admin\Account\AccountController@userActivityList')->name('admin/account/user-activity-list');
    Route::post('account/deactivate', '\App\Http\Controllers\Admin\Account\AccountController@accountDeactivate')->name('admin/account/deactivate');

    Route::get('user', '\App\Http\Controllers\Admin\UserController@index')->name('admin/user');
    Route::any('user/list', '\App\Http\Controllers\Admin\UserController@list')->name('admin/user/list');
    Route::get('user/create', '\App\Http\Controllers\Admin\UserController@create')->name('admin/user/create');
    Route::get('user/update', '\App\Http\Controllers\Admin\UserController@update')->name('admin/user/update');
    Route::post('user/save', '\App\Http\Controllers\Admin\UserController@save')->name('admin/user/save');
    Route::get('user/view', '\App\Http\Controllers\Admin\UserController@view')->name('admin/user/view');
    Route::post('user/delete', '\App\Http\Controllers\Admin\UserController@delete')->name('admin/user/delete');
    Route::post('user/change_status', '\App\Http\Controllers\Admin\UserController@changeStatus')->name('admin/user/change_status');
    // user/autologin removed - impersonation called Auth::guard('web')->login($user),
    // which App\Helpers\SessionTokenGuard doesn't implement (no StatefulGuard support).
    // user/mail, user/send-tfa-mail removed with the ContactMessages/legacy-TFA-mail frontend cut.

    Route::get('admin', '\App\Http\Controllers\Admin\AdminController@index')->name('admin/admin');
    Route::post('admin/list', '\App\Http\Controllers\Admin\AdminController@list')->name('admin/admin/list');
    Route::get('admin/create', '\App\Http\Controllers\Admin\AdminController@create')->name('admin/admin/create');
    Route::get('admin/update', '\App\Http\Controllers\Admin\AdminController@update')->name('admin/admin/update');
    Route::post('admin/save', '\App\Http\Controllers\Admin\AdminController@save')->name('admin/admin/save');
    Route::get('admin/view', '\App\Http\Controllers\Admin\AdminController@view')->name('admin/admin/view');
    Route::post('admin/delete', '\App\Http\Controllers\Admin\AdminController@delete')->name('admin/admin/delete');
    Route::post('admin/status-save', '\App\Http\Controllers\Admin\AdminController@statusSave')->name('admin/admin/status-save');

    Route::get('bucket', '\App\Http\Controllers\Admin\BucketController@index')->name('admin/bucket');
    Route::post('bucket/list', '\App\Http\Controllers\Admin\BucketController@list')->name('admin/bucket/list');
    Route::get('bucket/view', '\App\Http\Controllers\Admin\BucketController@view')->name('admin/bucket/view');

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
