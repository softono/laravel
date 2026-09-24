<?php

namespace Tests\Feature;

use App\Http\Middleware\AuthRateLimit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AuthRateLimitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // In-memory store: the test must not write counters into the real cache table.
        config(['cache.default' => 'array']);
        RateLimiter::clear('register:auth/register:203.0.113.50');
    }

    private function hit(string $ip)
    {
        $request = Request::create('/auth/register', 'POST', server: ['REMOTE_ADDR' => $ip]);
        $request->headers->set('Accept', 'application/json');

        return (new AuthRateLimit)->handle($request, fn () => response()->json(['status' => 1]), 'register');
    }

    public function test_blocks_after_the_limit_with_the_envelope_and_retry_headers(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->assertSame(200, $this->hit('203.0.113.50')->getStatusCode());
        }

        $blocked = $this->hit('203.0.113.50');

        $this->assertSame(429, $blocked->getStatusCode());
        $this->assertSame(0, json_decode($blocked->getContent(), true)['status']);
        $this->assertSame('5', $blocked->headers->get('X-RateLimit-Limit'));
        $this->assertNotNull($blocked->headers->get('Retry-After'));
    }

    public function test_limits_are_per_client_ip(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $this->hit('203.0.113.51');
        }

        $this->assertSame(429, $this->hit('203.0.113.51')->getStatusCode());
        $this->assertSame(200, $this->hit('203.0.113.52')->getStatusCode());
    }
}
