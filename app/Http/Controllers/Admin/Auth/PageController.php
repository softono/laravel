<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Admin\Controller;
use Illuminate\Http\Request;

/**
 * Renders the admin auth pages. All POST to the SAME shared /api/auth/*
 * endpoints as the user side (forgot/reset/verify-account/tfa are not
 * admin-specific - a `users` row is a `users` row regardless of role) -
 * only the admin LOGIN endpoint differs (Admin\Auth\LoginController),
 * because that's the one place requireAdmin:true matters.
 */
class PageController extends Controller
{
    public function showForgot()
    {
        return view('admin.auth.password-forgot');
    }

    public function showReset()
    {
        return view('admin.auth.reset-password');
    }

    public function showVerify()
    {
        return view('admin.auth.verify-tfa');
    }

    public function showVerifyAccount(Request $request)
    {
        $email = $request->query('code') ? base64_decode($request->query('code')) : null;

        return view('admin.auth.verify-account', ['email' => $email]);
    }
}
