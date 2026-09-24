<?php

namespace Tests\Feature;

use Tests\TestCase;

/** Read-only checks against the configured database: nothing here writes rows. */
class SiteSmokeTest extends TestCase
{
    public function test_public_pages_render(): void
    {
        foreach (['/', '/blog', '/contact', '/login', '/register', '/password-forgot', '/admin/auth/login'] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_unknown_blog_post_and_inactive_page_are_404(): void
    {
        $this->get('/blog/does-not-exist')->assertNotFound();
        $this->get('/page/does-not-exist')->assertNotFound();
    }

    public function test_signed_out_visitors_are_redirected_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login?redirect=%2Fdashboard');
        $this->get('/notes')->assertRedirect('/login?redirect=%2Fnotes');
        $this->get('/admin/dashboard')->assertRedirect('/admin/auth/login?redirect=%2Fadmin%2Fdashboard');
    }

    public function test_ajax_endpoints_answer_with_the_envelope_when_signed_out(): void
    {
        $this->getJson('/auth/session')
            ->assertStatus(401)
            ->assertExactJson(['status' => 0, 'message' => 'Not authenticated', 'data' => []]);

        $this->postJson('/admin/user/list')
            ->assertStatus(401)
            ->assertJsonPath('status', 0);
    }

    public function test_validation_and_login_failures_use_the_envelope(): void
    {
        $this->postJson('/auth/login', ['email' => 'not-an-email', 'password' => 'x'])
            ->assertStatus(422)
            ->assertJsonPath('status', 0)
            ->assertJsonPath('data', []);

        $this->postJson('/auth/login', ['email' => 'nobody@example.invalid', 'password' => 'wrong-password'])
            ->assertOk()
            ->assertJsonPath('status', 0)
            ->assertJsonPath('message', 'Invalid email or password');
    }

    public function test_health_check_is_up(): void
    {
        $this->get('/up')->assertOk();
    }

    public function test_responses_carry_the_security_headers(): void
    {
        $response = $this->get('/login');

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->assertStringContainsString("frame-ancestors 'none'", $response->headers->get('Content-Security-Policy'));
        $this->assertStringContainsString("object-src 'none'", $response->headers->get('Content-Security-Policy'));
    }
}
