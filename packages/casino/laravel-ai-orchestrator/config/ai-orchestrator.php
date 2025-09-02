<?php

return [
    /*
    |--------------------------------------------------------------------------
    | AI Orchestrator Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the AI Orchestrator service integration.
    | This package allows your Laravel application to communicate with
    | an AI workflow orchestration service.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Orchestrator Service URL
    |--------------------------------------------------------------------------
    |
    | The base URL of your AI orchestration service. This should point to
    | the service that hosts your Dify workflows and handles execution.
    |
    */
    'orchestrator_url' => env('AI_ORCHESTRATOR_URL', 'http://localhost:8088'),

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for API communication with the orchestrator service.
    |
    */
    'api' => [
        'key' => env('AI_ORCHESTRATOR_API_KEY'),
        'timeout' => env('AI_ORCHESTRATOR_TIMEOUT', 30),
        'retry' => [
            'times' => env('AI_ORCHESTRATOR_RETRY_TIMES', 3),
            'delay' => env('AI_ORCHESTRATOR_RETRY_DELAY', 100), // milliseconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for receiving webhooks from the orchestrator service
    | when workflow executions complete.
    |
    */
    'webhook' => [
        'enabled' => env('AI_ORCHESTRATOR_WEBHOOK_ENABLED', true),
        'secret' => env('AI_ORCHESTRATOR_WEBHOOK_SECRET'),
        'path' => env('AI_ORCHESTRATOR_WEBHOOK_PATH', '/webhooks/ai-orchestrator'),
        'verify_signature' => env('AI_ORCHESTRATOR_WEBHOOK_VERIFY', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration specific to this service instance.
    |
    */
    'service' => [
        'name' => env('AI_ORCHESTRATOR_SERVICE_NAME', env('APP_NAME', 'laravel-app')),
        'callback_url' => env('AI_ORCHESTRATOR_CALLBACK_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configuration for logging AI orchestrator operations.
    |
    */
    'logging' => [
        'enabled' => env('AI_ORCHESTRATOR_LOG_ENABLED', true),
        'channel' => env('AI_ORCHESTRATOR_LOG_CHANNEL', env('LOG_CHANNEL', 'stack')),
        'level' => env('AI_ORCHESTRATOR_LOG_LEVEL', 'info'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for caching workflow discovery and execution results.
    |
    */
    'cache' => [
        'enabled' => env('AI_ORCHESTRATOR_CACHE_ENABLED', true),
        'store' => env('AI_ORCHESTRATOR_CACHE_STORE'),
        'prefix' => env('AI_ORCHESTRATOR_CACHE_PREFIX', 'ai-orchestrator'),
        'ttl' => [
            'workflows' => env('AI_ORCHESTRATOR_CACHE_WORKFLOWS_TTL', 300), // 5 minutes
            'executions' => env('AI_ORCHESTRATOR_CACHE_EXECUTIONS_TTL', 60), // 1 minute
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for package routes (webhooks, etc.).
    |
    */
    'enable_webhook_routes' => env('AI_ORCHESTRATOR_ENABLE_WEBHOOK_ROUTES', false),
];