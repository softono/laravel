<?php

namespace App\Constants;

/**
 * `user_activities.type` stores the KEY (left side), never the label.
 */
final class UserActivity
{
    public const LOGIN_FAILED = 'LOGIN_FAILED';

    public const LOGIN_SUCCESS = 'LOGIN_SUCCESS';

    public const REGISTER = 'REGISTER';

    public const LOGIN_WITH_OTP = 'LOGIN_WITH_OTP';

    public const LOGIN_WITH_SOCIAL = 'LOGIN_WITH_SOCIAL';

    public const LOGIN_WITH_LINK = 'LOGIN_WITH_LINK';

    public const REGISTER_WITH_SOCIAL = 'REGISTER_WITH_SOCIAL';

    public const LOGOUT = 'LOGOUT';

    public const ACCOUNT_DEACTIVATE = 'ACCOUNT_DEACTIVATE';

    public const EMAIL_UPDATE = 'EMAIL_UPDATE';

    public const PASSWORD_CHANGED = 'PASSWORD_CHANGED';

    public const ACCOUNT_UPDATE = 'ACCOUNT_UPDATE';

    public const DEVICE_LOGGED_OUT = 'DEVICE_LOGGED_OUT';

    public const IMAGE_UPLOADED = 'IMAGE_UPLOADED';

    public const PASSWORD_SET = 'PASSWORD_SET';

    public const PASSKEY_ADDED = 'PASSKEY_ADDED';

    public const PASSKEY_DELETED = 'PASSKEY_DELETED';

    public const BACKUP_CODES_REGENERATED = 'BACKUP_CODES_REGENERATED';

    public const TFA_ENABLED = 'TFA_ENABLED';

    public const TFA_DISABLED = 'TFA_DISABLED';

    public const TFA_AUTHENTICATOR_REMOVED = 'TFA_AUTHENTICATOR_REMOVED';

    // Admin activity
    public const SETTING_UPDATE = 'SETTING_UPDATE';

    public const ADMIN_UPDATE = 'ADMIN_UPDATE';

    public const USER_UPDATE = 'USER_UPDATE';

    public const DATA_UPDATE = 'DATA_UPDATE';

    /** @var array<string,string> key => human label, for the activity log UI */
    public const LABELS = [
        self::LOGIN_FAILED => 'Login Fail',
        self::LOGIN_SUCCESS => 'Login Success',
        self::REGISTER => 'Register',
        self::LOGIN_WITH_OTP => 'Login With Otp',
        self::LOGIN_WITH_SOCIAL => 'Login With Social Media',
        self::LOGIN_WITH_LINK => 'Login With Magic Link',
        self::REGISTER_WITH_SOCIAL => 'Register With Social Media',
        self::LOGOUT => 'Logout',
        self::ACCOUNT_DEACTIVATE => 'Account deactivate',
        self::EMAIL_UPDATE => 'Email update',
        self::PASSWORD_CHANGED => 'password changed',
        self::ACCOUNT_UPDATE => 'Account update',
        self::DEVICE_LOGGED_OUT => 'device logged out',
        self::IMAGE_UPLOADED => 'Image uploaded',
        self::PASSWORD_SET => 'Password set',
        self::PASSKEY_ADDED => 'Passkey added',
        self::PASSKEY_DELETED => 'Passkey deleted',
        self::BACKUP_CODES_REGENERATED => 'Backup codes regenerated',
        self::TFA_ENABLED => 'TFA enabled',
        self::TFA_DISABLED => 'TFA disabled',
        self::TFA_AUTHENTICATOR_REMOVED => 'Authenticator app removed',
        self::SETTING_UPDATE => 'Setting update',
        self::ADMIN_UPDATE => 'Admin update',
        self::USER_UPDATE => 'User update',
        self::DATA_UPDATE => 'Data update',
    ];

    public static function label(string $key): string
    {
        return self::LABELS[$key] ?? $key;
    }
}
