<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserAuth;
use App\Models\User;
use App\Models\ContactMessages;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Services\TfaService;
use App\Helpers\General;
use Illuminate\Support\Facades\DB as DBFacade;

/**
 * Class UserController
 * @package App\Http\Controllers\Admin
 *
 * Handles user management functionalities in the admin panel.
 */
class UserController extends Controller
{
    /**
     * Display the user index view.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('admin/user/index');
    }

    public function sendMail(Request $request)
    {
        $to = $request->to;
        $subject = $request->subject;
        $message = $request->message;

        $data = [
            'subject' => $subject,
            'message' => $message
        ];

        $status = (new General())->sendEmail($to, 'send_mail', $data);

        return redirect()->route('admin/dashboard')->with('success', 'Email send successfully');

    }

    /**
     * Get a list of users.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function list(Request $request)
    {

        return response()->json((new User())->list($request->all()));
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $countrilist = User::getCountryList();

        return view('admin/user/create', compact('countrilist'));
    }

    /**
     * Show the form for updating a specific user.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        $model = User::find($request->id);
        $user = auth()->user();
        $permission = explode(',', $user->permission);
        // dd($model);
        if ($user->type == 1 && !in_array('admin/user/update', $permission)) {
            return redirect('admin/users')->with('error', 'No permission To Update User');
        }
        $countrilist = User::getCountryList();

        return view('admin/user/update', compact('permission', 'model', 'countrilist'));
    }

    /**
     * Save or update user data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function save(Request $request)
    {
        return response()->json((new User())->store($request->all()));
    }

    /**
     * View a specific user's details along with logs and devices.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function view(Request $request)
    {
        $id = $request->input('id');

        $contactMessages = ContactMessages::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->get();

        $logData = UserActivity::where('user_id', $id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        $userAuthList = UserAuth::where('user_id', $id)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();
        $model = User::where('id', $id)->first();


        return view('admin/user/view', compact('model', 'logData', 'userAuthList', 'ContactMessages'));
    }

    /**
     * Delete a specific user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function delete(Request $request)
    {
        $model = User::find($request->input('id'));
        if (!$model) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }
        $model->delete();
        return response()->json(['status' => 1, 'message' => 'User deleted successfully.', 'next' => 'table_refresh']);
    }

    /**
     * Change the status of a user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        $id = $request->input('id');
        $model = User::find($id);

        if (!$model) {
            return response()->json(['status' => 0, 'message' => 'User not found']);
        }

        $model->update(['status' => !$model->status]);  // Toggle the status
        return response()->json(['status' => 1, 'message' => 'User status updated successfully.', 'next' => 'refresh']);
    }

    /**
     * Revoke all devices for the currently authenticated user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function revokeAll()
    {
        $model = Auth::user();
        $model->ignore_tfa_device = '';
        $model->save();
        return response()->json(['status' => 1, 'message' => 'Your devices revoked successfully.']);
    }


    public function autoLogin(Request $request)
    {
        $id = $request->id;
        $user = User::find($id);
        if (!$user) {
            return redirect()->route('admin/dashboard')->with('error', 'User Not Found.');
        }
        $adminId = Auth::id();
        session([
            'admin_id' => $adminId
        ]);
        Auth::guard('web')->login($user);
        return redirect()->route('dashboard');
    }

    public function sendTfaMail(Request $request)
    {
        $id = $request->id;
        $user = User::find($id);
        if (!$user) {
            return redirect()->route('admin/dashboard')->with('error', 'User Not Found');
        }
        $tfaService = new TfaService();
        $tfaService->resendOTP(['type' => 'otp', 'code' => $tfaService->encryptCode($user->email)]);
        return redirect()->route('admin/dashboard')->with('success', 'Email send successfully');
    }
}
