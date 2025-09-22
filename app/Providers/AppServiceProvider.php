<?php

namespace App\Providers;

use App\Services\SettingsService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingsService::class);
        $this->app->bind(\App\Services\WebhookService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set dynamic job timeout from settings
        try {
            $settingsService = $this->app->make(SettingsService::class);
            $timeout = $settingsService->getJobTimeout();

            // Set environment variable for Horizon config
            if (!env('QUEUE_JOB_TIMEOUT')) {
                config(['horizon.defaults.supervisor-1.timeout' => $timeout]);
            }
        } catch (\Exception $e) {
            // Fallback to default timeout if settings not available
            config(['horizon.defaults.supervisor-1.timeout' => 14400]);
        }
    }
}
