<?php

namespace App\Modules\Admin\Controllers;

use App\Helpers\General;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public $general;

    public function __construct()
    {
        $this->general = new General;
        $this->general->configSettings();
        \View::share('general', $this->general);
    }
}
