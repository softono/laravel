<?php

namespace Tests\Feature;

use App\Helpers\ThemeCatalog;
use Illuminate\Http\Request;
use Tests\TestCase;

class ThemeTest extends TestCase
{
    public function test_every_catalog_theme_has_a_file_with_the_core_tokens(): void
    {
        $this->assertCount(43, ThemeCatalog::all());

        foreach (ThemeCatalog::all() as $theme) {
            $this->assertFileExists(resource_path("themes/{$theme['name']}.json"));
            $this->assertCount(4, $theme['swatches'], $theme['name']);

            if ($theme['name'] === ThemeCatalog::DEFAULT) {
                $this->assertSame('', ThemeCatalog::css('default'));

                continue;
            }

            $css = ThemeCatalog::css($theme['name']);
            $this->assertStringContainsString('--primary:', $css, $theme['name']);
            $this->assertStringContainsString('.dark, .dark body {', $css, $theme['name']);
        }
    }

    public function test_theme_fonts_win_over_the_default_and_only_web_fonts_are_requested(): void
    {
        $this->assertStringContainsString('!important;', ThemeCatalog::css('bold-tech'));
        $this->assertStringContainsString('family=Playfair+Display', ThemeCatalog::fontHref('bold-tech'));
        $this->assertNull(ThemeCatalog::fontHref('default'));
        $this->assertNull(ThemeCatalog::fontHref('claude'));
    }

    public function test_only_catalog_names_are_accepted(): void
    {
        foreach (['../../.env', 'CLAUDE', 'claude.json', '', 'x"><script>'] as $bad) {
            $this->assertFalse(ThemeCatalog::has($bad), $bad);
            $this->assertSame('', ThemeCatalog::css($bad));
        }

        $this->assertFalse(ThemeCatalog::has(null));
    }

    public function test_active_theme_is_the_visitors_pick_then_the_admin_default(): void
    {
        config(['setting.default_theme' => 'vercel']);
        $withCookie = fn (?string $value) => Request::create('/', 'GET', [], $value === null ? [] : ['app-theme' => $value]);

        $this->assertSame('claude', ThemeCatalog::activeName($withCookie('claude')));
        $this->assertSame('vercel', ThemeCatalog::activeName($withCookie(null)));
        $this->assertSame('vercel', ThemeCatalog::activeName($withCookie('not-a-theme')));

        config(['setting.default_theme' => 'gone']);
        $this->assertSame('default', ThemeCatalog::activeName($withCookie(null)));
    }

    public function test_endpoints_return_the_envelope(): void
    {
        $this->getJson('/theme')->assertOk()->assertJsonPath('status', 1)->assertJsonCount(43, 'data.themes');

        $this->getJson('/theme/claude')->assertOk()
            ->assertJsonPath('data.name', 'claude')
            ->assertJsonPath('data.font_href', null)
            ->assertJsonPath('status', 1);

        $this->getJson('/theme/nope')->assertNotFound()->assertJsonPath('status', 0);
    }

    public function test_pages_render_the_visitors_theme_from_a_plain_cookie(): void
    {
        $html = $this->withUnencryptedCookie('app-theme', 'claude')->get('/login')->assertOk()->getContent();
        $this->assertStringContainsString('id="app-theme-vars" data-theme-name="claude"', $html);

        $fallback = $this->withUnencryptedCookie('app-theme', '"><script>alert(1)</script>')->get('/login')->assertOk()->getContent();
        $this->assertStringContainsString('data-theme-name="default"', $fallback);
        $this->assertStringNotContainsString('<script>alert(1)', $fallback);
    }

    public function test_the_theme_style_comes_after_the_stylesheet_so_it_wins(): void
    {
        $html = $this->get('/login')->getContent();

        $this->assertLessThan(strpos($html, 'id="app-theme-vars"'), strpos($html, 'build/assets/app-'));
    }
}
