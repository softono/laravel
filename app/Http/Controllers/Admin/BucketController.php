<?php

namespace App\Http\Controllers\Admin;

use App\Repositories\Storage\BucketRepository;
use App\Repositories\Storage\StorageObjectRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Read-only, cross-user bucket listing for the Super Admin panel - for
 * support/troubleshooting only (see "Users & Storage Overview" in
 * docs/local/prd.md). Bucket ownership/mutation stays exclusively a
 * Bucket Admin concern via the S3 API and Bucket Admin panel.
 */
class BucketController extends Controller
{
    public function __construct(
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
        return view('admin/bucket/index');
    }

    /**
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $result = $this->buckets->listAllForAdmin($request->all());
        $sessionUser = auth()->user();

        foreach ($result['data'] as $row) {
            $row->owner = trim($row->owner_first_name.' '.$row->owner_last_name).' ('.$row->owner_email.')';
            $row->object_count = $this->objects->countForBucket($row->id);
            $row->storage_used = $this->general->formatBytes($this->objects->sizeForBucket($row->id));
            $row->action = $sessionUser && $sessionUser->hasPermission('admin/bucket/view')
                ? sprintf('<a href="admin/bucket/view?id=%d" class="btn btn-icon pjax" title="View"><i class="bx bxs-show icon-base"></i></a>', $row->id)
                : '';
        }

        return response()->json($result);
    }

    /**
     * @return View|RedirectResponse
     */
    public function view(Request $request)
    {
        $bucket = $this->buckets->findById($request->input('id'));
        if (! $bucket) {
            return redirect()->route('admin/bucket')->with('error', 'No data found');
        }

        $stats = [
            'object_count' => $this->objects->countForBucket($bucket->id),
            'storage_used' => $this->objects->sizeForBucket($bucket->id),
        ];

        return view('admin/bucket/view', compact('bucket', 'stats'));
    }
}
