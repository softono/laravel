<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class UserAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        if (Auth::guest()) {
            if ($request->ajax()) {
                return Response::make('unauthorized');
            } else {
                session('auth_redirect_url', url()->full());

                return redirect('login')->with('error', 'You are not authorized');
            }
        } else {
            $user = Auth::user();
            if (! $user->isUser()) {
                if ($request->ajax()) {
                    return Response::make('unauthorized');
                } else {
                    return redirect('/')->with('error', 'You are not authorized');
                }
            }
            if (session('verify_tfa')) {
                return redirect('auth/verify');
            }
        }

        return $next($request);
    }
}
