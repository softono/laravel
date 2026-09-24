<?php

namespace App\Modules\Admin\Page\Controllers;

use App\Helpers\Response;
use App\Modules\Admin\Controllers\Controller;
use App\Modules\Admin\Page\Requests\SavePageRequest;
use App\Modules\Admin\Page\Services\PageListService;
use App\Modules\Admin\Page\Services\PageService;
use App\Repositories\PageRepository;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(
        protected PageRepository $pages,
        protected PageListService $list,
        protected PageService $service,
    ) {
        parent::__construct();
    }

    public function index()
    {
        return view('modules.admin.page.index');
    }

    public function list(Request $request)
    {
        return Response::sendResult($this->list->datatable($request->all()));
    }

    public function update(Request $request)
    {
        $page = $this->pages->findById((string) $request->input('id'));

        if (! $page) {
            return redirect()->route('admin/page')->with('error', 'No data found');
        }

        return view('modules.admin.page.update', ['model' => $page]);
    }

    public function save(SavePageRequest $request)
    {
        return Response::sendResult($this->service->save($request->validated()));
    }

    /** Image uploads from the rich-text editor. */
    public function saveImage(Request $request)
    {
        $request->validate(['upload' => ['required', $this->general->fileRules('image')]]);

        return Response::sendResult($this->service->uploadImage($request->file('upload')));
    }
}
