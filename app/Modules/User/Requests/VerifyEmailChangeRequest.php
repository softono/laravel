<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailChangeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'handle' => ['required', 'string'],
            'method' => ['required', 'in:totp,otp,backup'],
            'code' => ['required', 'string'],
        ];
    }
}
