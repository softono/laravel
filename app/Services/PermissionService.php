<?php

namespace App\Services;

use App\Models\Auth\User;
use Illuminate\Http\Request;

class PermissionService
{
    /** Admin routes any signed-in admin may use: their own account and the dashboard. */
    private const SELF_SERVICE = ['admin/account/', 'admin/dashboard'];

    /** Endpoints that are not permission keys themselves, and the key that guards each. */
    private const GUARDED_BY = [
        'admin/pages' => 'admin/page',
        'admin/seo/list' => 'admin/seo/meta',
        'admin/seo/sitemap-update' => 'admin/seo/update',
        'admin/admin/change-status' => 'admin/admin/update',
        'admin/user/change-status' => 'admin/user/update',
        'admin/user/mail' => 'admin/user/view',
        'admin/page/save' => 'admin/page/update',
        'admin/page/save-image' => 'admin/page/update',
        'admin/email-template/save' => 'admin/email-template/update',
        'admin/email-template/save-image' => 'admin/email-template/update',
        'admin/blog/save-image' => 'admin/blog/update',
        'admin/setting/save' => 'admin/setting/update',
        'admin/setting/save-logo' => 'admin/setting/update',
        'admin/setting/cache-clear' => 'admin/setting/update',
        'admin/setting/mail-process' => 'admin/setting/update',
    ];

    /**
     * Whether the admin may run this request. Fails closed: a route that is
     * neither self-service nor mapped to a known permission key is refused.
     */
    public function allowsRequest(User $user, Request $request): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        $keys = $this->requiredKeys($request->route()->uri(), $request->filled('id'));

        return $keys === null || $this->hasPermission($keys, (string) $user->permission);
    }

    /**
     * Permission keys (any one suffices) guarding an admin route URI, or null for self-service routes.
     *
     * @return string[]|null
     */
    public function requiredKeys(string $uri, bool $hasId = false): ?array
    {
        foreach (self::SELF_SERVICE as $prefix) {
            if (str_starts_with($uri, $prefix)) {
                return null;
            }
        }

        $known = $this->getPermissionList();

        if (in_array($uri, $known, true)) {
            return [$uri];
        }

        if (isset(self::GUARDED_BY[$uri])) {
            return [self::GUARDED_BY[$uri]];
        }

        $base = dirname($uri);

        // `X/list` is the listing behind the page `X`.
        if (basename($uri) === 'list') {
            return [$base];
        }

        // `X/save` creates when no id is posted, otherwise updates.
        if (basename($uri) === 'save') {
            return [$base.($hasId ? '/update' : '/create')];
        }

        return [$uri];
    }

    /**
     * Check if the user has a specific permission.
     *
     * @param  string|array  $permission  The permission(s) to check; an array means any one of them.
     * @param  string  $userPermission  The user's comma-joined permissions.
     */
    public function hasPermission(string|array $permission, ?string $userPermission = ''): bool
    {
        foreach ((array) $permission as $key) {
            if ($this->checkPermission($key, (string) $userPermission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the user has a specific permission. Keys that are not in the
     * permission list are refused, except endpoints that map onto one (GUARDED_BY).
     */
    public function checkPermission(string $permission, string $userPermission): bool
    {
        $permission = self::GUARDED_BY[$permission] ?? $permission;

        return in_array($permission, $this->getPermissionList(), true)
            && in_array($permission, explode(',', $userPermission), true);
    }

    /**
     * Get the list of all permissions.
     */
    public function getPermissionList(): array
    {
        $permissionList = [];
        foreach ($this->getPermissionListData() as $permissionL) {
            $permissionList[] = $permissionL['key'];
            if (isset($permissionL['list']) && $permissionL['list']) {
                foreach ($permissionL['list'] as $permission) {
                    $permissionList[] = $permission['key'];
                }
            }
        }

        return $permissionList;
    }

    /**
     * Get the permission data list.
     */
    public function getPermissionListData(): array
    {
        return [
            [
                'title' => 'Admin',
                'key' => 'admin_admin',
                'list' => [
                    [
                        'title' => 'List',
                        'key' => 'admin/admin',
                    ],
                    [
                        'title' => 'View',
                        'key' => 'admin/admin/view',
                    ],
                    [
                        'title' => 'Create',
                        'key' => 'admin/admin/create',
                    ],
                    [
                        'title' => 'Update',
                        'key' => 'admin/admin/update',
                    ],
                    [
                        'title' => 'Delete',
                        'key' => 'admin/admin/delete',
                    ],
                ],
            ],
            [
                'title' => 'User',
                'key' => 'admin/user',
                'list' => [
                    [
                        'title' => 'List',
                        'key' => 'admin/user',
                    ],
                    [
                        'title' => 'View',
                        'key' => 'admin/user/view',
                    ],
                    [
                        'title' => 'Create',
                        'key' => 'admin/user/create',
                    ],
                    [
                        'title' => 'Update',
                        'key' => 'admin/user/update',
                    ],
                    [
                        'title' => 'Delete',
                        'key' => 'admin/user/delete',
                    ],

                ],
            ],
            [
                'title' => 'Page',
                'key' => 'admin_page',
                'list' => [
                    [
                        'title' => 'List',
                        'key' => 'admin/page',
                    ],
                    [
                        'title' => 'Update',
                        'key' => 'admin/page/update',
                    ],
                    [
                        'title' => 'View',
                        'key' => 'page/',
                    ],
                ],
            ],
            [
                'title' => 'Seo meta',
                'key' => 'admin_seo',
                'list' => [
                    [
                        'title' => 'List',
                        'key' => 'admin/seo/meta',
                    ],
                    [
                        'title' => 'Create',
                        'key' => 'admin/seo/create',
                    ],
                    [
                        'title' => 'Update',
                        'key' => 'admin/seo/update',
                    ],
                    [
                        'title' => 'Delete',
                        'key' => 'admin/seo/delete',
                    ],
                ],
            ],
            [
                'title' => 'Setting',
                'key' => 'admin_setting',
                'list' => [
                    [
                        'title' => 'Update',
                        'key' => 'admin/setting/update',
                    ],
                ],
            ],
            [
                'title' => 'Devices',
                'key' => 'admin_device',
                'list' => [
                    [
                        'title' => 'Index',
                        'key' => 'admin/device',
                    ],
                    [
                        'title' => 'Action',
                        'key' => 'admin/device/logout',
                    ],
                ],
            ],
            [
                'title' => 'Activity',
                'key' => 'admin_activity',
                'list' => [
                    [
                        'title' => 'view',
                        'key' => 'admin/activity',
                    ],

                ],
            ],
            [
                'title' => 'Email Template',
                'key' => 'admin_emailtemplate',
                'list' => [
                    ['title' => 'List', 'key' => 'admin/email-template'],
                    ['title' => 'Update', 'key' => 'admin/email-template/update'],
                    ['title' => 'View', 'key' => 'admin/email-template/view'],
                ],
            ],
            [
                'title' => 'Blog',
                'key' => 'admin_blog',
                'list' => [
                    ['title' => 'List', 'key' => 'admin/blog'],
                    ['title' => 'Create', 'key' => 'admin/blog/create'],
                    ['title' => 'Update', 'key' => 'admin/blog/update'],
                    ['title' => 'Delete', 'key' => 'admin/blog/delete'],
                ],
            ],
        ];
    }
}
