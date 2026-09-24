<?php

namespace App\Modules\User\Services;

use App\Constants\UserActivity;
use App\Helpers\ClientInfo;
use App\Helpers\General;
use App\Models\Auth\User;
use App\Models\Auth\UserSession;
use App\Modules\Auth\Services\SessionService;
use App\Repositories\Auth\UserSessionRepository;
use App\Services\ActivityService;
use Illuminate\Http\Request;

/**
 * Session (device) lists for the account area and the admin panel, and
 * signing a session out from them.
 */
class SessionListService
{
    public function __construct(
        protected UserSessionRepository $userSessions,
        protected SessionService $sessions,
        protected ActivityService $activity,
        protected General $general,
    ) {}

    /** DataTables payload for the signed-in user's own sessions. */
    public function forUser(Request $request, User $user, array $post): array
    {
        return $this->present($request, $this->userSessions->datatableForUser($user->id, $post), 'account/session-logout');
    }

    /** DataTables payload for every user's sessions (admin). */
    public function all(Request $request, array $post): array
    {
        return $this->present($request, $this->userSessions->datatableAll($post), 'admin/device/logout');
    }

    /**
     * Signs a session out. Users may only end their own; a session cannot end itself,
     * because that is what logging out is for.
     *
     * @return array{ok: bool, message: string}
     */
    public function logout(Request $request, User $actor, string $sessionId, bool $anyUser = false): array
    {
        $session = $anyUser
            ? $this->userSessions->findById($sessionId)
            : $this->userSessions->findForUser($actor->id, $sessionId);

        if (! $session) {
            return ['ok' => false, 'message' => 'Session not found'];
        }

        if ($session->id === $this->currentSession()?->id) {
            return ['ok' => false, 'message' => 'Cannot log out the current device'];
        }

        $this->sessions->revokeSession($session);
        $this->activity->log($request, $actor->id, UserActivity::DEVICE_LOGGED_OUT, ['session_user_id' => $session->user_id]);

        return ['ok' => true, 'message' => 'Device logged out successfully'];
    }

    protected function present(Request $request, array $result, string $logoutRoute): array
    {
        $currentId = $this->currentSession()?->id;

        $result['data'] = $result['data']->map(function ($row) use ($currentId, $logoutRoute) {
            $isCurrent = $row->id === $currentId;
            $row->first_name = trim(($row->first_name ?? '').' '.($row->last_name ?? ''));
            $row->client = ClientInfo::deviceNameFor($row->client).($isCurrent ? ' (This device)' : '');
            $row->location = $row->ip ?? '';
            $row->last_activity = $this->general->dateFormat($row->last_activity);
            $row->action = $isCurrent ? '' : view('modules.user.partials.session-action', ['id' => $row->id, 'route' => $logoutRoute])->render();

            return $row;
        })->all();

        return $result;
    }

    protected function currentSession(): ?UserSession
    {
        return auth()->guard()->session();
    }
}
