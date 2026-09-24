<?php

namespace App\Modules\Admin\Blog\Requests;

use App\Constants\BlogCategory;
use App\Constants\UserStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'exists:blogs,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('blogs', 'slug')->ignore($this->input('id'))],
            'excerpt' => ['required', 'string'],
            'body' => ['required', 'string'],
            'category' => ['required', Rule::in(array_keys(BlogCategory::LABELS))],
            'image' => ['nullable', 'file', 'mimes:jpeg,jpg,png,gif,webp', 'max:5120'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in([UserStatus::ACTIVE, UserStatus::INACTIVE])],
        ];
    }

    public function messages(): array
    {
        return ['slug.regex' => 'The slug may only contain lowercase letters, numbers and hyphens.'];
    }
}
