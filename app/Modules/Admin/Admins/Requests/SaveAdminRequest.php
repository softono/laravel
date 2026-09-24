<?php

namespace App\Modules\Admin\Admins\Requests;

use App\Modules\Admin\User\Requests\SaveUserRequest;

/** Same fields as a user, plus the permission checkboxes. */
class SaveAdminRequest extends SaveUserRequest
{
    public function rules(): array
    {
        return parent::rules() + [
            'permission' => ['nullable', 'array'],
            'permission.*' => ['string'],
        ];
    }
}
