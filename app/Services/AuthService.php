<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Helpers\General;
use App\Models\User;
use App\Models\UserAuth;
use App\Models\UserActivity;
use Illuminate\Support\Str;

class AuthService
{
    /**
     * Encrypt a password.
     *
     * @param string $password
     * @return string
     */
    public function encryptPassword(string $password): string
    {
        return Hash::make($password);
    }

    /**
     * Check if a given password matches the encrypted password.
     *
     * @param string $password
     * @param string $encryptedPassword
     * @return bool
     */
    public function checkPassword(string $password, string $encryptedPassword): bool
    {
        return Hash::check($password, $encryptedPassword);
    }

    /**
     * Generate a random token.
     *
     * @return string
     */
    public function generateToken(): string
    {
        return base64_encode(Str::random(32) . '_' . time());
    }

    /**
     * Check if a token has expired.
     *
     * @param string $token
     * @return bool
     */
    public function checkTokenIsExpired(string $token): bool
    {
        $token = base64_decode($token);
        $token = explode('_', $token);
        $timestamp = (int) (@$token[1] ?? 0);
        return time() > ($timestamp + (int) config('setting.token_expire_time'));
    }


    /**
     * Attempt to log in a user by authentication token.
     *
     * @param string $authToken
     * @return array
     */
    public function loginByAuthToken(string $authToken): array
    {
        if ($this->checkTokenIsExpired($authToken)) {
            return ['status' => 0, 'message' => 'Login failed'];
        }

        $deviceObj = new UserAuth();
        $device = $deviceObj->where(['token' => $authToken, 'device_uid' => @$_COOKIE[config("setting.app_uid") . '_token']])
            ->where('token_expire_at', '>', Carbon::now())
            ->first();

        if ($device && $device->user_id) {
            $user = User::where(['id' => $device->user_id, 'status' => 1])->first();
            if ($user) {
                auth()->login($user);
                (new UserActivity())->add($user->id, 2);
                (new UserAuth())->login($user->id);
                return ['status' => 1, 'message' => 'Login success'];
            }
        }

        return ['status' => 0, 'message' => 'Login failed'];
    }

