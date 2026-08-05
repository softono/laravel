<?php

namespace App\Http\Controllers;

use App\Services\Storage\StorageStatsService;
use Illuminate\View\View;

/**
 * Bucket Admin personal dashboard - see "Bucket Admin Panel" in
 * docs/local/prd.md. Every view here is scoped to the authenticated user.
 */
class SiteController extends Controller
{
    public function __construct(
        protected StorageStatsService $stats,
    ) {
        parent::__construct();
    }

    /**
     * @return View
     */
    public function dashboard()
    {
        $personal = $this->stats->personal(auth()->user());

        return view('site.dashboard', compact('personal'));
    }
}
