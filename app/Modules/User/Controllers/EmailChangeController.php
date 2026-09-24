<?php

namespace App\Modules\User\Controllers;

use App\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Modules\User\Requests\ResendEmailChangeRequest;
use App\Modules\User\Requests\StartEmailChangeRequest;
use App\Modules\User\Requests\VerifyEmailChangeRequest;
use App\Modules\User\Requests\VerifyNewEmailRequest;
use App\Modules\User\Services\EmailChangeService;

/** JSON endpoints behind the "change email" card; shared by the user and admin account areas. */
class EmailChangeController extends Controller
{
    public function __construct(protected EmailChangeService $emailChange)
    {
        parent::__construct();
    }

    public function start(StartEmailChangeRequest $request)
    {
        return Response::sendResult($this->emailChange->start(
            auth()->user(),
            (string) $request->input('password'),
            (string) $request->input('email'),
        ));
    }

    public function resend(ResendEmailChangeRequest $request)
    {
        return Response::sendResult($this->emailChange->resend(auth()->user(), (string) $request->input('handle')));
    }

    public function verifyNew(VerifyNewEmailRequest $request)
    {
        return Response::sendResult($this->emailChange->verifyNewEmail(
            auth()->user(),
            (string) $request->input('handle'),
            (string) $request->input('code'),
        ));
    }

    public function sendOtp()
    {
        $this->emailChange->sendCurrentEmailOtp(auth()->user());

        return Response::sendMessage('Code sent to your current email');
    }

    public function verify(VerifyEmailChangeRequest $request)
    {
        return Response::sendResult($this->emailChange->verify(
            $request,
            auth()->user(),
            (string) $request->input('handle'),
            (string) $request->input('method'),
            (string) $request->input('code'),
        ));
    }
}
