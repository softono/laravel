<?php

use App\Modules\Admin\Device\Controllers\DeviceController;
use Illuminate\Support\Facades\Route;

// Admin prefix and auth.admin are applied by the loader in bootstrap/app.php.
Route::prefix('device')->group(function () {
    Route::get('/', [DeviceController::class, 'index'])->name('admin/device');
    Route::post('list', [DeviceController::class, 'list'])->name('admin/device/list');
    Route::post('logout', [DeviceController::class, 'logout'])->name('admin/device/logout');
});
