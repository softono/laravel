<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Services\AccountService;
use App\Services\GeneralService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Class SiteController
 *
 * Controller for handling admin site functionalities such as dashboard statistics and chart data.
 */
class SiteController extends Controller
{
    /**
     * Display the admin dashboard with user statistics.
     *
     * @return View
     */
    public function dashboard()
    {
        $userModel = new User;
        $totalUser = User::whereIn('role', $userModel->userRole)->count();
        $activeUser = User::whereIn('role', $userModel->userRole)->where('status', 1)->count();
        $deactiveUser = User::whereIn('role', $userModel->userRole)->where('status', 0)->count();

        return view('admin.site.dashboard', compact('totalUser', 'activeUser', 'deactiveUser'));
    }

    /**
     * Get user chart data based on the selected duration.
     *
     * @return JsonResponse
     */
    public function getChartUser(Request $request)
    {
        $duration = $request->input('type');
        $userChartData = [];

        switch ($duration) {
            case 'day':
                $userChartData = (new GeneralService)->getUserLast7DaysChartData();
                break;
            case 'month':
                $userChartData = (new GeneralService)->getUserLast6MonthsChartData();
                break;
            default:
                $userChartData = (new GeneralService)->getUserMonthlyChartData();
                break;
        }

        return response()->json($userChartData);
    }

    /**
     * Display the user chart view.
     *
     * @return View
     */
    public function getChartUser2()
    {
        return view('admin.site.userchart');
    }

    /**
     * Display the password forgot view.
     *
     * @return View
     */
    public function passwordForgot()
    {
        return view('admin.site.password_forgot');
    }

    /**
     * Process password forgot request.
     *
     * @return RedirectResponse
     */
    public function passwordForgotProcess(Request $request)
    {

        return (new AccountService)->passwordForgotProcess($request->only(['email', 'otp', 'password', 'password_confirm', 'step']));
    }
}
