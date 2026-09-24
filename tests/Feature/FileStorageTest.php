<?php

namespace Tests\Feature;

use App\Helpers\General;
use App\Helpers\ImgProxy;
use App\Services\FileStorageService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class FileStorageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'filesystems.disks.test_public' => ['driver' => 'local', 'root' => storage_path('framework/testing/public'), 'url' => 'https://files.test/upload'],
            'filesystems.disks.test_private' => ['driver' => 'local', 'root' => storage_path('framework/testing/private')],
            'files.public_disk' => 'test_public',
            'files.private_disk' => 'test_private',
            'files.imgproxy.enabled' => false,
        ]);
    }

    protected function tearDown(): void
    {
        Storage::disk('test_public')->deleteDirectory('');
        Storage::disk('test_private')->deleteDirectory('');

        parent::tearDown();
    }

    public function test_public_types_get_a_plain_url_and_private_types_a_file_route(): void
    {
        $files = app(FileStorageService::class);

        $this->assertFalse($files->isPrivate('profile'));
        $this->assertTrue($files->isPrivate('documents'));
        $this->assertSame('https://files.test/upload/profile/a.png', $files->url('a.png', 'profile'));

        $private = $files->url('2026/09/a.pdf', 'documents');
        $this->assertStringContainsString('/file?p='.rawurlencode(base64_encode('documents/2026/09/a.pdf')), $private);
    }

    public function test_upload_goes_to_the_disk_of_its_visibility(): void
    {
        $files = app(FileStorageService::class);

        $public = $files->store(UploadedFile::fake()->image('avatar.png'), 'profile');
        $this->assertSame(1, $public['status']);
        Storage::disk('test_public')->assertExists('profile/'.$public['data']['file_name']);

        $private = $files->store(UploadedFile::fake()->create('paper.pdf', 10, 'application/pdf'), 'documents', 'date');
        $this->assertSame(1, $private['status']);
        Storage::disk('test_private')->assertExists('documents/'.$private['data']['file_name']);
        Storage::disk('test_public')->assertMissing('documents/'.$private['data']['file_name']);
    }

    /** A real file on disk: Laravel's fake files report the MIME type from their name, which is what these tests must not trust. */
    private function realUpload(string $name, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'upl');
        file_put_contents($path, $content);

        return new UploadedFile($path, $name, null, null, true);
    }

    public function test_extension_comes_from_the_content_not_the_client_name(): void
    {
        $result = app(FileStorageService::class)->store(UploadedFile::fake()->image('shell.php.png'), 'profile');
        $this->assertStringEndsWith('.png', $result['data']['file_name']);

        $stored = app(FileStorageService::class)->store($this->realUpload('evil.png', '<?php echo 1;'), 'profile');
        $this->assertStringEndsNotWith('.png', $stored['data']['file_name'] ?? '');
    }

    public function test_upload_rules_reject_svg_and_scripts(): void
    {
        $rule = app(General::class)->fileRules('image');
        $check = fn ($file) => Validator::make(['f' => $file], ['f' => $rule])->passes();

        $this->assertTrue($check(UploadedFile::fake()->image('ok.jpg')));
        $this->assertFalse($check($this->realUpload('x.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>')));
        $this->assertFalse($check($this->realUpload('x.png', '<?php echo 1;')));
        $this->assertFalse(Validator::make(['f' => UploadedFile::fake()->image('big.jpg')->size(2048)], ['f' => $rule])->passes());
    }

    public function test_imgproxy_urls_are_signed_like_next(): void
    {
        config(['files.imgproxy' => ['enabled' => true, 'key' => bin2hex('key'), 'salt' => bin2hex('salt'), 'url' => 'https://img.test/']]);

        $source = 'https://files.test/upload/profile/a.png';
        $encoded = rtrim(strtr(base64_encode($source), '+/', '-_'), '=');
        $path = '/rs:fill:64:64/'.$encoded;
        $signature = rtrim(strtr(base64_encode(hash_hmac('sha256', 'salt'.$path, 'key', true)), '+/', '-_'), '=');

        $this->assertSame("https://img.test/$signature$path", ImgProxy::url($source, 'rs:fill:64:64'));
        // Only images go through imgproxy.
        $this->assertSame('https://files.test/a.pdf', ImgProxy::url('https://files.test/a.pdf'));
        // Broken key material falls back to the original.
        config(['files.imgproxy.key' => 'not-hex']);
        $this->assertSame($source, ImgProxy::url($source));
    }

    public function test_general_falls_back_to_the_placeholder_for_missing_local_files(): void
    {
        $general = app(General::class);

        $this->assertSame('https://files.test/upload/no-image.jpg', $general->getFileUrl('missing.png', 'profile'));
        $this->assertSame('https://files.test/upload/no-image.jpg', $general->getFileUrl(null, 'profile'));

        Storage::disk('test_public')->put('profile/here.png', 'x');
        $this->assertSame('https://files.test/upload/profile/here.png', $general->getFileUrl('here.png', 'profile'));
    }
}
