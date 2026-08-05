<?php

namespace App\Http\Controllers;

use App\Repositories\Storage\BucketRepository;
use App\Services\Storage\ObjectService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Bucket Admin "simple file manager" - see "Object Browser" in
 * docs/local/prd.md: folder navigation (virtual, from object_key
 * prefixes), search, upload, download, delete, move/copy, metadata.
 */
class ObjectBrowserController extends Controller
{
    public function __construct(
        protected BucketRepository $buckets,
        protected ObjectService $objects,
    ) {
        parent::__construct();
    }

    /**
     * Resolves {bucket} scoped to the authenticated user, or null with a
     * 404/redirect-worthy failure already logged by the caller.
     */
    protected function ownedBucket(string $name)
    {
        $bucket = $this->buckets->findByNameGlobal($name);
        if (! $bucket || $bucket->user_id !== auth()->id()) {
            return null;
        }

        return $bucket;
    }

    /**
     * @return View|RedirectResponse
     */
    public function index(Request $request)
    {
        $bucketName = $request->query('bucket');
        $bucket = $bucketName ? $this->ownedBucket($bucketName) : null;

        if (! $bucket) {
            return redirect()->route('buckets')->with('error', 'Select a bucket to browse.');
        }

        $prefix = rtrim((string) $request->query('prefix', ''), '/');
        $prefix = $prefix === '' ? '' : $prefix.'/';

        return view('object.index', ['bucket' => $bucket, 'prefix' => $prefix]);
    }

    /**
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        $bucket = $this->ownedBucket((string) $request->input('bucket'));
        if (! $bucket) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }

        $prefix = (string) $request->input('prefix', '');
        $search = $request->input('search');

        if ($search) {
            $result = $this->objects->list($bucket, null, 200, 0, $search);

            return response()->json([
                'folders' => [],
                'files' => $result['data']->map(fn ($o) => $this->fileRow($o))->values(),
            ]);
        }

        $listing = $this->objects->listFolder($bucket, $prefix);
        $files = $this->objects->list($bucket, $prefix, 1000, 0)['data']
            ->filter(fn ($o) => in_array($o->object_key, $listing['files'], true))
            ->map(fn ($o) => $this->fileRow($o))
            ->values();

        $folders = collect($listing['folders'])->map(function ($folderKey) use ($prefix) {
            return [
                'name' => rtrim(substr($folderKey, strlen($prefix)), '/'),
                'prefix' => $folderKey,
            ];
        })->values();

        return response()->json(['folders' => $folders, 'files' => $files]);
    }

    protected function fileRow($object): array
    {
        return [
            'id' => $object->id,
            'key' => $object->object_key,
            'name' => basename($object->object_key),
            'size' => $this->general->formatBytes($object->size),
            'mime_type' => $object->mime_type,
            'etag' => $object->checksum,
            'created_at' => $this->general->dateFormat($object->created_at),
            'metadata' => $object->metadata_json,
        ];
    }

    /**
     * @return JsonResponse
     */
    public function upload(Request $request)
    {
        $bucket = $this->ownedBucket((string) $request->input('bucket'));
        if (! $bucket) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }

        $file = $request->file('file');
        if (! $file || ! $file->isValid()) {
            return response()->json(['status' => 0, 'message' => 'No valid file uploaded.']);
        }

        $maxUploadSize = (int) config('setting.storage_max_upload_size');
        if ($maxUploadSize > 0 && $file->getSize() > $maxUploadSize) {
            return response()->json(['status' => 0, 'message' => 'File exceeds the maximum allowed upload size.']);
        }

        $prefix = rtrim((string) $request->input('prefix', ''), '/');
        $objectKey = ($prefix ? $prefix.'/' : '').$file->getClientOriginalName();

        $stream = fopen($file->getRealPath(), 'rb');

        try {
            $this->objects->storeStream(
                $bucket,
                $objectKey,
                $stream,
                $file->getClientOriginalName(),
                $file->getClientMimeType(),
            );
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        return response()->json(['status' => 1, 'message' => 'File uploaded successfully.', 'next' => 'refresh']);
    }

    /**
     * @return StreamedResponse|JsonResponse
     */
    public function download(Request $request)
    {
        $bucket = $this->ownedBucket((string) $request->query('bucket'));
        if (! $bucket) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }

        $object = $this->objects->find($bucket, (string) $request->query('key'));
        if (! $object) {
            return response()->json(['status' => 0, 'message' => 'Object not found']);
        }

        return $this->objects->download($bucket, $object);
    }

    /**
     * @return JsonResponse
     */
    public function destroy(Request $request)
    {
        $bucket = $this->ownedBucket((string) $request->input('bucket'));
        if (! $bucket) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }

        $object = $this->objects->find($bucket, (string) $request->input('key'));
        if (! $object) {
            return response()->json(['status' => 0, 'message' => 'Object not found']);
        }

        $this->objects->delete($bucket, $object);

        return response()->json(['status' => 1, 'message' => 'Object deleted successfully.', 'next' => 'refresh']);
    }

    /**
     * Copy (S3-standard semantics, same-account) - see Move/Copy mechanics
     * in docs/local/prd.md Open Questions. Move is this same operation
     * followed by a delete of the source, driven from the panel.
     *
     * @return JsonResponse
     */
    public function copy(Request $request)
    {
        $sourceBucket = $this->ownedBucket((string) $request->input('source_bucket'));
        $destBucket = $this->ownedBucket((string) $request->input('dest_bucket', $request->input('source_bucket')));
        if (! $sourceBucket || ! $destBucket) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }

        $sourceObject = $this->objects->find($sourceBucket, (string) $request->input('source_key'));
        if (! $sourceObject) {
            return response()->json(['status' => 0, 'message' => 'Source object not found']);
        }

        $destKey = (string) $request->input('dest_key');
        $this->objects->copy($sourceBucket, $sourceObject, $destBucket, $destKey);

        if ($request->boolean('move')) {
            $this->objects->delete($sourceBucket, $sourceObject);
        }

        return response()->json([
            'status' => 1,
            'message' => $request->boolean('move') ? 'Object moved successfully.' : 'Object copied successfully.',
            'next' => 'refresh',
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function metadata(Request $request)
    {
        $bucket = $this->ownedBucket((string) $request->query('bucket'));
        if (! $bucket) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }

        $object = $this->objects->find($bucket, (string) $request->query('key'));
        if (! $object) {
            return response()->json(['status' => 0, 'message' => 'Object not found']);
        }

        return response()->json(['status' => 1, 'data' => [
            'key' => $object->object_key,
            'original_filename' => $object->original_filename,
            'mime_type' => $object->mime_type,
            'size' => $this->general->formatBytes($object->size),
            'checksum' => $object->checksum,
            'metadata' => $object->metadata_json,
            'created_at' => $this->general->dateFormat($object->created_at),
            'updated_at' => $this->general->dateFormat($object->updated_at),
        ]]);
    }
}
