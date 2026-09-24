<?php

use App\Modules\Contact\Controllers\ContactController;
use Illuminate\Support\Facades\Route;

Route::get('contact', [ContactController::class, 'show'])->name('contact');
Route::post('contact-process', [ContactController::class, 'submit'])->middleware('throttle:5,15')->name('contact-process');
