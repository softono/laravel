<?php

namespace App\Http\Controllers;

use App\Repositories\Storage\ApiKeyRepository;
use App\Repositories\Storage\BucketRepository;
use App\Services\Storage\ApiCredentialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Bucket Admin API credential management - see "API Keys" in
 * docs/local/prd.md. Scoped strictly to the logged-in user.
 */
class ApiKeyController extends Controller
{
    public function __construct(
        protected ApiCredentialService $credentials,
        protected ApiKeyRepository $apiKeys,
        protected BucketRepository $buckets,
    ) {
        parent::__construct();
    }

    /**
     * @return View
     */
    public function index()
    {
        $apiKeys = $this->apiKeys->listForUser(auth()->id());
        $buckets = $this->buckets->listForUser(auth()->id());

        return view('api-key.index', compact('apiKeys', 'buckets'));
    }

    /**
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $bucketId = $request->input('bucket_id');
        if ($bucketId && ! $this->buckets->listForUser(auth()->id())->contains('id', $bucketId)) {
            return response()->json(['status' => 0, 'message' => 'Invalid bucket selected.']);
        }

        $result = $this->credentials->create(auth()->user(), $request->input('title'), $bucketId ?: null);

        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'],
            'access_key' => $result['api_user']->access_key,
            'secret_key' => $result['secret_key'],
            'next' => 'refresh',
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function regenerate(Request $request)
    {
        $apiKey = $this->apiKeys->findById($request->input('id'));
        if (! $apiKey || $apiKey->user_id !== auth()->id()) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }

        $result = $this->credentials->regenerateSecret($apiKey);

        return response()->json([
            'status' => $result['status'],
            'message' => $result['message'],
            'secret_key' => $result['secret_key'],
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function toggleStatus(Request $request)
    {
        $apiKey = $this->apiKeys->findById($request->input('id'));
        if (! $apiKey || $apiKey->user_id !== auth()->id()) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }

        $result = $this->credentials->toggleStatus($apiKey);
        $result['next'] = 'refresh';

        return response()->json($result);
    }

    /**
     * @return JsonResponse
     */
    public function destroy(Request $request)
    {
        $apiKey = $this->apiKeys->findById($request->input('id'));
        if (! $apiKey || $apiKey->user_id !== auth()->id()) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }

        $result = $this->credentials->delete($apiKey);
        $result['next'] = 'table_refresh';

        return response()->json($result);
    }
}