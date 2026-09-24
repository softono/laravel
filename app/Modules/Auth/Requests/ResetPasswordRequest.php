<?php

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
            'password' => ['required', 'string', 'min:6', 'max:100'],
            'confirm_password' => ['required', 'same:password'],
        ];
    }
}
