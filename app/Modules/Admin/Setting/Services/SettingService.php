<?php

namespace App\Modules\Admin\Setting\Services;

use App\Constants\UserActivity;
use App\Helpers\ApiResult;
use App\Helpers\General;
use App\Repositories\SettingRepository;
use App\Services\ActivityService;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class SettingService
{
    public function __construct(
        protected SettingRepository $settings,
        protected ActivityService $activity,
        protected General $general,
    ) {}

    /** @return array<string, string> every setting, plus the fallbacks the form needs for the unset ones */
    public function formValues(): array
    {
        return $this->settings->all() + ['app_name' => config('app.name'), 'app_logo' => '', 'app_favicon' => ''];
    }

    /**
     * @param  array<string, mixed>  $values  the validated fields of one settings section
     */
    public function save(Request $request, array $values, string $section): array
    {
        $this->settings->setMany($values);
        $this->activity->log($request, auth()->id(), UserActivity::SETTING_UPDATE, ['section' => $section]);

        return ApiResult::success('Data saved successfully');
    }

    /** Logo and favicon uploads; the setting holds the stored file name. */
    public function saveLogo(string $key, UploadedFile $file): array
    {
        $upload = $this->general->uploadFile($file, 'logo');

        if (! $upload['status']) {
            return $upload;
        }

        $previous = $this->settings->all()[$key] ?? '';
        if ($previous !== '') {
            $this->general->deleteFile($previous, 'logo');
        }

        $this->settings->setMany([$key => $upload['data']['file_name']]);

        return ApiResult::success('Image saved successfully');
    }

    public function clearCache(): array
    {
        $this->settings->clearCache();

        return ApiResult::success('Setting cache cleared');
    }

    public function sendTestMail(string $email): array
    {
        $subject = 'Email Test | '.config('setting.app_name');
        $body = view('email.template', ['subject' => $subject, 'body' => 'Email Test'])->render();

        $this->general->sendEmailSMTP($email, $subject, $body);

        return ApiResult::success('Email Sent Successfully');
    }
}
