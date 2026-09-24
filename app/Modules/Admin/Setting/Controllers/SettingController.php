<?php

namespace App\Modules\Admin\Setting\Controllers;

use App\Helpers\Response;
use App\Modules\Admin\Controllers\Controller;
use App\Modules\Admin\Setting\Requests\SaveSettingRequest;
use App\Modules\Admin\Setting\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(protected SettingService $settings)
    {
        parent::__construct();
    }

    public function update()
    {
        return view('modules.admin.setting.update', ['setting' => $this->settings->formValues()]);
    }

    public function save(SaveSettingRequest $request)
    {
        return Response::sendResult($this->settings->save($request, $request->settings(), (string) $request->input('section')));
    }

    public function saveLogo(Request $request)
    {
        $request->validate([
            'key' => ['required', 'in:app_logo,app_favicon'],
            'image' => ['required', $this->general->fileRules('image')],
        ]);

        return Response::sendResult($this->settings->saveLogo($request->input('key'), $request->file('image')));
    }

    public function cacheClear()
    {
        return Response::sendResult($this->settings->clearCache());
    }

    public function mailProcess(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        return Response::sendResult($this->settings->sendTestMail($request->input('email')));
    }
}
