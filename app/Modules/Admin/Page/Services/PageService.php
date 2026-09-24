<?php

namespace App\Modules\Admin\Page\Services;

use App\Helpers\ApiResult;
use App\Helpers\General;
use App\Repositories\PageRepository;
use Illuminate\Http\UploadedFile;

class PageService
{
    public function __construct(
        protected PageRepository $pages,
        protected General $general,
    ) {}

    /** @param  array<string, mixed>  $data  validated form data including the page `id` */
    public function save(array $data): array
    {
        $page = $this->pages->findById((int) $data['id']);

        if (! $page) {
            return ApiResult::failure('No data found');
        }

        unset($data['id']);
        $this->pages->update($page, $data);

        return ApiResult::success('Page saved successfully');
    }

    /** Image uploaded from the rich-text editor; `data` carries the stored name and its URL. */
    public function uploadImage(UploadedFile $file): array
    {
        $upload = $this->general->uploadFile($file, 'content');

        if (! $upload['status']) {
            return $upload;
        }

        $name = $upload['data']['file_name'];

        return ApiResult::success('', ['file_name' => $name, 'file_url' => $this->general->getFileUrl($name, 'content')]);
    }
}
