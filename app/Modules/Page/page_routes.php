<?php

use App\Modules\Page\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('page/{slug}', [PageController::class, 'show'])->name('page');
