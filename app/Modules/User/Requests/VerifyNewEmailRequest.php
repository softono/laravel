<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyNewEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'handle' => ['required', 'string'],
            'code' => ['required', 'string'],
        ];
    }
}
