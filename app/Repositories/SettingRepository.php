<?php

namespace App\Repositories;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class SettingRepository
{
    public function getOne(string $key): string|bool
    {
        $setting = Setting::where('key', $key)->first();

        return $setting ? $setting->value : false;
    }

    public function setOne(string $key, string $value): void
    {
        $setting = Setting::firstOrNew(['key' => $key]);
        if ($setting->exists && $setting->value === $value) {
            return;
        }
        $setting->value = $value;
        $setting->save();
    }

    /**
     * Upserts by key - previously update-only (a no-op for a key with no
     * existing row), which silently dropped the first save of any newly
     * introduced setting. type=0 matches allSettings()'s filter, so a
     * freshly created key is picked up by the config-merge cache too.
     */
    public function updateOne(string $key, ?string $value): void
    {
        $setting = Setting::firstOrNew(['key' => $key]);

        if ($setting->exists && $setting->value === $value) {
            return;
        }

        $setting->value = $value;
        if (! $setting->exists) {
            $setting->type = 0;
        }
        $setting->save();
    }

    public function updateAll(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->updateOne($key, $value);
        }
        $this->clearCache();
    }

    public function clearCache(): void
    {
        Cache::put('setting', $this->allSettings(), now()->addDay());
    }

    public function allSettings(): array
    {
        $data = [];
        $options = Setting::where('type', 0)->get();

        foreach ($options as $row) {
            $data[$row['key']] = $row['value'];
        }

        return $data;
    }

    public function getAllSettings(): array
    {
        return Cache::remember('setting', now()->addDay(), function () {
            return $this->allSettings();
        });
    }

    public function store(array $postData): array
    {
        $validator = null;
        $updateData = [];

        if ($postData['type'] == 'general') {
            $validator = Validator::make($postData, [
                'setting_app_name' => 'required|string',
                'setting_date_format' => 'required|string',
                'setting_date_time_format' => 'required|string',
                'setting_user_email_verify' => 'required|boolean',
                'setting_user_login_with_otp' => 'required|boolean',
                'setting_admin_email' => 'required|email',
                'setting_cookie_consent' => 'required|boolean',
            ]);
            $updateData = [
                'setting.app_name' => $postData['setting_app_name'],
                'setting.date_format' => $postData['setting_date_format'],
                'setting.date_time_format' => $postData['setting_date_time_format'],
                'setting.user_email_verify' => $postData['setting_user_email_verify'],
                'setting.user_login_with_otp' => $postData['setting_user_login_with_otp'],
                'setting.admin_email' => $postData['setting_admin_email'],
                'setting.cookie_consent' => $postData['setting_cookie_consent'],
            ];
        } elseif ($postData['type'] == 'smtp') {
            $validator = Validator::make($postData, [
                'mail_mailers_smtp_host' => 'required|string',
                'mail_mailers_smtp_username' => 'required|string',
                'mail_mailers_smtp_password' => 'required|string',
                'mail_mailers_smtp_encryption' => 'required|string',
                'mail_mailers_smtp_port' => 'required|integer',
                'mail_from_address' => 'required|email',
                'mail_from_name' => 'required|string',
            ]);
            $updateData = [
                'mail.mailers.smtp.host' => $postData['mail_mailers_smtp_host'],
                'mail.mailers.smtp.username' => $postData['mail_mailers_smtp_username'],
                'mail.mailers.smtp.password' => $postData['mail_mailers_smtp_password'],
                'mail.mailers.smtp.encryption' => $postData['mail_mailers_smtp_encryption'],
                'mail.mailers.smtp.port' => $postData['mail_mailers_smtp_port'],
                'mail.from.address' => $postData['mail_from_address'],
                'mail.from.name' => $postData['mail_from_name'],
            ];
        } elseif ($postData['type'] == 'captcha') {
            $validator = Validator::make($postData, [
                'setting_google_recaptcha' => 'required|string',
                'setting_google_recaptcha_secret_key' => 'required|string',
                'setting_google_recaptcha_public_key' => 'required|string',
            ]);
            $updateData = [
                'setting.google_recaptcha' => $postData['setting_google_recaptcha'],
                'setting.google_recaptcha_secret_key' => $postData['setting_google_recaptcha_secret_key'],
                'setting.google_recaptcha_public_key' => $postData['setting_google_recaptcha_public_key'],
            ];
        } elseif ($postData['type'] == 'social') {
            $postData['services_google_login'] = $postData['services_google_login'] ?? 0;
            $validator = Validator::make($postData, [
                'services_google_client_id' => 'required|string',
                'services_google_client_secret' => 'required|string',
                'services_google_login' => 'required|boolean',
            ]);
            $updateData = [
                'services.google_client_id' => $postData['services_google_client_id'],
                'services.google_client_secret' => $postData['services_google_client_secret'],
                'services.google_login' => $postData['services_google_login'],
            ];
        } elseif ($postData['type'] == 'content') {
            $validator = Validator::make($postData, [
                'setting_header_content' => 'string|nullable',
                'setting_footer_content' => 'string|nullable',
            ]);
            $updateData = [
                'setting.header_content' => $postData['setting_header_content'],
                'setting.footer_content' => $postData['setting_footer_content'],
            ];
        } elseif ($postData['type'] == 'storage') {
            $validator = Validator::make($postData, [
                'setting_storage_path' => 'required|string|max:255',
                'setting_storage_max_upload_size' => 'required|integer|min:1',
                'setting_storage_allowed_file_types' => 'nullable|string',
                'setting_storage_default_visibility' => 'required|in:public,private',
                'setting_storage_api_endpoint' => 'nullable|string',
                'setting_storage_cors_allowed_origins' => 'nullable|string',
                'setting_storage_rate_limit_per_minute' => 'required|integer|min:1',
            ]);
            $updateData = [
                'setting.storage_path' => $postData['setting_storage_path'],
                'setting.storage_max_upload_size' => $postData['setting_storage_max_upload_size'],
                'setting.storage_allowed_file_types' => $postData['setting_storage_allowed_file_types'] ?? '*',
                'setting.storage_default_visibility' => $postData['setting_storage_default_visibility'],
                'setting.storage_api_endpoint' => $postData['setting_storage_api_endpoint'] ?? '',
                'setting.storage_cors_allowed_origins' => $postData['setting_storage_cors_allowed_origins'] ?? '*',
                'setting.storage_rate_limit_per_minute' => $postData['setting_storage_rate_limit_per_minute'],
            ];
        }

        if ($validator && $validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }

        $this->updateAll($updateData);

        return [
            'status' => 1,
            'message' => 'Data saved successfully',
        ];
    }
}
