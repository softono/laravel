<?php

namespace App\Helpers;

/**
 * {status: 1|0, message, data} envelope - the shape the whole app speaks,
 * consumed by ajaxSuccess() in public/assets/js/app.js.
 */
class Response
{
    public static function sendResponse(int $http_status, array $data = [])
    {
        return response()->json($data, $http_status);
    }

    /**
     * Maps a service result array to the {status, message, data} envelope.
     * Services return `['ok' => bool, 'message' => ?string, ...extra]`
     * (see AGENTS.md's "Service returns a result array" pattern) - `ok`
     * becomes `status` (1/0), and any extra keys (including an existing
     * `data` key, e.g. TfaService::enable()) are merged into `data` so
     * callers don't need their own per-field mapping.
     */
    public static function sendResult(array $result = [])
    {
        if (array_key_exists('ok', $result)) {
            $data = array_merge(
                $result['data'] ?? [],
                array_diff_key($result, array_flip(['ok', 'message', 'data', 'http_status']))
            );

            return Response::sendResponse($result['http_status'] ?? 200, [
                'status' => $result['ok'] ? 1 : 0,
                'message' => $result['message'] ?? null,
                'data' => $data,
            ]);
        }

        return Response::sendResponse($result['http_status'] ?? 200, $result);
    }

    public static function sendMessage(string $message = '', int $status = 1)
    {
        return Response::sendResponse(200, ['status' => $status, 'message' => $message, 'data' => []]);
    }

    public static function sendError(int $http_status = 500, string $message = 'Internal Server Error')
    {
        return Response::sendResponse($http_status, ['status' => 0, 'message' => $message, 'data' => []]);
    }

    public static function sendData(array $data, string $message = '')
    {
        return Response::sendResponse(200, ['status' => 1, 'message' => $message, 'data' => $data]);
    }

    public static function success(?string $message = null, array $data = [])
    {
        return Response::sendResponse(200, ['status' => 1, 'message' => $message, 'data' => $data]);
    }
}
