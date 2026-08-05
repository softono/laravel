<?php

namespace App\Http\Controllers;

use App\Repositories\Storage\BucketRepository;
use App\Repositories\Storage\StorageObjectRepository;
use App\Services\Storage\BucketService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Bucket Admin self-service bucket management - see "Bucket Management" in
 * docs/local/prd.md. Every query is scoped to the authenticated user; no
 * cross-user visibility (that's the Super Admin panel's read-only view).
 */
class BucketController extends Controller
{
    public function __construct(
        protected BucketService $bucketService,
        protected BucketRepository $buckets,
        protected StorageObjectRepository $objects,
    ) {
        parent::__construct();
    }

    /**
     * @return View
     */
    public function index()
    {
        return view('bucket.index');
    }

    /**
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $buckets = $this->buckets->listForUser(auth()->id());
        $sessionUser = auth()->user();

        $data = $buckets->map(function ($bucket) use ($sessionUser) {
            return [
                'id' => $bucket->id,
                'name' => $bucket->name,
                'visibility' => $bucket->visibility,
                'object_count' => $this->objects->countForBucket($bucket->id),
                'storage_used' => $this->general->formatBytes($this->objects->sizeForBucket($bucket->id)),
                'created_at' => $this->general->dateFormat($bucket->created_at),
                'action' => $this->actionLinks($bucket, $sessionUser),
            ];
        });

        return response()->json(['data' => $data, 'recordsTotal' => $data->count(), 'recordsFiltered' => $data->count()]);
    }

    protected function actionLinks($bucket, $sessionUser): string
    {
        return '<div class="d-flex align-items-center">'
            .sprintf('<a href="buckets/view?id=%d" class="btn btn-icon" title="View"><i class="bx bxs-show icon-base"></i></a>', $bucket->id)
            .sprintf('<a href="objects?bucket=%s" class="btn btn-icon" title="Browse Objects"><i class="bx bxs-folder-open icon-base"></i></a>', urlencode($bucket->name))
            .sprintf('<a href="buckets/update?id=%d" class="btn btn-icon" title="Edit"><i class="bx bxs-edit icon-base"></i></a>', $bucket->id)
            .sprintf('<button onclick="app.confirmAction(this);" data-action="buckets/delete" data-id="%d" class="btn btn-icon" title="Delete"><i class="bx bxs-trash icon-base"></i></button>', $bucket->id)
            .'</div>';
    }

    /**
     * @return View
     */
    public function create()
    {
        return view('bucket.create');
    }

    /**
     * @return View|RedirectResponse
     */
    public function update(Request $request)
    {
        $bucket = $this->buckets->findById($request->input('id'));
        if (! $bucket || $bucket->user_id !== auth()->id()) {
            return redirect()->route('buckets')->with('error', 'No data found');
        }

        return view('bucket.update', compact('bucket'));
    }

    /**
     * @return JsonResponse
     */
    public function save(Request $request)
    {
        $id = $request->input('id');

        if ($id) {
            $bucket = $this->buckets->findById($id);
            if (! $bucket || $bucket->user_id !== auth()->id()) {
                return response()->json(['status' => 0, 'message' => 'No data found']);
            }

            return response()->json($this->bucketService->update($bucket, $request->all()));
        }

        return response()->json($this->bucketService->create(auth()->user(), $request->all()));
    }

    /**
     * @return JsonResponse
     */
    public function delete(Request $request)
    {
        $bucket = $this->buckets->findById($request->input('id'));
        if (! $bucket || $bucket->user_id !== auth()->id()) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }

        $result = $this->bucketService->delete($bucket);
        if ($result['status']) {
            $result['next'] = 'table_refresh';
        }

        return response()->json($result);
    }

    /**
     * @return View|RedirectResponse
     */
    public function view(Request $request)
    {
        $bucket = $this->buckets->findById($request->input('id'));
        if (! $bucket || $bucket->user_id !== auth()->id()) {
            return redirect()->route('buckets')->with('error', 'No data found');
        }

        $stats = $this->bucketService->stats($bucket);

        return view('bucket.view', compact('bucket', 'stats'));
    }
}