    /**
     * Process the login.
     *
     * @param array $postData
     * @param int $type
     * @return array
     */
    public function loginProcess($postData, $type = 1): array
    {

        $general = new General();
        if ($general->rateLimit('login')) {
            return ['status' => 0, 'message' => 'Too many attempts, please try again later.'];
        }

        $validator = Validator::make($postData, [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }
        $userModel = new User();
        $user = $userModel::where(function ($query) use ($postData) {
            $query->where('email', $postData['email'])
                ->orWhere("phone", $postData['email']);
        })->whereIn('role', $type ? $userModel->userRole : $userModel->adminRole)->first();

        if (!$user) {
            return ['status' => 0, 'message' => 'Email/Phone or password is not valid'];
        }
        if ($user->status == 0) {
            return ['status' => 0, 'message' => 'Your Account is blocked'];
        }

        $loginFailed = Carbon::parse($user->login_failed_at)->addSeconds(60);

        if ($user->login_failed >= config('setting.login_max_attempt') && $user->login_failed_at && $loginFailed->isFuture()) {
            $remainingSeconds = Carbon::now()->diffInSeconds($loginFailed);
            return ['status' => 0, 'message' => 'Max login attempt exceed. Please Try after ' . ceil($remainingSeconds / 60) . ' Minutes'];
        }

        $LogObj = new UserActivity();
        if (!$this->checkPassword($postData['password'], $user->password)) {

            $user->login_failed = $user->login_failed + 1;
            $user->login_failed_at = Carbon::now();
            $user->save();

            $LogObj->add($user->id, 0);
            return ['status' => 0, 'message' => 'Email or password is not valid'];
        }

        if (config('setting.user_email_verify') && $user->email_verified != '1') {
            (new \App\Services\TfaService())->sendOTP($user, 'verify_account');
            return ['status' => 0, 'message' => 'Please verify your email, <a class="noroute" href="' . route('site/verify-account', ['code' => base64_encode($user->email)]) . '">Click here</a> to verify your email address.'];
        }

        if ($user->login_failed) {
            $user->login_failed = 0;
            $user->save();
        }


        (new \App\Models\Device())->login($user->id, @$postData['remember']);
        Auth::guard()->login($user);
        $LogObj->add($user->id, 1);

        (new UserAuth())->login($user->id, @$postData['remember']);
        $LogObj->sendNewDeviceMail($user);

        if ($user->status_tfa == 1) {
            $deviceUid = $_COOKIE[config("setting.app_uid") . '_token'] ?? null;

            $ignoredDevices = $user->ignore_tfa_device
                ? array_map('trim', explode(',', $user->ignore_tfa_device))
                : [];

            \Log::info('Device UID check:', [$deviceUid]);
            \Log::info('Trusted devices:', $ignoredDevices);

            $matched = $deviceUid && in_array($deviceUid, $ignoredDevices);

            \Log::info('TFA skipped:', [$matched]);

            if (!$matched) {
                session(['verify_tfa' => 1]);
                (new \App\Services\TfaService())->sendOTP($user, 'otp');
                return [
                    'status' => 1,
                    'message' => '',
                    'next' => 'redirect',
                    'url' => route($type ? 'auth/verify' : 'admin/auth/verify', ['type' => 'tfa'])
                ];
            }
        }

        $redirectUrl = $general->authRedirectUrl($type ? config('setting.login_redirect_url', 'dashboard') : config('setting.admin_login_redirect_url', 'admin/dashboard'));
        return ['status' => 1, 'message' => 'Login success', 'next' => 'redirect', 'url' => url($redirectUrl)];
    }

    /**
     * Process the otp login.
     *
     * @param array $postData
     * @param int $type
     * @return array
     */
    public function loginOtpProcess($postData)
    {
        $general = new General();
        if ($general->rateLimit('otp_login')) {
            return ['status' => 0, 'message' => 'Too many attempts, please try again later.'];
        }
        $step = $postData['step'];
        if ($step == 1) {
            if ($general->recaptchaFails()) {
                return ['status' => 0, 'message' => 'Please complete the captcha.'];
            }
        }

        $validationRules = ['email' => 'required|email'];
        if ($step == 2) {
            $validationRules['otp'] = 'required';
        }

        $validator = Validator::make($postData, $validationRules);
        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }

        // Fetch user by email
        $userModel = new User();
        $user = $userModel::where('email', $postData['email'])->whereIn('role', $userModel->userRole)->first();
        if (!$user) {
            return ['status' => 0, 'message' => 'Email Not Valid'];
        }
        // Check if the user's account is active
        if ($user->status == 0) {
            return ['status' => 0, 'message' => 'Your Account is blocked'];
        }
        $general = new General();
        $tfaService = new TfaService();
        if ($step == 1) {
            $tfaService->sendOTP($user, 'otp');
            return ['status' => 1, 'message' => 'Otp sent successfully', 'next' => 'step_2'];
        }
        if ($user->otp_failed >= config('setting.login_max_attempt')) {
            return ['status' => 0, 'message' => 'Too many attempts, please try again later.'];
        }
        $result = $tfaService->checkOtp($postData['otp'], $user->otp);
        if (!$result['status']) {
            $user->otp_failed = $user->otp_failed + 1;
            $user->save();
            return $result;
        }
        $user->otp = null;
        $user->otp_failed = 0;
        $user->save();
        Auth::login($user);

        (new UserActivity())->add($user->id, 4);
        (new UserAuth())->login($user->id);

        return ['status' => 1, 'message' => 'Login successful', 'next' => 'redirect', 'url' => $general->authRedirectUrl(config('setting.login_redirect_url'))];
    }
}
