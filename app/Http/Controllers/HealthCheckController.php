<?php

namespace App\Http\Controllers;

use App\Utils\JsendFormatter;

class HealthCheckController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        return JsendFormatter::success('OK');
    }
}
