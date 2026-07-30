<?php

namespace App\Http\Requests\Auth;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

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

    protected function failedValidation(ValidatorContract $validator)
    {
        throw new HttpResponseException(
            Response::sendError(422,$validator->errors()->first())
        );
    }
}