<?php

use App\Http\Controllers\Account\AccountSecurityController;
use Illuminate\Support\Facades\Route;

// Temporary home of the account pages that move to the User module in Phase 4.

Route::middleware(['device.uid', 'auth.user'])->group(function () {
    Route::get('/account/two-factor', [AccountSecurityController::class, 'twoFactor'])->name('account/two-factor');
    Route::get('/account/passkeys', [AccountSecurityController::class, 'passkeys'])->name('account/passkeys');
});
