<?php

namespace Tests\Feature;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

/**
 * The UI is Tailwind + the x-ui components (Next's shadcn classes). These tests fail when
 * Bootstrap markup, attributes or class names creep back into views, scripts or styles.
 */
class NoBootstrapTest extends TestCase
{
    /** Bootstrap-only tokens. Anything that is also valid Tailwind (mb-4, flex, w-full, ...) is deliberately absent. */
    private const CLASS_TOKENS = [
        'btn', 'btn-primary', 'btn-secondary', 'btn-success', 'btn-danger', 'btn-dark', 'btn-outline', 'btn-close', 'btn-icon', 'btn-sm',
        'btn-outline-primary', 'btn-outline-danger', 'form-control', 'form-select', 'form-label', 'form-input', 'form-check',
        'form-check-input', 'form-check-label', 'input-group', 'input-group-text', 'invalid-feedback', 'is-invalid',
        'container-fluid', 'card-body', 'modal-dialog', 'modal-content', 'modal-header', 'modal-body', 'modal-footer',
        'modal-backdrop', 'modal-title', 'alert-success', 'alert-danger', 'alert-warning', 'alert-info', 'alert-dismiss',
        'badge-soft-primary', 'badge-soft-success', 'badge-soft-danger', 'badge-soft-secondary', 'breadcrumb', 'breadcrumb-item',
        'breadcrumb-box', 'dropdown-menu', 'dropdown-item', 'nav-item', 'nav-link', 'navbar', 'navbar-nav', 'spinner-border',
        'd-flex', 'd-none', 'd-grid', 'd-block', 'd-inline', 'text-muted', 'text-danger', 'text-body', 'text-break', 'text-end',
        'text-start', 'bg-light', 'w-100', 'fw-bold', 'fw-semibold', 'fs-6', 'font-monospace', 'icon-base', 'menu-link', 'menu-item',
        'page-item', 'page-link', 'toast-msg',
    ];

    /** @return list<string> */
    private function files(array $dirs, array $extensions): array
    {
        $files = [];

        foreach ($dirs as $dir) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path($dir), FilesystemIterator::SKIP_DOTS));

            foreach ($iterator as $file) {
                $path = $file->getPathname();
                $name = $file->getFilename();

                // Emails use inline styles for mail clients; minified vendor files are not ours.
                if (str_contains($path, '/views/email/') || str_contains($name, '.min.')) {
                    continue;
                }
                if (in_array($file->getExtension(), $extensions, true)) {
                    $files[] = $path;
                }
            }
        }

        $this->assertNotEmpty($files);

        return $files;
    }

    /** @return list<string> */
    private function viewsAndScripts(): array
    {
        return $this->files(['resources/views', 'public/assets/js'], ['php', 'js']);
    }

    public function test_views_and_scripts_have_no_bootstrap_attributes_or_names(): void
    {
        $found = [];

        foreach ($this->viewsAndScripts() as $path) {
            $source = file_get_contents($path);

            foreach (['data-bs-', 'bootstrap', 'col-md-', 'col-lg-', 'col-sm-', 'modal-dialog', 'form-control', 'd-flex', 'btn-primary'] as $needle) {
                if (stripos($source, $needle) !== false) {
                    $found[] = str_replace(base_path().'/', '', $path)." contains '{$needle}'";
                }
            }
        }

        $this->assertSame([], $found);
    }

    public function test_class_attributes_and_script_class_strings_use_no_bootstrap_tokens(): void
    {
        $tokens = implode('|', array_map('preg_quote', self::CLASS_TOKENS));
        $found = [];

        foreach ($this->viewsAndScripts() as $path) {
            $source = file_get_contents($path);

            // class="…", className = '…', addClass('…') and friends: every quoted string that is applied as classes.
            preg_match_all('/(?:class(?:Name)?\s*[=:]\s*|(?:add|remove|toggle|has)Class\(\s*|classList\.\w+\(\s*)(["\'])(.*?)\1/s', $source, $matches);

            foreach ($matches[2] as $classes) {
                foreach (preg_split('/\s+/', trim($classes)) as $class) {
                    if (preg_match('/^(?:'.$tokens.')$/', $class) || preg_match('/^(?:col-(?:xs|sm|md|lg|xl)-\d+|me-\d|ms-\d|ps-\d|pe-\d|fs-\d)$/', $class)) {
                        $found[] = str_replace(base_path().'/', '', $path)." uses class '{$class}'";
                    }
                }
            }
        }

        $this->assertSame([], array_values(array_unique($found)));
    }

    public function test_stylesheets_do_not_define_bootstrap_component_classes(): void
    {
        $found = [];

        foreach ($this->files(['resources/css'], ['css']) as $path) {
            if (preg_match_all('/(?:^|[\s,}])\.(btn(?:-[\w-]+)?|form-(?:control|select|label|input|check[\w-]*)|input-group[\w-]*|modal(?:-[\w-]+)?|alert-\w+|badge-soft-\w+|breadcrumb[\w-]*|card-(?:body|header|title)|page-(?:item|link))\b/m', file_get_contents($path), $m)) {
                $found[] = str_replace(base_path().'/', '', $path).' defines .'.implode(', .', array_unique($m[1]));
            }
        }

        $this->assertSame([], $found);
    }

    public function test_no_bootstrap_assets_or_packages_are_shipped(): void
    {
        $this->assertSame([], glob(base_path('public/assets/*/*bootstrap*')) ?: []);
        $this->assertSame([], glob(base_path('public/assets/*bootstrap*')) ?: []);

        $package = json_decode(file_get_contents(base_path('package.json')), true);
        $dependencies = array_merge($package['dependencies'] ?? [], $package['devDependencies'] ?? []);
        $this->assertSame([], array_values(preg_grep('/bootstrap/i', array_keys($dependencies))));
    }

    public function test_every_layout_has_the_theme_switch_and_the_early_theme_script(): void
    {
        foreach (['layouts/main', 'layouts/blank', 'modules/admin/layouts/main', 'modules/admin/layouts/blank'] as $layout) {
            $source = file_get_contents(resource_path("views/{$layout}.blade.php"));
            $this->assertStringContainsString("@include('common.theme-init')", $source, "{$layout} lacks the early theme script");
        }

        foreach (['layouts/blank', 'modules/admin/layouts/blank', 'layouts/component/main_navbar', 'modules/admin/layouts/component/main_navbar'] as $view) {
            $source = file_get_contents(resource_path("views/{$view}.blade.php"));
            $this->assertStringContainsString('<x-ui.theme-switch', $source, "{$view} lacks the theme switch");
        }
    }
}
