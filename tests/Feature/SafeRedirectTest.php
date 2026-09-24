<?php

namespace Tests\Feature;

use App\Helpers\SafeRedirect;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class SafeRedirectTest extends TestCase
{
    #[DataProvider('unsafe')]
    public function test_unsafe_targets_fall_back(?string $raw): void
    {
        $this->assertSame('/dashboard', SafeRedirect::path($raw, '/dashboard'));
    }

    public static function unsafe(): array
    {
        return [
            'null' => [null],
            'empty' => [''],
            'absolute url' => ['https://evil.com'],
            'protocol relative' => ['//evil.com'],
            'backslash' => ['/\\evil.com'],
            'encoded slash' => ['/%2Fevil.com'],
            'encoded backslash' => ['/%5Cevil.com'],
            'relative' => ['evil.com'],
            'control char' => ["/a\nb"],
            'javascript' => ['javascript:alert(1)'],
        ];
    }

    public function test_safe_paths_pass_through(): void
    {
        $this->assertSame('/notes?page=2', SafeRedirect::path('/notes?page=2', '/dashboard'));
        $this->assertSame('/admin/dashboard', SafeRedirect::path('/admin', '/x'));
    }
}
