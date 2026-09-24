<?php

namespace App\Modules\Admin\Account\Controllers;

use App\Modules\User\Controllers\AccountController as UserAccountController;

/**
 * The signed-in admin's own profile, password, 2FA, passkeys, sessions and
 * activity. Same behaviour and views as the user account area, rendered in
 * the admin layout under the admin/ route names.
 */
class AccountController extends UserAccountController
{
    protected string $layout = 'modules.admin.layouts.main';

    protected string $prefix = 'admin/';

    protected string $loginRoute = 'admin/auth/login';
}
