<?php

namespace App\Modules\Contact\Controllers;

use App\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Modules\Contact\Requests\ContactRequest;
use App\Modules\Contact\Services\ContactService;

class ContactController extends Controller
{
    public function __construct(protected ContactService $contact)
    {
        parent::__construct();
    }

    public function show()
    {
        return view('modules.contact.index');
    }

    public function submit(ContactRequest $request)
    {
        return Response::sendResult($this->contact->submit($request->validated(), auth()->user()));
    }
}
