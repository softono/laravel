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
    /** Layout, route-name prefix and post-deactivation route; the admin subclass overrides them. */
    protected string $layout = 'layouts.main';

    protected string $prefix = '';

    protected string $loginRoute = 'login';

    public function __construct(
        protected ProfileService $profile,
        protected SessionListService $sessionList,
        protected ActivityListService $activityList,
    ) {
        parent::__construct();
    }

    public function update()
    {
        return $this->page('update', [
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
        return $this->page('change_password', ['hasPassword' => $this->profile->hasPassword(auth()->user())]);
    }

    public function image()
    {
        return $this->page('component.image');
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
        return $this->page('two-factor');
    }

    public function passkeys()
    {
        return $this->page('passkeys');
    }

    public function session()
    {
        return $this->page('session');
    }

    public function sessionList(Request $request)
    {
        return response()->json($this->sessionList->forUser($request, auth()->user(), $request->all(), $this->prefix.'account/session-logout'));
    }

    public function sessionLogout(Request $request)
    {
        $request->validate(['id' => ['required', 'string']]);

        $result = $this->sessionList->logout($request, auth()->user(), $request->string('id'));

        return Response::sendResult($result);
    }

    public function userActivity()
    {
        return $this->page('user_activity');
    }

    public function userActivityList(Request $request)
    {
        return response()->json($this->activityList->forUser(auth()->user(), $request->all()));
    }

    public function deactivate(Request $request)
    {
        $this->profile->deactivate($request, auth()->user());

        SignedCookie::forget('session_token');

        return redirect()->route($this->loginRoute);
    }

    protected function page(string $view, array $data = [])
    {
        return view('modules.user.account.'.$view, [
            'model' => auth()->user(),
            'layout' => $this->layout,
            'prefix' => $this->prefix,
            'loginRoute' => $this->loginRoute,
        ] + $data);
    }
}
