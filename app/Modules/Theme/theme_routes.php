<?php

use App\Modules\Theme\Controllers\ThemeController;
use Illuminate\Support\Facades\Route;

// Public: the picker is on the guest pages too.
Route::middleware('throttle:60,1')->group(function () {
    Route::get('theme', [ThemeController::class, 'index'])->name('theme');
    Route::get('theme/{name}', [ThemeController::class, 'show'])->where('name', '[a-z0-9-]+')->name('theme/show');
});
