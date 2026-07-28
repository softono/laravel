<?php

namespace App\Constants;

/**
 * USER_ROLES / ADMIN_ROLES.
 * Admin and user share one `users` table, distinguished only by this
 * `role` string column - there is no separate admin table in either app.
 */
final class UserRole
{
    public const SUPER_ADMIN = 'SUPER_ADMIN';
    public const ADMIN = 'ADMIN';
    public const USER = 'USER';

    /** @var string[] */
    public const ADMIN_ROLES = [self::ADMIN, self::SUPER_ADMIN];
}