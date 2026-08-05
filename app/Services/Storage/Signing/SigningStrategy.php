<?php

namespace App\Services\Storage\Signing;

use Illuminate\Http\Request;

/**
 * Swappable request-signing strategy - see Request Signing (v1) in
 * docs/local/prd.md. The v1 HMAC scheme is one implementation; a future
 * AWS SigV4 strategy can be added alongside it (see SigV4Strategy stub)
 * without touching StorageApiAuth, which only depends on this interface.
 */
interface SigningStrategy
{
    /**
     * Whether this strategy recognises the request's auth shape (its
     * header or query parameters), before any verification happens.
     */
    public function supports(Request $request): bool;

    /**
     * The access key the request claims, extracted but not yet verified.
     */
    public function accessKey(Request $request): ?string;

    /**
     * Verifies the request was actually signed with $secretKey.
     *
     * @return array{ok: bool, message: ?string}
     */
    public function verify(Request $request, string $secretKey): array;
}
