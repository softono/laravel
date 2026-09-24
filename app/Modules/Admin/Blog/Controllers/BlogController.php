<?php

namespace App\Modules\Admin\Blog\Controllers;

use App\Constants\BlogCategory;
use App\Helpers\Response;
use App\Modules\Admin\Blog\Requests\SaveBlogRequest;
use App\Modules\Admin\Blog\Services\BlogListService;
use App\Modules\Admin\Blog\Services\BlogService;
use App\Modules\Admin\Controllers\Controller;
use App\Repositories\BlogRepository;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function __construct(
        protected BlogRepository $blogs,
        protected BlogService $service,
        protected BlogListService $list,
    ) {
        parent::__construct();
    }

    public function index()
    {
        return view('modules.admin.blog.index');
    }

    public function list(Request $request)
    {
        return Response::sendResult($this->list->datatable($request->all()));
    }

    public function create()
    {
        return view('modules.admin.blog.create', ['categories' => BlogCategory::LABELS]);
    }

    public function update(Request $request)
    {
        $model = $this->blogs->findById((string) $request->input('id'));

        if (! $model) {
            return redirect()->route('admin/blog')->with('error', 'No data found');
        }

        return view('modules.admin.blog.update', ['model' => $model, 'categories' => BlogCategory::LABELS]);
    }

    public function save(SaveBlogRequest $request)
    {
        return Response::sendResult($this->service->save($request->validated()));
    }

    public function delete(Request $request)
    {
        return Response::sendResult($this->service->delete((string) $request->input('id')));
    }

    /** Image uploads from the rich-text editor. */
    public function saveImage(Request $request)
    {
        $request->validate(['upload' => ['required', $this->general->fileRules('image')]]);

        return Response::sendResult($this->service->uploadImage($request->file('upload')));
    }
}
