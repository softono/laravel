<?php

namespace App\Modules\Admin\Blog\Services;

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
    public function save(array $data): Blog
    {
        $id = $data['id'] ?? null;
        $upload = $data['image'] ?? null;
        unset($data['id'], $data['image']);

        $blog = $id ? $this->blogs->findById($id) : null;

        if ($upload instanceof UploadedFile) {
            $stored = $this->general->uploadFile($upload, 'blog');
            if ($stored['status']) {
                $this->removeImage($blog);
                $data['image'] = $stored['file_name'];
            }
        }

        if ($blog) {
            $this->blogs->update($blog, $data);

            return $blog->refresh();
        }

        return $this->blogs->create($data);
    }

    public function delete(Blog $blog): void
    {
        $this->removeImage($blog);
        $this->blogs->delete($blog);
    }

    protected function removeImage(?Blog $blog): void
    {
        if ($blog?->image) {
            $this->general->deleteFile($blog->image, 'blog');
        }
    }
}
