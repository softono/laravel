<?php

namespace App\Models\Auth;

use App\Constants\UserRole;
use App\Constants\UserStatus;
use App\Services\PermissionService;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * The credential password lives on the related `user_accounts` row
 * (provider_id='credential'), not on this model - see getAuthPassword().
 */
class User extends Authenticatable
{
    use HasUuids;

    protected $table = 'users';

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'email',
        'email_verified',
        'image',
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
        'permission',
    ];

    protected function casts(): array
    {
        return [
            'email_verified' => 'boolean',
            'two_factor_enabled' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * The stock SessionGuard::attempt() is never used for this model -
     * password verification always goes through AuthService against the
     * related credential UserAccount row.
     */
    public function getAuthPassword(): string
    {
        return '';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, UserRole::ADMIN_ROLES, true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SUPER_ADMIN;
    }

    public function isUser(): bool
    {
        return $this->role === UserRole::USER;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::ACTIVE;
    }

    /**
     * Mirrors the legacy App\Models\User::hasPermission() so the existing
     * App\Services\PermissionService (route-key tree checked against the
     * comma-joined `permission` string) keeps working unchanged for the
     * new admin session guard - the column format is identical between
     * the legacy `user` table and this one.
     */
    public function hasPermission($permission = ''): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return (new PermissionService)->hasPermission($permission, $this->permission);
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(UserAccount::class, 'user_id');
    }

    public function credentialAccount(): HasOne
    {
        return $this->hasOne(UserAccount::class, 'user_id')->where('provider_id', 'credential');
    }

    public function activities(): HasMany
    {
        return $this->hasMany(UserActivity::class, 'user_id');
    }

    public function devices(): HasMany
    {
        return $this->hasMany(UserDevice::class, 'user_id');
    }

    public function loginLinks(): HasMany
    {
        return $this->hasMany(UserLoginLink::class, 'user_id');
    }

    public function passkeys(): HasMany
    {
        return $this->hasMany(UserPasskey::class, 'user_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class, 'user_id');
    }

    public function twoFactor(): HasOne
    {
        return $this->hasOne(UserTwoFactor::class, 'user_id');
    }
    
    
}
