<?php

namespace App\Helpers;

/**
 * The value every service method returns, the same shape as Next's `ApiResult`:
 *
 *   ['http_status' => 200, 'status' => 1, 'message' => '', 'data' => [...]]
 *
 * Controllers hand it to Response::sendResult(), which sends `http_status`
 * as the HTTP code and `status`/`message`/`data` as the body. Failures the
 * page handles itself keep HTTP 200 (jQuery only runs the callback for 2xx);
 * pass another code for the real 4xx cases.
 */
class ApiResult
{
    /**
     * @param  array<string, mixed>  $data
     * @return array{http_status: int, status: int, message: string, data: array<string, mixed>}
     */
    public static function success(string $message = '', array $data = [], int $http_status = 200): array
    {
        return ['http_status' => $http_status, 'status' => 1, 'message' => $message, 'data' => $data];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{http_status: int, status: int, message: string, data: array<string, mixed>}
     */
    public static function failure(string $message, array $data = [], int $http_status = 200): array
    {
        return ['http_status' => $http_status, 'status' => 0, 'message' => $message, 'data' => $data];
    }
}
