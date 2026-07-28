<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\ApiResult;
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
        return ApiResult::success(null, $this->passkeys->loginOptions())->toResponse();
    }

    public function loginVerify(Request $request)
    {
        $request->validate(['credential' => ['required', 'array']]);

        $result = $this->passkeys->loginVerify($request, $request->input('credential'));

        return ($result['ok'] ? ApiResult::success($result['message']) : ApiResult::failure($result['message']))->toResponse();
    }

    // --- Authenticated (account management) ---

    public function index(Request $request)
    {
        return ApiResult::success(null, ['passkeys' => $this->passkeys->list($request->user())])->toResponse();
    }

    public function registerOptions(Request $request)
    {
        return ApiResult::success(null, $this->passkeys->registerOptions($request->user()))->toResponse();
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

        return ($result['ok'] ? ApiResult::success($result['message']) : ApiResult::failure($result['message']))->toResponse();
    }

    public function destroy(Request $request)
    {
        $request->validate(['id' => ['required', 'string']]);

        $result = $this->passkeys->delete($request, $request->user(), $request->string('id'));

        return ($result['ok'] ? ApiResult::success($result['message']) : ApiResult::failure($result['message']))->toResponse();
    }
}
