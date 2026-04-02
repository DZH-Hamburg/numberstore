<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RunScreenshotJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        // Stub: spätere Ausführung (Screenshot).
    }
}
