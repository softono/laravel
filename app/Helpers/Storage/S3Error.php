<?php

namespace App\Helpers\Storage;

use Illuminate\Http\Response;

/**
 * Real S3 clients/SDKs (including the AWS SDK) parse error bodies as XML
 * regardless of the request's Content-Type - a JSON error body (the shape
 * the rest of this app speaks, see App\Helpers\Response) makes them throw
 * "Unable to parse error information from response" instead of surfacing
 * the actual message. Used only by the S3-compatible API surface
 * (routes/storage_api.php), not the rest of the app.
 */
class S3Error
{
    public static function send(int $httpStatus, string $code, string $message): Response
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            ."\n".'<Error>'
            .'<Code>'.htmlspecialchars($code, ENT_XML1).'</Code>'
            .'<Message>'.htmlspecialchars($message, ENT_XML1).'</Message>'
            .'</Error>';

        return response($xml, $httpStatus, ['Content-Type' => 'application/xml']);
    }
}
