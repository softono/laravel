<?php

namespace App\Modules\Admin\User\Controllers;

use App\Constants\UserRole;
use App\Helpers\Response;
use App\Modules\Admin\Controllers\Controller;
use App\Modules\Admin\Services\AccountListService;
use App\Modules\Admin\Services\AccountManagementService;
use App\Modules\Admin\User\Requests\SaveUserRequest;
use App\Modules\Admin\User\Requests\SendMailRequest;
use App\Modules\Admin\User\Services\UserMailService;
use App\Repositories\Auth\UserActivityRepository;
use App\Repositories\Auth\UserRepository;
use App\Repositories\Auth\UserSessionRepository;
use App\Repositories\ContactMessageRepository;
use Illuminate\Http\Request;

/** Manage end-user accounts (role USER). */
class UserController extends Controller
{
    public function __construct(
        protected AccountManagementService $accounts,
        protected AccountListService $list,
        protected UserMailService $mail,
        protected UserRepository $users,
        protected UserSessionRepository $sessions,
        protected UserActivityRepository $activities,
        protected ContactMessageRepository $messages,
    ) {
        parent::__construct();
    }

    public function index()
    {
        return view('modules.admin.user.index');
    }

    public function list(Request $request)
    {
        return response()->json($this->list->datatable(auth()->user(), $request->all(), UserRole::USER, 'admin/user'));
    }

    public function create()
    {
        return view('modules.admin.user.create', ['countries' => config('countries')]);
    }

    public function update(Request $request)
    {
        $model = $this->users->findByIdAndRole((string) $request->input('id'), UserRole::USER);

        if (! $model) {
            return redirect()->route('admin/user')->with('error', 'No data found');
        }

        return view('modules.admin.user.update', ['model' => $model, 'countries' => config('countries')]);
    }

    public function save(SaveUserRequest $request)
    {
        $data = $request->validated();

        $result = $request->filled('id')
            ? $this->accounts->update($request, auth()->user(), UserRole::USER, $request->string('id'), $data)
            : $this->accounts->create($request, auth()->user(), UserRole::USER, $data);

        if ($result['ok']) {
            $result['data'] = ['next' => 'load', 'url' => route('admin/user')];
        }

        return Response::sendResult($result);
    }

    public function view(Request $request)
    {
        $model = $this->users->findByIdAndRole((string) $request->input('id'), UserRole::USER);

        if (! $model) {
            return redirect()->route('admin/user')->with('error', 'No data found');
        }

        return view('modules.admin.user.view', [
            'model' => $model,
            'sessions' => $this->sessions->latestForUser($model->id),
            'activities' => $this->activities->getByUserId($model->id, 10),
            'mails' => $this->messages->forUser($model->id),
        ]);
    }

    public function delete(Request $request)
    {
        $result = $this->accounts->delete($request, auth()->user(), UserRole::USER, (string) $request->input('id'));

        if ($result['ok']) {
            $result['data'] = ['next' => 'table_refresh'];
        }

        return Response::sendResult($result);
    }

    public function changeStatus(Request $request)
    {
        $result = $this->accounts->toggleStatus($request, auth()->user(), UserRole::USER, (string) $request->input('id'));

        if ($result['ok']) {
            $result['data'] = ['next' => 'refresh'];
        }

        return Response::sendResult($result);
    }

    public function sendMail(SendMailRequest $request)
    {
        $recipient = $this->users->findByIdAndRole($request->string('user_id'), UserRole::USER);

        if (! $recipient) {
            return Response::sendMessage('No data found', 0);
        }

        $this->mail->send($recipient, $request->string('subject'), $request->string('message'));

        return Response::sendData(['next' => 'refresh'], 'Email sent successfully');
    }
}
