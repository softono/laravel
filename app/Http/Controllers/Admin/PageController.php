<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PageController extends Controller
{
    /**
     * Display the admin page index view.
     *
     * @return View
     */
    public function index()
    {
        return view('admin/page/index');
    }

    /**
     * Retrieve a list of pages for admin.
     *
     * @return JsonResponse
     */
    public function list(Request $request)
    {
        return response()->json((new Page)->listAdmin($request->all()));
    }

    /**
     * Show the form for updating the specified page.
     *
     * @return View
     */
    public function update(Request $request)
    {
        $model = Page::find($request->input('id'));

        // Redirect if not found
        if (! $model) {
            return redirect()->route('admin.page.index')->withErrors(['error' => 'No data found']);
        }

        return view('admin/page/update', compact('model'));
    }

    /**
     * Save the page details.
     *
     * @return JsonResponse
     */
    public function save(Request $request)
    {
        return response()->json((new Page)->store($request->only(['id', 'title', 'body', 'slug'])));
    }

    /**
     * Handle the file upload for the page.
     *
     * @return JsonResponse|null
     */
    public function saveFile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'upload' => 'required|'.$this->general->fileRules(),
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 0, 'message' => $validator->errors()->first()]);
        }

        $fileName = $this->general->upload($request->file('upload'), 'content');

        return response()->json(['status' => 1, 'fileName' => $fileName, 'url' => $this->general->getFileUrl($fileName, 'content')]);
    }
}
