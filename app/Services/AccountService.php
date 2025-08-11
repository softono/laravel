<?php

namespace App\Services;

use App\Helpers\General;
use App\Models\UserActivity;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class AccountService
{

    /**
     * Process user registration.
     *
     * @param array $postData
     * @return array
     */
    public function registerProcess(array $postData): array
    {
        $general = new General();
        if ($general->rateLimit('register')) {
            return ['status' => 0, 'message' => 'Too many attempts, please try again later.'];
        }
        if ($general->recaptchaFails()) {
            return ['status' => 0, 'message' => 'Please check recaptcha.'];
        }

        $validator = Validator::make($postData, [
            'first_name' => 'required|alpha|max:255',
            'last_name' => 'required|alpha|max:255',
            'phone' => 'required|digits:10|numeric|unique:user,phone',
            'email' => 'required|email|unique:user,email',
            'password' => [
                'required',
                $general->passwordType()
            ],
            'password_confirm' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }

        $result = $general->verifyEmail($postData['email']);
        if (!$result['status']) {
            return $result;
        }

        $ip = $general->getClientIp();
        $userObj = new User();

        $user = $userObj->create([
            'first_name' => $postData['first_name'],
            'last_name' => $postData['last_name'],
            'email' => $postData['email'],
            'phone' => $postData['phone'],
            'password' => (new AuthService())->encryptPassword($postData['password']),
            'country' => $general->getIpInfoCountry($ip),
            'status' => 1,
            'timezone' => $general->getClientTimezone(),
            'registered_ip' => $ip,
            'role' => 4,
        ]);

        (new UserActivity())->add($user->id, 3);

        if (config('setting.user_email_verify')) {
            (new TfaService())->sendOTP($user, 'verify_account');
            $token = base64_encode($user->email);
            return ['status' => 1, 'message' => 'Thank you for registration, Please verify your email.', 'next' => 'redirect', 'url' => 'site/verify-account?code=' . $token];
        } else {
            Auth::guard()->login($user);
            (new UserAuth())->login($user->id, 0);
            (new UserActivity())->add($user->id, 1);
        }
        (new General())->sendEmail($user->email, 'welcome', [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name
        ]);
        return ['status' => 1, 'message' => 'Thank you for registration', 'next' => 'redirect', 'url' => config('setting.login_redirect_url')];
    }

    /**
     * Verify the OTP process.
     *
     * @param array $postData The post data containing the OTP and other details.
     * @return array The result of the OTP verification process.
     */
    public function verifyAccountProcess(array $postData): array
    {
        $general = new General();
        if ($general->rateLimit('verify_tfa')) {
            return ['status' => 0, 'message' => 'Too many attempts, please try again later.'];
        }

        $validator = Validator::make($postData, [
            'otp' => 'required|digits:6',
            'code' => 'required',
        ]);
        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }
        $tfaService = (new TfaService());
        $email = $tfaService->decryptCode($postData['code']);
        $user = User::where('email', $email)->first();

        if ($user->status == 0) {
            return ['status' => 0, 'message' => 'Your Account is blocked'];
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

        $user->email_verified = 1;
        $user->otp = '';
        $user->save();
        return ['status' => 1, 'message' => 'OTP verified successfully.', 'next' => 'redirect', 'url' => 'login'];
    }

    /**
     * Process the password forgot.
     *
     * @param array $postData
     * @param int $type
     * @return array
     */
    public function passwordForgotProcess($postData)
    {
        $general = new General();
        if ($general->rateLimit('password_forgot')) {
            return ['status' => 0, 'message' => 'Too many attempts, please try again later.'];
        }
        $step = $postData['step'];
        if ($step == 1 && $general->recaptchaFails()) {
            return ['status' => 0, 'message' => 'Please complete the captcha.'];
        }

        $validationRules = ['email' => 'required|email'];
        if ($step == 2) {
            $validationRules['otp'] = 'required';
        }
        if ($step == 3) {
            $validationRules['otp'] = 'required';
            $validationRules['password'] = [
                'required',
                $general->passwordType()
            ];
            $validationRules['password_confirm'] = 'required|same:password';
        }

        $validator = Validator::make($postData, $validationRules);
        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }

        $user = User::where(function ($query) use ($postData) {
            $query->where('email', $postData['email'])
                ->orWhere("phone", $postData['email']);
        })->first();

        if (!$user) {
            return ['status' => 0, 'message' => 'Email Not Valid'];
        }
        $tfaService = new TfaService();
        if ($step == 1) {
            $tfaService->sendOTP($user, 'forgot_password');
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

        if ($step == 2) {
            return ['status' => 1, 'message' => 'Otp is valid', 'next'  => 'step_3'];
        } else {
            $user->update([
                'password' => (new AuthService())->encryptPassword($postData['password']),
                'data' => $user->otp = '',
            ]);
            return ['status' => 1, 'message' => 'Password reset successfully. You can now log in', 'next' => 'redirect', 'url' => 'admin/auth/login'];
        }
    }

    /**
     * Save user account details.
     *
     * @param Request $request
     * @param User $user
     * @return array
     */
    public function updateProcess(Request $request, User $user): array
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|alpha',
            'last_name' => 'required|alpha',
            'phone' => 'required',
            'email' => 'required|email|regex:/(.+)@(.+)\.(.+)/i',
        ]);

        if ($validator->fails()) {
            return [
                'status' => 0,
                'message' => $validator->errors()->first()
            ];
        }
        $user->update([
            'first_name' => $request->input('first_name'),
            'last_name' => $request->input('last_name'),
        ]);

        if ($request->input('phone') != $user->phone || $request->input('email') != $user->email) {
            $user->new_phone = $request->input('phone');
            $user->new_email = $request->input('email');
            $user->save();
            (new \App\Services\TfaService())->sendOTP($user, 'otp');
            return ['status' => 1, 'message' => 'Account Updated Successfully', 'next' => 'redirect', 'url' => 'auth/verify?type=new_email'];
        }

        return ['status' => 1, 'message' => 'Account Updated Successfully', 'next' => 'reload'];
    }

    /**
     * Change user password.
     *
     * @param Request $request
     * @param User $user
     * @return array
     */
    public function changePassword(Request $request, User $user): array
    {
        $general = new General();
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'password' => [
                'required',
                $general->passwordType()
            ],
            'confirm_password' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }

        if (!Hash::check($request->input('current_password'), $user->password)) {
            return ['status' => 0, 'message' => 'Old password does not match!'];
        }

        $user->update(['password' => bcrypt($request->input('confirm_password'))]);

        return ['status' => 1, 'message' => 'Password Updated Successfully', 'next' => 'refresh'];
    }

    /**
     * Save user profile image.
     *
     * @param Request $request
     * @param User $user
     * @return array
     */
    public function saveImage(Request $request, User $user): array
    {
        $general = new General();
        $validator = Validator::make($request->all(), [
            'image' => 'required|' . $general->fileRules('image'),
        ]);
        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }
        $uploadResult = $general->uploadFile($request->file('image'), 'profile');
        if (!$uploadResult['status']) {
            return $uploadResult;
        }
        if ($uploadResult['file_name']) {
            if ($user->image) {
                $general->deleteFile($user->image, 'profile');
            }
            $user->update(['image' => $uploadResult['file_name']]);
            return ['status' => 1, 'message' => 'Account Updated Successfully', 'next' => 'hide_modal,reload'];
        }
        return ['status' => 0, 'message' => 'Upload Unsuccessful'];
    }

    /**
     * Delete user profile image.
     *
     * @param User $user
     * @return array
     */
    public function deleteImage(User $user): array
    {
        $general = new General();
        if (!$user->image) {
            return ['status' => 0, 'message' => 'Image not found'];
        }
        $general->deleteFile($user->image, 'profile');
        $user->update(['image' => null]);
        return ['status' => 1, 'message' => 'Image Deleted Successfully', 'next' => 'reload'];
    }

    /**
     * Revoke all TFA devices for the user.
     *
     * @param User $user
     * @return array
     */
    public function revokeAll2FADevices(User $user): array
    {
        $user->ignore_tfa_device = '';
        $user->save();
        // dd($user);
        return ['status' => 1, 'message' => 'Your Devices Revoked Successfully.', 'next' => 'refresh'];
    }
}
