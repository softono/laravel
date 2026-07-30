<?php

namespace App\Http\Middleware;

use App\Helpers\Response;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Replaces app/Http/Middleware/UserAuth.php for the new auth stack.
 * The 'web' guard already resolves the user from the session cookie
 * (App\Helpers\SessionTokenGuard); this middleware just enforces that a
 * user actually resolved, redirecting/erroring otherwise.
 */
class AuthenticateSession
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guest()) {
            if ($request->expectsJson() || $request->ajax()) {
                return Response::sendError(401,'Authentication required');
            }

            return redirect('/login?redirect='.urlencode($request->path()));
        }

        return $next($request);
    }
}