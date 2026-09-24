<?php

namespace App\Modules\Admin\Seo\Controllers;

use App\Helpers\Response;
use App\Modules\Admin\Controllers\Controller;
use App\Modules\Admin\Seo\Requests\SaveSeoRequest;
use App\Modules\Admin\Seo\Services\SeoListService;
use App\Modules\Admin\Seo\Services\SeoService;
use App\Modules\Admin\Seo\Services\SitemapService;
use App\Repositories\SeoMetaRepository;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function __construct(
        protected SeoMetaRepository $seo,
        protected SeoListService $list,
        protected SitemapService $sitemap,
        protected SeoService $service,
    ) {
        parent::__construct();
    }

    public function index()
    {
        return view('modules.admin.seo.index');
    }

    public function list(Request $request)
    {
        return Response::sendResult($this->list->datatable($request->all()));
    }

    public function create()
    {
        return view('modules.admin.seo.create');
    }

    public function update(Request $request)
    {
        $model = $this->seo->findById((string) $request->input('id'));

        if (! $model) {
            return redirect()->route('admin/seo/meta')->with('error', 'No data found');
        }

        return view('modules.admin.seo.update', ['model' => $model]);
    }

    public function save(SaveSeoRequest $request)
    {
        return Response::sendResult($this->service->save($request->validated()));
    }

    public function delete(Request $request)
    {
        $request->validate(['id' => ['required', 'integer']]);

        return Response::sendResult($this->service->delete($request->integer('id')));
    }

    public function sitemapUpdate()
    {
        return Response::sendResult($this->sitemap->generate());
    }
}
