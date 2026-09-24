<?php

use App\Modules\Admin\Dashboard\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Admin prefix and auth.admin are applied by the loader in bootstrap/app.php.
Route::prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('admin/dashboard');
    Route::post('chart-user', [DashboardController::class, 'chartUser'])->name('admin/dashboard/chart-user');
});
