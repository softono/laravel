<?php

namespace App\Modules\Admin\Page\Requests;

use App\Constants\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:pages,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^\S+$/', Rule::unique('pages', 'slug')->ignore($this->input('id'))],
            'status' => ['required', Rule::in([UserStatus::ACTIVE, UserStatus::INACTIVE])],
            'body' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return ['slug.regex' => 'Spaces are not allowed in the slug.'];
    }
}
