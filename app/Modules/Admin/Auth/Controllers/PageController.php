<?php

namespace App\Modules\Admin\Auth\Controllers;

use App\Modules\Admin\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Renders the admin auth pages. All POST to the SAME shared /auth/*
 * endpoints as the user side (forgot/reset/verify-account/tfa are not
 * admin-specific - a `users` row is a `users` row regardless of role) -
 * only the admin LOGIN endpoint differs (Admin\Auth\LoginController),
 * because that's the one place requireAdmin:true matters.
 */
class PageController extends Controller
{
    public function showForgot()
    {
        return view('modules.admin.auth.password-forgot');
    }

    public function showReset()
    {
        return view('modules.admin.auth.reset-password');
    }

    public function showVerify()
    {
        return view('modules.admin.auth.verify-tfa');
    }

    public function showVerifyAccount(Request $request)
    {
        $email = $request->query('code') ? base64_decode($request->query('code')) : null;

        return view('modules.admin.auth.verify-account', ['email' => $email]);
    }
}
