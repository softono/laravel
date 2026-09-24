<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

/* User routes =========================================================================== */

Route::get('cron/schedule/run', function () {
    $artisan = new Artisan;
    $artisan::call('schedule:run');

    return $artisan::output();
})->name('cron/schedule/run');

Route::group(['middleware' => ['web']], function () {
    Route::get('/', '\App\Http\Controllers\FrontController@index')->name('home');
    Route::get('page/{slug}', '\App\Http\Controllers\FrontController@page')->name('page');
    Route::get('contact', '\App\Http\Controllers\FrontController@contact')->name('contact');
    Route::post('contact-process', '\App\Http\Controllers\FrontController@contactProcess')->name('contact-process');

    // register, login, logout, password-forgot, verify-account, login-otp,
    // verify (2FA), login-link, passkeys and Google OAuth all live in
    // routes/auth.php. The legacy SiteController/AuthController routes for
    // these are removed here - they called Auth::guard()->login($user) and
    // read columns that don't exist on the current `users` model.
});

/* Admin routes =========================================================================== */
// admin/auth/*, admin/site/password-forgot(-process) removed here - admin
// auth lives in routes/auth.php (Admin\Auth\{LoginController,PageController}).

Route::group(['prefix' => 'admin', 'middleware' => ['web', 'auth.admin']], function () {
    Route::get('dashboard', '\App\Http\Controllers\Admin\SiteController@dashboard')->name('admin/dashboard');
    Route::post('site/get-chart-user', '\App\Http\Controllers\Admin\SiteController@getChartUser')->name('admin/site/get-chart-user');

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
    // user/autologin removed - impersonation called Auth::guard('web')->login($user),
    // which App\Helpers\SessionTokenGuard doesn't implement (no StatefulGuard support).
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
