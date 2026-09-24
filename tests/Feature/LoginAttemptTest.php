<?php

namespace Tests\Feature;

use App\Modules\Auth\Services\LoginAttemptService;
use Tests\TestCase;

class LoginAttemptTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['cache.default' => 'array']);
    }

    public function test_captcha_then_lockout_thresholds(): void
    {
        $attempts = new LoginAttemptService;

        foreach (range(1, LoginAttemptService::RECAPTCHA_THRESHOLD - 1) as $ignored) {
            $attempts->recordFailure('User@Example.com');
        }
        $this->assertFalse($attempts->needsCaptcha('user@example.com'));

        $attempts->recordFailure('user@example.com');
        $this->assertTrue($attempts->needsCaptcha('USER@example.com'));
        $this->assertFalse($attempts->isLocked('user@example.com'));

        foreach (range(1, LoginAttemptService::LOCKOUT_THRESHOLD - LoginAttemptService::RECAPTCHA_THRESHOLD) as $ignored) {
            $attempts->recordFailure('user@example.com');
        }
        $this->assertTrue($attempts->isLocked('user@example.com'));
    }

    public function test_success_clears_the_counter(): void
    {
        $attempts = new LoginAttemptService;

        foreach (range(1, LoginAttemptService::LOCKOUT_THRESHOLD) as $ignored) {
            $attempts->recordFailure('a@b.co');
        }
        $attempts->clear('a@b.co');

        $this->assertFalse($attempts->isLocked('a@b.co'));
        $this->assertSame(0, $attempts->failureCount('a@b.co'));
    }
}
