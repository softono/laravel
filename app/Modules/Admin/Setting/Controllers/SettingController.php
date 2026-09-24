<?php

namespace App\Modules\Admin\Setting\Controllers;

use App\Constants\UserActivity;
use App\Helpers\Response;
use App\Modules\Admin\Controllers\Controller;
use App\Modules\Admin\Setting\Requests\SaveSettingRequest;
use App\Repositories\SettingRepository;
use App\Services\ActivityService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(
        protected SettingRepository $settings,
        protected ActivityService $activity,
    ) {
        parent::__construct();
    }

    public function update()
    {
        return view('modules.admin.setting.update', ['setting' => $this->settings->all() + ['app_name' => config('app.name'), 'app_logo' => '', 'app_favicon' => '']]);
    }

    public function save(SaveSettingRequest $request)
    {
        $this->settings->setMany($request->settings());
        $this->activity->log($request, auth()->id(), UserActivity::SETTING_UPDATE, ['section' => $request->input('section')]);

        return Response::sendMessage('Data saved successfully');
    }

    /** Logo and favicon uploads; the setting holds the stored file name. */
    public function saveLogo(Request $request)
    {
        $request->validate([
            'key' => ['required', 'in:app_logo,app_favicon'],
            'image' => ['required', $this->general->fileRules('image')],
        ]);

        $upload = $this->general->uploadFile($request->file('image'), 'logo');

        if (! $upload['status']) {
            return Response::sendMessage($upload['message'], 0);
        }

        $previous = $this->settings->all()[$request->input('key')] ?? '';
        if ($previous !== '') {
            $this->general->deleteFile($previous, 'logo');
        }

        $this->settings->setMany([$request->input('key') => $upload['file_name']]);

        return Response::sendMessage('Image saved successfully');
    }

    public function cacheClear()
    {
        $this->settings->clearCache();

        return Response::sendMessage('Setting cache cleared');
    }

    public function mailProcess(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $subject = 'Email Test | '.config('setting.app_name');
        $body = view('email.template', ['subject' => $subject, 'body' => 'Email Test'])->render();

        $this->general->sendEmailSMTP($request->input('email'), $subject, $body);

        return Response::sendMessage('Email Sent Successfully');
    }
}
