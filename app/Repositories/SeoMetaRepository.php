<?php

namespace App\Repositories;

use App\Helpers\Pagination;
use App\Models\SeoMeta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeoMetaRepository
{
    public function __construct(protected Pagination $pagination) {}

    public function findById(int|string $id): ?SeoMeta
    {
        return SeoMeta::find($id);
    }

    public function create(array $data): SeoMeta
    {
        return SeoMeta::create($data);
    }

    public function update(SeoMeta $seo, array $data): bool
    {
        $this->forget($seo->url);

        return $seo->update($data);
    }

    public function delete(SeoMeta $seo): ?bool
    {
        $this->forget($seo->url);

        return $seo->delete();
    }

    /**
     * The title, keyword and description for a route, cached for a day. Rows are keyed by
     * route name (`seos.url`), so an unknown route yields empty strings.
     *
     * @return array{title: string, keyword: string, description: string}
     */
    public function metaForRoute(string $route): array
    {
        return Cache::remember($this->cacheKey($route), 86400, function () use ($route) {
            $seo = SeoMeta::where('url', $route)->first()
                ?? SeoMeta::where('type', 'DYNAMIC')->get()->first(fn ($row) => Str::is($row->url, $route));

            return [
                'title' => $seo->title ?? '',
                'keyword' => $seo->keyword ?? '',
                'description' => $seo->description ?? '',
            ];
        });
    }

    /** @return Collection<int, SeoMeta> */
    public function sitemapEntries(): Collection
    {
        return SeoMeta::where('sitemap_enable', 1)->get(['url', 'last_modified', 'change_frequency', 'priority']);
    }

    /**
     * @return array{recordsTotal: int, recordsFiltered: int, draw: int|string, data: Collection}
     */
    public function datatable(array $post): array
    {
        $query = DB::table('seos')->select('id', 'type', 'url', 'title', 'keyword', 'sitemap_enable', 'updated_at');

        $search = trim($post['search']['value'] ?? '');
        if (strlen($search) > 2) {
            $like = '%'.$search.'%';
            $query->where(fn ($q) => $q
                ->where('title', 'like', $like)
                ->orWhere('keyword', 'like', $like)
                ->orWhere('url', 'like', $like)
                ->orWhere('description', 'like', $like));
        }

        return $this->pagination->getDataTable($query, $post);
    }

    protected function forget(string $route): void
    {
        Cache::forget($this->cacheKey($route));
    }

    protected function cacheKey(string $route): string
    {
        return 'seo_meta_'.$route;
    }
}
