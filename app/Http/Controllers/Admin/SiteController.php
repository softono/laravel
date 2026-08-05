<?php

namespace App\Http\Controllers\Admin;

use App\Services\Storage\StorageStatsService;
use Illuminate\View\View;

/**
 * Super Admin dashboard - oversight and statistics only, no bucket/API-key
 * ownership (see "Super Admin Panel" in docs/local/prd.md).
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
        $systemWide = $this->stats->systemWide();
        $perUser = $this->stats->perUserBreakdown();

        return view('admin.site.dashboard', compact('systemWide', 'perUser'));
    }
}
