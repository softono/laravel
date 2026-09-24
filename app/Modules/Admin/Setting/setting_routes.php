<?php

use App\Modules\Admin\Setting\Controllers\SettingController;
use Illuminate\Support\Facades\Route;

// Admin prefix and auth.admin are applied by the loader in bootstrap/app.php.
Route::prefix('setting')->group(function () {
    Route::get('update', [SettingController::class, 'update'])->name('admin/setting/update');
    Route::post('save', [SettingController::class, 'save'])->name('admin/setting/save');
    Route::post('save-logo', [SettingController::class, 'saveLogo'])->name('admin/setting/save-logo');
    Route::post('cache-clear', [SettingController::class, 'cacheClear'])->name('admin/setting/cache-clear');
    Route::post('mail-process', [SettingController::class, 'mailProcess'])->name('admin/setting/mail-process');
});
