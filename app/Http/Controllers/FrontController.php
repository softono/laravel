<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Services\GeneralService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FrontController extends Controller
{
    /**
     * Display the front index page.
     *
     * @return View
     */
    public function index()
    {
        return view('front.index');
    }

    /**
     * Display a specific page based on the slug.
     *
     * @return View
     *
     * @throws ModelNotFoundException
     */
    public function page(Request $request)
    {
        $page = Page::where('slug', $request->slug)->firstOrFail();

        return view('front.page', compact('page'));
    }

    /**
     * Display the contact page.
     *
     * @return View
     */
    public function contact()
    {
        return view('front.contact');
    }

    /**
     * Process the contact form submission.
     *
     * @return JsonResponse
     */
    public function contactProcess(Request $request)
    {
        return response()->json((new GeneralService)->contactProcess($request->only(['name', 'email', 'subject', 'message'])));
    }
}
