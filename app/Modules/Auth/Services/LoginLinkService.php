<?php

namespace App\Modules\Auth\Services;

use App\Constants\UserActivity;
use App\Helpers\ApiResult;
use App\Helpers\ClientInfo;
use App\Helpers\General;
use App\Models\Auth\User;
use App\Models\Auth\UserLoginLink;
use App\Repositories\Auth\UserLoginLinkRepository;
use App\Repositories\Auth\UserRepository;
use App\Services\ActivityService;
use App\Services\IpLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Magic login link with second-device (or same-device) approval. Used for
 * sign-in (purpose 'signin') and as a 2FA method (purpose 'tfa': approving
 * consumes the pending 2FA challenge and issues the session).
 */
class LoginLinkService
{
    public function __construct(
        protected SessionService $sessions,
        protected ActivityService $activity,
        protected DeviceService $devices,
        protected ChallengeService $challenges,
        protected UserRepository $users,
        protected UserLoginLinkRepository $userLoginLinks,
    ) {}

    /**
     * `data`: request_id, expires_at, poll_token, code.
     *
     * @return array{http_status: int, status: int, message: string, data: array<string, string>}
     */
    public function start(Request $request, string $email, bool $remember, bool $trustDevice): array
    {
        $email = strtolower(trim($email));
        $user = $this->users->findByEmail($email);
        $expiresAt = now()->addSeconds((int) config('auth_next.login_link_expire_sec'));

        // Unknown email: fabricate a requestId AND a plausible-looking code,
        // write nothing, return an identical shape - enumeration-safe (the
        // response shape must not reveal whether the email exists, so even
        // the code field must be populated).
        if (! $user) {
            return ApiResult::success('Login link sent', [
                'request_id' => (string) Str::uuid(),
                'expires_at' => $expiresAt->toIso8601String(),
                'poll_token' => bin2hex(random_bytes(32)),
                'code' => str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            ]);
        }

        return ApiResult::success('Login link sent', $this->createLink($request, $user, [
            'purpose' => 'signin',
            'remember' => $remember,
            'trust_device' => $trustDevice,
        ]));
    }

    /**
     * Sends a login link as the second factor of a pending 2FA challenge.
     *
     * @return array{http_status: int, status: int, message: string, data: array<string, string>}
     */
    public function startTfa(Request $request, string $tfaHandle, bool $trustDevice): array
    {
        $pending = $this->challenges->peekTfa($tfaHandle);
        $user = $pending ? $this->users->findById($pending['user_id']) : null;

        if (! $user) {
            return ApiResult::failure('Challenge expired', [], 401);
        }

        return ApiResult::success('Login link sent', $this->createLink($request, $user, [
            'purpose' => 'tfa',
            'remember' => $pending['remember'],
            'trust_device' => $trustDevice,
            'tfa_handle' => $tfaHandle,
        ]));
    }

    /**
     * @param  array{purpose: string, remember: bool, trust_device: bool, tfa_handle?: string}  $attributes
     * @return array{request_id: string, expires_at: string, poll_token: string, code: string}
     */
    protected function createLink(Request $request, User $user, array $attributes): array
    {
        $expiresAt = now()->addSeconds((int) config('auth_next.login_link_expire_sec'));
        $pollToken = bin2hex(random_bytes(32));
        $linkToken = bin2hex(random_bytes(32));
        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $link = $this->userLoginLinks->create($attributes + [
            'email' => $user->email,
            'user_id' => $user->id,
            'poll_token_hash' => Hash::make($pollToken),
            'link_token_hash' => Hash::make($linkToken),
            'code' => $code,
            'status' => 'pending',
            'device_name' => ClientInfo::deviceName($request),
            'ip' => ClientInfo::ip($request),
            'location' => app(IpLocationService::class)->lookup(ClientInfo::ip($request)),
            'expires_at' => $expiresAt,
        ]);

        $approveUrl = rtrim(config('app.url'), '/').'/login/approve?id='.$link->id.'&token='.$linkToken;

        (new General)->sendEmail($user->email, 'login-link', [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'link' => $approveUrl,
            'message' => $attributes['purpose'] === 'tfa' ? 'login verification' : 'login',
            'code' => $code,
        ]);

        return [
            'request_id' => $link->id,
            'expires_at' => $expiresAt->toIso8601String(),
            'poll_token' => $pollToken,
            'code' => $code,
        ];
    }

