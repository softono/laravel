<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Models\User;
use App\Services\AuthService;
use App\Services\TfaService;

/**
 * Class AuthController
 *
 * Manages user authentication, including login, TFA (Two-Factor Authentication), OTP-based login, social login, and password recovery.
 */
class AuthController extends Controller
{
    /**
     * Display the login view or redirect if the user is authenticated via cookie.
     *
     * @param Request $request
     * @return RedirectResponse|View
     */
    public function login(Request $request)
    {

        // Check if the user is already authenticated
        if (Auth::check()) {
            return redirect($this->general->authRedirectUrl(config('setting.login_redirect_url')));
        }
        // Check if the user is already authenticated via cookie
        $userToken = $request->cookie(config('setting.app_uid') . '_user_token');
        if ($userToken && !$this->general->rateLimit('remember_login')) {
            $result = (new AuthService())->loginByAuthToken($userToken);

            if ($result['status']) {

                return redirect($this->general->authRedirectUrl(config('setting.login_redirect_url')));
            }
        }

        return view('auth/login');
    }

    /**
     * Process login with validation, rate limiting, and authentication.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function loginProcess(Request $request)
    {

        return response()->json((new AuthService())->loginProcess($request->only(['email', 'password', 'remember'])));
    }

    /**
     * Process the OTP login.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loginOtpProcess(Request $request)
    {
        return (new AuthService())->loginOtpProcess($request->only(['email', 'otp', 'step']));
    }


    /**
     * Log out the authenticated user and clear session data.
     *
     * @return RedirectResponse
     */
    public function logout()
    {
        if (Auth::check()) {
            Auth::logout();
            (new \App\Models\UserAuth())->logout();
        }
        return redirect('login')->withCookie(cookie()->forget(config('setting.app_uid') . '_user_token'));
    }




    // ----------------- Two-Factor Authentication (TFA) Methods -------------------
    /**
     * Show the TFA verification page.
     *
     * @return View
     */
    public function verify(Request $request)
    {
        $type = $request->get('type');
        $code = $request->get('code', '');
        return view('auth/verify', compact('type', 'code'));
    }

    /** 
     * Process TFA OTP verification.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function verifyProcess(Request $request)
    {
        return response()->json((new TfaService())->verifyProcess($request->only(['otp', 'skip_tfa', 'type'])));
    }

    /**
     * resend otp.
     *
     * @return \Illuminate\View\View
     */
    public function resendOTP(Request $request)
    {
        return response()->json((new TfaService())->resendOTP($request->only(['type', 'code'])));
    }

    // ----------------- Two-Factor Authentication (TFA) Methods END-------------------



    // Social Login methods

    /**
     * Redirect to social login provider.
     *
     * @param Request $request
     * @return RedirectResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function socialLogin(Request $request)
    {
        return \Laravel\Socialite\Facades\Socialite::driver($request->route('type'))->redirect();
    }

    /**
     * Handle social login callback and process user data.
     *
     * @param string $provider
     * @return RedirectResponse
     */
    public function socialLoginCallback(string $type): RedirectResponse
    {
        $socialUser = \Laravel\Socialite\Facades\Socialite::driver($type)->stateless()->user();
        $user = User::where(function ($query) use ($socialUser) {
            $query->where('email', $socialUser->email)
                ->orWhere("phone", $socialUser->phone);
        })->where('type', 1)->first();
        $user = (new User())->where('email', $socialUser->email)->first();
        Auth::login($user);
        return redirect($this->general->authRedirectUrl(config('setting.login_redirect_url')));
    }

    public function getTotpModel(Request $request)
    {
        $user = auth()->user();

        if ($user->totp_secret_key) {
            return response()->json(['error' => 'TOTP already enabled.'], 403);
        }

        $data = (new TfaService())->generateTotpQrcode($user->user_name);
        $secretKey = $data['secretKey'];
        $qrCode = $data['qrCode'];

        return view('common.totp_modal', compact('secretKey', 'qrCode'));
    }

    public function verifyOtpModal(Request $request)
    {
        $secretKey = $request->secretKey;
        $id = auth()->user()->id;
        return view('common/verify_otp_modal', compact('secretKey', 'id'));
    }

    public function optVerifyProcess(Request $request)
    {
        $otp = str_replace(',', '', $request->otp);
        $secretKey = $request->secretKey;
        $data = (new TfaService())->verifyTotp($secretKey, $otp);

        if ($data) {
            $userModel = User::find($request->id);
            $userModel->totp_secret_key = $secretKey;
            $backupCodes = collect(range(1, 5))->map(function () {
                return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            })->toArray();

            $userModel->backup_code = implode(',', $backupCodes);
            $userModel->status_tfa = 1;

            $userModel->save();
            return response()->json(['status' => 1, 'message' => 'Verify successfully.', 'next' => 'refresh']);
        } else {
            return response()->json(['status' => 0, 'message' => 'Verify fail.']);
        }
    }

    // Backup regenerate (5 new codes)
    public function regenerateBackupProcess(Request $request)
    {
        $user = auth()->user();
        if (!$user->totp_secret_key) {
            return response()->json(['status' => 0, 'message' => 'TOTP not enabled']);
        }
        $backupCodes = collect(range(1, 5))->map(fn() => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT))->toArray();
        $user->backup_code = implode(',', $backupCodes);
        $user->save();
        return response()->json(['status' => 1, 'message' => 'New backup codes generated!', 'backup_code' => $user->backup_code]);
    }


    public function backupCode()
    {
        $user = auth()->user();
        $backupCode = $user->backup_code;
        return view('account/backup', compact('backupCode', 'user'));
    }


    public function removeTotp()
    {
        $user = auth()->user();
        $user->totp_secret_key = null;
        $user->backup_code = null;
        // Keep ignore_tfa_device on TOTP remove
        $user->status_tfa = 0;
        $user->save();

        return response()->json(['message' => 'TOTP removed']);
    }

    /**
     * Regenerate backup codes
     */
    public function regenerateBackupCodes()
    {
        $user = auth()->user();
        if (!$user->status_tfa) {
            return response()->json(['status' => 0, 'message' => 'TFA not enabled'], 400);
        }
        $backupCodes = collect(range(1, 10))->map(fn() => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT))->toArray();
        $user->backup_code = implode(',', $backupCodes);
        $user->save();
        return response()->json(['status' => 1, 'message' => 'Backup codes regenerated', 'backupCodes' => $backupCodes]);
    }
}
