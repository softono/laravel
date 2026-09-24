<?php

namespace App\Http\Controllers;

use App\Services\AccountService;
use Illuminate\Http\Request;

/**
 * Class SiteController
 *
 * Controller for handling site-related actions such as user registration, email verification, and dashboard.
 */
class SiteController extends Controller
{
    /**
     * Show the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {

        return view('modules.user.dashboard');
    }

    /**
     * Show the registration form.
     *
     * @return \Illuminate\View\View
     */
    public function register()
    {
        return view('site/register');
    }

    /**
     * Handle the registration process.
     *
     * @param  Request  $request  The incoming request.
     * @return \Illuminate\Http\RedirectResponse
     */
    public function registerProcess(Request $request)
    {
        return response()->json((new AccountService)->registerProcess($request->all()));
    }

    /**
     * Show the TFA verification page.
     *
     * @return View
     */
    public function verifyAccount(Request $request)
    {
        $code = $request->get('code', '');

        return view('site.verify_account', compact('code'));
    }

    /**
     * Process TFA OTP verification.
     *
     * @return RedirectResponse
     */
    public function verifyAccountProcess(Request $request)
    {
        return response()->json((new AccountService)->verifyAccountProcess($request->only(['otp', 'code'])));
    }

    /**
     * Display the password forgot view.
     *
     * @return \Illuminate\View\View
     */
    public function passwordForgot()
    {
        return view('site.password_forgot');
    }

    /**
     * Process password forgot request.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function passwordForgotProcess(Request $request)
    {

        return (new AccountService)->passwordForgotProcess($request->only(['email', 'otp', 'password', 'password_confirm', 'step']));
    }
}
