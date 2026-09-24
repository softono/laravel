<?php

namespace App\Modules\Admin\Setting\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** One settings form (section) per request; the rules depend on which section was posted. */
class SaveSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $sections = [
            'general' => [
                'app_name' => ['required', 'string', 'max:255'],
                'date_format' => ['required', Rule::in(['yyyy-MM-dd', 'dd-MM-yyyy', 'MM-dd-yyyy'])],
                'date_time_format' => ['required', Rule::in(['yyyy-MM-dd hh:mm a', 'dd-MM-yyyy hh:mm a', 'MM-dd-yyyy hh:mm a'])],
                'user_email_verify' => ['required', 'boolean'],
                'user_login_with_otp' => ['required', 'boolean'],
                'admin_email' => ['required', 'email'],
                'cookie_consent' => ['required', 'boolean'],
            ],
            'mail' => [
                'smtp_host' => ['required', 'string'],
                'smtp_username' => ['required', 'string'],
                'smtp_password' => ['nullable', 'string'],
                'smtp_encryption' => ['required', Rule::in(['ssl', 'tls'])],
                'smtp_port' => ['required', 'integer'],
                'mail_from_address' => ['required', 'email'],
                'mail_from_name' => ['required', 'string'],
            ],
            'captcha' => [
                'google_recaptcha' => ['required', 'boolean'],
                'google_recaptcha_secret_key' => ['nullable', 'string'],
                'google_recaptcha_public_key' => ['nullable', 'string'],
            ],
            'social' => [
                'google_client_id' => ['required', 'string'],
                'google_client_secret' => ['nullable', 'string'],
                'google_login' => ['required', 'boolean'],
            ],
            'content' => [
                'header_content' => ['nullable', 'string'],
                'footer_content' => ['nullable', 'string'],
            ],
        ];

        return ['section' => ['required', Rule::in(array_keys($sections))]]
            + ($sections[$this->input('section')] ?? []);
    }

    /**
     * The validated settings of the posted section, without the section marker.
     *
     * @return array<string, mixed>
     */
    public function settings(): array
    {
        return $this->safe()->except('section');
    }
}
