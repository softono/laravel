<?php

use App\Modules\Blog\Controllers\BlogController;
use Illuminate\Support\Facades\Route;

Route::get('blog', [BlogController::class, 'index'])->name('blog');
Route::get('blog/{slug}', [BlogController::class, 'show'])->name('blog/show');
