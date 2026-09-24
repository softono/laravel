<?php

namespace App\Modules\Auth\Services;

use App\Constants\UserActivity;
use App\Helpers\SignedCookie;
use App\Models\Auth\User;
use App\Models\Auth\UserPasskey;
use App\Repositories\Auth\UserPasskeyRepository;
use App\Repositories\Auth\UserRepository;
use App\Services\ActivityService;
use Cose\Algorithm\Manager as AlgorithmManager;
use Cose\Algorithm\Signature\ECDSA\ES256;
use Cose\Algorithm\Signature\RSA\RS256;
use Illuminate\Http\Request;
use Symfony\Component\Uid\Uuid;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\CredentialRecord;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialDescriptor;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRequestOptions;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\TrustPath\EmptyTrustPath;

/**
 * WebAuthn passkey registration and login, built on web-auth/webauthn-lib
 * v5. rpID = hostname of APP_URL, attestationType 'none', residentKey and
 * userVerification both 'preferred', login omits allowCredentials
 * (discoverable credentials - no email needed).
 */
class PasskeyService
{
    public function __construct(
        protected SessionService $sessions,
        protected DeviceService $devices,
        protected ActivityService $activity,
        protected UserRepository $users,
        protected UserPasskeyRepository $userPasskeys,
    ) {}

    protected function rpId(): string
    {
        return parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';
    }

    protected function attestationSupportManager(): AttestationStatementSupportManager
    {
        return new AttestationStatementSupportManager([new NoneAttestationStatementSupport]);
    }

    protected function serializer()
    {
        return (new WebauthnSerializerFactory($this->attestationSupportManager()))->create();
    }

    protected function ceremonyFactory(): CeremonyStepManagerFactory
    {
        $factory = new CeremonyStepManagerFactory;
        $factory->setAlgorithmManager(AlgorithmManager::create()->add(ES256::create(), RS256::create()));
        $factory->setAllowedOrigins([rtrim(config('app.url'), '/')]);

        return $factory;
    }

    // --- Registration (authenticated) -------------------------------------------------

