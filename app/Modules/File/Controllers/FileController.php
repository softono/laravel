<?php

namespace App\Modules\File\Controllers;

use App\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Modules\File\Services\PrivateFileService;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function __construct(protected PrivateFileService $files)
    {
        parent::__construct();
    }

    /** GET /file?p=<base64 path> - a private file, for signed-in users only. */
    public function show(Request $request)
    {
        $encoded = (string) $request->query('p', '');

        if ($encoded === '') {
            return Response::sendError(400, 'Missing file parameter');
        }

        $result = $this->files->resolve($encoded);

        if (! $result['status']) {
            return Response::sendResult($result);
        }

        return response()->file($result['data']['path'], [
            'Cache-Control' => 'private, no-store',
            // Whatever was uploaded must never run as a page on this origin.
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ]);
    }
}
