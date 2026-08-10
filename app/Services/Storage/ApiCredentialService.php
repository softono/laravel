<?php

namespace App\Services\Storage;

use App\Models\Auth\User;
use App\Models\Storage\ApiKey;
use App\Repositories\Storage\ApiKeyRepository;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

/**
 * The secret key is only ever returned to the caller at creation /
 * regeneration time. It is stored encrypted at rest (Crypt, backed by
 * APP_KEY) rather than one-way hashed like a password: HMAC request
 * verification (see Request Signing (v1) in docs/local/prd.md) needs the
 * plaintext secret back server-side to recompute the signature, which a
 * one-way hash could never provide.
 */
class ApiCredentialService
{
    public function __construct(
        protected ApiKeyRepository $apiKeys,
    ) {}

    /**
     * @return array{status: int, message: string, api_user?: ApiKey, secret_key?: string}
     */
    public function create(User $user, ?string $title = null, ?int $bucketId = null): array
    {
        $accessKey = 'AK'.strtoupper(Str::random(18));
        $secretKey = Str::random(40);

        $apiKey = $this->apiKeys->create([
            'user_id' => $user->id,
            'title' => $title,
            'bucket_id' => $bucketId,
            'access_key' => $accessKey,
            'secret_key' => Crypt::encryptString($secretKey),
            'status' => 'active',
        ]);

        return [
            'status' => 1,
            'message' => 'API credential created successfully. Copy the secret key now - it will not be shown again.',
            'api_user' => $apiKey,
            'secret_key' => $secretKey,
        ];
    }

    /**
     * @return array{status: int, message: string, secret_key?: string}
     */
    public function regenerateSecret(ApiKey $apiKey): array
    {
        $secretKey = Str::random(40);
        $this->apiKeys->update($apiKey, ['secret_key' => Crypt::encryptString($secretKey)]);

        return [
            'status' => 1,
            'message' => 'Secret key regenerated. Copy it now - it will not be shown again.',
            'secret_key' => $secretKey,
        ];
    }

    public function toggleStatus(ApiKey $apiKey): array
    {
        $apiKey->update(['status' => $apiKey->isActive() ? 'inactive' : 'active']);

        return ['status' => 1, 'message' => 'API credential status updated.'];
    }

    public function delete(ApiKey $apiKey): array
    {
        $this->apiKeys->delete($apiKey);

        return ['status' => 1, 'message' => 'API credential deleted.'];
    }
}
