<?php

namespace App\Http\Middleware;

use App\Helpers\SignedCookie;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

/**
 * Admin mirror of RedirectIfAuthenticated: /admin/auth/* redirects to
 * /admin/dashboard when already signed in as an admin. A non-admin user
 * hitting an admin guest page is left alone rather than redirected -
 * they're not "logged in as an admin", so there's nowhere sensible to
 * send them.
 */
class RedirectIfAdminAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check() && Auth::user()->isAdmin()) {
            return redirect('/admin/dashboard');
        }

        if (Auth::guest() && $request->cookie(SignedCookie::name('session_token'))) {
            Cookie::queue(Cookie::forget(SignedCookie::name('session_token')));
        }

        return $next($request);
    }
}
