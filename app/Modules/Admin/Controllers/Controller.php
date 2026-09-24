<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller as BaseController;

/** Base class for every admin controller; shares $general and the app settings with the views. */
abstract class Controller extends BaseController {}
