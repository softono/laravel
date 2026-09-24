<?php

namespace App\Modules\Admin\Device\Controllers;

use App\Helpers\Response;
use App\Modules\Admin\Controllers\Controller;
use App\Modules\User\Services\SessionListService;
use Illuminate\Http\Request;

/** Sessions (devices) of all users, with the ability to sign one out. */
class DeviceController extends Controller
{
    public function __construct(protected SessionListService $sessions)
    {
        parent::__construct();
    }

    public function index()
    {
        return view('modules.admin.device.index');
    }

    public function list(Request $request)
    {
        return response()->json($this->sessions->all($request->all()));
    }

    public function logout(Request $request)
    {
        $request->validate(['id' => ['required', 'string']]);

        $result = $this->sessions->logout($request, auth()->user(), $request->string('id'), anyUser: true);

        if ($result['ok']) {
            $result['data'] = ['next' => 'table_refresh'];
        }

        return Response::sendResult($result);
    }
}
