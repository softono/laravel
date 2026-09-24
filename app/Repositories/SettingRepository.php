<?php

namespace App\Repositories;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

/**
 * `settings` rows use the same plain keys as the Next.js app (`smtp_host`,
 * `user_email_verify`, ...). Rows with a `type` of `private` hold secrets.
 */
class SettingRepository
{
    /** Setting keys that override a non-`setting.*` Laravel config key. */
    private const CONFIG_KEYS = [
        'smtp_host' => 'mail.mailers.smtp.host',
        'smtp_port' => 'mail.mailers.smtp.port',
        'smtp_username' => 'mail.mailers.smtp.username',
        'smtp_password' => 'mail.mailers.smtp.password',
        'smtp_encryption' => 'mail.mailers.smtp.encryption',
        'mail_from_address' => 'mail.from.address',
        'mail_from_name' => 'mail.from.name',
        'google_client_id' => 'services.google.client_id',
        'google_client_secret' => 'services.google.client_secret',
    ];

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

    public function updateOne(string $key, ?string $value): void
    {
        $setting = Setting::where('key', $key)->first();

        // `value` is NOT NULL, matching the Next schema.
        $value ??= '';

        if ($setting && $setting->value !== $value) {
            $setting->update(['value' => $value]);
        }
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
        return Setting::pluck('value', 'key')->all();
    }

    /**
     * Rows keep Next's date-fns patterns (`dd-MM-yyyy hh:mm a`); PHP's date() needs its own.
     */
    public static function toPhpDateFormat(string $pattern): string
    {
        return strtr($pattern, [
            'yyyy' => 'Y', 'MM' => 'm', 'dd' => 'd', 'HH' => 'H', 'hh' => 'h', 'mm' => 'i', 'ss' => 's', 'a' => 'A',
        ]);
    }

    /**
     * Laravel config entries derived from the settings rows.
     *
     * @return array<string, string>
     */
    public function configOverrides(): array
    {
        $config = [];
        foreach ($this->allSettings() as $key => $value) {
            if ($key === 'date_format' || $key === 'date_time_format') {
                $value = self::toPhpDateFormat($value);
            }
            $config[self::CONFIG_KEYS[$key] ?? 'setting.'.$key] = $value;
        }

        return $config;
    }

    public function getAllSettings(): array
    {
        return $this->allSettings();
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
                'app_name' => $postData['setting_app_name'],
                'date_format' => $postData['setting_date_format'],
                'date_time_format' => $postData['setting_date_time_format'],
                'user_email_verify' => $postData['setting_user_email_verify'],
                'user_login_with_otp' => $postData['setting_user_login_with_otp'],
                'admin_email' => $postData['setting_admin_email'],
                'cookie_consent' => $postData['setting_cookie_consent'],
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
                'smtp_host' => $postData['mail_mailers_smtp_host'],
                'smtp_username' => $postData['mail_mailers_smtp_username'],
                'smtp_password' => $postData['mail_mailers_smtp_password'],
                'smtp_encryption' => $postData['mail_mailers_smtp_encryption'],
                'smtp_port' => $postData['mail_mailers_smtp_port'],
                'mail_from_address' => $postData['mail_from_address'],
                'mail_from_name' => $postData['mail_from_name'],
            ];
        } elseif ($postData['type'] == 'captcha') {
            $validator = Validator::make($postData, [
                'setting_google_recaptcha' => 'required|string',
                'setting_google_recaptcha_secret_key' => 'required|string',
                'setting_google_recaptcha_public_key' => 'required|string',
            ]);
            $updateData = [
                'google_recaptcha' => $postData['setting_google_recaptcha'],
                'google_recaptcha_secret_key' => $postData['setting_google_recaptcha_secret_key'],
                'google_recaptcha_public_key' => $postData['setting_google_recaptcha_public_key'],
            ];
        } elseif ($postData['type'] == 'social') {
            $postData['services_google_login'] = $postData['services_google_login'] ?? 0;
            $validator = Validator::make($postData, [
                'services_google_client_id' => 'required|string',
                'services_google_client_secret' => 'required|string',
                'services_google_login' => 'required|boolean',
            ]);
            $updateData = [
                'google_client_id' => $postData['services_google_client_id'],
                'google_client_secret' => $postData['services_google_client_secret'],
                'google_login' => $postData['services_google_login'],
            ];
        } elseif ($postData['type'] == 'content') {
            $validator = Validator::make($postData, [
                'setting_header_content' => 'string|nullable',
                'setting_footer_content' => 'string|nullable',
            ]);
            $updateData = [
                'header_content' => $postData['setting_header_content'],
                'footer_content' => $postData['setting_footer_content'],
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
