<?php

namespace App\Modules\User\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'phone' => ['nullable', 'digits_between:6,15'],
            'country' => ['nullable', Rule::in(array_keys(config('countries')))],
            'timezone' => ['nullable', 'timezone:all_with_bc'],
        ];
    }
}
