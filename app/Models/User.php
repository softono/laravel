<?php

namespace App\Models;

use App\Services\PermissionService;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';

    protected $primaryKey = 'id';

    public $timestamps = true;

    protected $fillable = [
        'email',
        'email_verified',
        'image',
        'created_at',
        'updated_at',
        'two_factor_enabled',
        'role',
        'permission',
        'status',
        'first_name',
        'last_name',
        'phone',
        'country',
        'timezone',
        'registered_ip',
    ];

    protected $hidden = [
        'password',
        'password_reset_token',
        'email_verified',
        'otp',
    ];

    public $userRole = [4];

    public $superAdminRole = [0];

    public $adminRole = [1, 2, 3];

    public function isUser()
    {
        return in_array($this->role, $this->userRole) ? true : false;
    }

    public function isAdmin()
    {
        return in_array($this->role, $this->adminRole) ? true : false;
    }

    public function isSuperAdmin()
    {
        return $this->role == $this->superAdminRole ? true : false;
    }

    public function getPermissionListData(): array
    {
        return (new PermissionService)->getPermissionListData();
    }

    public function hasPermission($permission = '')
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return (new PermissionService)->hasPermission($permission, $this->permission);
    }

    public function getStatusBadge($status)
    {
        return $status == 1 ? '<span class="badge rounded-pill bg-label-success">Active</span>' : '<span class="badge rounded-pill bg-label-danger">Inactive</span>';
    }
}
