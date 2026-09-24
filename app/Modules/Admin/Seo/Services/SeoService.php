<?php

namespace App\Modules\Admin\Seo\Services;

use App\Helpers\ApiResult;
use App\Repositories\SeoMetaRepository;

class SeoService
{
    public function __construct(protected SeoMetaRepository $seo) {}

    /** @param  array<string, mixed>  $data  validated form data; an `id` means update */
    public function save(array $data): array
    {
        $id = $data['id'] ?? null;
        unset($data['id']);
        $data['change_frequency'] = $data['change_frequency'] ?? null;

        if ($id) {
            $seo = $this->seo->findById((int) $id);

            if (! $seo) {
                return ApiResult::failure('No data found');
            }

            $this->seo->update($seo, $data);
        } else {
            $this->seo->create($data);
        }

        return ApiResult::success('SEO saved successfully');
    }

    public function delete(int $id): array
    {
        $seo = $this->seo->findById($id);

        if (! $seo) {
            return ApiResult::failure('No data found');
        }

        $this->seo->delete($seo);

        return ApiResult::success('SEO deleted successfully');
    }
}
