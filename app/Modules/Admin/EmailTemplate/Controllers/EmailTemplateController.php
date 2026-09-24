<?php

namespace App\Modules\Admin\EmailTemplate\Controllers;

use App\Helpers\Response;
use App\Modules\Admin\Controllers\Controller;
use App\Modules\Admin\EmailTemplate\Requests\SaveEmailTemplateRequest;
use App\Modules\Admin\EmailTemplate\Services\EmailTemplateListService;
use App\Modules\Admin\EmailTemplate\Services\EmailTemplateManagementService;
use App\Repositories\EmailTemplateRepository;
use App\Services\EmailTemplateService;
use Illuminate\Http\Request;

class EmailTemplateController extends Controller
{
    public function __construct(
        protected EmailTemplateRepository $templates,
        protected EmailTemplateListService $list,
        protected EmailTemplateManagementService $service,
    ) {
        parent::__construct();
    }

    public function index()
    {
        return view('modules.admin.email-template.index');
    }

    public function list(Request $request)
    {
        return Response::sendResult($this->list->datatable($request->all()));
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
        return Response::sendResult($this->service->save($request->validated()));
    }

    /** The template wrapped in the shared email layout, as recipients see it (placeholders unfilled). */
    public function view(Request $request, EmailTemplateService $renderer)
    {
        $model = $this->templates->findById((string) $request->input('id'));

        abort_unless($model, 404);

        return response($renderer->render($model->key)['body']);
    }

    /** Image uploads from the rich-text editor. */
    public function saveImage(Request $request)
    {
        $request->validate(['upload' => ['required', $this->general->fileRules('image')]]);

        return Response::sendResult($this->service->uploadImage($request->file('upload')));
    }
}
