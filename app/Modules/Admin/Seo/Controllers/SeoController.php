<?php

namespace App\Modules\Admin\Seo\Controllers;

use App\Helpers\Response;
use App\Modules\Admin\Controllers\Controller;
use App\Modules\Admin\Seo\Requests\SaveSeoRequest;
use App\Modules\Admin\Seo\Services\SeoListService;
use App\Modules\Admin\Seo\Services\SitemapService;
use App\Repositories\SeoMetaRepository;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function __construct(
        protected SeoMetaRepository $seo,
        protected SeoListService $list,
        protected SitemapService $sitemap,
    ) {
        parent::__construct();
    }

    public function index()
    {
        return view('modules.admin.seo.index');
    }

    public function list(Request $request)
    {
        return response()->json($this->list->datatable($request->all()));
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
        $data = $request->safe()->except('id');
        $data['change_frequency'] = $data['change_frequency'] ?? null;

        if ($request->filled('id')) {
            $this->seo->update($this->seo->findById($request->integer('id')), $data);
        } else {
            $this->seo->create($data);
        }

        return Response::sendMessage('SEO saved successfully');
    }

    public function delete(Request $request)
    {
        $request->validate(['id' => ['required', 'integer']]);

        $seo = $this->seo->findById($request->integer('id'));

        if (! $seo) {
            return Response::sendMessage('No data found', 0);
        }

        $this->seo->delete($seo);

        return Response::sendMessage('SEO deleted successfully');
    }

    public function sitemapUpdate()
    {
        $count = $this->sitemap->generate();

        return Response::sendMessage("Sitemap updated with {$count} URLs");
    }
}
