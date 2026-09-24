<?php

use App\Modules\Site\Controllers\HomeController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Lets an external cron hit the scheduler on hosts without a crontab entry.
Route::get('cron/schedule/run', function () {
    Artisan::call('schedule:run');

    return Artisan::output();
})->name('cron/schedule/run');
