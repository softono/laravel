<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Account security pages.
 */
class AccountSecurityController extends Controller
{
    public function twoFactor(Request $request)
    {
        return view('account.two-factor', ['model' => auth()->user()]);
    }

    public function passkeys(Request $request)
    {
        return view('account.passkeys', ['model' => auth()->user()]);
    }
}
