<?php

namespace App\Modules\Page\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\PageRepository;

/** Public static pages (terms, privacy policy, ...). Only active pages are visible. */
class PageController extends Controller
{
    public function __construct(protected PageRepository $pages)
    {
        parent::__construct();
    }

    public function show(string $slug)
    {
        $page = $this->pages->findBySlug($slug);

        abort_unless($page && $page->status === 'active', 404);

        return view('modules.page.show', ['page' => $page]);
    }
}
