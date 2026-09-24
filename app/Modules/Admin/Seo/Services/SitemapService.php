<?php

namespace App\Modules\Admin\Seo\Services;

use App\Helpers\ApiResult;
use App\Modules\Admin\Seo\Requests\SaveSeoRequest;
use App\Repositories\SeoMetaRepository;

/** Writes public/sitemap.xml from the SEO rows that are enabled for the sitemap. */
class SitemapService
{
    public function __construct(protected SeoMetaRepository $seo) {}

    /** `data.count`: how many URLs were written. */
    public function generate(): array
    {
        $entries = $this->seo->sitemapEntries()
            ->reject(fn ($entry) => str_contains($entry->url, '*'))
            ->map(fn ($entry) => [
                'loc' => $entry->url === 'home' ? url('/') : url($entry->url),
                'lastmod' => $entry->last_modified,
                'changefreq' => in_array($entry->change_frequency, SaveSeoRequest::FREQUENCIES, true) ? $entry->change_frequency : null,
                'priority' => $entry->priority,
            ]);

        file_put_contents(public_path('sitemap.xml'), view('modules.admin.seo.sitemap', ['entries' => $entries])->render());

        return ApiResult::success("Sitemap updated with {$entries->count()} URLs", ['count' => $entries->count()]);
    }
}