    /** @return array{options: array, challenge_handle: string} */
    public function registerOptions(User $user): array
    {
        $challenge = random_bytes(32);

        $excludeCredentials = $this->userPasskeys->getByUserId($user->id)->map(
            fn (UserPasskey $p) => PublicKeyCredentialDescriptor::create(
                'public-key',
                SignedCookie::base64UrlDecode($p->credential_id),
                $p->transports ? explode(',', $p->transports) : [],
            )
        )->all();

        $options = PublicKeyCredentialCreationOptions::create(
            rp: PublicKeyCredentialRpEntity::create(config('setting.app_name') ?: config('app.name'), $this->rpId()),
            user: PublicKeyCredentialUserEntity::create($user->email, $user->id, $user->first_name.' '.$user->last_name),
            challenge: $challenge,
            pubKeyCredParams: [
                PublicKeyCredentialParameters::create('public-key', -7),  // ES256
                PublicKeyCredentialParameters::create('public-key', -257), // RS256
            ],
            authenticatorSelection: AuthenticatorSelectionCriteria::create(
                userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
                residentKey: AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_PREFERRED,
            ),
            attestation: 'none',
            excludeCredentials: $excludeCredentials,
        );

        $handle = app(ChallengeService::class)->createWebAuthn(SignedCookie::base64UrlEncode($challenge), $user->id);
        SignedCookie::queue('wac', $handle, (int) config('auth_next.webauthn_ttl'));

        return [
            'options' => json_decode($this->serializer()->serialize($options, 'json'), true),
        ];
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function registerVerify(Request $request, User $user, array $credentialJson, ?string $name = null): array
    {
        $challengeHandle = $request->cookie(SignedCookie::name('wac'));
        $signed = $challengeHandle ? SignedCookie::verify($challengeHandle) : null;
        $challengeData = $signed ? app(ChallengeService::class)->consumeWebAuthn($signed) : null;

        if (! $challengeData || $challengeData['user_id'] !== $user->id) {
            return ['ok' => false, 'message' => 'Registration session expired. Please try again.'];
        }

        $credential = $this->serializer()->deserialize(json_encode($credentialJson), PublicKeyCredential::class, 'json');

        if (! $credential->response instanceof AuthenticatorAttestationResponse) {
            return ['ok' => false, 'message' => 'Invalid registration response.'];
        }

        $options = PublicKeyCredentialCreationOptions::create(
            rp: PublicKeyCredentialRpEntity::create(config('setting.app_name') ?: config('app.name'), $this->rpId()),
            user: PublicKeyCredentialUserEntity::create($user->email, $user->id, $user->first_name.' '.$user->last_name),
            challenge: SignedCookie::base64UrlDecode($challengeData['challenge']),
            attestation: 'none',
        );

        try {
            $csm = $this->ceremonyFactory()->creationCeremony();
            $record = AuthenticatorAttestationResponseValidator::create($csm)->check(
                $credential->response,
                $options,
                $this->rpId(),
            );
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Passkey registration failed: '.$e->getMessage()];
        }

        $this->userPasskeys->create([
            'name' => $name ?: 'Passkey',
            'public_key' => SignedCookie::base64UrlEncode($record->credentialPublicKey),
            'user_id' => $user->id,
            'credential_id' => SignedCookie::base64UrlEncode($record->publicKeyCredentialId),
            'counter' => $record->counter,
            'device_type' => ($record->backupEligible ?? false) ? 'multiDevice' : 'singleDevice',
            'backed_up' => (bool) ($record->backupStatus ?? false),
            'transports' => $record->transports ? implode(',', $record->transports) : null,
            'aaguid' => (string) $record->aaguid,
            'created_at' => now(),
        ]);

        $this->activity->log($request, $user->id, UserActivity::PASSKEY_ADDED);

        return ['ok' => true, 'message' => 'Passkey added successfully'];
    }

    public function list(User $user)
    {
        return $this->userPasskeys->getByUserId($user->id);
    }

    public function delete(Request $request, User $user, string $id): array
    {
        $passkey = UserPasskey::where('user_id', $user->id)->where('id', $id)->first();

        if (! $passkey) {
            return ['ok' => false, 'message' => 'Passkey not found'];
        }

        $this->userPasskeys->delete($passkey);
        $this->activity->log($request, $user->id, UserActivity::PASSKEY_DELETED);

        return ['ok' => true, 'message' => 'Passkey removed'];
    }

    // --- Login (public - discoverable credentials, no email needed) -------------------

    /** @return array{options: array} */
    public function loginOptions(): array
    {
        $challenge = random_bytes(32);

        $options = PublicKeyCredentialRequestOptions::create(
            challenge: $challenge,
            rpId: $this->rpId(),
            allowCredentials: [], // discoverable - no allowCredentials needed
            userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
        );

        $handle = app(ChallengeService::class)->createWebAuthn(SignedCookie::base64UrlEncode($challenge), null);
        SignedCookie::queue('wac', $handle, (int) config('auth_next.webauthn_ttl'));

        return ['options' => json_decode($this->serializer()->serialize($options, 'json'), true)];
    }

    /**
     * @return array{ok: bool, message: string}
     */
    public function loginVerify(Request $request, array $credentialJson): array
    {
        $challengeHandle = $request->cookie(SignedCookie::name('wac'));
        $signed = $challengeHandle ? SignedCookie::verify($challengeHandle) : null;
        $challengeData = $signed ? app(ChallengeService::class)->consumeWebAuthn($signed) : null;

        if (! $challengeData) {
            return ['ok' => false, 'message' => 'Login session expired. Please try again.'];
        }

        $credential = $this->serializer()->deserialize(json_encode($credentialJson), PublicKeyCredential::class, 'json');

        if (! $credential->response instanceof AuthenticatorAssertionResponse) {
            return ['ok' => false, 'message' => 'Invalid passkey response.'];
        }

        $credentialId = SignedCookie::base64UrlEncode($credential->rawId);
        $passkey = $this->userPasskeys->findByCredentialId($credentialId);

        if (! $passkey) {
            return ['ok' => false, 'message' => 'This passkey is not registered.'];
        }

        $user = $this->users->findById($passkey->user_id);

        if (! $user || ! $user->isActive()) {
            return ['ok' => false, 'message' => 'Account is disabled'];
        }

        $record = new CredentialRecord(
            publicKeyCredentialId: SignedCookie::base64UrlDecode($passkey->credential_id),
            type: 'public-key',
            transports: $passkey->transports ? explode(',', $passkey->transports) : [],
            attestationType: 'none',
            trustPath: EmptyTrustPath::create(),
            aaguid: $passkey->aaguid ? Uuid::fromString($passkey->aaguid) : Uuid::v4(),
            credentialPublicKey: SignedCookie::base64UrlDecode($passkey->public_key),
            userHandle: $user->id,
            counter: $passkey->counter,
            backupEligible: $passkey->device_type === 'multiDevice',
            backupStatus: $passkey->backed_up,
        );

        $options = PublicKeyCredentialRequestOptions::create(
            challenge: SignedCookie::base64UrlDecode($challengeData['challenge']),
            rpId: $this->rpId(),
            userVerification: AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_PREFERRED,
        );

        try {
            $csm = $this->ceremonyFactory()->requestCeremony();
            $updated = AuthenticatorAssertionResponseValidator::create($csm)->check(
                $record,
                $credential->response,
                $options,
                $this->rpId(),
                $user->id,
            );
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Passkey verification failed: '.$e->getMessage()];
        }

        $passkey->update(['counter' => $updated->counter]);

        $session = $this->sessions->issue($request, $user->id, true);
        $this->activity->log($request, $user->id, UserActivity::LOGIN_SUCCESS);

        SignedCookie::queueRaw('session_token', $session->token, config('auth_next.session_ttl_days.remember') * 86400);
        SignedCookie::forget('wac');

        return ['ok' => true, 'message' => 'Logged in successfully'];
    }
}
