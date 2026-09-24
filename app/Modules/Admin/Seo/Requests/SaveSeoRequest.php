<?php

namespace App\Modules\Admin\Seo\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSeoRequest extends FormRequest
{
    public const FREQUENCIES = ['always', 'hourly', 'daily', 'weekly', 'monthly', 'yearly', 'never'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['nullable', 'integer', 'exists:seos,id'],
            'type' => ['required', Rule::in(['STATIC', 'DYNAMIC'])],
            'url' => ['required', 'string', 'max:255', 'regex:/^\S+$/', Rule::unique('seos', 'url')->ignore($this->input('id'))],
            'title' => ['required', 'string', 'max:255'],
            'keyword' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'last_modified' => ['nullable', 'string', 'max:255'],
            'change_frequency' => ['nullable', Rule::in(self::FREQUENCIES)],
            'priority' => ['nullable', 'numeric', 'between:0,1'],
            'sitemap_enable' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return ['url.regex' => 'Spaces are not allowed in the URL.'];
    }
}
