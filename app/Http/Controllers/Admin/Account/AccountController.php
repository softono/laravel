<?php

namespace App\Http\Controllers\Admin\Account;

use App\Models\UserActivity;
use App\Models\UserAuth;
use App\Modules\Admin\Controllers\Controller;
use App\Services\AccountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Display the user dashboard.
     *
     * @return View
     */
    public function dashboard()
    {
        return view('modules.user.dashboard');
    }

    /**
     * Display the account update form.
     *
     * @return View
     */
    public function update(Request $request)
    {
        $model = auth()->user();

        return view('admin/account/update', compact('model'));
    }

    /**
     * Save updated account details.
     *
     * @return JsonResponse
     */
    public function save(Request $request)
    {
        return response()->json((new AccountService)->updateProcess($request, auth()->user()));
    }

    /**
     * Display the change password form.
     *
     * @return View
     */
    public function passwordChange(Request $request)
    {
        $model = auth()->user();

        return view('admin.account.change_password', compact('model'));
    }

    /**
     * Process password change request.
     *
     * @return JsonResponse
     */
    public function changePasswordProcess(Request $request)
    {
        return response()->json((new AccountService)->changePassword($request, auth()->user()));
    }

    /**
     * Display the profile image update form.
     *
     * @return View
     */
    public function image()
    {
        $model = auth()->user();

        return view('admin.account.component.image', compact('model'));
    }

    /**
     * Save updated profile image.
     *
     * @return JsonResponse
     */
    public function imagesave(Request $request)
    {
        return response()->json((new AccountService)->saveImage($request, auth()->user()));
    }

    /**
     * Delete the current user's profile image.
     *
     * @return JsonResponse
     */
    public function deleteImage()
    {
        return response()->json((new AccountService)->deleteImage(auth()->user()));
    }

    public function tfa()
    {
        $model = auth()->user();

        return view('admin.account.tfa', compact('model'));
    }

    /**
     * Toggle TFA status.
     *
     * @return JsonResponse
     */
    public function tfaStatusChange()
    {
        $user = auth()->user();
        $status_tfa = ! $user->status_tfa;
        $user->status_tfa = $status_tfa;
        $user->save();

        return response()->json(['status' => 1, 'next' => 'refresh', 'message' => $status_tfa ? 'Two Factor Authentication is enabled' : 'Two Factor Authentication is disabled']);
    }

    /**
     * Revoke all trusted devices for the current user.
     *
     * @return JsonResponse
     */
    public function revokeAll()
    {
        return response()->json((new AccountService)->revokeAll2FADevices(auth()->user()));
    }

    /**
     * Display the device management view.
     *
     * @return View
     */
    public function device()
    {
        return view('admin.account.device');
    }

    /**
     * Retrieve the list of user's devices.
     *
     * @return JsonResponse
     */
    public function deviceList(Request $request)
    {
        return response()->json((new UserAuth)->list($request->all(), auth()->id()));
    }

    /**
     * Log out the specified device.
     *
     * @return JsonResponse
     */
    public function deviceLogout(Request $request)
    {
        (new UserAuth)->forceLogout($request->input('id'));

        return response()->json(['status' => 1, 'message' => 'Device Logout Successfully', 'next' => 'reload']);
    }

    /**
     * Display the user activity log view.
     *
     * @return View
     */
    public function userActivity()
    {
        return view('admin.account.user_activity');
    }

    /**
     * Retrieve the list of user activity logs.
     *
     * @return JsonResponse
     */
    public function userActivityList(Request $request)
    {
        return response()->json((new UserActivity)->list($request->all(), auth()->id()));
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
