<?php

namespace App\Helpers;

use Illuminate\Http\Request;

/**
 * The colour themes (Next's "switchcn"): `resources/themes/catalog.json` lists them for the picker and
 * `resources/themes/<name>.json` holds each theme's CSS variables. The `default` theme is the token set in
 * resources/css/next-theme.css, so it needs no override. Names are only ever looked up in the catalog,
 * never used as a path or emitted unchecked.
 */
class ThemeCatalog
{
    public const DEFAULT = 'default';

    private const GENERIC_FONTS = [
        'sans-serif', 'serif', 'monospace', 'system-ui', 'ui-sans-serif', 'ui-serif', 'ui-monospace', 'cursive',
        'fantasy', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'Helvetica Neue', 'Arial',
    ];

    /** @var array<int, array{name: string, label: string, swatches: array<int, string>}>|null */
    private static ?array $catalog = null;

    /** @return array<int, array{name: string, label: string, swatches: array<int, string>}> */
    public static function all(): array
    {
        return self::$catalog ??= json_decode(file_get_contents(resource_path('themes/catalog.json')), true);
    }

    public static function has(?string $name): bool
    {
        return $name !== null && collect(self::all())->contains('name', $name);
    }

    /** The theme to render for this request: the visitor's own pick, else the admin's default, else `default`. */
    public static function activeName(Request $request): string
    {
        foreach ([$request->cookie('app-theme'), config('setting.default_theme')] as $candidate) {
            if (is_string($candidate) && self::has($candidate)) {
                return $candidate;
            }
        }

        return self::DEFAULT;
    }

    /** CSS overriding the token set for `$name`, or '' for the default theme and unknown names. */
    public static function css(string $name): string
    {
        $theme = self::load($name);

        if (! $theme) {
            return '';
        }

        $root = self::declarations(($theme['cssVars']['theme'] ?? []) + ($theme['cssVars']['light'] ?? []));
        $dark = self::declarations($theme['cssVars']['dark'] ?? []);

        return ":root, body {\n{$root}\n}\n.dark, .dark body {\n{$dark}\n}";
    }

    /** Google Fonts stylesheet for the theme's font families, or null when it only uses system fonts. */
    public static function fontHref(string $name): ?string
    {
        $theme = self::load($name);
        $families = [];

        foreach (['font-sans', 'font-serif', 'font-mono'] as $key) {
            $first = trim(explode(',', $theme['cssVars']['theme'][$key] ?? '')[0]);
            $family = trim($first, '\'"');

            if ($family !== '' && ! in_array($family, self::GENERIC_FONTS, true)) {
                $families[] = 'family='.str_replace('%20', '+', rawurlencode($family)).':wght@300;400;500;600;700';
            }
        }

        return $families ? 'https://fonts.googleapis.com/css2?'.implode('&', array_unique($families)).'&display=swap' : null;
    }

    /** @return array<string, mixed>|null */
    private static function load(string $name): ?array
    {
        if ($name === self::DEFAULT || ! self::has($name)) {
            return null;
        }

        return json_decode(file_get_contents(resource_path("themes/$name.json")), true);
    }

    /** @param  array<string, string|null>  $tokens */
    private static function declarations(array $tokens): string
    {
        $lines = [];

        foreach ($tokens as $key => $value) {
            if ($value !== null) {
                // Theme fonts must beat the `font-sans` fallback declared by Tailwind's @theme.
                $lines[] = "  --$key: $value".(str_starts_with($key, 'font-') ? ' !important' : '').';';
            }
        }

        return implode("\n", $lines);
    }
}
