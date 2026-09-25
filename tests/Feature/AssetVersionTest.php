<?php

namespace Tests\Feature;

use App\Helpers\General;
use Tests\TestCase;

class AssetVersionTest extends TestCase
{
    public function test_layout_scripts_are_absolute_and_versioned_so_a_cached_copy_cannot_go_stale(): void
    {
        foreach (['/', '/login', '/admin/auth/login'] as $path) {
            $html = $this->get($path)->assertOk()->getContent();

            foreach (['app.js', 'pjax.js'] as $script) {
                $this->assertMatchesRegularExpression(
                    '#<script src="https?://[^"]+/assets/js/'.preg_quote($script, '#').'\?v=\d+"#',
                    $html,
                    "$path: $script must be an absolute, versioned URL (relative ones break under index.php/ and are cached for hours)"
                );
            }
        }
    }

    public function test_the_version_changes_with_the_file(): void
    {
        $file = public_path('assets/images/no-image.svg');
        $url = app(General::class)->assetUrl('assets/images/no-image.svg');

        $this->assertStringEndsWith('?v='.filemtime($file), $url);
        $this->assertSame(asset('assets/nope.js'), app(General::class)->assetUrl('assets/nope.js'), 'a missing file gets no version');
    }

    public function test_every_referenced_layout_script_exists(): void
    {
        foreach (glob(resource_path('views/{layouts,modules/admin/layouts}/*.blade.php'), GLOB_BRACE) as $layout) {
            preg_match_all("#assetUrl\('(assets/js/[^']+)'\)#", file_get_contents($layout), $matches);

            foreach ($matches[1] as $script) {
                $this->assertFileExists(public_path($script), basename($layout).' references a missing script');
            }
        }
    }

    public function test_no_view_loads_a_script_relative_to_the_base_href(): void
    {
        $offenders = [];

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator(resource_path('views'))) as $file) {
            if ($file->isFile() && preg_match('#<script[^>]+src="(?!https?:|//|\{\{)[^"]#', file_get_contents($file->getPathname()))) {
                $offenders[] = str_replace(resource_path('views').'/', '', $file->getPathname());
            }
        }

        $this->assertSame([], $offenders, 'use {{ $general->assetUrl(...) }} for local scripts');
    }
}
