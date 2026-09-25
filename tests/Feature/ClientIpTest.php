<?php

namespace Tests\Feature;

use App\Helpers\ClientInfo;
use Illuminate\Http\Request;
use Tests\TestCase;

class ClientIpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Requests made by earlier tests leave Symfony's static trusted-proxy list at `*` (bootstrap/app.php),
        // which would make `$request->ip()` follow X-Forwarded-For here.
        Request::setTrustedProxies([], Request::HEADER_X_FORWARDED_FOR);
    }

    private function request(string $forwarded): Request
    {
        return Request::create('/', 'GET', [], [], [], ['REMOTE_ADDR' => '10.0.0.1', 'HTTP_X_FORWARDED_FOR' => $forwarded]);
    }

    public function test_takes_the_entry_at_the_trusted_hop_from_the_right(): void
    {
        config(['auth_next.trusted_proxy_count' => 1]);
        $this->assertSame('203.0.113.9', ClientInfo::ip($this->request('6.6.6.6, 203.0.113.9')));

        config(['auth_next.trusted_proxy_count' => 2]);
        $this->assertSame('198.51.100.7', ClientInfo::ip($this->request('6.6.6.6, 198.51.100.7, 203.0.113.9')));
    }

    public function test_a_client_prepending_fake_entries_cannot_change_the_result(): void
    {
        config(['auth_next.trusted_proxy_count' => 1]);

        $this->assertSame('203.0.113.9', ClientInfo::ip($this->request('1.1.1.1, 2.2.2.2, 203.0.113.9')));
    }

    public function test_falls_back_to_the_connection_address(): void
    {
        config(['auth_next.trusted_proxy_count' => 3]);

        $this->assertSame('10.0.0.1', ClientInfo::ip($this->request('203.0.113.9')));
    }
}
