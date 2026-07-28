<?php

namespace App\Http\Middleware;

use App\Helpers\ApiResult;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Replaces app/Http/Middleware/AdminAuth.php for the new auth stack. Same
 * session cookie/guard as the user side (single `users` table, both
 * distinguished only by `role` - see AuthService::authenticate's
 * requireAdmin flag), plus the isAdmin() check.
 */
class AuthenticateAdminSession
{
    public function handle(Request $request, Closure $next)
    {
        $isJson = $request->expectsJson() || $request->ajax();

        if (Auth::guest()) {
            if ($isJson) {
                return ApiResult::failure('Authentication required')->toResponse();
            }

            return redirect('/admin/auth/login?redirect='.urlencode($request->path()));
        }

        $user = Auth::user();

        if (! $user->isAdmin()) {
            if ($isJson) {
                return ApiResult::failure('You are not authorized')->toResponse();
            }

            return redirect('/admin/auth/login');
        }

        if (! $user->hasPermission()) {
            if ($isJson) {
                return ApiResult::failure('You are not authorized')->toResponse();
            }

            return redirect('/admin/dashboard')->with('error', 'You are not authorized');
        }

        return $next($request);
    }
}
