<?php

namespace App\Modules\User\Controllers;

use App\Helpers\Response;
use App\Helpers\SignedCookie;
use App\Http\Controllers\Controller;
use App\Modules\User\Requests\ImageRequest;
use App\Modules\User\Requests\UpdateProfileRequest;
use App\Modules\User\Services\ActivityListService;
use App\Modules\User\Services\ProfileService;
use App\Modules\User\Services\SessionListService;
use Illuminate\Http\Request;

/**
 * The signed-in user's own account area. Password, 2FA and passkey changes
 * are handled by the Auth module's endpoints (auth/change-password, auth/2fa/*,
 * auth/passkey/*); these pages only host their forms.
 */
class AccountController extends Controller
{
    public function __construct(
        protected ProfileService $profile,
        protected SessionListService $sessionList,
        protected ActivityListService $activityList,
    ) {
        parent::__construct();
    }

    public function update()
    {
        return view('modules.user.account.update', [
            'model' => auth()->user(),
            'countries' => config('countries'),
            'timezones' => \DateTimeZone::listIdentifiers(\DateTimeZone::ALL_WITH_BC),
        ]);
    }

    public function updateProcess(UpdateProfileRequest $request)
    {
        return Response::sendResult($this->profile->update($request, auth()->user(), $request->validated()));
    }

    public function passwordChange()
    {
        return view('modules.user.account.change_password', ['model' => auth()->user()]);
    }

    public function image()
    {
        return view('modules.user.account.component.image', ['model' => auth()->user()]);
    }

    public function imageSave(ImageRequest $request)
    {
        return Response::sendResult($this->profile->saveImage($request, auth()->user(), $request->file('image')));
    }

    public function deleteImage()
    {
        return Response::sendResult($this->profile->deleteImage(auth()->user()));
    }

    public function twoFactor()
    {
        return view('modules.user.account.two-factor', ['model' => auth()->user()]);
    }

    public function passkeys()
    {
        return view('modules.user.account.passkeys', ['model' => auth()->user()]);
    }

    public function session()
    {
        return view('modules.user.account.session', ['model' => auth()->user()]);
    }

    public function sessionList(Request $request)
    {
        return response()->json($this->sessionList->forUser($request, auth()->user(), $request->all()));
    }

    public function sessionLogout(Request $request)
    {
        $request->validate(['id' => ['required', 'string']]);

        $result = $this->sessionList->logout($request, auth()->user(), $request->string('id'));

        if ($result['ok']) {
            $result['data'] = ['next' => 'table_refresh'];
        }

        return Response::sendResult($result);
    }

    public function userActivity()
    {
        return view('modules.user.account.user_activity', ['model' => auth()->user()]);
    }

    public function userActivityList(Request $request)
    {
        return response()->json($this->activityList->forUser(auth()->user(), $request->all()));
    }

    public function deactivate(Request $request)
    {
        $this->profile->deactivate($request, auth()->user());

        SignedCookie::forget('session_token');

        return redirect()->route('login');
    }
}
