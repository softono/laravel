<?php

namespace App\Modules\Admin\User\Requests;

use App\Constants\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Create (no id) or update (id) of an end-user account. */
class SaveUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'string'],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255'],
            'password' => [$this->filled('id') ? 'nullable' : 'required', 'string', 'min:6', 'max:100'],
            'phone' => ['nullable', 'digits_between:6,15'],
            'country' => ['nullable', Rule::in(array_keys(config('countries')))],
            'status' => ['required', Rule::in([UserStatus::ACTIVE, UserStatus::INACTIVE])],
            'image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
        ];
    }
}
