<?php

namespace Tests\Feature;

use App\Modules\File\Services\PrivateFileService;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PrivateFileTest extends TestCase
{
    private string $file = 'phpunit-private/note.txt';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::disk('local')->put($this->file, 'hello');
    }

    protected function tearDown(): void
    {
        Storage::disk('local')->deleteDirectory('phpunit-private');
        parent::tearDown();
    }

    public function test_resolves_a_file_inside_the_private_disk(): void
    {
        $path = (new PrivateFileService)->resolve(base64_encode($this->file));

        $this->assertSame(realpath(Storage::disk('local')->path($this->file)), $path);
    }

    public function test_refuses_traversal_and_bad_input(): void
    {
        $service = new PrivateFileService;

        foreach (['../.env', 'phpunit-private/../../.env', '/etc/passwd', 'phpunit-private', 'missing.txt'] as $bad) {
            $this->assertNull($service->resolve(base64_encode($bad)), $bad);
        }

        $this->assertNull($service->resolve('%%%'));
        $this->assertNull($service->resolve(''));
    }

    public function test_the_route_needs_a_session(): void
    {
        $this->get('/file?p='.base64_encode($this->file))->assertRedirect('/login?redirect=%2Ffile');
    }
}
