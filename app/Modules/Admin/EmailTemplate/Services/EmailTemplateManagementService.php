<?php

namespace App\Modules\Admin\EmailTemplate\Services;

use App\Helpers\ApiResult;
use App\Helpers\General;
use App\Repositories\EmailTemplateRepository;
use Illuminate\Http\UploadedFile;

class EmailTemplateManagementService
{
    public function __construct(
        protected EmailTemplateRepository $templates,
        protected General $general,
    ) {}

    /** @param  array<string, mixed>  $data  validated form data including the template `id` */
    public function save(array $data): array
    {
        $template = $this->templates->findById((int) $data['id']);

        if (! $template) {
            return ApiResult::failure('No data found');
        }

        unset($data['id']);
        $this->templates->update($template, $data);

        return ApiResult::success('Email template saved successfully');
    }

    /** Image uploaded from the rich-text editor; `data` carries the stored name and its URL. */
    public function uploadImage(UploadedFile $file): array
    {
        $upload = $this->general->uploadFile($file, 'email');

        if (! $upload['status']) {
            return $upload;
        }

        $name = $upload['data']['file_name'];

        return ApiResult::success('', ['file_name' => $name, 'file_url' => $this->general->getFileUrl($name, 'email')]);
    }
}
