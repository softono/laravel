<?php

namespace App\Modules\Admin\Page\Controllers;

use App\Helpers\General;
use App\Helpers\Response;
use App\Modules\Admin\Controllers\Controller;
use App\Modules\Admin\Page\Requests\SavePageRequest;
use App\Modules\Admin\Page\Services\PageListService;
use App\Repositories\PageRepository;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function __construct(
        protected PageRepository $pages,
        protected PageListService $list,
    ) {
        parent::__construct();
    }

    public function index()
    {
        return view('modules.admin.page.index');
    }

    public function list(Request $request)
    {
        return response()->json($this->list->datatable($request->all()));
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
        $page = $this->pages->findById($request->integer('id'));
        $this->pages->update($page, $request->safe()->except('id'));

        return Response::sendMessage('Page saved successfully');
    }

    /** Image uploads from the rich-text editor. */
    public function saveImage(Request $request, General $general)
    {
        $request->validate(['upload' => ['required', $general->fileRules('image')]]);

        $upload = $general->uploadFile($request->file('upload'), 'content');

        if (! $upload['status']) {
            return Response::sendMessage($upload['message'], 0);
        }

        return Response::sendData(['file_name' => $upload['file_name'], 'file_url' => $general->getFileUrl($upload['file_name'], 'content')]);
    }
}
