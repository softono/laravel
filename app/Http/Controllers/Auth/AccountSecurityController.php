<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * New-stack account security pages. Deliberately a different URL
 * (/account/two-factor) than the legacy /account/tfa route, which still
 * serves the old TfaService/User model until Phase 7's cutover.
 */
class AccountSecurityController extends Controller
{
    public function twoFactor(Request $request)
    {
        return view('account.two-factor');
    }

    public function passkeys(Request $request)
    {
        return view('account.passkeys');
    }
}
