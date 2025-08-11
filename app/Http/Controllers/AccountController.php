<?php

namespace App\Http\Controllers;

use App\Models\UserAuth;
use App\Models\Device;
use App\Models\UserActivity;
use App\Services\AccountService;
use Illuminate\Http\Request;
use App\Helpers\General;
use Illuminate\Support\Facades\DB;


class AccountController extends Controller
{
    /**
     * Display the account update form.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function update()
    {
        return view('account/update', ['model' => auth()->user()]);
    }

    /**
     * Save updated account details.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateProcess(Request $request)
    {
        return response()->json((new AccountService())->updateProcess($request, auth()->user()));
    }

    /**
     * Display the change password form.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function passwordChange()
    {
        return view('account.change_password', ['model' => auth()->user()]);
    }

    /**
     * Process password change request.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changePasswordProcess(Request $request)
    {
        return response()->json((new AccountService())->changePassword($request, auth()->user()));
    }

    /**
     * Display the profile image update form.
     *
     * @return \Illuminate\View\View
     */
    public function image()
    {
        return view('account/component/image', ['model' => auth()->user()]);
    }

    /**
     * Save updated profile image.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function imagesave(Request $request)
    {
        return response()->json((new AccountService())->saveImage($request, auth()->user()));
    }

    /**
     * Delete the current user's profile image.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteImage()
    {
        return response()->json((new AccountService())->deleteImage(auth()->user()));
    }

    /**
     * Show TFA settings page.
     */

    public function tfa()
    {
        $model = auth()->user();
        $userAuthList = DB::table('user_auth')
            ->leftjoin('user', 'user_auth.device_uid', '=', 'user.ignore_tfa_device')
            ->where('user.ignore_tfa_device', $model->ignore_tfa_device)
            ->select('user_auth.*', 'user.ignore_tfa_device')
            ->get();

        foreach ($userAuthList as $key => $userAuth) {
            $userAuthList[$key]->client =  (new General())->deviceName($userAuth->client) . ' ' . ($userAuth->device_uid == @$_COOKIE[config("setting.app_uid") . '_token'] ? ' (This Device)' : '');
            $userAuthList[$key]->location = (new General)->getIpLocation($userAuth->ip);
        }
        return view('account/tfa', compact('model', 'userAuthList'));
    }


    /**
     * Toggle TFA status.
     *
     * @return JsonResponse
     */
    public function tfaStatusChange()
    {
        $user = auth()->user();
        $status_tfa = !$user->status_tfa;
        $user->status_tfa = $status_tfa;
        $user->save();
        return response()->json(['status' => 1, 'next' => 'refresh', 'message' => $status_tfa ? 'Two Factor Authentication is enabled' : 'Two Factor Authentication is disabled']);
    }

    /**
     * Revoke all trusted devices for the current user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeAll()
    {
        return response()->json((new AccountService())->revokeAll2FADevices(auth()->user()));
    }


    /**
     * Display the device management view.
     *
     * @return \Illuminate\View\View
     */
    public function device()
    {
        return view('account/device', ['model' => auth()->user()]);
    }

    /**
     * Retrieve the list of user's devices.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deviceList(Request $request)
    {
        return response()->json((new Device())->list($request->all(), auth()->id()));
    }

    /**
     * Log out the specified device.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deviceLogout(Request $request)
    {
        (new UserAuth())->forceLogout($request->input('id'));

        return response()->json(['status' => 1, 'message' => 'Device Logout Successfully', 'next' => 'reload']);
    }

    /**
     * Display the user activity log view.
     *
     * @return \Illuminate\View\View
     */
    public function userActivity()
    {
        return view('account/user_activity', ['model' => auth()->user()]);
    }

    /**
     * Retrieve the list of user activity logs.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userActivityList(Request $request)
    {
        return response()->json((new UserActivity())->list($request->all(), auth()->id()));
    }


    public function accountDeactivate()
    {
        $model = auth()->user();
        $model->status = 0;
        Auth::logout();
        $model->update();
        return redirect('logout');
    }
}
