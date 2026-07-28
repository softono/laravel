<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class Setting
 *
 * Represents application settings stored in the database.
 * Provides methods to retrieve, update, and cache settings.
 */
class Setting extends Model
{
    /**
     * @var string The table associated with the model.
     */
    protected $table = 'settings';

    /**
     * @var string The primary key associated with the table.
     */
    protected $primaryKey = 'id';

    /**
     * @var array The attributes that are mass assignable.
     */
    protected $fillable = ['key', 'value', 'type', 'group', 'created_at', 'updated_at'];

    /**
     * @var bool Indicates if the model should be timestamped.
     */
    public $timestamps = false;

    /**
     * Retrieves the value of a setting by key.
     *
     * @param  string  $key  The key of the setting.
     * @return string|bool The value of the setting if found, false otherwise.
     */
    public function getOne(string $key): string|bool
    {
        $setting = $this->where('key', $key)->first();

        return $setting ? $setting->value : false;
    }

    /**
     * Sets a setting by key. If the setting does not exist, it is created.
     * If the setting exists and its value differs, it is updated.
     *
     * @param  string  $key  The key of the setting.
     * @param  string  $value  The value to set or update.
     */
    public function setOne(string $key, string $value): void
    {
        $setting = $this->firstOrNew(['key' => $key]);
        if ($setting->exists && $setting->value === $value) {
            return; // No update necessary
        }
        $setting->value = $value;
        $setting->save();
    }

    /**
     * Updates the value of an existing setting by key if it differs from the current value.
     *
     * @param  string  $key  The key of the setting.
     * @param  string|null  $value  The new value to update.
     */
    public function updateOne(string $key, ?string $value): void
    {
        $setting = $this->where('key', $key)->first();

        if ($setting && $setting->value !== $value) {
            $setting->update(['value' => $value]);
        }
    }

    /**
     * Updates multiple settings based on an associative array of key-value pairs.
     * Clears the cache after updating all settings.
     *
     * @param  array  $data  An associative array of key-value pairs to update.
     */
    public function updateAll(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->updateOne($key, $value);
        }
        $this->clearCache();
    }

    /**
     * Clears the settings cache by re-caching all settings for one day.
     */
    public function clearCache(): void
    {
        Cache::put('setting', $this->allSettings(), now()->addDay());
    }

    /**
     * Retrieves all settings from the database and returns them as an associative array.
     * The keys are the setting keys, and the values are the corresponding setting values.
     *
     * @return array An associative array of all settings.
     */
    public function allSettings(): array
    {
        $data = [];
        $options = $this->where('type', 0)->get();

        foreach ($options as $row) {
            $data[$row['key']] = $row['value'];
        }

        return $data;
    }

    /**
     * Retrieves all settings from cache if available; otherwise, fetches and caches them.
     *
     * @return array An associative array of all settings.
     */
    public function getAllSettings(): array
    {
        return Cache::remember('setting', now()->addDay(), function () {
            return $this->allSettings();
        });
    }

    /**
     * Stores settings provided in the post data after validation.
     *
     * @param  array  $postData  The associative array containing setting data.
     * @return array An associative array indicating the status and message.
     *
     * @throws ValidationException
     */
    public function store(array $postData): array
    {
        // $settingData=$this->allSettings();
        // $postData=array_merge($settingData,$postData);
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
        }

        if ($validator->fails()) {
            return ['status' => 0, 'message' => $validator->errors()->first()];
        }

        $this->updateAll($updateData);

        return [
            'status' => 1,
            'message' => 'Data saved successfully',
        ];
    }
}
