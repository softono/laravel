<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Services\Auth\PasskeyService;
use Illuminate\Http\Request;

class PasskeyController extends Controller
{
    public function __construct(protected PasskeyService $passkeys)
    {
        parent::__construct();
    }

    // --- Public (login) ---

    public function loginOptions(Request $request)
    {
        return Response::success(null, $this->passkeys->loginOptions());
    }

    public function loginVerify(Request $request)
    {
        $request->validate(['credential' => ['required', 'array']]);

        $result = $this->passkeys->loginVerify($request, $request->input('credential'));

        return Response::sendResult($result);
    }

    // --- Authenticated (account management) ---

    public function index(Request $request)
    {
        return Response::sendResult($this->passkeys->list($request->user()));
    }

    public function registerOptions(Request $request)
    {
        return Response::sendResult($this->passkeys->registerOptions($request->user()));
    }

    public function registerVerify(Request $request)
    {
        $request->validate(['credential' => ['required', 'array']]);

        $result = $this->passkeys->registerVerify(
            $request,
            $request->user(),
            $request->input('credential'),
            $request->input('name'),
        );

        return Response::sendResult($result);
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => ['required', 'string']]);

        $result = $this->passkeys->delete($request, $request->user(), $request->string('id'));

        return Response::sendResult($result);
    }
}
