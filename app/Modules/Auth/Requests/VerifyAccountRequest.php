<?php

namespace App\Modules\Auth\Requests;

use App\Helpers\Response;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class VerifyAccountRequest extends FormRequest
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
        ];
    }

    protected function failedValidation(ValidatorContract $validator)
    {
        throw new HttpResponseException(
            Response::sendError(422, $validator->errors()->first())
        );
    }
}
