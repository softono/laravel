<?php

namespace App\Modules\Admin\EmailTemplate\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SaveEmailTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:email_templates,id'],
            'title' => ['required', 'string', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
        ];
    }
}
