<?php

namespace App\Modules\Admin\EmailTemplate\Controllers;

use App\Helpers\General;
use App\Helpers\Response;
use App\Modules\Admin\Controllers\Controller;
use App\Modules\Admin\EmailTemplate\Requests\SaveEmailTemplateRequest;
use App\Modules\Admin\EmailTemplate\Services\EmailTemplateListService;
use App\Repositories\EmailTemplateRepository;
use App\Services\EmailTemplateService;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function __construct(
        protected EmailTemplateRepository $templates,
        protected EmailTemplateListService $list,
    ) {
        parent::__construct();
    }

    public function index()
    {
        return view('modules.admin.email-template.index');
    }

    public function list(Request $request)
    {
        return response()->json($this->list->datatable($request->all()));
    }

    public function update(Request $request)
    {
        $model = $this->templates->findById((string) $request->input('id'));

        if (! $model) {
            return redirect()->route('admin/email-template')->with('error', 'No data found');
        }

        return view('modules.admin.email-template.update', ['model' => $model]);
    }

    public function save(SaveEmailTemplateRequest $request)
    {
        $template = $this->templates->findById($request->integer('id'));
        $this->templates->update($template, $request->safe()->except('id'));

        return Response::sendMessage('Email template saved successfully');
    }

    /** The template wrapped in the shared email layout, as recipients see it (placeholders unfilled). */
    public function view(Request $request, EmailTemplateService $renderer)
    {
        $model = $this->templates->findById((string) $request->input('id'));

        abort_unless($model, 404);

        return response($renderer->render($model->key)['body']);
    }

    /** Image uploads from the rich-text editor. */
    public function saveImage(Request $request, General $general)
    {
        $request->validate(['upload' => ['required', $general->fileRules('image')]]);

        $upload = $general->uploadFile($request->file('upload'), 'email');

        if (! $upload['status']) {
            return Response::sendMessage($upload['message'], 0);
        }

        return Response::sendData(['file_name' => $upload['file_name'], 'file_url' => $general->getFileUrl($upload['file_name'], 'email')]);
    }
}
