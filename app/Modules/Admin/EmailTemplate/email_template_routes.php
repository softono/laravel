<?php

use App\Modules\Admin\EmailTemplate\Controllers\EmailTemplateController;
use Illuminate\Support\Facades\Route;

// Admin prefix and auth.admin are applied by the loader in bootstrap/app.php.
Route::prefix('email-template')->group(function () {
    Route::get('/', [EmailTemplateController::class, 'index'])->name('admin/email-template');
    Route::post('list', [EmailTemplateController::class, 'list'])->name('admin/email-template/list');
    Route::get('update', [EmailTemplateController::class, 'update'])->name('admin/email-template/update');
    Route::get('view', [EmailTemplateController::class, 'view'])->name('admin/email-template/view');
    Route::post('save', [EmailTemplateController::class, 'save'])->name('admin/email-template/save');
    Route::post('save-image', [EmailTemplateController::class, 'saveImage'])->name('admin/email-template/save-image');
});
