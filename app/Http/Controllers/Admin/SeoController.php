<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SeoMeta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;

class SeoController extends Controller
{
    /**
     * Update the sitemap file with enabled SEO metadata.
     */
    public function sitemapUpdate(): JsonResponse
    {
        $data = [];
        $seoRecords = SeoMeta::select('url', 'last_modified', 'change_frequency', 'priority')
            ->where('sitemap_enable', 1)
            ->get();

        if ($seoRecords->isNotEmpty()) {
            foreach ($seoRecords as $record) {
                $data[] = [
                    'url' => $record->url,
                    'last_modified' => $record->last_modified,
                    'change_frequency' => $record->change_frequency,
                    'priority' => $record->priority,
                ];
            }
        } else {
            $data[] = [
                'url' => URL::to('/'),
                'last_modified' => now()->subYear()->format('Y-m-d H:i:s'),
                'change_frequency' => 'weekly',
                'priority' => 1.0,
            ];
        }

        file_put_contents(
            public_path('sitemap.xml'),
            view('admin/seo/sitemap', compact('data'))
        );

        return response()->json([
            'status' => 1,
            'message' => 'Sitemap updated successfully.',
        ]);
    }

    /**
     * Show the SEO metadata index page.
     */
    public function index(): View
    {
        return view('admin/seo/index');
    }

    /**
     * List all SEO metadata.
     */
    public function list(Request $request): JsonResponse
    {
        $seoMetaList = (new SeoMeta)->Seometalist($request->all());

        return response()->json($seoMetaList);
    }

    /**
     * Show the create SEO metadata form.
     */
    public function create(): View
    {
        return view('admin/seo/create');
    }

    /**
     * Show the update form for a specific SEO metadata record.
     */
    public function update(Request $request): View|RedirectResponse
    {
        $model = SeoMeta::find($request->input('id'));
        if (! $model) {
            return redirect()->route('note')->withErrors(['error' => 'No data found.']);
        }

        return view('admin/seo/update', compact('model'));
    }

    /**
     * Save a new or updated SEO metadata record.
     */
    public function save(Request $request): JsonResponse
    {
        $response = (new SeoMeta)->store($request->all());

        return response()->json($response);
    }

    /**
     * Delete a specific SEO metadata record.
     */
    public function delete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:seo_meta,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 0,
                'message' => 'Validation failed.',
            ]);
        }

        $seoMeta = SeoMeta::find($request->input('id'));

        if (! $seoMeta) {
            return response()->json([
                'status' => 0,
                'message' => 'No data found.',
            ]);
        }

        Cache::forget('seo_meta_'.$seoMeta->url);
        $seoMeta->delete();

        return response()->json([
            'status' => 1,
            'message' => 'SEO metadata deleted successfully.',
            'next' => 'table_refresh',
        ]);
    }
}
