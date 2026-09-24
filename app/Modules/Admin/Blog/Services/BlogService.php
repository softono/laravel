<?php

namespace App\Modules\Admin\Blog\Services;

use App\Helpers\ApiResult;
use App\Helpers\General;
use App\Models\Blog;
use App\Repositories\BlogRepository;
use Illuminate\Http\UploadedFile;

class BlogService
{
    public function __construct(
        protected BlogRepository $blogs,
        protected General $general,
    ) {}

    /**
     * Creates the post, or updates it when an id is given.
     *
     * @param  array<string, mixed>  $data  validated form data; `image` is an optional upload
     */
    public function save(array $data): array
    {
        $id = $data['id'] ?? null;
        $upload = $data['image'] ?? null;
        unset($data['id'], $data['image']);

        $blog = $id ? $this->blogs->findById($id) : null;

        if ($upload instanceof UploadedFile) {
            $stored = $this->general->uploadFile($upload, 'blog');
            if ($stored['status']) {
                $this->removeImage($blog);
                $data['image'] = $stored['data']['file_name'];
            }
        }

        if ($blog) {
            $this->blogs->update($blog, $data);
        } else {
            $this->blogs->create($data);
        }

        return ApiResult::success('Blog saved successfully');
    }

    public function delete(string $id): array
    {
        $blog = $this->blogs->findById($id);

        if (! $blog) {
            return ApiResult::failure('No data found');
        }

        $this->removeImage($blog);
        $this->blogs->delete($blog);

        return ApiResult::success('Blog deleted successfully');
    }

    /** Image uploaded from the rich-text editor; `data` carries the stored name and its URL. */
    public function uploadImage(UploadedFile $file): array
    {
        $upload = $this->general->uploadFile($file, 'blog');

        if (! $upload['status']) {
            return $upload;
        }

        $name = $upload['data']['file_name'];

        return ApiResult::success('', ['file_name' => $name, 'file_url' => $this->general->getFileUrl($name, 'blog')]);
    }

    protected function removeImage(?Blog $blog): void
    {
        if ($blog?->image) {
            $this->general->deleteFile($blog->image, 'blog');
        }
    }
}
