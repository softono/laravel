<?php

namespace App\Repositories;

use App\Helpers\Pagination;
use App\Models\SeoMeta;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class SeoMetaRepository
{
    public function findById($id): ?SeoMeta
    {
        return SeoMeta::find($id);
    }

    public function getMetaData(): ?array
    {
        $currentUrl = Route::current()?->getName();
        if (! $currentUrl) {
            return null;
        }

        $cacheKey = 'seo_meta_'.$currentUrl;
        $metaData = Cache::get($cacheKey);

        if ($metaData) {
            return $metaData;
        }
        $siteMeta = SeoMeta::where('url', $currentUrl)->first();
        if ($siteMeta) {
            $metaData = [
                'title' => $siteMeta->title,
                'keyword' => $siteMeta->keyword,
                'description' => $siteMeta->description,
            ];
        } else {
            $metaData = [
                'title' => '',
                'keyword' => '',
                'description' => '',
            ];
        }
        Cache::put($cacheKey, $metaData, 86400);

        return $metaData;
    }

    public function getActiveStatus(): Collection
    {
        return DB::table('seos')->where('sitemap_enable', 1)->get();
    }

    public function listAdmin(array $postData): array
    {
        $query = DB::table('seos');
        $searchText = $postData['search']['value'] ?? '';

        if (strlen($searchText) > 2) {
            $query->where('title', 'like', '%'.$searchText.'%');
        }

        $pagination = new Pagination;
        $result = $pagination->getDataTable($query, $postData);

        foreach ($result['data'] as $row) {
            $row->action = sprintf(
                '<a href="page/%s" class="text-body pjax" title="View"><i class="bx bxs-show icon-base"></i></a>&nbsp;'.
                '<a href="admin/page/update?id=%d" class="btn btn-info pjax" title="Update"><i class="bx bxs-edit icon-base"></i></a>',
                e($row->slug ?? ''),
                $row->id
            );
        }

        return $result;
    }

    public function Seometalist(array $postData): array
    {
        $query = DB::table('seos')->select('*');
        $searchText = $postData['search']['value'] ?? '';

        if (strlen($searchText) > 2) {
            $query->where(function ($query) use ($searchText) {
                $searchPattern = '%'.$searchText.'%';
                $query->where('title', 'like', $searchPattern)
                    ->orWhere('keyword', 'like', $searchPattern)
                    ->orWhere('url', 'like', $searchPattern)
                    ->orWhere('description', 'like', $searchPattern)
                    ->orWhere('sitemap_enable', 'like', $searchPattern);
            });
        }

        $pagination = new Pagination;
        $result = $pagination->getDataTable($query, $postData);
        $sessionUser = auth()->user();
        $seoMeta = new SeoMeta;

        foreach ($result['data'] as $row) {
            $row->sitemap_enable = $seoMeta->getStatusBadge((int) $row->sitemap_enable);
            $row->action = '';

            if ($sessionUser && $sessionUser->hasPermission('admin/seo/update')) {
                $row->action .= sprintf(
                    '<a href="admin/seo/update?id=%d" class="text-body pjax" title="Update"><i class="bx bxs-edit icon-base"></i></a>&nbsp;',
                    $row->id
                );
            }

            if ($sessionUser && $sessionUser->hasPermission('admin/seo/delete')) {
                $row->action .= sprintf(
                    '<button style="border:none; background:none;" onclick="app.confirmAction(this);" data-action="admin/seo/delete?id=%d"  class="text-body pjax" title="Delete"><i class="bx bxs-trash icon-base"></i></button>',
                    $row->id
                );
            }
        }

        return $result;
    }

    public function store(array $postData): array
    {
        $validator = Validator::make($postData, [
            'title' => 'required',
            'url' => 'required',
            'keyword' => 'required',
            'description' => 'required',
        ]);
        if ($validator->fails()) {
            return [
                'status' => 0,
                'message' => $validator->errors()->first(),
            ];
        }
        $id = $postData['id'] ?? null;
        $seometa = $id ? SeoMeta::find($id) : new SeoMeta;
        if ($id && ! $seometa) {
            return ['status' => 0, 'message' => 'Seo meta not found.'];
        }

        $seometa->url = $postData['url'];
        $seometa->title = $postData['title'];
        $seometa->keyword = $postData['keyword'];
        $seometa->description = $postData['description'];
        $seometa->sitemap_enable = $postData['site_map'];
        $seometa->last_modified = $postData['last_modified'];
        $seometa->change_frequency = $postData['frequency'];
        $seometa->priority = $postData['priority'];

        return $seometa->save()
            ? ['status' => 1, 'message' => 'Seo Saved successfully', 'next' => 'load', 'url' => 'admin/seo/meta']
            : ['status' => 0, 'message' => 'Failed to save the Seo Meta.'];
    }
}
