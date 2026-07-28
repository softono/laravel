<?php

namespace App\Services;

class PermissionService
{
    /**
     * Check if the user has a specific permission.
     *
     * @param  string|array  $permission  The permission(s) to check.
     * @param  string  $userPermission  The user's permissions.
     */
    public function hasPermission($permission = '', $userPermission = ''): bool
    {
        // dd($permission);
        if ($permission == '') {
            $permission = \Route::getCurrentRoute()->uri;
        }
        if (is_null($userPermission)) {
            $userPermission = $this->permission ?? '';
        }

        if (is_array($permission)) {
            foreach ($permission as $p) {
                if ($this->checkPermission($p, $userPermission)) {
                    return true;
                }
            }

            return false;
        }

        return $this->checkPermission($permission, $userPermission);
    }

    /**
     * Check if the user has a specific permission in the permission list.
     *
     * @param  string  $permission  The permission to check.
     * @param  string  $userPermission  The user's permissions.
     */
    public function checkPermission(string $permission, string $userPermission): bool
    {
        if (in_array($permission, $this->getPermissionList())) {
            return in_array($permission, explode(',', $userPermission));
        }

        return true;
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
                    [
                        'title' => 'Autologin',
                        'key' => 'admin/admin/autologin',
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
        ];
    }
}
