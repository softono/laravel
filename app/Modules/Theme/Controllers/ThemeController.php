<?php

namespace App\Modules\Theme\Controllers;

use App\Helpers\Response;
use App\Http\Controllers\Controller;
use App\Modules\Theme\Services\ThemeService;

class ThemeController extends Controller
{
    public function __construct(protected ThemeService $themes)
    {
        parent::__construct();
    }

    public function index()
    {
        return Response::sendResult($this->themes->list());
    }

    public function show(string $name)
    {
        return Response::sendResult($this->themes->show($name));
    }
}
