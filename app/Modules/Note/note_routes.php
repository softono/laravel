<?php

use App\Modules\Note\Controllers\NoteController;
use Illuminate\Support\Facades\Route;

Route::middleware(['device.uid', 'auth.user'])->prefix('notes')->group(function () {
    Route::get('/', [NoteController::class, 'index'])->name('notes');
    Route::post('list', [NoteController::class, 'list'])->name('notes/list');
    Route::post('save', [NoteController::class, 'save'])->name('notes/save');
    Route::post('delete', [NoteController::class, 'delete'])->name('notes/delete');
});
