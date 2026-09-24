<?php

namespace App\Modules\Note\Controllers;

use App\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Modules\Note\Requests\SaveNoteRequest;
use App\Modules\Note\Services\NoteService;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function __construct(protected NoteService $notes)
    {
        parent::__construct();
    }

    public function index()
    {
        return view('modules.note.index');
    }

    public function list(Request $request)
    {
        return Response::sendResult($this->notes->datatable(auth()->user(), $request->all()));
    }

    public function save(SaveNoteRequest $request)
    {
        return Response::sendResult($this->notes->save(auth()->user(), $request->validated()));
    }

    public function delete(Request $request)
    {
        $request->validate(['id' => ['required', 'integer']]);

        return Response::sendResult($this->notes->delete(auth()->user(), $request->integer('id')));
    }
}
