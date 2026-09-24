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
     * Maps a service result to the envelope. Accepts both shapes in use:
     *  - Next-style `['status' => 1|0, 'message', 'data', 'http_status']`
     *  - this app's service style `['ok' => bool, 'message', ...extra]`, where
     *    `ok` becomes `status` and every extra key is merged into `data`.
     *
     * @param  array<string, mixed>  $result
     */
    public static function sendResult(array $result = [])
    {
        return self::sendResponse($result['http_status'] ?? 200, self::toEnvelope($result));
    }

    public static function sendMessage(string $message = 'Ok', int $status = 1)
    {
        return self::sendResponse(200, ['status' => $status, 'message' => $message, 'data' => []]);
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

    /**
     * @param  array<string, mixed>  $result
     * @return array{status: int, message: string, data: mixed}
     */
    protected static function toEnvelope(array $result): array
    {
        $status = $result['status'] ?? (array_key_exists('ok', $result) ? (int) (bool) $result['ok'] : 1);

        $extra = array_diff_key($result, array_flip(['ok', 'status', 'message', 'data', 'http_status']));
        $data = $result['data'] ?? [];
        if ($extra !== []) {
            $data = array_merge(is_array($data) ? $data : [], $extra);
        }

        return ['status' => (int) $status, 'message' => (string) ($result['message'] ?? ''), 'data' => $data];
    }
}
