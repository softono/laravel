<?php

namespace App\Http\Controllers\Admin;

use App\Models\Setting;
use App\Repositories\SettingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * Class SettingController
 *
 * Handles the management of application settings.
 */
class SettingController extends Controller
{
    /**
     * Display the settings update form.
     *
     * @return View
     */
    public function update(Request $request)
    {
        $setting = $this->general->getAllSettings();

        return view('admin/setting/update', ['setting' => $setting]);
    }

    /**
     * Save application settings.
     *
     * @return JsonResponse
     */
    public function save(Request $request)
    {
        return response()->json((new SettingRepository)->store($request->all()));
    }

    /**
     * Clear the settings cache.
     *
     * @return RedirectResponse
     */
    public function cacheClear(Request $request)
    {
        (new SettingRepository)->clearCache();

        return response()->json(['status' => 1, 'message' => 'Setting cache cleared']);
    }

    /**
     * Save an uploaded file for the setting.
     *
     * @return JsonResponse
     */
    public function saveLogo(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'key' => 'required|string',
            'image' => $this->general->fileRules('image'),
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $this->general->getError($validator)]);
        }
        $key = $request->input('key');
        $settingRepo = new SettingRepository;
        $setting = Setting::where('key', $key)->first();
        if ($setting) {
            $result = $this->general->uploadFile($request->file('image'), 'logo', '', 'same');
            if ($result['status']) {
                if ($setting->value != $result['file_name']) {
                    $this->general->deleteFile($setting->value, 'logo');
                }
                $setting->value = $result['file_name'];
                $setting->save();
                $settingRepo->clearCache();
            }
        }

        return response()->json($result);
    }

    /**
     * Send a test email.
     *
     * @return JsonResponse
     */
    public function mailProcess(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $this->general->getError($validator)]);
        }
        $subject = 'Email Test | '.config('setting.app_name');
        $body = view('email/template', ['subject' => $subject, 'body' => 'Email Test'])->render();
        $this->general->sendEmailSMTP($request->input('email'), $subject, $body);

        return response()->json(['status' => 1, 'message' => 'Email Sent Successfully']);
    }
}
