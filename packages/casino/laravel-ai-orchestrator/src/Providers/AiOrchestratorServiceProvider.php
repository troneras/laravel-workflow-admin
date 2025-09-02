<?php

namespace Casino\LaravelAiOrchestrator\Providers;

use Casino\LaravelAiOrchestrator\AiOrchestrator;
use Casino\LaravelAiOrchestrator\Commands\TestWorkflowCommand;
use Casino\LaravelAiOrchestrator\Contracts\AiOrchestratorInterface;
use Illuminate\Support\ServiceProvider;

class AiOrchestratorServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Merge configuration
        $this->mergeConfigFrom(__DIR__ . '/../../config/ai-orchestrator.php', 'ai-orchestrator');

        // Bind the interface to implementation
        $this->app->singleton(AiOrchestratorInterface::class, AiOrchestrator::class);
        $this->app->alias(AiOrchestratorInterface::class, 'ai-orchestrator');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish configuration
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/ai-orchestrator.php' => config_path('ai-orchestrator.php'),
            ], 'ai-orchestrator-config');

            // Register commands
            $this->commands([
                TestWorkflowCommand::class,
            ]);
        }

        // Register routes if needed
        if (config('ai-orchestrator.enable_webhook_routes', false)) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/webhooks.php');
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            AiOrchestratorInterface::class,
            'ai-orchestrator',
        ];
    }
}