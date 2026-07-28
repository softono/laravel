<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;

/**
 * {status: 1|0, message, data} envelope - the same shape the existing
 * app already uses (see app/Http/Controllers/AuthController.php's
 * `response()->json((new AuthService())->loginProcess(...))` and
 * public/assets/js/app.js's ajaxSuccess).
 *
 * IMPORTANT: the HTTP status is always 200. public/assets/js/app.js's
 * ajaxRequest() uses $.ajax, whose `success` callback (and therefore the
 * caller's own cb passed to app.ajaxForm/app.ajaxPost) only fires on a
 * 2xx response - anything else routes to the generic, bodyless
 * `error: this.ajaxError` handler instead, discarding response.data
 * entirely. Since our inline page scripts need response.data.next
 * (verify-account / tfa redirects, etc.) on the FAILURE path too, every
 * envelope response must be 200 so it reaches `success`/`cb`; the
 * `status` field inside the body is what actually carries success/failure.
 */
class ApiResult
{
    public function __construct(
        public bool $ok,
        public ?string $message = null,
        public array $data = [],
        public int $httpStatus = 200,
    ) {}

    public static function success(?string $message = null, array $data = [], int $httpStatus = 200): self
    {
        return new self(true, $message, $data, $httpStatus);
    }

    public static function failure(string $message, array $data = [], int $httpStatus = 200): self
    {
        return new self(false, $message, $data, $httpStatus);
    }

    public function withCookies(array $cookies): JsonResponse
    {
        $response = $this->toResponse();
        foreach ($cookies as $cookie) {
            $response->withCookie($cookie);
        }

        return $response;
    }

    public function toResponse(): JsonResponse
    {
        return response()->json([
            'status' => $this->ok ? 1 : 0,
            'message' => $this->message,
            'data' => (object) $this->data,
        ], $this->httpStatus);
    }
}
