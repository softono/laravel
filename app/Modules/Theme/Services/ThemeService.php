<?php

namespace App\Modules\Theme\Services;

use App\Helpers\ApiResult;
use App\Helpers\ThemeCatalog;

class ThemeService
{
    /** `data.themes`: name, label and four swatch colours for each theme, for the picker. */
    public function list(): array
    {
        return ApiResult::success('', ['themes' => ThemeCatalog::all()]);
    }

    /** `data`: name, css and font_href (the switcher injects them without a reload); 404 for an unknown theme. */
    public function show(string $name): array
    {
        if (! ThemeCatalog::has($name)) {
            return ApiResult::failure('Theme not found', [], 404);
        }

        return ApiResult::success('', [
            'name' => $name,
            'css' => ThemeCatalog::css($name),
            'font_href' => ThemeCatalog::fontHref($name),
        ]);
    }
}
