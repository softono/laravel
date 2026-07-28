<?php

namespace App\Http\Requests\Auth;

use App\Helpers\ApiResult;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

/**
 * Mirrors the Next app's registerSchema (zod) in
 * src/modules/auth/register/register.fields.ts.
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'regex:/^[a-zA-Z ]+$/', 'min:3', 'max:50'],
            'last_name' => ['required', 'string', 'regex:/^[a-zA-Z ]+$/', 'min:3', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'digits:10'],
            'password' => ['required', 'string', 'min:6', 'max:100'],
            'confirm_password' => ['required', 'same:password'],
            'agree' => ['accepted'],
            'timezone' => ['nullable', 'string'],
            'country' => ['nullable', 'string'],
        ];
    }

    protected function failedValidation(ValidatorContract $validator)
    {
        throw new HttpResponseException(
            ApiResult::failure($validator->errors()->first())->toResponse()
        );
    }
}