    /**
     * `data.state` is what the page sees. On approval `data` also carries `session_token`, `remember`
     * and `tfa` for the controller, which sets the cookie and must strip them before responding.
     *
     * @return array{http_status: int, status: int, message: string, data: array<string, mixed>}
     */
    public function poll(Request $request, string $requestId, string $pollToken): array
    {
        $link = $this->userLoginLinks->findById($requestId);

        if (! $link || ! Hash::check($pollToken, $link->poll_token_hash)) {
            return ApiResult::success('', ['state' => 'pending']);
        }

        if ($link->expires_at->isPast() && $link->status === 'pending') {
            $link->update(['status' => 'expired']);
        }

        if ($link->status === 'pending') {
            return ApiResult::success('', ['state' => 'pending']);
        }

        if ($link->status === 'rejected') {
            return ApiResult::success('', ['state' => 'rejected']);
        }

        if ($link->status === 'expired') {
            return ApiResult::success('', ['state' => 'expired']);
        }

        if ($link->status === 'approved') {
            // Conditional update: only the FIRST poll to observe 'approved'
            // successfully claims it (single-use).
            $claimed = $this->userLoginLinks->claimApproved($link->id);

            if ($claimed === 0) {
                // Someone else's poll already claimed it in this same instant.
                return ApiResult::success('', ['state' => 'pending']);
            }

            $result = $this->finalizeApprovedLogin($request, $link->fresh());

            // The 2FA challenge this link was answering has expired or was already used.
            if (! $result) {
                return ApiResult::success('', ['state' => 'expired']);
            }

            return ApiResult::success('', ['state' => 'approved'] + $result);
        }

        // 'consumed' - already claimed by an earlier poll from this same browser.
        return ApiResult::success('', ['state' => 'pending']);
    }

    /** @return array{session_token: string, remember: bool, tfa: bool}|null null when a 2FA challenge is no longer pending */
    protected function finalizeApprovedLogin(Request $request, UserLoginLink $link): ?array
    {
        $isTfa = $link->purpose === 'tfa';

        if ($isTfa && ! ($link->tfa_handle && $this->challenges->consumeTfa($link->tfa_handle))) {
            return null;
        }

        $user = $link->user;

        if ($link->trust_device) {
            $this->devices->trust($request, $user->id);
        }

        $session = $this->sessions->issue($request, $user->id, $link->remember);
        $this->activity->log($request, $user->id, $isTfa ? UserActivity::LOGIN_SUCCESS : UserActivity::LOGIN_WITH_LINK);

        return ['session_token' => $session->token, 'remember' => (bool) $link->remember, 'tfa' => $isTfa];
    }

    public function approvalInfo(string $id, string $token): array
    {
        $link = $this->userLoginLinks->findById($id);

        if (! $link || ! Hash::check($token, $link->link_token_hash)) {
            return ApiResult::failure('This login request is no longer valid', [], 400);
        }

        if ($link->expires_at->isPast() && $link->status === 'pending') {
            $link->update(['status' => 'expired']);
        }

        if ($link->status !== 'pending') {
            return ApiResult::failure('This login request is no longer valid', [], 400);
        }

        return ApiResult::success('', [
            'device_name' => $link->device_name,
            'location' => $link->location,
            'code' => $link->code,
            'email' => $link->email,
        ]);
    }

    public function respond(string $id, string $token, string $action): array
    {
        $link = $this->userLoginLinks->findById($id);

        if (! $link || ! Hash::check($token, $link->link_token_hash)) {
            return ApiResult::failure('This login request is no longer valid');
        }

        if ($link->expires_at->isPast()) {
            $link->update(['status' => 'expired']);

            return ApiResult::failure('This login request has expired');
        }

        if ($link->status !== 'pending') {
            return ApiResult::failure('This login request has already been handled');
        }

        if ($action === 'approve') {
            $link->update(['status' => 'approved', 'approved_at' => now()]);

            return ApiResult::success('Login approved');
        }

        $link->update(['status' => 'rejected']);

        return ApiResult::success('Login rejected');
    }
}
