<?php

namespace App\Http\Middleware;

use App\Helpers\SignedCookie;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

/**
 * Port of the Next app's proxy.ts rule: /login, /register, / redirect to
 * /dashboard when already signed in. Deliberate refinement over Next:
 * proxy.ts only checks cookie PRESENCE, which bounces a user with a stale
 * cookie between /login and /dashboard in a loop. This does a real
 * (cached) validate() and clears the cookie if it's invalid.
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
