<?php

namespace App\Http\Middleware;

use App\Helpers\General;
use App\Helpers\Response;
use Closure;
use Illuminate\Http\Request;

/**
 * Rejects the request unless the Google reCAPTCHA token verifies. A no-op
 * while the captcha is switched off in settings (see General::recaptchaFails).
 */
class VerifyRecaptcha
{
    public function __construct(protected General $general) {}

    public function handle(Request $request, Closure $next)
    {
        if ($this->general->recaptchaFails()) {
            return Response::sendResponse(200, [
                'status' => 0,
                'message' => 'Please complete the captcha verification',
                'data' => ['requires_captcha' => true],
            ]);
        }

        return $next($request);
    }
}
