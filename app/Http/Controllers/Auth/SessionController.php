<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResult;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    /** GET /api/auth/session - the currently authenticated user, or a guest result. */
    public function show(Request $request)
    {
        $user = $request->user();

        if (! $user) {
            return ApiResult::failure('Not authenticated')->toResponse();
        }

        return ApiResult::success(null, [
            'id' => $user->id,
            'email' => $user->email,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'role' => $user->role,
            'email_verified' => $user->email_verified,
            'two_factor_enabled' => $user->two_factor_enabled,
        ])->toResponse();
    }
}
