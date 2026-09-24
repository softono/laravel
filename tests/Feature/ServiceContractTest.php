<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

/** Services answer with Next's ApiResult shape (see App\Helpers\ApiResult); the old ad-hoc shapes must not return. */
class ServiceContractTest extends TestCase
{
    public function test_no_service_returns_ok_or_valid_arrays(): void
    {
        $offenders = [];

        foreach (File::allFiles(app_path()) as $file) {
            if (! str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'Services'.DIRECTORY_SEPARATOR)) {
                continue;
            }

            if (preg_match("/'(ok|valid)'\s*=>/", File::get($file->getPathname()))) {
                $offenders[] = $file->getRelativePathname();
            }
        }

        $this->assertSame([], $offenders, 'Services must return ApiResult::success()/failure(), not ok/valid arrays.');
    }

    public function test_controllers_do_not_read_the_old_ok_key(): void
    {
        foreach (File::allFiles(app_path('Modules')) as $file) {
            if (str_ends_with($file->getFilename(), 'Controller.php')) {
                $this->assertStringNotContainsString("['ok']", File::get($file->getPathname()), $file->getFilename());
            }
        }
    }
}
