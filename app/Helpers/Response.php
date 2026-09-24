<?php

namespace App\Helpers;

/**
 * {status: 1|0, message, data} envelope - the shape the whole app speaks,
 * consumed by ajaxSuccess() in public/assets/js/app.js.
 *
 * Mirrors the Next app's src/server/utils/response.ts (same function names,
 * same body): the shape is frozen to status/message/data, and anything extra
 * belongs inside `data`, never on the top level.
 */
class Response
{
    /**
     * @param  array{status?: int, message?: ?string, data?: mixed}  $result
     */
    public static function sendResponse(int $http_status = 200, array $result = [])
    {
        return response()->json([
            'status' => $result['status'] ?? 1,
            'message' => $result['message'] ?? '',
            'data' => $result['data'] ?? [],
        ], $http_status);
    }

    /**
     * Sends a service result (see ApiResult): `http_status` becomes the HTTP code,
     * `status`, `message` and `data` the body.
     *
     * @param  array{http_status?: int, status?: int, message?: string, data?: mixed}  $result
     */
    public static function sendResult(array $result = [])
    {
        return self::sendResponse($result['http_status'] ?? 200, $result);
    }

    public static function sendMessage(string $message = 'Ok', int $status = 1, array $data = [])
    {
        return self::sendResponse(200, ['status' => $status, 'message' => $message, 'data' => $data]);
    }

    public static function sendError(int $http_status = 500, string $message = 'Internal Server Error')
    {
        return self::sendResponse($http_status, ['status' => 0, 'message' => $message, 'data' => []]);
    }

    /**
     * Same as sendResult() but also sets response headers (Next passes a Headers object).
     *
     * @param  array<string, mixed>  $result
     * @param  array<string, string>  $headers
     */
    public static function sendResultWithHeaders(array $result, array $headers = [])
    {
        return self::sendResult($result)->withHeaders($headers);
    }

    /**
     * Success envelope carrying a payload; the common case of sendResult().
     *
     * @param  array<string, mixed>  $data
     */
    public static function sendData(array $data, string $message = '')
    {
        return self::sendResponse(200, ['status' => 1, 'message' => $message, 'data' => $data]);
    }
}
