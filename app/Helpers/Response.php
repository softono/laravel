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

    public static function sendResult(array $result = [])
    {
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
}