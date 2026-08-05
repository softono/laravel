<?php

namespace App\Http\Controllers;

use App\Repositories\Storage\ApiUserRepository;
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
        protected ApiUserRepository $apiUsers,
    ) {
        parent::__construct();
    }

    /**
     * @return View
     */
    public function index()
    {
        $apiUsers = $this->apiUsers->listForUser(auth()->id());

        return view('api-key.index', compact('apiUsers'));
    }

    /**
     * @return JsonResponse
     */
    public function store()
    {
        $result = $this->credentials->create(auth()->user());

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
        $apiUser = $this->apiUsers->findById($request->input('id'));
        if (! $apiUser || $apiUser->user_id !== auth()->id()) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }

        $result = $this->credentials->regenerateSecret($apiUser);

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
        $apiUser = $this->apiUsers->findById($request->input('id'));
        if (! $apiUser || $apiUser->user_id !== auth()->id()) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }

        $result = $this->credentials->toggleStatus($apiUser);
        $result['next'] = 'refresh';

        return response()->json($result);
    }

    /**
     * @return JsonResponse
     */
    public function destroy(Request $request)
    {
        $apiUser = $this->apiUsers->findById($request->input('id'));
        if (! $apiUser || $apiUser->user_id !== auth()->id()) {
            return response()->json(['status' => 0, 'message' => 'No data found']);
        }

        $result = $this->credentials->delete($apiUser);
        $result['next'] = 'table_refresh';

        return response()->json($result);
    }
}
