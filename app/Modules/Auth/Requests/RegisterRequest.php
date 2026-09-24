<?php

namespace App\Modules\Auth\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
}
