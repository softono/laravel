<?php

namespace Tests\Feature;

use App\Helpers\Encryption;
use RuntimeException;
use Tests\TestCase;

class EncryptionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['auth_next.encryption_key' => 'test-key-of-at-least-thirty-two-chars']);
    }

    public function test_round_trip_uses_a_fresh_iv_each_time(): void
    {
        $first = Encryption::encrypt('smtp-secret');
        $second = Encryption::encrypt('smtp-secret');

        $this->assertNotSame($first, $second);
        $this->assertNotSame('smtp-secret', $first);
        $this->assertSame('smtp-secret', Encryption::decrypt($first));
    }

    public function test_empty_values_pass_through(): void
    {
        $this->assertSame('', Encryption::encrypt(''));
        $this->assertSame('', Encryption::decrypt(''));
    }

    public function test_tampered_ciphertext_is_rejected(): void
    {
        $bytes = base64_decode(Encryption::encrypt('secret'));
        $bytes[strlen($bytes) - 1] = $bytes[strlen($bytes) - 1] ^ "\x01";

        $this->expectException(RuntimeException::class);
        Encryption::decrypt(base64_encode($bytes));
    }

    public function test_a_missing_key_fails_closed(): void
    {
        config(['auth_next.encryption_key' => '']);

        $this->expectException(RuntimeException::class);
        Encryption::encrypt('secret');
    }
}
