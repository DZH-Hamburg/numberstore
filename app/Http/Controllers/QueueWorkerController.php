<?php

namespace App\Http\Controllers;

use App\Services\SystemStatusBarService;
use Illuminate\View\View;

class QueueWorkerController extends Controller
{
    public function __invoke(SystemStatusBarService $status): View
    {
        return view('system.queue-worker', [
            'queueReport' => $status->queueWorkerReport(),
        ]);
    }
}
