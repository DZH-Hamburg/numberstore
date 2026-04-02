<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunReportJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        // Stub: spätere Ausführung (Report-Mail).
    }
}
