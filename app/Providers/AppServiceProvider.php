<?php

namespace App\Providers;

use App\Services\SystemStatusBarService;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(JobProcessed::class, function (JobProcessed $event): void {
            if ($event->connectionName === 'sync') {
                return;
            }

            SystemStatusBarService::incrementQueueSuccessForCurrentMinute();
        });

        View::composer('layouts.app', function ($view): void {
            $view->with('systemStatus', app(SystemStatusBarService::class)->statusBarSnapshot());
        });
    }
}
