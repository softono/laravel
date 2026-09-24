<?php

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'step' => ['required', 'in:1,2'],
            'email' => ['required', 'email'],
            'otp' => ['required_if:step,2', 'nullable', 'digits:6'],
            'remember' => ['nullable', 'boolean'],
        ];
    }
}
