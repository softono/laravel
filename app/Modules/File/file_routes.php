<?php

use App\Modules\File\Controllers\FileController;
use Illuminate\Support\Facades\Route;

Route::middleware(['device.uid', 'auth.user'])->group(function () {
    Route::get('file', [FileController::class, 'show'])->name('file');
});
