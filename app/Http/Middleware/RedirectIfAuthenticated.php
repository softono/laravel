<?php

namespace App\Http\Middleware;

use App\Helpers\SignedCookie;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

/**
 * /login, /register, / redirect to /dashboard when already signed in.
 * Does a real (cached) validate() rather than just checking cookie
 * presence, and clears the cookie if it's invalid - otherwise a stale
 * cookie would bounce the user between /login and /dashboard in a loop.
 */
class RedirectIfAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::check()) {
            return redirect('/dashboard');
        }

        if ($request->cookie(SignedCookie::name('session_token'))) {
            Cookie::queue(Cookie::forget(SignedCookie::name('session_token')));
        }

        return $next($request);
    }
}
