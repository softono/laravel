<?php

namespace App\Modules\Admin\Blog\Controllers;

use App\Constants\BlogCategory;
use App\Helpers\General;
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
        return response()->json($this->list->datatable($request->all()));
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
        $this->service->save($request->validated());

        return Response::sendMessage('Blog saved successfully');
    }

    public function delete(Request $request)
    {
        $blog = $this->blogs->findById((string) $request->input('id'));

        if (! $blog) {
            return Response::sendMessage('No data found', 0);
        }

        $this->service->delete($blog);

        return Response::sendMessage('Blog deleted successfully');
    }

    /** Image uploads from the rich-text editor. */
    public function saveImage(Request $request, General $general)
    {
        $request->validate(['upload' => ['required', $general->fileRules('image')]]);

        $upload = $general->uploadFile($request->file('upload'), 'blog');

        if (! $upload['status']) {
            return Response::sendMessage($upload['message'], 0);
        }

        return Response::sendData(['file_name' => $upload['file_name'], 'file_url' => $general->getFileUrl($upload['file_name'], 'blog')]);
    }
}
