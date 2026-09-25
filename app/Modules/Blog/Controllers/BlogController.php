<?php

namespace App\Modules\Blog\Controllers;

use App\Http\Controllers\Controller;
use App\Repositories\BlogRepository;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BlogController extends Controller
{
    public const PAGE_SIZES = [12, 24, 48];

    public function __construct(protected BlogRepository $blogs)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $request->validate(['search' => ['nullable', 'string', 'max:100'], 'category' => ['nullable', 'string', 'max:100'], 'limit' => ['nullable', 'integer', Rule::in(self::PAGE_SIZES)]]);

        return view('modules.blog.index', [
            'posts' => $this->blogs->paginatePublic($request->input('search'), $request->input('category'), (int) $request->input('limit', self::PAGE_SIZES[0])),
        ]);
    }

    public function show(string $slug)
    {
        $post = $this->blogs->findActiveBySlug($slug);

        abort_unless($post, 404);

        return view('modules.blog.show', ['post' => $post]);
    }
}
